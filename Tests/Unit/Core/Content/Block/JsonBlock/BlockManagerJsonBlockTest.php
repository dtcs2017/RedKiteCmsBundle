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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\JsonBlock;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block\JsonBlock\AlBlockManagerJsonBlock;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\Base\AlBlockManagerContainerBase;

/**
 * AlBlockManagerJsonBlockTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlBlockManagerJsonBlockTest extends AlBlockManagerContainerBase
{
    protected $blockManager;

    protected function setUp()
    {
        parent::setUp();

        $this->validator = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Validator\AlParametersValidatorPageManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->blockRepository = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->factoryRepository = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');
        $this->factoryRepository->expects($this->any())
            ->method('createRepository')
            ->will($this->returnValue($this->blockRepository));

        $this->blockManager = new AlBlockManagerJsonBlockTester($this->eventsHandler, $this->factoryRepository, $this->validator);
    }

    /**
     * @expectedException \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidFormConfigurationException
     */
    public function testAnExceptionIsThrownWhenTheFormHasAWrongName()
    {
        $block = $this->initBlock();

        $value ="wrong_form_name[id]=0&wrong_form_name[title]=Home&wrong_form_name[subtitle]=Welcome!&wrong_form_name[link]=my-link";
        $params = array('Content' => $value);
        $this->blockManager->set($block);
        $this->blockManager->save($params);
    }
    
    /**
     * @expectedException \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidJsonFormatException
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
        $params = array('Content' => $value);
        $this->blockManager->set($block);
        $this->blockManager->save($params);
    }

    public function testJsonBlockHasBeenAdded()
    {
        $block = $this->initBlock();
        $value ="al_json_block[id]=&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('Content' => $value);
        $this->doSave($block, $params);
    }

    public function testJsonBlockHasBeenEdited()
    {
        $block = $this->initBlock();
        $value ="al_json_block[id]=0&al_json_block[title]=Home&al_json_block[subtitle]=Welcome!&al_json_block[link]=my-link";
        $params = array('Content' => $value);
        $this->doSave($block, $params);
    }
    
    public function testJsonBlockHasBeenEditedWithEmptyValue()
    {
        $block = $this->initBlock();
        $value = '';
        $params = array('Content' => $value);
        $this->doSave($block, $params);
    }

    /**
     * @expectedException \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block\JsonBlock\Exception\InvalidItemException
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

        $block = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlBlock');
        $block->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($id));

        $block->expects($this->any())
                ->method('getContent')
                ->will($this->returnValue($htmlContent));

        return $block;
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

        return array("Content" => $defaultContent);
    }
}
