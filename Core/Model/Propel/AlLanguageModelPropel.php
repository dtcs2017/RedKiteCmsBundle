<?php
/*
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Model\Propel;

use AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguage;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Query\Language;
use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Query\LanguagesEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\Entities\LanguageModelInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\InvalidParameterTypeException;

/**
 *  Adds some filters to the AlLanguageQuery object
 *
 *  @author alphalemon <webmaster@alphalemon.com>
 */
class AlLanguageModelPropel extends Base\AlPropelModel implements LanguageModelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getModelObjectClassName()
    {
        return '\AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguage';
    }

    /**
     * {@inheritdoc}
     */
    public function setModelObject($object = null)
    {
        if (null !== $object && !$object instanceof AlLanguage) {
            throw new InvalidParameterTypeException('AlLanguageModelPropel accepts only AlLanguage propel objects');
        }

        return parent::setModelObject($object);
    }

    /**
     * {@inheritdoc}
     */
    public function fromPK($id)
    {
        return AlLanguageQuery::create()->findPk($id);
    }

    /**
     * {@inheritdoc}
     */
    public function mainLanguage()
    {
        return AlLanguageQuery::create()
                    ->filterByMainLanguage(1)
                    ->filterByToDelete(0)
                    ->findOne();
    }

    /**
     * {@inheritdoc}
     */
    public function fromLanguageName($languageName)
    {
        if (null === $languageName)
        {
            return null;
        }

        if (!is_string($languageName))
        {
            throw new \InvalidArgumentException('The name of the laguage must be a string. The language cannot be retrieved');
        }

        return AlLanguageQuery::create()
                    ->filterByToDelete(0)
                    ->filterByLanguage($languageName)
                    ->findOne();
    }

    /**
     * {@inheritdoc}
     */
    public function activeLanguages()
    {
        return AlLanguageQuery::create()
                ->filterByToDelete(0)
                ->where('id > 1')
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function firstOne()
    {
        return AlLanguageQuery::create()
                    ->filterByToDelete(0)
                    ->where('id > 1')
                    ->findOne();
    }
}
