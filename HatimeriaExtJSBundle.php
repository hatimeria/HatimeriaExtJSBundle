<?php

namespace Hatimeria\ExtJSBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder;

use Hatimeria\ExtJSBundle\DependencyInjection\Compiler\RegisterMappingsPass;

class HatimeriaExtJSBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMappingsPass());
    }

}
