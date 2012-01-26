<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Hatimeria\ExtJSBundle\Response\Failure;
use Hatimeria\ExtJSBundle\Annotation\Remote;
use JMS\SecurityExtraBundle\Annotation\Secure;

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
        $api = new Api($this->container);
        
        $response = sprintf("Ext.syncRequire(['Ext.direct.Manager','Ext.direct.RemotingProvider'], function() { 
                Ext.direct.Manager.addProvider(%s); 
                // fixes app_dev.php missing prefix
                Ext.direct.Manager.getProvider(0).url = Routing.prefix + Ext.direct.Manager.getProvider(0).url;
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
     * Quick entity pager based on configured name
     * 
     * @remote
     * @Secure("ROLE_ADMIN")
     *
     * @param ParameterBag $params 
     */
    public function listAction($params)
    {
        $lists = $this->container->getParameter('hatimeria_ext_js.exposed_lists');
        
        if(isset($lists[$params->get('name')])) {
            $class = $lists[$params->get('name')]['class'];
        } else {
            return new Failure("No exposed lists with name: ".$params->get('name'));
        }
        
        return $this->get('hatimeria_extjs.pager')->fromEntity($class, $params);
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
