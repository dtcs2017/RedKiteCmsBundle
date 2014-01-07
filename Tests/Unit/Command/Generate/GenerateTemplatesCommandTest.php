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

namespace RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Command;

use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateCommandTest;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;
use RedKiteLabs\RedKiteCmsBundle\Command\Generate\GenerateTemplatesCommand;

/**
 * GenerateAppThemeBundleCommandTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class GenerateTemplatesCommandTest extends GenerateCommandTest
{
    private $root;
    protected function setUp()
    {
        $this->root = vfsStream::setup('root', null, array('DependencyInjection' => array('Extension.php' => '')));
    }
    
    public function testTemplateParserInjectedBySetters()
    {
        $templateParser = $this->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\TemplateParser\AlTemplateParser')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $command = new GenerateTemplatesCommand();
        $command->setTemplateParser($templateParser);
        $this->assertEquals($templateParser, $command->getTemplateParser());
    }
    
    public function testTemplateGeneratorInjectedBySetters()
    {
        $templateGenerator = $this->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\AlTemplateGenerator')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $command = new GenerateTemplatesCommand();
        $command->setTemplateGenerator($templateGenerator);
        $this->assertEquals($templateGenerator, $command->getTemplateGenerator());
    }
    
    public function testSlotsGeneratorInjectedBySetters()
    {
        $slotsGenerator = $this->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\AlSlotsGenerator')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $command = new GenerateTemplatesCommand();
        $command->setSlotsGenerator($slotsGenerator);
        $this->assertEquals($slotsGenerator, $command->getSlotsGenerator());
    }
    
    public function testExtensionGeneratorInjectedBySetters()
    {
        $extensionGenerator = $this->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\AlExtensionGenerator')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $command = new GenerateTemplatesCommand();
        $command->setExtensionGenerator($extensionGenerator);
        $this->assertEquals($extensionGenerator, $command->getExtensionGenerator());
    }

    public function testAnySlotDefined()
    {
        $values = array(
            "templates" => array(
                array(
                    "name" => "home.html.twig",
                    "slots" => array(),
                ),
            ),
            "slots" => array(),
        );
        $this->generationTest($values);
    }

    /**
     * @dataProvider templatesProvider 
     */
    public function testTemplatesGeneration($values, $templatePattern, $slotPattern)
    {
        $fakeCode = '{' . PHP_EOL;
        $fakeCode .= '        $loader->load(\'services.xml\');' . PHP_EOL;
        $fakeCode .= '}';
        file_put_contents(vfsStream::url('root/DependencyInjection/Extension.php'), $fakeCode);

        $this->generationTest($values);
        $extensionContents = file_get_contents(vfsStream::url('root/DependencyInjection/Extension.php'));
    
        $this->assertRegExp($templatePattern, $extensionContents);
        $this->assertRegExp($slotPattern, $extensionContents);
    }

    public function testWhenTheExtensionFileHasAlreadyWrittenCodeIsRegenerated()
    {
        file_put_contents(vfsStream::url('root/DependencyInjection/Extension.php'), 'Extension content');

        $this->generationTest();
        $extensionContents = file_get_contents(vfsStream::url('root/DependencyInjection/Extension.php'));
        $pattern = "/'templates' =>\n[\s]+array\(\n[\s]+'templates\/home.xml',\n[\s]+\),/";
        $this->assertRegExp($pattern, $extensionContents);
    }
    
    public function templatesProvider()
    {
        return array(
            array(
                array(
                    "templates" => array(
                        array(
                            "name" => "home.html.twig",
                            "slots" => array("page_content"),
                        ),
                    ),      
                    "slots" => array
                    (
                        "page_content",
                    ),       
                    "generate_template" => true,             
                ),
                "/'templates' =>\n[\s]+array\(\n[\s]+'templates\/home.xml',\n[\s]+\),/",
                "/'slots' =>\n[\s]+array\(\n[\s]+'slots\/slots.xml',\n[\s]+\),/",
            ),
            array(
                array(
                    "templates" => array(
                        array(
                            "name" => "home.html.twig",
                            "slots" => array("page_content"),
                        ),
                        array(
                            "name" => "template.html.twig",
                            "slots" => array("logo"),
                        ),
                    ),      
                    "slots" => array
                    (
                        "page_content",
                        "logo",
                    ),       
                    "generate_template" => true,             
                ),
                "/'templates' =>\n[\s]+array\(\n[\s]+'templates\/home.xml',\n[\s]+'templates\/template.xml',\n[\s]+\),/",
                "/'slots' =>\n[\s]+array\(\n[\s]+'slots\/slots.xml',\n[\s]+\),/",
            ),
        );
    }
    
    protected function generationTest($values = null)
    {
        if (null === $values) {
            $values = array(
                "templates" => array(
                    array(
                        "name" => "home.html.twig",
                        "slots" => array("page_content"),
                    ),
                ),      
                "slots" => array
                (
                    "page_content",
                ),       
                "generate_template" => true,             
            );
        }   

        $slotsGenerator = $this->getSlotsGenerator();
        $themeName = 'FakeThemeBundle';
        
        $t = 0;
        $c = 0;
        $templateParser = $this->getTemplateParser($values);        
        $templateGenerator = $this->getTemplateGenerator();
        foreach($values["templates"] as $value) {
            $template = $value["name"];
            $templateName = basename($template, '.html.twig');
            
            
            $templateGenerator
                ->expects($this->at($t))
                ->method('generateTemplate')
                ->with(vfsStream::url('root/Resources/config/templates'), null, $templateName, $value["slots"])
            ;
            $t++;
        }
        
        $slotsGenerator
            ->expects($this->once())
            ->method('generateSlots')
            ->with(vfsStream::url('root/Resources/config/slots'), null, $values["slots"])
        ;
        
        $tester = new CommandTester($this->getCommand($templateParser, $templateGenerator, $slotsGenerator, ''));
        $tester->execute(array(array('theme' => $themeName)), array('interactive' => false));
    }

    protected function getCommand($templateParser, $templateGenerator, $slotsGenerator, $input)
    {
        $command = $this
            ->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Command\Generate\GenerateTemplatesCommand')
            ->setMethods(array('checkAutoloader', 'updateKernel'))
            ->getMock()
        ;

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setTemplateParser($templateParser);
        $command->setTemplateGenerator($templateGenerator);
        $command->setSlotsGenerator($slotsGenerator);

        return $command;
    }

    protected function getContainer()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        
        $kernel
            ->expects($this->once())
            ->method('locateResource')
            ->will($this->returnValue(vfsStream::url('root/')))
        ;
        
        $kernel
            ->expects($this->once())
            ->method('getBundle')
            ->will($this->returnValue($bundle))
        ;
        
        $bundle
            ->expects($this->once())
            ->method('getNamespace')
            ->will($this->returnValue('\bundle\namespace'))
        ;

        $container = new Container();
        $container->set('kernel', $kernel);

        return $container;
    }

    protected function getTemplateParser($values)
    {
        $templateParser = $this
            ->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\TemplateParser\AlTemplateParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parse'))
            ->getMock()
        ;
        
        $templateParser
            ->expects($this->once())
            ->method('parse')
            ->will($this->returnValue($values))
        ;

        return $templateParser;
    }

    protected function getTemplateGenerator()
    {
        return $this
            ->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\AlTemplateGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateTemplate'))
            ->getMock()
        ;
    }

    protected function getSlotsGenerator()
    {
        return $this
            ->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Generator\AlSlotsGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateSlots'))
            ->getMock()
        ;
    }
}