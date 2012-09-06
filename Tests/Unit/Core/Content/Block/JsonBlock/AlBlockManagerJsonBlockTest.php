<?php
/*
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Unit\Core\Content\Block\JsonBlock;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\AlBlockManagerJsonBlock;

/**
 * AlBlockManagerJsonBlockTest
 *
 * @author AlphaLemon <webmaster@alphalemon.com>
 */
class AlBlockManagerJsonBlockTest extends TestCase
{
    private $dispatcher;
    private $blockManager;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->validator = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Validator\AlParametersValidatorPageManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->blockRepository = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->factoryRepository = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');
        $this->factoryRepository->expects($this->any())
            ->method('createRepository')
            ->will($this->returnValue($this->blockRepository));

        $this->blockManager = new AlBlockManagerJsonBlockTester($this->dispatcher, $this->factoryRepository, $this->validator);
    }

    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidFormConfigurationException
     */
    public function testAnExceptionIsThrownWhenTheFormHasAWrongName()
    {
        $block = $this->initBlock();

        $value ="wrong_form_name[id]=0&wrong_form_name[title]=Home&wrong_form_name[subtitle]=Welcome!&wrong_form_name[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->blockManager->set($block);
        $result = $this->blockManager->save($params);
    }

    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidFormConfigurationException
     */
    public function testAnExceptionIsThrownWhenTheFormHasNotAnIdFieled()
    {
        $block = $this->initBlock();

        $value ="al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->blockManager->set($block);
        $result = $this->blockManager->save($params);
    }

    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidJsonFormatException
     */
    public function testAnExceptionIsThrownWhenTheSavedJsonContentIsNotDecodable()
    {
        $htmlContent = '{
            "0" : {
                "title" : "Home",
                "subtitle" : "Welcome!",
                "link" : "#"
            },
        }';
        $block = $this->initBlock(2, $htmlContent);

        $value ="al_json_block[id]=0&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->blockManager->set($block);
        $this->blockManager->save($params);
    }

    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidItemException
     */
    public function testAnExceptionIsThrownWhenEditingAndTheContentDoesNotContainTheRequestedItem()
    {
        $block = $this->initBlock();

        $value ="al_json_block[id]=1&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->blockManager->set($block);
        $this->blockManager->save($params);
    }

    public function testJsonBlockHasBeenAdded()
    {
        $block = $this->initBlock();
        $value ="al_json_block[id]=&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->doSave($block, $params);
    }

    public function testJsonBlockHasBeenEdited()
    {
        $block = $this->initBlock();
        $value ="al_json_block[id]=0&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('HtmlContent' => $value);
        $this->doSave($block, $params);
    }

    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidItemException
     */
    public function testAnExceptionIsThrownWhenDeletingAndTheContentDoesNotContainTheRequestedItem()
    {
        $block = $this->initBlock();

        $params = array('RemoveItem' => '1');
        $this->blockManager->set($block);
        $this->blockManager->save($params);
    }

    public function testJsonBlockHasBeenDeleted()
    {
        $block = $this->initBlock();
        $value ="al_json_block[id]=0&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('RemoveItem' => '0');
        $this->doSave($block, $params);
    }

    private function initBlock($id = null, $htmlContent = null)
    {
        if (null === $id) $id = 2;
        if (null === $htmlContent) $htmlContent = '{
            "0" : {
                "title" : "Home",
                "subtitle" : "Welcome!",
                "link" : "#"
            }
        }';

        $block = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlBlock');
        $block->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($id));

        $block->expects($this->any())
                ->method('getHtmlContent')
                ->will($this->returnValue($htmlContent));

        return $block;
    }

    private function doSave($block, array $params)
    {
        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->blockRepository->expects($this->once())
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('commit');

        $this->blockRepository->expects($this->never())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
                ->method('save')
                ->will($this->returnValue(true));

         $this->blockRepository->expects($this->once())
                ->method('setRepositoryObject')
                ->with($block);

        $this->blockManager->set($block);
        $result = $this->blockManager->save($params);
        $this->assertEquals(true, $result);
    }
}

class AlBlockManagerJsonBlockTester extends AlBlockManagerJsonBlock
{
    public function getDefaultValue()
    {
        $defaultContent =
        '{
            "0" : {
                "title" : "Home",
                "subtitle" : "Welcome!",
                "link" : "#"
            }
        }';

        return array("HtmlContent" => $defaultContent);
    }
}
