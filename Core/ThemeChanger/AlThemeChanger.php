<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ThemeChanger;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Template\AlTemplateManager;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface;
use RedKiteLabs\ThemeEngineBundle\Core\Theme\AlThemeInterface;

/**
 * AlThemeChanger is deputated to change the website template
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlThemeChanger
{
    /** @var AlTemplateManager */
    protected $templateManager;
    /** @var AlFactoryRepositoryInterface */
    protected $factoryRepository;
    /** @var \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\LanguageRepositoryInterface */
    protected $languagesRepository;
    /** @var \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\PageRepositoryInterface */
    protected $pagesRepository;

    /**
     * Constructor
     *
     * @param AlTemplateManager            $templateManager
     * @param AlFactoryRepositoryInterface $factoryRepository
     */
    public function __construct(AlTemplateManager $templateManager, AlFactoryRepositoryInterface $factoryRepository)
    {
        $this->templateManager = $templateManager;
        $this->factoryRepository = $factoryRepository;
        $this->languagesRepository = $this->factoryRepository->createRepository('Language');
        $this->pagesRepository = $this->factoryRepository->createRepository('Page');
    }

    /**
     * Changes the current theme
     *
     * @param AlThemeInterface $previousTheme
     * @param AlThemeInterface $theme
     * @param string           $path
     * @param array            $templatesMap
     */
    public function change(AlThemeInterface $previousTheme, AlThemeInterface $theme, $path, array $templatesMap)
    {
        $this->saveThemeStructure($previousTheme, $path);
        $this->changeTemplate($theme, $templatesMap);
    }

    /**
     * Changes the website templates with the new ones provided into the $templatesMap
     * array
     *
     * @param  AlThemeInterface $theme
     * @param  array            $templatesMap
     * @throws \Exception
     */
    protected function changeTemplate(AlThemeInterface $theme, array $templatesMap)
    {
        $ignoreRepeatedSlots = false;
        foreach ($this->languagesRepository->activeLanguages() as $language) {
            foreach ($this->pagesRepository->activePages() as $page) {
                $templateName = $page->getTemplateName();
                if ( ! array_key_exists($templateName, $templatesMap)) {
                    continue;
                }

                $page->setTemplateName($templatesMap[$templateName]);
                $page->save();

                $template = $theme->getTemplate($page->getTemplateName());
                $this->templateManager
                    ->refresh($theme->getThemeSlots(), $template);

                $this->templateManager->populate($language->getId(), $page->getId(), $ignoreRepeatedSlots);
                $ignoreRepeatedSlots = true;
            }
        }
    }

    /**
     * Saves the current theme structure into a file
     *
     * @param  AlThemeInterface $theme
     * @param  string           $themeStructureFile
     * @throws \Exception
     */
    protected function saveThemeStructure(AlThemeInterface $theme, $themeStructureFile)
    {
        $templates = array();
        foreach ($this->languagesRepository->activeLanguages() as $language) {
            foreach ($this->pagesRepository->activePages() as $page) {
                $key = $language->getId() . '-' . $page->getId();
                $templates[$key] = $page->getTemplateName();
            }
        }

        $themeName = $theme->getThemeName();
        $currentTheme = array(
            "Theme" => $themeName,
            "Templates" => $templates,
        );

        file_put_contents($themeStructureFile, json_encode($currentTheme));
    }
}
