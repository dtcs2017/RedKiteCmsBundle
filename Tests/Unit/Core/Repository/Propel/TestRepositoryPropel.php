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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Repository\Propel;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\Base\PropelRepository;

class TestRepositoryPropel extends PropelRepository
{
    public function getRepositoryObjectClassName()
    {
        return 'fake';
    }
}
