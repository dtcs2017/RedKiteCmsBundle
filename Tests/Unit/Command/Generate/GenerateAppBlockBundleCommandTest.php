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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Command\Generate;

use Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateCommandTest;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;

/**
 * GenerateAppBlockBundleCommandTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class GenerateAppBlockBundleCommandTest extends GenerateCommandTest
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A strict RedKiteCms App-Block namespace must start with RedKiteCms\Block suffix
     */
    public function testAnExceptionIsThrownWhenTheNamespaceIsInvalidInStrictMode()
    {
        $options = array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--format' => 'yml', '--bundle-name' => 'BarBundle', '--structure' => true);
        $input = "Foo\FooBarBundle\n";
        
        $generator = $this->getGenerator();        
        $generator
            ->expects($this->never())
            ->method('generateExt')
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);
    }
    
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($namespace, $bundle, $dir, $format, $structure, $description, $group, $noStrict) = $expected;

        $commandOptions = array(
            'description' => $description,
            'group' => $group,
            'no-strict' => $noStrict,
        );
        
        $generator = $this->getGenerator();        
        $generator
            ->expects($this->once())
            ->method('generateExt')
            ->with($namespace, $bundle, $dir, $format, $structure, $commandOptions)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        $root = vfsStream::setup('root');
        
        $options = array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--no-strict' => true);
        return array(
            array($options, "Foo/BarBundle\n", array('Foo\BarBundle', 'FooBarBundle', vfsStream::url('root/'), 'annotation', false, 'Fake block', 'fake-group', true)),
            array(array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--no-strict' => true, '--format' => 'yml', '--bundle-name' => 'BarBundle', '--structure' => true), "Foo/BarBundle\n", array('Foo\BarBundle', 'BarBundle', vfsStream::url('root').'/', 'yml', true, 'Fake block', 'fake-group', true)),
            array(array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--format' => 'annotation', '--bundle-name' => 'BarBundle', '--structure' => true), "RedKiteCms/Block/BarBundle\n", array('RedKiteCms\Block\BarBundle', 'BarBundle', vfsStream::url('root').'/', 'annotation', true, 'Fake block', 'fake-group', false)),
        );
    }
    
    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($namespace, $bundle, $dir, $format, $structure, $description, $group, $noStrict) = $expected;

        $commandOptions = array(
            'description' => $description,
            'group' => $group,
            'no-strict' => $noStrict,
        );
        
        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generateExt')
            ->with($namespace, $bundle, $dir, $format, $structure, $commandOptions)
        ;

        $tester = new CommandTester($this->getCommand($generator, ''));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        $root = vfsStream::setup('root');
        
        return array(
            array(array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--no-strict' => true, '--namespace' => 'Foo/BarBundle'), array('Foo\BarBundle', 'FooBarBundle', vfsStream::url('root/'), 'annotation', false, 'Fake block', 'fake-group', true)),
            array(array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--no-strict' => true, '--namespace' => 'Foo/BarBundle', '--format' => 'yml', '--bundle-name' => 'BarBundle', '--structure' => true), array('Foo\BarBundle', 'BarBundle', vfsStream::url('root/'), 'yml', true, 'Fake block', 'fake-group', true)),
            array(array('--dir' => vfsStream::url('root'), '--description' => 'Fake block', '--group' => 'fake-group', '--namespace' => 'RedKiteCms/Block/BarBundle', '--format' => 'yml', '--bundle-name' => 'BarBundle', '--structure' => true), array('RedKiteCms\Block\BarBundle', 'BarBundle', vfsStream::url('root/'), 'yml', true, 'Fake block', 'fake-group', false)),
        );
    }
    
    protected function getCommand($generator, $input)
    {
        $command = $this
            ->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Command\Generate\GenerateAppBlockBundleCommand')
            ->setMethods(array('checkAutoloader', 'updateKernel'))
            ->getMock()
        ;

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        if (null !== $generator) {
            $command->setGenerator($generator);
        }

        return $command;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Generator\AlAppBlockGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateExt'))
            ->getMock()
        ;
    }
}
