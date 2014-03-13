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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\ElFinder;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ElFinder\ElFinderJavascriptsConnector;

class ElFinderJavascriptsConnectorExt extends ElFinderJavascriptsConnector
{
    public function getOptions()
    {
        return $this->options;
    }
}

/**
 * ElFinderJavascriptsConnectorTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ElFinderJavascriptsConnectorTest extends TestCase
{
    public function testOptions()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                                    ->disableOriginalConstructor()
                                    ->getMock();
/* TODO Removable?
        $request->expects($this->once())
            ->method('getScheme')
            ->will($this->returnValue('http'));

        $request->expects($this->once())
            ->method('getHttpHost')
            ->will($this->returnValue('example.com'));*/
        

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('RedKiteCmsBundle'));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue($request));
        
        $container->expects($this->at(0))
            ->method('getParameter')
            ->with('red_kite_cms.deploy_bundle.js_dir')
            ->will($this->returnValue('js')); 
       
        $container->expects($this->at(1))
            ->method('getParameter')
            ->with('red_kite_cms.upload_assets_full_path')
            ->will($this->returnValue('/full/base/path/to/web/uploads/assets')); 
        
        $container->expects($this->at(4))
            ->method('getParameter')
            ->with('red_kite_cms.upload_assets_dir')
            ->will($this->returnValue('uploads/assets'));

        $espected = array
        (
            "locale" => "",
            "roots" => array
                (
                    array
                        (
                            "driver" => "LocalFileSystem",
                            "path" => "/full/base/path/to/web/uploads/assets/js",
                            // TODO "URL" => "http://example.com/uploads/assets/js",
                            "URL" => "/uploads/assets/js",
                            "accessControl" => "access",
                            "rootAlias" => "Javascripts"
                        )

                )

        );

        $connector = new ElFinderJavascriptsConnectorExt($container);
        $this->assertEquals($espected, $connector->getOptions());
    }
}
