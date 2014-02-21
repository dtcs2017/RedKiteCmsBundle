<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Controller;

use RedKiteLabs\RedKiteCmsBundle\Core\Form\ModelChoiceValues\ChoiceValues;
use RedKiteLabs\ThemeEngineBundle\Core\Rendering\Controller\BaseFrontendController;
use RedKiteLabs\ThemeEngineBundle\Core\Asset\AlAsset;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements the controller to load RedKiteCms
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlCmsController extends BaseFrontendController
{
    protected $kernel = null;
    protected $factoryRepository = null;
    protected $pageRepository = null;
    protected $languageRepository = null;
    protected $configuration = null;

    public function showAction()
    {
        $request = $this->container->get('request');
        $this->kernel = $this->container->get('kernel');
        $pageTree = $this->container->get('red_kite_cms.page_tree');
        $isSecure = (null !== $this->get('security.context')->getToken()) ? true : false;
        $this->factoryRepository = $this->container->get('red_kite_cms.factory_repository');
        $this->languageRepository = $this->factoryRepository->createRepository('Language');
        $this->pageRepository = $this->factoryRepository->createRepository('Page');
        $this->seoRepository = $this->factoryRepository->createRepository('Seo');
        $bootstrapVersion = $this->container->get('red_kite_cms.active_theme')->getThemeBootstrapVersion();

        $params = array(
            'template' => 'RedKiteCmsBundle:Cms:Welcome/welcome.html.twig',
            'templateStylesheets' => null,
            'templateJavascripts' => null,
            'available_blocks' => null,
            'internal_stylesheets' => null,
            'internal_javascripts' => null,
            'skin_path' => $this->getSkin(),
            'is_secure' => $isSecure,
            'page' => 0,
            'language' => 0,
            'available_languages' => $this->container->getParameter('red_kite_cms.available_languages'),
            'frontController' => $this->getFrontcontroller($request),
        );

        if (null !== $pageTree) {
            $pageId = 0;
            $languageId = 0;
            $pageName = '';
            $languageName = '';
            $page = $pageTree->getAlPage();
            $language = $pageTree->getAlLanguage();
            if (null !== $page) {
                $pageId =  $page->getId();
                $pageName = $page->getPageName();
            }

            if (null !== $language) {
                $languageId =  $language->getId();
                $languageName = $language->getLanguageName();
            }

            $template = $this->findTemplate($pageTree);
            $params = array_merge($params, array(
                    'metatitle' => $pageTree->getMetaTitle(),
                    'metadescription' => $pageTree->getMetaDescription(),
                    'metakeywords' => $pageTree->getMetaKeywords(),
                    'internal_stylesheets' => $pageTree->getInternalStylesheets(),
                    'internal_javascripts' => $pageTree->getInternalJavascripts(),
                    'template' => $template,
                    'pages' => ChoiceValues::getPages($this->pageRepository),
                    'languages' => ChoiceValues::getLanguages($this->languageRepository),
                    'permalinks' => ChoiceValues::getPermalinks($this->seoRepository, $languageId),
                    'page' => $pageId,
                    'language' => $languageId,
                    'page_name' => $pageName,
                    'language_name' => $languageName,
                    'base_template' => $this->container->getParameter('red_kite_labs_theme_engine.base_template'),
                    'templateStylesheets' => $pageTree->getExternalStylesheets(),
                    'templateJavascripts' => $this->fixAssets($pageTree->getExternalJavascripts()),
                    'available_blocks' => $this->container->get('red_kite_cms.block_manager_factory')->getBlocks(),
                )
            );
        } else {
            $configuration = $this->container->get('red_kite_cms.configuration');
            $cmsLanguage = $configuration->read('language');
            $message = $this->container->get('translator')->trans(
                'cms_controller_page_not_exists_for_given_language',
                array(
                    '%page%' => $request->get('page'),
                    '%language%' => $request->get('_locale')
                ),
                'RedKiteCmsBundle',
                $cmsLanguage
            );
            $this->container->get('session')->getFlashBag()->add('notice', $message);
        }

        $response = $this->render(sprintf('RedKiteCmsBundle:Bootstrap:%s/Template/Cms/template.html.twig', $bootstrapVersion), $params);

        return $this->dispatchEvents($request, $response);
    }

    /**
     * Overrides the base method to replace the permalink when it is used instad
     * of the page name
     *
     * @param type $request
     */
    protected function dispatchCurrentPageEvent(Request $request)
    {
        $pageName = $request->get('page');
        $seo = $this->seoRepository->fromPermalink($pageName);
        if (null !== $seo) {
            $page = $this->pageRepository->fromPk($seo->getPageId());
            $pageName = $page->getPageName();
        }

        $eventName = sprintf('page_renderer.before_%s_rendering', $pageName);
        $this->dispatcher->dispatch($eventName, $this->event);
    }

    protected function findTemplate($pageTree)
    {
        $templateTwig = 'RedKiteCmsBundle:Cms:Welcome/welcome.html.twig';
        if (null !== $template = $pageTree->getTemplate()) {
            $themeName = $template->getThemeName();
            $templateName = $template->getTemplateName();

            $asset = new AlAsset($this->kernel, $themeName);
            $themeFolder = $asset->getRealPath();
            if (false === $themeFolder || !is_file($themeFolder .'/Resources/views/Theme/' . $templateName . '.html.twig')) {
                $configuration = $this->container->get('red_kite_cms.configuration');
                $cmsLanguage = $configuration->read('language');
                $message = $this->container->get('translator')->trans(
                    'The template assigned to this page does not exist. This happens when you change a theme with a different number of templates from the active one. To fix this issue you shoud activate the previous theme again and change the pages which cannot be rendered by this theme',
                    array(),
                    'RedKiteCmsBundle',
                    $cmsLanguage
                );

                $this->container->get('session')->getFlashBag()->add('notice', $message);

                return $templateTwig;
            }

            if ($themeName != "" && $templateName != "") {
                $this->kernelPath = $this->container->getParameter('kernel.root_dir');
                $templateTwig = (is_file(sprintf('%s/Resources/views/%s/%s.html.twig', $this->kernelPath, $themeName, $templateName))) ? sprintf('::%s/%s.html.twig', $themeName, $templateName) : sprintf('%s:Theme:%s.html.twig', $themeName, $templateName);
            }
        }

        return $templateTwig;
    }

    /**
     * Workaround due to static assetic javascripts/stylesheets declaration
     */
    protected function fixAssets(array $assets)
    {
        $ignore = array('jquery-last.min.js',
                        'jquery-ui.min.js',
                        'jquery.easing-1.3.js',
                        'jquery.metadata.js',
                        'jquery.ui.position.js',);
        foreach ($assets as $key => $asset) {
            if (in_array(basename($asset), $ignore)) {
                unset($assets[$key]);
            }
        }

        return $assets;
    }

    protected function getSkin()
    {
        $asset = new AlAsset($this->kernel, '@RedKiteCmsBundle');

        return $asset->getAbsolutePath() . '/css/skins/' . $this->container->getParameter('red_kite_cms.skin');
    }

    protected function getFrontcontroller(Request $request = null)
    {
        if (null === $request) {
            $request = $this->container->get('request');
        }

        return $request->getBaseUrl() . '/';
    }
}
