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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Language;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Base\BaseBlockType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Implements the form to manage the website languages
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class LanguagesForm extends BaseBlockType
{
    /**
     * Builds the form
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('language', 'language', array(
            'label' => 'languages_controller_label_language',
        ));
        $builder->add('isMain', 'checkbox', array(
            'label' => 'languages_controller_is_main_language',
            'attr' => array(
                'title' => 'languages_controller_is_main_language_explanation',
            ),
        ));
    }

    /**
     * Sets the default options for this type
     *
     * @param OptionsResolverInterface $resolver The resolver for the options
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => 'RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Language\Language',
        ));
    }

    /**
     * Returns the name of this type
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'languages';
    }
}
