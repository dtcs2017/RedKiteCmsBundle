<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Slot\Repeated\Converter;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Language;
use RedKiteLabs\ThemeEngineBundle\Core\ThemeSlots\Slot;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Slot\Repeated\Converter\SlotConverterToLanguage;

/**
 * SlotsConverterToLanguageTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class SlotsConverterToLanguageTest extends SlotsConverterBase
{
    public function testConvertReturnsNullWhenAnyBlockExists()
    {
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array()));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertNull($converter->convert());
    }

    public function testConvertReturnsNullWhenAnyLanguageExists()
    {
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
            ->method('save');

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2))));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertNull($converter->convert());
    }

    public function testConvertFailsOnAnEmptySlotWhenDbSavingFails()
    {
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2))));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertFalse($converter->convert());
    }

    public function testConvertFailsWhenExistingBlocksRemovingFails()
    {
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('rollback');

        $this->blockRepository->expects($this->never())
            ->method('save');

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(false));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertFalse($converter->convert());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertFailsWhenAnUnespectedExceptionIsThrowsWhenRemovingBlocks()
    {
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->exactly(2))
            ->method('rollback');

        $this->blockRepository->expects($this->never())
            ->method('save');

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \RuntimeException));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertFalse($converter->convert());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertFailsWhenAnUnespectedExceptionIsThrowsWhenSavingNewBlocks()
    {
        $block = $this->setUpBlock();
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($block)));

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2))));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('commit');

        $this->blockRepository->expects($this->once())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \RuntimeException));

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertTrue($converter->convert());
    }

    public function testSingleBlockSlotWhenSingleLanguageHasBeenConverted()
    {
        $block = $this->setUpBlock();
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($block)));

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2))));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->exactly(2))
            ->method('commit');

        $this->blockRepository->expects($this->never())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertTrue($converter->convert());
    }

    public function testMoreBlockSlotWhenSingleLanguageHasBeenConverted()
    {
        $block = $this->setUpBlock();
        $block1 = $this->setUpBlock();
        $block2 = $this->setUpBlock();
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($block, $block1, $block2)));

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2))));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->exactly(2))
            ->method('commit');

        $this->blockRepository->expects($this->never())
            ->method('rollback');

        $this->blockRepository->expects($this->exactly(3))
            ->method('save')
            ->will($this->returnValue(true));

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertTrue($converter->convert());
    }

    public function testSingleBlockSlotWhenMoreLanguagesHasBeenConverted()
    {
        $block = $this->setUpBlock();
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($block)));

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2), $this->setUpLanguage(3))));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->exactly(2))
            ->method('commit');

        $this->blockRepository->expects($this->never())
            ->method('rollback');

        $this->blockRepository->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValue(true));

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertTrue($converter->convert());
    }

    public function testMoreBlockSlotWhenMoreLanguagesHasBeenConverted()
    {
        $block = $this->setUpBlock();
        $block1 = $this->setUpBlock();
        $block2 = $this->setUpBlock();
        $this->pageContents->expects($this->once())
            ->method('getSlotBlocks')
            ->will($this->returnValue(array($block, $block1, $block2)));

        $this->blockRepository->expects($this->exactly(2))
            ->method('startTransaction');

        $this->blockRepository->expects($this->exactly(2))
            ->method('commit');

        $this->languageRepository->expects($this->once())
            ->method('activeLanguages')
            ->will($this->returnValue(array($this->setUpLanguage(2),$this->setUpLanguage(3))));

        $this->blockRepository->expects($this->exactly(6))
            ->method('save')
            ->will($this->returnValue(true));

        $this->blockRepository->expects($this->any())
            ->method('retrieveContentsBySlotName')
            ->will($this->returnValue(array($this->setUpBlock())));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $slot = new Slot('test', array('repeated' => 'page'));
        $converter = new SlotConverterToLanguage($slot, $this->pageContents, $this->factoryRepository);
        $this->assertTrue($converter->convert());
    }

    private function setUpBlock()
    {
        $block = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Block');
        $block->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array("Id" => 2, "Type" => "Text")));

        return $block;
    }

    private function setUpLanguage($id)
    {
        $language = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Language');
        $language->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $language;
    }
}
