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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Event\PageTree;

/**
 * Defines the names for the PageTree events
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
final class PageTreeEvents
{
    // rkcms.event_listener
    const BEFORE_PAGE_TREE_SETUP = 'page_tree.before_setup';
    const AFTER_PAGE_TREE_SETUP = 'page_tree.after_setup';

    const BEFORE_PAGE_TREE_REFRESH = 'page_tree.before_refresh';
    const AFTER_PAGE_TREE_REFRESH = 'page_tree.after_refresh';
}
