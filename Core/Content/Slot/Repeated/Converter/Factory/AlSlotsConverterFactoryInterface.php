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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Slot\Repeated\Converter\Factory;

use RedKiteLabs\ThemeEngineBundle\Core\ThemeSlots\AlSlot;

/**
 * Used by the Slots converter factory to create the appropriate converter to change the
 * repeated status of a slot to another one
 *
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
interface AlSlotsConverterFactoryInterface
{
    /**
     * Creates the appropriate conver using the given parameter
     *
     * @param  AlSlot                                                                                      $slot
     * @param  string                                                                                      $newRepeatedStatus
     * @return \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Slot\Repeated\Converter\AlSlotConverterInterface
     */
    public function createConverter(AlSlot $slot, $newRepeatedStatus);
}
