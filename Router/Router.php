<?php

namespace Hatimeria\ExtJSBundle\Router;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Hatimeria\ExtJSBundle\Exception\ExtJSException;
use Hatimeria\ExtJSBundle\Response\Success;
use Hatimeria\ExtJSBundle\Response\Failure;
use Hatimeria\ExtJSBundle\Response\Response as ResponseInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Router is the ExtDirect Router class.
 *
 * It provide the ExtDirect Router mechanism.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class Router
{
    /**
     * The ExtDirect Request object.
     * 
     * @var Hatimeria\ExtJSBundle\Router\Request
     */
    protected $request;
    
    /**
     * The ExtDirect Response object.
     * 
     * @var Hatimeria\ExtJSBundle\Response
     */
    protected $response;
    
    /**
     * The application container.
     * 
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;
    
    /**
     * Initialize the router object.
     * 
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->request = new Request($container->get('request'));
        $this->response = new Response($this->request->getCallType());
    }

    /**
     * @return \Hatimeria\ExtJSBundle\Router\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Do the ExtDirect routing processing.
     *
     * @return JSON
     */
    public function route()
    {
        $batch = array();
        
        foreach ($this->request->getCalls() as $call) {
            $batch[] = $this->dispatch($call);
        }

        return $this->response->encode($batch);
    }

    /**
     * Dispatch a remote method call.
     * 
     * @param  Hatimeria\ExtJSBundle\Router\Call $call
     * @return Mixed
     */
    private function dispatch($call)
    {
        $controller = $this->resolveController($call->getAction());
        $method = $call->getMethod()."Action";

        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName("remote");
        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName("form");
        
        $event = new FilterControllerEvent($this->container->get("kernel"), array($controller, $method), $this->container->get('request'), 'GET');
        
        $listener = $this->container->get("security.extra.controller_listener");
        $listener->onCoreController($event);

        $controller = $event->getController();
        
        if (!is_callable($controller)) {
            throw new ExtJSException(sprintf("Controller %s method %s is not callable", get_class($controller), $method));
        }

        try
        {
            if($controller instanceof \Closure) {
                $response = $controller($call->getParams(), $this->request->getFiles());
            } else {
                $response = $controller[0]->$method($call->getParams(), $this->request->getFiles());
            }
            

            // default behavior - everything went fine
            if($response == null) {
                $response = new Success();
            }
            
            if(is_object($response)) {
                
                if($response instanceof ResponseInterface) {
                    $response = $response->toArray();
                } else {
                    $response = $this->container->get('hatimeria_extjs.dumper')->dump($response)->toArray();
                }
            }
            
            $result = $call->getResponse($response);
        }
                
        catch (NotFoundHttpException $e)
        {
            $result = $call->getResponse(array('success' => false, 'exception' => true, 'code' => $e->getStatusCode(), 'msg' => $e->getMessage()));
        }
        catch (AccessDeniedException $e)
        {
            $result = $call->getResponse(array(
                'success' => false,
                'exception' => true,
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'anonymous' => $this->container->get('security.context')->getToken() instanceof AnonymousToken
            ));
        }

        return $result;
    }

    /**
     * Resolve the called controller from action.
     * 
     * @param  string $action
     * @return <type>
     */
    private function resolveController($action)
    {
        list($bundleName, $controllerName) = explode('_',$action);
        $bundleName.= "Bundle";
        
        $bundle = $this->container->get('kernel')->getBundle($bundleName);
        $namespace = $bundle->getNamespace()."\\Controller";

        $class = $namespace."\\".$controllerName."Controller";

        try {
            $controller = new $class();

            if ($controller instanceof ContainerAware) {
                $controller->setContainer($this->container);
            }

            return $controller;
        } catch(Exception $e) {
            // @todo handle
        }
    }
}
