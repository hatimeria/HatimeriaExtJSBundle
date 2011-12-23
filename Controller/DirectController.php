<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Hatimeria\ExtJSBundle\Api\Api;
use Hatimeria\ExtJSBundle\Router\Router;

/**
 * Direct controller.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class DirectController extends Controller
{
    /**
     * Generate the ExtDirect API.
     * 
     * @return Response 
     */
    public function getApiAction()
    {
        $api = new Api($this->container, $this->get('request'));
        
        
        $response = sprintf("Ext.syncRequire(['Ext.direct.Manager','Ext.direct.RemotingProvider'], function() { 
                Ext.direct.Manager.addProvider(%s); 
            }, window); ", $api);
        // @todo move aditional content to direct parameters class 
        $response .= sprintf("
            Ext.ns('App.Direct'); 
            App.Direct.environment = '%s';
            ", $this->container->getParameter("kernel.environment"));

        $this->addSigninRoute($response);

        $r = new Response($response);
        $r->headers->set("Content-Type","text/javascript");
        
        return $r;
    }
    
    private function addSigninRoute(&$response)
    {
        $signinRoute = $this->container->getParameter('hatimeria_ext_js.signin_route');
        
        if($signinRoute) {
            $signinUrl = $this->container->get('router')->generate($signinRoute);
            $response.= sprintf("App.Direct.signinUrl = '%s'", $signinUrl);
        }
    }

    /**
     * Route the ExtDirect calls.
     *
     * @return Response
     */
    public function routeAction()
    {
        $router  = new Router($this->container);
        $request = $router->getRequest();
        $content = $router->route();
        
        if ($request->isFormCallType() && !$request->isXmlHttpRequest()) {
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
