<?php
/**
 * This file is part of the BusinessDropCapBundle and it is distributed
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

namespace RedKiteLabs\RedKiteCmsBundle\Core\Listener\JsonBlock;

use RedKiteLabs\RedKiteCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\Deprecated\RedKiteDeprecatedException;

/**
 * Renders the editor to manipulate a Json item
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @deprecated BaseRenderingEditorListener class has been deprecated since RedKite CMS 1.1.0
 * @codeCoverageIgnore
 */
abstract class RenderingItemEditorListener extends BaseRenderingEditorListener
{

    public function __construct()
    {
        throw new RedKiteDeprecatedException("RenderingItemEditorListener has been deprecated since RedKiteCms 1.1.0");
    }

    /**
     * {@inheritdoc}
     */
    protected function renderEditor(BlockEditorRenderingEvent $event, array $params)
    {
        if (!array_key_exists('formClass', $params)) {
            throw new \InvalidArgumentException(sprintf('The array returned by the "configure" method of the class "%s" method must contain the "formClass" option', get_class($this)));
        }

        if (!class_exists($params['formClass'])) {
            throw new \InvalidArgumentException(sprintf('The form class "%s" defined in "%s" does not exists', $params['formClass'], get_class($this)));
        }

        try {
            $alBlockManager = $event->getBlockManager();
            if ($alBlockManager instanceof $params['blockClass']) {
                $container = $event->getContainer();
                $block = $alBlockManager->get();
                $className = $block->getType();
                $content = json_decode($block->getContent(), true);
                $content = $content[0];
                $content = $this->formatContent($content);
                $content['id'] = 0;

                if (array_key_exists('embeddedClass', $params)) {
                    $embeddedClass = new $params['embeddedClass']();
                    $form = $container->get('form.factory')->create(new $params['formClass'](), $embeddedClass);
                    $form->bind($content);
                } else {
                    $form = $container->get('form.factory')->create(new $params['formClass'](), $content);
                }

                $template = sprintf('%sBundle:Block:%s_item.html.twig', $className, strtolower($className));
                $editor = $container->get('templating')->render($template, array("form" => $form->createView()));
                $event->setEditor($editor);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Override this function to format the content in a different way than the saved one
     *
     * @param  type AlBlock $block
     * @return type
     */
    protected function formatContent($content)
    {
        return $content;
    }
}
