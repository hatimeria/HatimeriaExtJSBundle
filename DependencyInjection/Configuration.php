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
                ->arrayNode("mappings")
                    ->prototype("array")
                    ->useAttributeAsKey('class')
                        ->children()
                            ->arrayNode('fields')->prototype('scalar')->end()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('signin_route')->defaultFalse()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
    
}
