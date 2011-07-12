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
        
        $response = sprintf("Ext.Direct.addProvider(%s);", $api);

        // @todo optional - if fos is not installed it will break this bundle
        $url = $this->container->get('router')->generate('fos_user_security_login');
        $response .= sprintf("
            Ext.ns('App.Direct'); 
            App.Direct.signinUrl = '%s';
            App.Direct.environment = '%s';
            ", $url, $this->container->getParameter("kernel.environment"));
        
        $r = new Response($response);
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
        $hasFiles = count($_FILES) > 0;

        
        if ($router->getRequest()->isFormCallType() && !$router->getRequest()->isXmlHttpRequest()) {
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
