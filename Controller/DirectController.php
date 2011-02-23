<?php

namespace Neton\DirectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Neton\DirectBundle\Api\Api;
use Neton\DirectBundle\Router\Router;

class DirectController extends Controller
{
    /**
     * Generate the ExtDirect API.
     * 
     * @return Response 
     */
    public function getApiAction()
    {
        // instantiate the api object
        $api = new Api($this->container);

        // return the json api description
        return new Response("Ext.Direct.addProvider(".$api.");");
    }

    /**
     * Route the ExtDirect calls.
     *
     * @return Response
     */
    public function routeAction()
    {
        // instantiate the router object
        $router = new Router($this->container);

        // return the routing result
        return new Response($router->route());
        //echo $router->route();
    }
}
