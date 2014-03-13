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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configures the CMS parameters
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('red_kite_cms');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('orm')->defaultValue('Propel')->end()
                ->scalarNode('skin')->defaultValue('bootstrap')->end()
                ->scalarNode('web_folder_dir')->defaultValue('web')->end()
                ->scalarNode('web_folder_dir_full_path')->defaultValue('%kernel.root_dir%/../%red_kite_cms.web_folder%')->end()
                ->scalarNode('upload_assets_dir')->defaultValue('uploads/assets')->end()
                ->scalarNode('upload_assets_full_path')->defaultValue('%red_kite_cms.web_folder_full_path%/uploads/assets')->end()
                ->scalarNode('upload_assets_absolute_path')->defaultValue('/%red_kite_cms.web_folder%/uploads/assets')->end()
                ->scalarNode('theme_structure_file')->defaultValue('%kernel.root_dir%/Resources/.theme_structure')->end()
                ->scalarNode('love')->defaultValue('yes')->end()
                ->scalarNode('website_url')->defaultValue('')->end()
                ->scalarNode('bootstrap_version')->defaultValue('3.x')->end()
                ->scalarNode('active_theme_file')->defaultValue('%kernel.root_dir%/Resources/.active_theme')->end()
                ->arrayNode('deploy_bundle')
                    ->children()
                        ->scalarNode('resources_dir')->defaultValue('Resources')->end()
                        ->scalarNode('assets_base_dir')->defaultValue('%red_kite_cms.deploy_bundle.resources_dir%/public')->end()
                        ->scalarNode('config_dir')->defaultValue('%red_kite_cms.deploy_bundle.resources_dir%/config')->end()
                        ->scalarNode('views_dir')->defaultValue('%red_kite_cms.deploy_bundle.resources_dir%/views')->end()
                        ->scalarNode('media_dir')->defaultValue('media')->end()
                        ->scalarNode('js_dir')->defaultValue('js')->end()
                        ->scalarNode('css_dir')->defaultValue('css')->end()
                        ->scalarNode('controller')->defaultValue('WebSite')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
