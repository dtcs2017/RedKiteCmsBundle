<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\AssetsPath;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AssetsPath provides the paths for common assets folders
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 * @codeCoverageIgnore
 */
class AssetsPath
{
    /**
     * Returns the upload folder path
     *
     * @param  ContainerInterface $container
     * @return string
     */
    public static function getUploadFolder(ContainerInterface $container)
    {
        $request = $container->get('request');

        $baseUrl = dirname($request->getBaseUrl());
        $baseUrl = substr($baseUrl, 1);
        if ( ! empty($baseUrl)) {
            $baseUrl .= '/';
        }

        return $baseUrl . $container->getParameter('red_kite_cms.upload_assets_dir');
    }

    /**
     * Returns the upload folder absolute path
     *
     * @param  ContainerInterface $container
     * @return string
     */
    public static function getAbsoluteUploadFolder(ContainerInterface $container)
    {
        $uploaderFolder = self::getUploadFolder($container);
        $uploaderFolder = (empty($uploaderFolder)) ? '/' : '/' . $uploaderFolder;

        return $uploaderFolder;
    }
}
