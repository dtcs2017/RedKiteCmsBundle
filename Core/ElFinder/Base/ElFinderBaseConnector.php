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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ElFinder\Base;

use RedKiteLabs\RedKiteCms\ElFinderBundle\Core\Connector\RedKiteLabsElFinderBaseConnector;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\AssetsPath\AlAssetsPath;

/**
 * Configures the ElFinder library to manage media files, like images, flash, pdf and more
 */
abstract class ElFinderBaseConnector extends RedKiteLabsElFinderBaseConnector
{
    /**
     * {@inheritdoc}
     */
    protected function generateOptions($folder, $rootAlias)
    {
        $assetsPath = $this->container->getParameter('red_kite_cms.upload_assets_full_path') . '/' . $folder;
        if (!is_dir($assetsPath)) {
            @mkdir($assetsPath);
        }

        $request = $this->container->get('request');
        $options = array(
            'locale' => '',
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $assetsPath,         // path to files (REQUIRED)
                    //'URL'           => $request->getScheme().'://'.$request->getHttpHost() . '/' . AlAssetsPath::getUploadFolder($this->container) . '/' . $folder, // URL to files (REQUIRED)
                    'URL'           => '/' . AlAssetsPath::getUploadFolder($this->container) . '/' . $folder, // URL to files (REQUIRED)
                    'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
                    'rootAlias'     => $rootAlias             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        return $options;
    }
}
