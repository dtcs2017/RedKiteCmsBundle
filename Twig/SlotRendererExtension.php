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

namespace RedKiteLabs\RedKiteCmsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RedKiteLabs\RedKiteCmsBundle\Core\Deploy\TwigTemplateWriter\TwigTemplateWriter;
use RedKiteLabs\RedKiteCmsBundle\Core\Content\Block\AlBlockManager;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\RuntimeException;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\InvalidArgumentException;

/**
 * Adds the renderSlot function to Twig engine
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class SlotRendererExtension extends \Twig_Extension
{
    private $container;
    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('red_kite_cms.translator');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slotRenderer';
    }

    /**
     * Overrides the base renderSlot method
     */
    public function renderSlot($slotName = null, $extraAttributes = "")
    {
        $this->checkSlotName($slotName);

        try {
            $slotContents = array();
            $pageTree = $this->container->get('red_kite_cms.page_tree');
            $blockManagers = $pageTree->getBlockManagers($slotName);

            foreach ($blockManagers as $blockManager) {
                if (null === $blockManager) {
                    continue;
                }

                $slotContents[] = $this->renderBlock($blockManager, null, false, $extraAttributes);
            }

            if (empty($slotContents) && $pageTree->isCmsMode()) {
                $slotContents[] = sprintf('<div data-editor="enabled" data-block-id="0" data-slot-name="%s" class="al-empty-slot-placeholer">%s</div>', $slotName, $this->translator->translate('twig_extension_empty_slot', array(), 'RedKiteCmsBundle'));
            }

            $content = implode(PHP_EOL, $slotContents);
            $content = TwigTemplateWriter::MarkSlotContents($slotName, $content);

        } catch (\Exception $ex) {
            $content = $this->translator->translate('twig_extension_slot_rendering_error', array('%slot_name%' => $slotName, '%error%' => $ex->getMessage()), 'RedKiteCmsBundle');
        }

        return sprintf('<div class="al_%s">%s</div>', $slotName, $content);
    }

    /**
     * Renders a block
     *
     * @param  AlBlockManager $blockManager
     * @param  string|null    $template
     * @param  bool           $included
     * @param  string         $extraAttributes
     * @param  array          $extraOptions
     * @throws \Exception
     * @return string
     */
    public function renderBlock(AlBlockManager $blockManager, $template = null, $included = false, $extraAttributes = '', array $extraOptions = null)
    {
        try {
            $block = $blockManager->toArray();
            if (empty($block)) {
                return "";
            }

            $templating = $this->container->get('templating');
            $slotName = $block["Block"]["SlotName"];
            $content = $this->blockContentToHtml($block['Content'], $extraOptions);

            if (strpos($content, '<script') !== false) {
                $content = sprintf('<div data-editor="true">%s</div>', $this->translator->translate('twig_extension_script_not_rendered', array(), 'RedKiteCmsBundle'));
            }

            if (null === $template) {
                $template = '_block.html.twig';
            }

            if ( ! $blockManager->getEditorDisabled() && preg_match('/data\-editor="true"/s', $content)) {
                $hideInEditMode = (array_key_exists('HideInEditMode', $block) && $block['HideInEditMode']) ? 'true' : 'false';
                $editorParameters = $blockManager->editorParameters();
                $cmsAttributes = $templating->render('RedKiteCmsBundle:Block:Editor/_editable_block_attributes.html.twig', array(
                    'block_id' => $block['Block']["Id"],
                    'hide_in_edit_mode' => $hideInEditMode,
                    'slot_name' => $slotName,
                    'type' => $block['Block']['Type'],
                    'content' => $content,
                    'edit_inline' => $block['EditInline'],
                    'editor' => $editorParameters,
                    'extra_attributes' => $extraAttributes,
                    'included' => $included,
                ));

                if (preg_match('/data\-encoded\-content=\'(.*?)\'/s', $cmsAttributes, $matches)) {
                    $cmsAttributes = preg_replace('/data\-encoded\-content=\'(.*?)\'/s', 'data-encoded-content=\'' . rawurlencode($matches[1]) . '\'', $cmsAttributes);
                }

                $content = preg_replace('/data\-editor="true"/', $cmsAttributes . ' data-editor="enabled"', $content);
            }

            return $templating->render('RedKiteCmsBundle:Slot:Page/' . $template, array(
                'block_id' => $block['Block']["Id"],
                'slot_name' => $slotName,
                'type' => $block['Block']['Type'],
                'content' => $content,
                'edit_inline' => $block['EditInline'],
            ));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Converts a block's content to html
     *
     * @param  string $content
     * @param  array  $extraOptions
     * @return string
     */
    public function blockContentToHtml($content, array $extraOptions = null)
    {
        $result = $content;
        if (is_array($content)) {
            $result = "";
            if (\array_key_exists('RenderView', $content)) {
                if (null !== $extraOptions) {
                    $content['RenderView']['options'] = array_merge($content['RenderView']['options'], $extraOptions);
                }
                $viewsRenderer = $this->container->get('red_kite_cms.view_renderer');
                $result = $viewsRenderer->render($content['RenderView']);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'renderSlot' => new \Twig_Function_Method($this, 'renderSlot', array(
                'is_safe' => array('html'),
            )),
            'renderBlock' => new \Twig_Function_Method($this, 'renderBlock', array(
                'is_safe' => array('html'),
            )),
            'blockContentToHtml' => new \Twig_Function_Method($this, 'blockContentToHtml', array(
                'is_safe' => array('html'),
            )),
            'renderIncludedBlock' => new \Twig_Function_Method($this, 'renderIncludedBlock', array(
                'is_safe' => array('html'),
            )),
        );
    }

    public function renderIncludedBlock($key, AlBlockManager $parent = null, $type = "Text", $addWhenEmpty = false, $defaultContent = "", $editorExtraAttributes = "", $blockExtraOptions = array())
    {
        $blocksRepository = $this->container->get('red_kite_cms.factory_repository');
        $repository = $blocksRepository->createRepository('Block');
        $blocks = $repository->retrieveContents(null,  null, $key);
        $blockManagerFactory = $this->container->get('red_kite_cms.block_manager_factory');

        $extraOptions = array('parent_slot_name' => $key); //array_merge(array('parent_slot_name' => $key), $extraOptions);
        if (null !== $parent && preg_match('/' . $parent->get()->getId() .  '\-([0-9]+)/', $key, $matches)) {
            $extraOptions['key'] = $matches[1];
        }

        if (count($blocks) > 0) {
            $alBlock = $blocks[0];
            $type = $alBlock->getType();
            $blockManager = $blockManagerFactory->createBlockManager($type);
            if (null !== $blockManager) {
                $blockManager->set($alBlock);
                $blockManager->setBlockExtraOptions($blockExtraOptions);
                if (null !== $parent) {
                    $blockManager->setEditorDisabled($parent->getEditorDisabled());
                }

                return $this->renderBlock($blockManager, '_included_block.html.twig', true, $editorExtraAttributes, $extraOptions);
            }
        // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (true === $addWhenEmpty) {
            if (null === $parent) {
                throw new RuntimeException($this->translator->translate('twig_extension_valid_block_manager_required', array(), 'RedKiteCmsBundle'));
            }

            $blockManager = $blockManagerFactory->createBlockManager($type);
            if (null !== $blockManager) {
                $blockManager->setEditorDisabled($parent->getEditorDisabled());
                $blockManager->setBlockExtraOptions($blockExtraOptions);
                $parentBlock = $parent->get();

                $values = array(
                  "PageId"          => $parentBlock->getPageId(),
                  "LanguageId"      => $parentBlock->getLanguageId(),
                  "SlotName"        => $key,
                  "Type"            => $type,
                  "ContentPosition" => 1,
                );

                if ( ! empty($defaultContent)) {
                    $values["Content"] = $defaultContent;
                }

                $blockManager->save($values);

                return $this->renderBlock($blockManager, '_included_block.html.twig', true, $editorExtraAttributes, $extraOptions);
            }
        // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        return sprintf('<div data-editor="enabled" data-block-id="0" data-slot-name="%s" data-included="1">%s</div>', $key, $this->translator->translate('twig_extension_empty_slot', array(), 'RedKiteCmsBundle'));
    }

    /**
     * Validates the slot name
     *
     * @param  string                                                                        $slotName
     * @throws \RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\InvalidArgumentException
     */
    protected function checkSlotName($slotName)
    {
        if (null === $slotName) {
            throw new InvalidArgumentException("twig_extension_invalid_slot_name");
        }

        if (!is_string($slotName)) {
            throw new InvalidArgumentException("twig_extension_slot_name_must_be_string");
        }
    }
}
