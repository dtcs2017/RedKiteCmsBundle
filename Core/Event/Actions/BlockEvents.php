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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Event\Actions;

/**
 * Defines the names for the block actions events
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
final class BlockEvents
{
    // rkcms.event_listener

    const BLOCK_EDITED= 'actions.block_edited';
    const BLOCK_EDITOR_RENDERING = 'actions.block_editor_rendering';
    const BLOCK_EDITOR_RENDERED = 'actions.block_editor_rendered';
}
