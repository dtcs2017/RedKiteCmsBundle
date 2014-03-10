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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Form\Security;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Form\Base\AlBaseType;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Security\AlRoleType;

/**
 * AlRoleTypeFormTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlRoleTypeFormTest extends AlBaseType
{
    protected function configureFields()
    {
        return array(
            'id',
            'role',
        );
    }
    
    protected function getForm()
    {
        return new AlRoleType();
    }
    
    public function testDefaultOptions()
    {
        $this->setBaseResolver();

        $options = array(
            'data_class' => 'RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlRole',
        );
        $this->resolver
            ->expects($this->at(1))
            ->method('setDefaults')
            ->with($options)
        ;

        $this->getForm()->setDefaultOptions($this->resolver);
    }
    
    public function testGetName()
    {
        $this->assertEquals('al_role', $this->getForm()->getName());
    }
}