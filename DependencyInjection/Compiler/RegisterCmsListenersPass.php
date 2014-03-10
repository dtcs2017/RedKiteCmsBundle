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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RedKiteLabs\ThemeEngineBundle\Core\Rendering\Compiler\EventListenersRegistrator;

/**
 * Registers the CMS events
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RegisterCmsListenersPass implements CompilerPassInterface
{
    /**
     * Registers the RedKiteCms events
     *
     * @param ContainerBuilder $container
     * @codeCoverageIgnore
     */
    public function process(ContainerBuilder $container)
    {
        EventListenersRegistrator::registerByTaggedServiceId($container, 'rkcms.event_listener');
    }
}
