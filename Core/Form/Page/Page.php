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

namespace RedKiteLabs\RedKiteCmsBundle\Core\Form\Page;

/**
 * Defines the pages form fields
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class Page
{
    protected $pageName;
    protected $template;
    protected $isHome;
    protected $isPublished;

    public function getPageName()
    {
        return $this->pageName;
    }

    public function setPageName($pageName)
    {
        $this->pageName = $pageName;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getIsHome()
    {
        return $this->isHome;
    }

    public function setIsHome($home)
    {
        $this->isHome = $home;
    }

    public function getIsPublished()
    {
        return $this->isPublished;
    }

    public function setIsPublished($published)
    {
        $this->isPublished = $published;
    }
}
