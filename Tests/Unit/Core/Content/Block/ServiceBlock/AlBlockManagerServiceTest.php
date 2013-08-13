<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Core\Content\Block\ServiceBlock;

use RedKiteLabs\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCmsBundle\Core\Content\Block\ServiceBlock\AlBlockManagerService;

/**
 * AlBlockManagerServiceBlock
 *
 * @author AlphaLemon <webmaster@alphalemon.com>
 */
class AlBlockManagerServiceBlockTest extends TestCase
{
    public function testServiceBlockDefaultValueReturnsNull()
    {
        $eventsHandler = $this->getMock('RedKiteLabs\RedKiteCmsBundle\Core\EventsHandler\AlEventsHandlerInterface');
        $factoryRepository = $this->getMock('RedKiteLabs\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');

        $blockManager = new AlBlockManagerService($eventsHandler, $factoryRepository);
        $this->assertNull($blockManager->getDefaultValue());
    }
}
