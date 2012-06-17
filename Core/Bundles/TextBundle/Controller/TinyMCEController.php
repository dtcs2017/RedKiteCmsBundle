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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Bundles\TextBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use AlphaLemon\PageTreeBundle\Core\Tools\AlToolkit;


/**
 * TinyMCEController
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class TinyMCEController extends Controller
{
    public function createImagesListAction()
    {
        $bundleFolder = AlToolkit::retrieveBundleWebFolder($this->container, 'AlphaLemonCmsBundle');
        $assetsFolder = $this->getLocatedAssetsFolder();

        $mceImages = array();
        $imagesFiles = $this->retrieveMediaFiles(array('*.jpg', '*.jpeg', '*.png', '*.gif', '*.tif'));
        foreach($imagesFiles as $imagesFile)
        {
            $absoluteFolderPath = '/uploads/assets' . \str_replace($assetsFolder, '', dirname($imagesFile));
            $mceImages[] = sprintf("[\"%1\$s\", \"%2\$s/%1\$s\"]", basename($imagesFile), "/" . $bundleFolder . $absoluteFolderPath);
        }
        $list = 'var tinyMCEImageList = new Array(' . implode(",", $mceImages) . ');';

        return $this->setResponse($list);
    }

    public function createLinksListAction()
    {     
        $alPagesAttribute = AlPageAttributeQuery::create()->setContainer($this->container)->fromLanguageId($this->getRequest()->get('language'))->find();
        
        $mcsLinks = array();
        foreach($alPagesAttribute as $alPageAttribute)
        {
            $mcsLinks[] = sprintf("[\"%1\$s\", \"%1\$s\"]", $alPageAttribute->getPermalink(), $alPageAttribute->getPermalink()); //%2\$s/ , 'en'
        }
        $list = 'var tinyMCELinkList = new Array(' . implode(",", $mcsLinks) . ');';
        
        return $this->setResponse($list);
    }

    protected function retrieveMediaFiles(array $types)
    {
        $finder = new Finder();
        $finder = $finder->directories()->files()->exclude('.tmb')->sortByName();
        foreach($types as $type)
        {
            $finder = $finder->name(trim($type));
        }
        
        return $finder->in($this->getLocatedAssetsFolder() . '/' . $this->container->getParameter('alphalemon_cms.deploy_bundle.media_folder'));
    }

    private function setResponse($content)
    {
        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    private function getLocatedAssetsFolder()
    {
        $bundleFolder = $this->container->getParameter('kernel.root_dir') . '/../' . $this->container->getParameter('alphalemon_cms.web_folder') . '/' . AlToolkit::retrieveBundleWebFolder($this->container, 'AlphaLemonCmsBundle');
        
        return AlToolkit::normalizePath($bundleFolder . '/' . $this->container->getParameter('alphalemon_cms.upload_assets_dir'));
    }
}

