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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\Base;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Base\AlContentManagerBase;


class AlContentManagerTester extends AlContentManagerBase
{

}

/**
 * AlContentManagerBaseTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlContentManagerBaseTest extends TestCase
{
    protected $eventsHandler;
    protected $validator;
    protected $contentManager;

    protected function setUp()
    {
        parent::setUp();

        $this->eventsHandler = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\EventsHandler\AlEventsHandlerInterface');

        $this->validator = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Validator\AlParametersValidator')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->contentManager = new AlContentManagerTester($this->eventsHandler, $this->validator);
    }

    public function testEventsHandlerByContructor()
    {
        $this->assertEquals($this->eventsHandler, $this->contentManager->getEventsHandler());
    }

    public function testEventsHandlerBySetters()
    {
        $eventsHandler = $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\EventsHandler\AlEventsHandlerInterface');
        $this->assertSame($this->contentManager, $this->contentManager->setEventsHandler($eventsHandler));
        $this->assertSame($eventsHandler, $this->contentManager->getEventsHandler());
        $this->assertNotSame($this->eventsHandler, $this->contentManager->getEventsHandler());
    }

    public function testValidatorInjectedByContructor()
    {
        $this->assertEquals($this->validator, $this->contentManager->getValidator());
    }

    public function testValidatorInjectedBySetters()
    {
        $validator = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Validator\AlParametersValidatorPageManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->assertSame($this->contentManager, $this->contentManager->setValidator($validator));
        $this->assertSame($validator, $this->contentManager->getValidator());
        $this->assertNotSame($this->validator, $this->contentManager->getValidator());
    }
}
