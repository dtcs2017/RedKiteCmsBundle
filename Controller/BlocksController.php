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

use Symfony\Component\HttpFoundation\Response;
use RedKiteLabs\RedKiteCmsBundle\Core\Event\Actions\BlockEvents;
use RedKiteLabs\RedKiteCmsBundle\Core\Event\Actions\Block;
use Symfony\Component\HttpFoundation\Request;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\InvalidOperationException;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\RuntimeException;

/**
 * Implements the actions to manage the blocks on a slot's page
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlocksController extends Base\BaseController
{
    public function addBlockAction(Request $request)
    {
        $this->areValidAttributes($request, $this->container->get('red_kite_cms.factory_repository'));

        $slotName = $request->get('slotName');
        $blockRepository = $this->createRepository('Block');

        if (null !== $request->get('included') && count($blockRepository->retrieveContentsBySlotName($slotName)) > 0 && filter_var($request->get('included'), FILTER_VALIDATE_BOOLEAN)) {
            throw new InvalidOperationException('blocks_controller_included_blocks_accept_only_a_block');
        }

        $contentType = ($request->get('contentType') != null) ? $request->get('contentType') : 'Text';
        $slotManager = $this->fetchSlotManager($request, false);
        if (null !== $slotManager) {
            $options = array(
                "idLanguage" => $request->get('languageId'),
                "idPage" => $request->get('pageId'),
                "type" => $contentType,
                "referenceBlockId" => $request->get('idBlock'),
                "insertDirection" => $request->get('insertDirection'),
            );
            $res = $slotManager->addBlock($options);
            if (! $res) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('blocks_controller_block_not_added_due_to_unespected_exception');
                // @codeCoverageIgnoreEnd
            }

            $template = 'RedKiteCmsBundle:Slot:Render/_block.html.twig';
            $blockManager = $slotManager->lastAdded();
        } else {
            if ( ! $request->get('included')) {
                throw new RuntimeException('blocks_controller_invalid_or_empty_slot');
            }
            $template = 'RedKiteCmsBundle:Slot:Render/_included_block.html.twig';

            $blockManagerFactory = $this->container->get('red_kite_cms.block_manager_factory');
            $blockManager = $blockManagerFactory->createBlockManager($contentType);

            $values = array(
                "PageId"          => $request->get('pageId'),
                "LanguageId"      => $request->get('languageId'),
                "SlotName"        => $slotName,
                "Type"            => $contentType,
                "ContentPosition" => 1,
                'CreatedAt'       => date("Y-m-d H:i:s")
            );
            $blockManager->save($values);
        }

        $idBlock = 0;
        if (null !== $request->get('idBlock')) {
            $idBlock = $request->get('idBlock');
        }

        $values = array(
            array(
                "key" => "message",
                "value" => $this->translate('blocks_controller_block_added')
            ),
            array(
                "key" => "add-block",
                "insertAfter" => "block_" . $idBlock,
                "blockId" => "block_" . $blockManager->get()->getId(),
                "slotName" => $blockManager->get()->getSlotName(),
                "value" => $this->renderView(
                    $template,
                    array("blockManager" => $blockManager, 'add' => true)
                )
            )
        );

        return $this->buildJSonResponse($values);
    }

    public function editBlockAction(Request $request)
    {
        $factoryRepository = $this->container->get('red_kite_cms.factory_repository');
        $this->areValidAttributes($request, $factoryRepository);

        $slotManager = $this->fetchSlotManager($request);

        $value = urldecode($request->get('value'));
        $values = array($request->get('key') => $value);

        // @codeCoverageIgnoreStart
        if (null !== $request->get('options') && is_array($request->get('options'))) {
            $values = array_merge($values, $request->get('options'));
        }
        // @codeCoverageIgnoreEnd

        $result = $slotManager->editBlock($request->get('idBlock'), $values);
        // @codeCoverageIgnoreStart
        if (false === $result) {
            throw new RuntimeException('blocks_controller_block_editing_error');
        }
        // @codeCoverageIgnoreEnd

        if (null === $result) {
            throw new RuntimeException('blocks_controller_nothing_changed_with_these_values');
        }

        $response = null;
        $dispatcher = $this->container->get('event_dispatcher');
        if (null !== $dispatcher) {
            $event = new Block\BlockEditedEvent($request, $slotManager->lastEdited());
            $dispatcher->dispatch(BlockEvents::BLOCK_EDITED, $event);
            $response = $event->getResponse();
            $blockManager = $event->getBlockManager();
        }

        if (null === $response) {
            $template = 'RedKiteCmsBundle:Slot:Render/_block.html.twig';
            if ($request->get('included')) {
                $template = 'RedKiteCmsBundle:Slot:Render/_included_block.html.twig';
            }

            $blockOptions = array();
            if ($request->get('parent')) {
                $blockManagerFactory = $this->container->get('red_kite_cms.block_manager_factory');
                $parentBlockManager = $blockManagerFactory->createBlockManager($request->get('parent'));
                $blockOptions = $parentBlockManager->blockExtraOptions();
            }

            $values = array(
                array("key" => "message", "value" => $this->translate("blocks_controller_block_edited")),
                array("key" => "edit-block",
                      "blockName" => "block_" . $blockManager->get()->getId(),
                      "value" => $this->renderView($template, array("blockManager" => $blockManager, 'item' => $request->get('item'), 'options' => $blockOptions)),
                ),
            );

            $response = $this->buildJSonResponse($values);
        }

        return $response;
    }

    public function deleteBlockAction(Request $request)
    {
        $factoryRepository = $this->container->get('red_kite_cms.factory_repository');
        $this->areValidAttributes($request, $factoryRepository);

        $slotManager = $this->fetchSlotManager($request);
        $res = $slotManager->deleteBlock($request->get('idBlock'));
        if (null !== $res) {
            $message = 'blocks_controller_block_removed';
            // @codeCoverageIgnoreStart
            if (!$res) {
                $message = 'blocks_controller_block_not_removed';
            }
            // @codeCoverageIgnoreEnd

            $values = array(
                array("key" => "message", "value" => $this->translate($message))
            );

            if ($slotManager->getBlockManagersCollection()->count() > 0) {
                $values[] = array(
                    "key" => "remove-block",
                    "blockName" => "block_" . $request->get('idBlock')
                );

                return $this->buildJSonResponse($values);
            }

            $values[] = array(
                "key" => "redraw-slot",
                "slotName" => $request->get('slotName'),
                "blockId" => 'block_' . $request->get('idBlock'),
                "value" => $this->renderView('RedKiteCmsBundle:Slot:Render/_slot.html.twig', array("slotName" => $request->get('slotName'), "included" => filter_var($request->get('included'), FILTER_VALIDATE_BOOLEAN)))
            );

            return $this->buildJSonResponse($values);
        }

        throw new RuntimeException('blocks_controller_block_does_not_exists');
    }

    protected function buildJSonResponse($values)
    {
        $response = new Response(json_encode($values));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function areValidAttributes(Request $request, $factoryRepository)
    {
        $dataManager = new \RedKiteLabs\RedKiteCmsBundle\Core\PageTree\DataManager\DataManager($factoryRepository);
        $options = array(
            "pageName" => "",
            "languageName" => "",
            "pageId" => (int) $request->get('pageId'),
            "languageId" => (int) $request->get('languageId'),
            "permalink" => "",
        );

        $dataManager->fromOptions($options);
        if (null === $dataManager->getPage() || null === $dataManager->getLanguage()) {
            throw new RuntimeException("blocks_controller_page_does_not_exists");
        }
    }

    /**
     * @param  Request                                                       $request
     * @param  bool                                                          $throwExceptionWhenNull
     * @return \RedKiteLabs\RedKiteCmsBundle\Core\Content\Slot\AlSlotManager
     * @throws RuntimeException
     */
    private function fetchSlotManager(Request $request, $throwExceptionWhenNull = true)
    {
        $slotManager = $this->container->get('red_kite_cms.template_manager')->getSlotManager($request->get('slotName'));
        if ($throwExceptionWhenNull && null === $slotManager) {
            throw new RuntimeException("blocks_controller_invalid_or_empty_slot");
        }

        return $slotManager;
    }
}
