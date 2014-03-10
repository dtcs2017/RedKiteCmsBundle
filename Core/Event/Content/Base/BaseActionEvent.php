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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Event\Content\Base;

use Symfony\Component\EventDispatcher\Event;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\AlContentManagerInterface;

/**
 * Defines a base event raised from a ContentManager
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
abstract class BaseActionEvent extends Event
{
    /** @var AlContentManagerInterface */
    protected $alManager;

    /**
     * Constructor
     *
     * @param AlContentManagerInterface $alBlockManager
     */
    public function __construct(AlContentManagerInterface $alBlockManager = null)
    {
        $this->alManager = $alBlockManager;
    }

    /**
     * Returns the current AlContentManager object
     *
     * @return AlContentManagerInterface
     */
    public function getContentManager()
    {
        return $this->alManager;
    }

    /**
     * Sets the current AlContentManager object
     *
     * @param AlContentManagerInterface $value
     */
    public function setContentManager(AlContentManagerInterface $value)
    {
        $this->alManager = $value;
    }
}
