<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license infpageRepositoryation, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Core\Repository\Repository;

use RedKiteLabs\RedKiteCmsBundle\Model\AlRole;

/**
 * Defines the methods used to fetch role records
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
interface RoleRepositoryInterface
{
    /**
     * Fetches a role record using its primary key
     *
     * @param  int    $id The primary key
     * @return AlRole The fetched object
     */
    public function fromPK($id);

    /**
     * Fetches a role record using its primary key
     *
     * @param  int    $roleName The primary key
     * @return AlRole The fetched object
     */
    public function fromRoleName($roleName);

    /**
     * Fetches the active roles
     *
     * @return \Iterator|AlRole[] The fetched objects
     */
    public function activeRoles();
}
