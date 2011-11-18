<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Hatimeria\ExtJSBundle\DependencyInjection\HatimeriaExtJSExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Default Controller
 *
 * @author Michal Wujas
 */
class DefaultController extends Controller
{
    /**
     * All bundle html headers
     *
     * @return string
     */
    public function headersAction()
    {
        
        return $this->render('HatimeriaExtJSBundle:Default:headers.html.twig', 
                array(
                    'main_filename' => $this->getParameter("js_filename"),
                    'javascript_vendor_path' => $this->getParameter("javascript_vendor_path"),
                    'locale'      => $this->container->getParameter("locale"),
                    'disable_caching' => $this->getParameter('loader_disable_caching')
                ));
    }
    
    /**
     * Variables for javascript
     * 
     * @return string
     */
    public function variablesAction()
    {
        return $this->render('HatimeriaExtJSBundle:Default:variables.html.twig', 
                array(
                    'dev_mode' => $this->container->getParameter("kernel.debug")
                ));
    }
    
    /**
     * Bundle parameter
     *
     * @param string $key
     * 
     * @return mixed
     */
    private function getParameter($key)
    {
        return $this->container->getParameter(HatimeriaExtJSExtension::CONFIG_NAMESPACE.'.'.$key);
    }    
    
    /**
     * Load default view which extends base layout 
     * with javascript which creates provided from configuration extjs class 
     */
    public function initModuleAction($name)
    {
        $class = false;
        
        if($this->container->hasParameter("extjs_init_modules")) {
            $modules = $this->container->getParameter("extjs_init_modules");
            if(isset($modules[$name])) {
                $class = $modules[$name];
            }
        } 
        
        if($class === false) {
            throw new NotFoundHttpException(sprintf("No extjs module assigned to route %s", $name));
        }
        
        return $this->render('HatimeriaExtJSBundle:Default:module.html.twig', array('class' => $class));
    }
}