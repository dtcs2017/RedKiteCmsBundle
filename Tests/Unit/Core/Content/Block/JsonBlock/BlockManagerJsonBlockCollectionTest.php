<?php
/*
 * This file is part of the BootstrapThumbnailBlockBundle and it is distributed
 * under the MIT LICENSE. To use this application you must leave intact this copyright 
 * notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 * 
 * @license    MIT LICENSE
 * 
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\JsonBlock;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\Base\BlockManagerContainerBase;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block\JsonBlock\BlockManagerJsonBlockCollection;

class BlockManagerJsonBlockCollectionTester extends BlockManagerJsonBlockCollection
{
    public function getDefaultValue()
    {
        return array();
    }
    
    public function manageCollectionTester($values)
    {
        return $this->manageCollection($values);
    }
}

/**
 * BlockManagerJsonBlockCollectionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerJsonBlockCollectionTest extends BlockManagerContainerBase
{  
    protected function setUp()
    {
        parent::setUp();
        
        $this->blocksRepository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\BlockRepositoryPropel');
    }
    
    public function testManageJsonCollection()
    {
        $value = '
        {
            "0" : {
                "type": "BootstrapThumbnailBlock"
            },
            "1" : {
                "type": "BootstrapThumbnailBlock"
            }
        }';
        
        $values = array(
            array(
                'ToDelete' => '0',
            ),
            array(
                'ToDelete' => '0',
            ),
        );
        
        $repository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Factory\FactoryRepositoryInterface');
        $this->container->expects($this->at(1))
                      ->method('get')
                      ->will($this->returnValue($repository));
                
        $blockManager = new BlockManagerJsonBlockCollectionTester($this->container, $this->validator);
        if (array_key_exists("Content", $values)) {
            $block = $this->initBlock($value);        
            $blockManager->set($block);            
        }
        $result = $blockManager->manageCollectionTester($values);
        
        $this->assertEquals($values, $result);
    }
    
    public function testItemIdAddedToEndOfCollectionWhenItemParamIsNotSpecified()
    {
        $values = array(
            'Content' => '{"operation": "add", "value": { "type": "TestBlock" }}',
        );
        
        $expectedResult = array(
            'Content' => '[{"type":"LinkBlock"},{"type":"BootstrapNavbarBlock"},{"type":"LinkBlock"},{"type":"TestBlock"}]',
        );
                
        $value = '
        {
            "0" : {
                "type": "LinkBlock"
            },
            "1" : {
                "type": "BootstrapNavbarBlock"
            },
            "2" : {
                "type": "LinkBlock"
            }
        }';
        
        $this->blocksRepository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\BlockRepositoryPropel');
        $repository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Factory\FactoryRepositoryInterface');
        $repository->expects($this->any())
              ->method('createRepository')
              ->with('Block')
              ->will($this->returnValue($this->blocksRepository))
        ;
        
        $this->container->expects($this->at(1))
                      ->method('get')
                      ->will($this->returnValue($repository));
        
        $block = $this->setUpBaseBlock($value, $this->initBlockSimple('nav-menu')); 
        $blockManager = new BlockManagerJsonBlockCollectionTester($this->container, $this->validator);
        $blockManager->set($block);
        $result = $blockManager->manageCollectionTester($values);
        
        $this->assertEquals($expectedResult, $result);
    }
    
    /**
     * @dataProvider addItemProvider
     */
    public function testAddItem($blockValue, $values, $blocks, $childrenBlocks, $expectedResult)
    {
        $repository = $this->setUpRepository($blocks, $expectedResult);        
        $this->retrieveContentsBySlotName($childrenBlocks);
        $this->container->expects($this->at(1))
                      ->method('get')
                      ->will($this->returnValue($repository));
        
        $block = $this->setUpBaseBlock($blockValue); 
        $blockManager = new BlockManagerJsonBlockCollectionTester($this->container, $this->validator);
        $blockManager->set($block);
        $result = $blockManager->manageCollectionTester($values);
        
        $this->assertEquals($expectedResult, $result);
    }
    
    /**
     * @dataProvider deleteItemProvider
     */
    public function testDeleteItem($blockValue, $values, $blocks, $childrenBlocks, $deletingBlocks, $expectedResult)
    {     
        $repository = $this->setUpRepository($blocks, $expectedResult); 
        $this->deleteBlocks($deletingBlocks);           
        
        $at = 1;
        foreach($deletingBlocks as $block) {
            $this->blocksRepository->expects($this->at($at))
                  ->method('retrieveContentsBySlotName')
                  ->will($this->returnValue($block))
            ;
            $at += 1;
        }
        $this->retrieveContentsBySlotName($childrenBlocks, $at);
        
        $this->container->expects($this->at(1))
                      ->method('get')
                      ->will($this->returnValue($repository));
        
        $block = $this->setUpBaseBlock($blockValue); 
        $blockManager = new BlockManagerJsonBlockCollectionTester($this->container, $this->validator);
        $blockManager->set($block);
        $result = $blockManager->manageCollectionTester($values);
        
        $this->assertEquals($expectedResult, $result);
    }
    
    public function addItemProvider()
    {
        return array(
            array(
                '[{"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "add", "item": "-1", "value": { "type": "TestBlock" }}',
                ),
                array(
                    $this->initBlock('2-0', '2-1', null, true, 2),  
                ),
                array(
                    array(),
                ),
                array(
                    'Content' => '[{"type":"TestBlock"},{"type":"AccordionBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "add", "item": "-1", "value": { "type": "TestBlock" }}',
                ),
                array(
                    $this->initBlock('2-0', '2-1', null, true, 2),  
                ),
                array(
                    array(
                        $this->initBlock('2-0-0', '2-1-0', null, true, 3),
                        $this->initBlock('2-0-1', '2-1-1', null, true, 4)
                    )                    
                ),
                array(
                    'Content' => '[{"type":"TestBlock"},{"type":"AccordionBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}, {"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "add", "item": "-1", "value": { "type": "TestBlock" }}',
                ),
                array(
                    $this->initBlock('2-0', '2-1', null, true, 2),  
                    $this->initBlock('2-1', '2-2', null, true, 5), 
                ),
                array(
                    array(
                        $this->initBlock('2-0-0', '2-1-0', null, true, 3),
                        $this->initBlock('2-0-1', '2-1-1', null, true, 4)
                    ),
                    array(
                        $this->initBlock('5-1-0', '5-2-0', null, true, 6),
                        $this->initBlock('5-1-1', '5-2-1', null, true, 7)
                    ) 
                ),
                array(
                    'Content' => '[{"type":"TestBlock"},{"type":"AccordionBlock"},{"type":"AccordionBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}, {"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "add", "item": "0", "value": { "type": "TestBlock" }}',
                ),
                array(
                    $this->initBlock('2-0', '2-0', null, true, 2),  
                    $this->initBlock('2-1', '2-2', null, true, 5), 
                ),
                array(
                    array(
                        $this->initBlock('2-0-0', '2-0-0', null, true, 3),
                        $this->initBlock('2-0-1', '2-0-1', null, true, 4)
                    ),
                    array(
                        $this->initBlock('5-1-0', '5-2-0', null, true, 6),
                        $this->initBlock('5-1-1', '5-2-1', null, true, 7)
                    ) 
                ),
                array(
                    'Content' => '[{"type":"AccordionBlock"},{"type":"TestBlock"},{"type":"AccordionBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}, {"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "add", "item": "1", "value": { "type": "TestBlock" }}',
                ),
                array(
                    $this->initBlock('2-0', '2-0', null, true, 2),  
                    $this->initBlock('2-1', '2-2', null, true, 5), 
                ),
                array(
                ),
                array(
                    'Content' => '[{"type":"AccordionBlock"},{"type":"AccordionBlock"},{"type":"TestBlock"}]',
                ),
            ),
        );
    }
    
    public function deleteItemProvider()
    {
        return array(
            array(
                '[{"type":"AccordionBlock"}, {"type":"TestBlock"}]',
                array(
                    'Content' => '{"operation": "remove", "item": "1"}',
                ),
                array(
                    $this->initBlock('2-0', '2-0', null, true, 2),  
                    $this->initBlock('2-1', '2-2', null, true, 5), 
                ),
                array(
                ),
                array(
                    $this->initBlock('5-1-0', '5-1-0', null, true, 6),
                    $this->initBlock('5-1-1', '5-1-1', null, true, 7)
                ) ,
                array(
                    'Content' => '[{"type":"AccordionBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}, {"type":"TestBlock"}]',
                array(
                    'Content' => '{"operation": "remove", "item": "0"}',
                ),
                array(
                    $this->initBlock('2-0', '2-0', null, true, 2),  
                    $this->initBlock('2-1', '2-0', null, true, 5), 
                ),
                array(                    
                    $this->initBlock('5-1-0', '5-0-0', null, true, 6),
                    $this->initBlock('5-1-1', '5-0-1', null, true, 7)
                ),
                array(
                    $this->initBlock('2-0-0', '2-0-0', null, true, 3),
                    $this->initBlock('2-0-1', '2-0-1', null, true, 4)
                ) ,
                array(
                    'Content' => '[{"type":"TestBlock"}]',
                ),
            ),
            array(
                '[{"type":"AccordionBlock"}, {"type":"TestBlock"}, {"type":"AccordionBlock"}]',
                array(
                    'Content' => '{"operation": "remove", "item": "1"}',
                ),
                array(
                    $this->initBlock('2-0', '2-0', null, true, 2),  
                    $this->initBlock('2-1', '2-0', null, true, 5),   
                    $this->initBlock('2-2', '2-1', null, true, 8), 
                ),
                array(                    
                    $this->initBlock('8-2-0', '8-1-0', null, true, 6),
                    $this->initBlock('8-2-1', '8-1-1', null, true, 7)
                ),
                array(
                   $this->initBlock('2-1-0', '2-1-0', null, true, 3),
                   $this->initBlock('2-1-1', '2-1-1', null, true, 4)
                ) ,
                array(
                    'Content' => '[{"type":"AccordionBlock"},{"type":"AccordionBlock"}]',
                ),
            ),
        );
    }
    
    protected function initBlockSimple($slotName)
    {
        $block = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Block');
        
        $block->expects($this->any())
              ->method('getSlotName')
              ->will($this->returnValue($slotName))
        ;
        
        $block->expects($this->never())
                ->method('setSlotName')
          ;
        
        $block->expects($this->never())
                ->method('save')
          ;

        return $block;
    }
    
    protected function initBlock($slotName, $newSlotName = null, $toDetete = null, $result = null, $id = 2)
    {
        $block = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Block');
        
        $block->expects($this->any())
              ->method('getId')
              ->will($this->returnValue($id))
        ;
        
        $block->expects($this->any())
              ->method('getSlotName')
              ->will($this->returnValue($slotName))
        ;
        
        if (null !== $newSlotName) {
            $block->expects($this->once())
                  ->method('setSlotName')
                  ->with($newSlotName)
            ;
        }
        
        if (null !== $toDetete) {
            $block->expects($this->once())
                  ->method('setToDelete')
            ;
        }
        
        if (null !== $result) {
            $block->expects($this->once())
                  ->method('save')
                  ->will($this->returnValue($result))
            ;
        }

        return $block;
    }
    
    
    private function setUpBaseBlock($value, $block = null)
    {
        if (null === $block) { 
            $block = $this->initBlock('nav-menu');
        
            $block->expects($this->once())
                      ->method('getId')
                      ->will($this->returnValue(2));
        }
        
        $block->expects($this->once())
                  ->method('getContent')
                  ->will($this->returnValue($value));
                  
        return $block;
    }
    
    private function setUpRepository($blocks, $expectedResult)
    {
        $this->blocksRepository->expects($this->at(0))
              ->method('retrieveContentsBySlotName')
              ->will($this->returnValue($blocks))
        ;
        
        $this->blocksRepository->expects($this->once())
              ->method('startTransaction')
        ;
        
        if (is_array($expectedResult)) {
            $this->blocksRepository->expects($this->once())
                  ->method('commit')
            ;
        }
        
        if (is_bool($expectedResult)) {
            $this->blocksRepository->expects($this->once())
                  ->method('rollback')
            ;
        }

        $repository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Factory\FactoryRepositoryInterface');
        $repository->expects($this->any())
              ->method('createRepository')
              ->with('Block')
              ->will($this->returnValue($this->blocksRepository))
        ;
        
        return $repository;
    }
    
    private function retrieveContentsBySlotName($blocks, $startIndex = 2)
    {
        $at = $startIndex;
        foreach($blocks as $block) {
            $this->blocksRepository->expects($this->at($at))
                  ->method('retrieveContentsBySlotName')
                  ->will($this->returnValue($block))
            ;
            $at++;
        }
    }
    
    private function deleteBlocks($blocks, $startIndex = 2)
    {
        $at = $startIndex;
        foreach($blocks as $block) {
            $block->expects($this->once())
                  ->method('setToDelete')
                  ->with(1)
                  ->will($this->returnValue(true))
            ;
            $at++;
        }
    }
}