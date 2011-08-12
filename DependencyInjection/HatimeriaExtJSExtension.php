<?php
namespace Hatimeria\ExtJSBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class HatimeriaExtJSExtension extends Extension
{
    const CONFIG_NAMESPACE = "hatimeria_ext_js";
    
    /**
     * Loads the Direct configuration.
     *
     * @param array $config An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor     = new Processor();
        $configuration = new Configuration();
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('direct.xml');
        
        foreach ($configs as $config) {
            $this->registerApiConfiguration($config, $container);
        }

        $config = $processor->processConfiguration($configuration, $configs);
        $this->updateParameters($config, $container);
        $this->setMainFilename($container, $config);
    }
    
    private function setMainFilename($container, $config)
    {
        $filenames = array('normal' => 'ext-all', 'debug' => 'ext-all-debug', 'debug-comments' => 'ext-all-debug-w-comments');
        $this->setParameter($container, 'js_filename', $filenames[$config['javascript_mode']]);  
    }

    /**
     * Register the api configuration to container.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function registerApiConfiguration($config, ContainerBuilder $container)
    {
        if (isset($config['api']['route_pattern'])) {
            $container->setParameter('direct.api.route_pattern', $config['api']['route_pattern']);
        }

        if (isset($config['api']['type'])) {
            $container->setParameter('direct.api.type', $config['api']['type']);
        }

        if (isset($config['api']['namespace'])) {
            $container->setParameter('direct.api.namespace', $config['api']['namespace']);
        }

        if (isset($config['api']['id'])) {
            $container->setParameter('direct.api.id', $config['api']['id']);
        }

        if (isset($config['api']['remote_attribute'])) {
            $container->setParameter('direct.api.remote_attribute', $config['api']['remote_attribute']);
        }

        if (isset($config['api']['form_attribute'])) {
            $container->setParameter('direct.api.form_attribute', $config['api']['form_attribute']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'extjs';
    }
    
    private function setParameter($container, $key, $value) 
    {
        $container->setParameter(self::CONFIG_NAMESPACE.'.'.$key, $value);
    }
    
    public function updateParameters($config, ContainerBuilder $container)
    {
        foreach ($config as $key => $value)
        {
            $this->setParameter($container, $key, $value);
        }
    }    
}
