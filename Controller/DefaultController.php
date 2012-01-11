<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Hatimeria\ExtJSBundle\DependencyInjection\HatimeriaExtJSExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;

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
        $sufix = 'default';
        if($this->getParameter("compiled")) {
            $allowedLocales = $this->getParameter('locales');
            $sufix = $this->container->getParameter("locale");
            // take first allowed locales
            if(!in_array($sufix, $allowedLocales)) {
                $sufix = $allowedLocales[0];
            }
        }
        
        return $this->render('HatimeriaExtJSBundle:Default:headers.html.twig',   
                array(
                    'main_filename' => $this->getParameter("js_filename"),
                    'javascript_vendor_path' => $this->getParameter("javascript_vendor_path"),
                    'sufix'      => $sufix,
                    'locale'        => $this->container->getParameter("locale"),
                    'compiled'      => $this->getParameter("compiled"),
                ));
    }
    
    /**
     * Dynamic javascript file
     *
     * @return string
     */
    public function dynamicAction()
    {
        return $this->render('HatimeriaExtJSBundle:Default:dynamic.js.twig', 
                array(
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
        
        if($this->getRequest()->query->has('test')) {
            $testClass = $this->getRequest()->query->get('test');
        } else {
            $testClass = false;
        }
        
        return $this->render('HatimeriaExtJSBundle:Default:module.html.twig', array('class' => $class, 'testClass' => $testClass));
    }
}