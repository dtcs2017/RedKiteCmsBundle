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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlConfiguration;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlConfigurationQuery;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\ConfigurationRepositoryInterface;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException;

/**
 *  Implements the BlockRepositoryInterface to work with Propel
 *
 *  @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlConfigurationRepositoryPropel extends Base\AlPropelRepository implements ConfigurationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRepositoryObjectClassName()
    {
        return '\RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlConfiguration';
    }

    /**
     * {@inheritdoc}
     */
    public function setRepositoryObject($object = null)
    {
        if (null !== $object && !$object instanceof AlConfiguration) {
            throw new InvalidArgumentTypeException('exception_only_propel_configuration_objects_are_accepted');
        }

        return parent::setRepositoryObject($object);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchParameter($parameter)
    {
        return AlConfigurationQuery::create()
            ->filterByParameter($parameter)
            ->findOne();
    }
}
