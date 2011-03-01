<?php
namespace Neton\DirectBundle\Api;

/**
 * Api is the ExtDirect Api class.
 *
 * It provide the ExtDirect Api descriptor of exposed Controllers and methods.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class Api
{
    /**
     * The application container.
     *
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * The ExtDirect JSON API description.
     * 
     * @var JSON
     */
    protected $api;

    /**
     * Initialize the API.
     */
    public function __construct($container)
    {
        $this->container = $container;

        if ($container->get('kernel')->isDebug()) {
            $this->api = json_encode($this->createApi());
        } else {            
            $this->api = $this->getApiFromCache();
        }        
    }

    /**
     * Return the API in JSON format.
     *
     * @return string JSON API description
     */
    public function  __toString()
    {        
        return $this->api;
    }

    /**
     * Create the ExtDirect API based on controllers files.
     *
     * @return string JSON description of Direct API
     */
    protected function createApi()
    {
        $bundles = $this->getControllers();

        $actions = array();        

        foreach ($bundles as $bundle => $controllers ) {
            $bundleShortName = str_replace('Bundle', '', $bundle);
            
            foreach ($controllers as $controller) {
                $api = new ControllerApi($this->container, $controller);

                if ($api->isExposed()) {
                    $actions[$bundleShortName."_".$api->getActionName()] = $api->getApi();
                }
            }
        }

        return array(
            'url' => $this->container->get('request')->getBaseUrl().
                     $this->container->getParameter('direct.api.route_pattern'),
            'type' => $this->container->getParameter('direct.api.type'),
            'namespace' => $this->container->getParameter('direct.api.namespace'),
            'id' => $this->container->getParameter('direct.api.id'),
            'actions' => $actions
        );
    }

    /**
     * Return the cached ExtDirect API.
     *
     * @return string JSON description of Direct API
     */
    protected function getApiFromCache()
    {
        //@todo: implement the cache mechanism
        return json_encode($this->createApi());
    }

    /**
     * Get all controllers from all bundles.
     *
     * @return array Controllers list
     */
    protected function getControllers()
    {
        $controllers = array();
        $finder = new ControllerFinder();
        
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            $found = $finder->getControllers($bundle);
            if (!empty ($found)) {
                $controllers[$bundle->getName()] = $found;
            }
        }

        return $controllers;
    }    
}
