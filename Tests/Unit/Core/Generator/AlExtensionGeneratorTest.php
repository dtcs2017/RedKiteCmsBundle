<?php
/**
 * This file is part of the RedKiteLabsRedKiteCmsBundle and it is distributed
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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Generator;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Generator\AlExtensionGenerator;
use org\bovigo\vfs\vfsStream;

/**
 * AlExtensionGeneratorTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlExtensionGeneratorTest extends Base\AlGeneratorBase
{
    private $extensionGenerator;

    protected function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup('root', null, array('DependencyInjection'));
        vfsStream::copyFromFileSystem(__DIR__ . '/../../../../Resources/skeleton', $this->root);

        $this->extensionGenerator = new AlExtensionGenerator(vfsStream::url('root/app-theme'));
    }
/*
    public function testExtensionFileHasBeenGeneratedWithEmptySlots()
    {
        $templates = array(
            array(
                'name' => 'home.html.twig',
            ),
        );

        $message = $this->extensionGenerator->generateExtension('my/namespace/', vfsStream::url('root/DependencyInjection'), 'FakeThemeBundle', $templates);

        $expected = '';

        $file = vfsStream::url('root/DependencyInjection/FakeThemeExtension.php');
        $this->assertFileExists($file);
        $extensionContents = file_get_contents($file);
        $pattern = "/'path' =\> __DIR__\.'\/\.\.\/Resources\/config',\n[\s]+'theme' =\>\n[\s]+array\(\n[\s]+'fake_theme.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/'templates' =\>\n[\s]+array\(\n[\s]+'templates\/home.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/\n[\s]+'configFiles' =\>\n[\s]+array\(\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/return 'fake_theme';/";
        $this->assertRegExp($pattern, $extensionContents);

        $expected = 'The extension file <info>FakeThemeExtension.php</info> has been generated into <info>vfs://root/DependencyInjection</info>';
        $this->assertEquals($expected, $message);
    }*/

    public function testExtensionFileHasBeenGenerated()
    {
        $templates = array(
            array(
                'name' => 'home.html.twig',
            ),
        );

        $message = $this->extensionGenerator->generateExtension('my/namespace/', vfsStream::url('root/DependencyInjection'), 'FakeThemeBundle', $templates);

        $expected = '';

        $file = vfsStream::url('root/DependencyInjection/FakeThemeExtension.php');
        $this->assertFileExists($file);
        $extensionContents = file_get_contents($file);
        $pattern = "/'path' =\> __DIR__\.'\/\.\.\/Resources\/config',\n[\s]+'theme' =\>\n[\s]+array\(\n[\s]+'fake_theme.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/'templates' =\>\n[\s]+array\(\n[\s]+'templates\/home.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/'slots' =\>\n[\s]+array\(\n[\s]+'slots\/slots.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
        $pattern = "/return 'fake_theme';/";
        $this->assertRegExp($pattern, $extensionContents);

        $expected = 'The extension file <info>FakeThemeExtension.php</info> has been generated into <info>vfs://root/DependencyInjection</info>';
        $this->assertEquals($expected, $message);
    }
}