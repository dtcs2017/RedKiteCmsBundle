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

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Functional\Core\Content\Slot\Repeated\Converter;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Slot\Repeated\Converter\AlSlotConverterToLanguage;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlPageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlLanguageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlBlockQuery;
use AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot;

class AlSlotConverterToLanguageTest extends AlSlotConverterBase
{   
    public function testConvert()
    {
        $container = $this->setupPageTree()->getContainer();         
        $alPage = AlPageQuery::create()->homePage()->findOne();
        $alLanguage = AlLanguageQuery::create()->mainLanguage()->findOne();
        
        $slot = new AlSlot('logo');
        $this->assertEquals(1, AlBlockQuery::create()->filterBySlotName('logo')->filterByToDelete(0)->count());
        $converter = new AlSlotConverterToLanguage($container, $slot, $alPage, $alLanguage);
        $this->assertTrue($converter->convert());
        $this->assertEquals(2, AlBlockQuery::create()->filterBySlotName('logo')->filterByToDelete(0)->count());
        
        $slot = new AlSlot('header');
        $this->assertEquals(8, AlBlockQuery::create()->filterBySlotName('header')->filterByToDelete(0)->count());
        $converter = new AlSlotConverterToLanguage($container, $slot, $alPage, $alLanguage);
        $this->assertTrue($converter->convert());
        $this->assertEquals(2, AlBlockQuery::create()->filterBySlotName('header')->filterByToDelete(0)->count());
        
    }
}