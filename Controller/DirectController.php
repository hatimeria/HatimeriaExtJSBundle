<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Hatimeria\ExtJSBundle\Api\Api;
use Hatimeria\ExtJSBundle\Router\Router;

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
        $r = new Response("Ext.Direct.addProvider(".$api.");");
        $r->headers->set("Content-Type","text/javascript");
        
        return $r;
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

        $content = $router->route();
        // return the routing result
        $r = new Response($content);
        $hasFiles = count($_FILES) > 0;
        
        if ($hasFiles) {
           $content = sprintf("<html><body><textarea>%s</textarea></body></html>", $content);
           $contentType = "text/html";
        } else {
           $contentType = "application/json";
        }
        
        $r = new Response($content);
        $r->headers->set("Content-Type", $contentType);
        
        return $r;
    }
}
