<?php

namespace Hatimeria\ExtJSBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\Reference;

class RegisterMappingsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $collector = $container->getDefinition('hatimeria_extjs.mappings');
        
        foreach ($container->findTaggedServiceIds('hatimeria_extjs.mappings') as $id => $attr) {
            $collector->addMethodCall('addMappingsProvider', array(new Reference($id)));
        }
    }
    
}