<?php
namespace Hatimeria\ExtJSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hatimeria_ext_js');
        $rootNode
            ->children()
                ->scalarNode('javascript_mode')->defaultValue("ext-all-dev")->end()
                ->scalarNode('loader_disable_caching')->defaultTrue()->end()
                ->scalarNode('compiled')->defaultFalse()->end()
                ->arrayNode("locales")->defaultValue(array())
                    ->prototype("scalar")->end()
                ->end()
                ->arrayNode("loader")->defaultValue(array())
                ->useAttributeAsKey('id')
                    ->prototype("scalar")->end()
                ->end()
                ->arrayNode("translation_domains")->defaultValue(array())
                    ->prototype("scalar")->end()
                ->end()
                ->scalarNode('javascript_vendor_path')->defaultValue("bundles/hatimeriaextjs/js/extjs/vendor/extjs-4.0.7/")->end()
                ->arrayNode("mappings")
                ->useAttributeAsKey('id')
                    ->prototype("array")
                        ->children()
                            ->arrayNode('fields')
                                ->useAttributeAsKey('id')
                                ->prototype('array')->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('signin_route')->defaultFalse()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
    
}
