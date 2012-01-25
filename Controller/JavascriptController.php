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
class JavascriptController extends Controller
{
    /**
     * All bundle html headers
     *
     * @return string
     */
    public function headersAction()
    {
        $allowedLocales = $this->getParameter('locales');
        $locale = $this->get('session')->getLocale();
        // take first allowed locales
        if(!in_array($locale, $allowedLocales)) {
            $locale = $allowedLocales[0];
        }
        
        if($this->getParameter("compiled")) {
            return $this->compiledHeaders($locale);
        } else {
            return $this->allHeaders($locale);
        }
    }
    
    private function allHeaders($locale)
    {
        return $this->render('HatimeriaExtJSBundle:Javascript:headers/all.html.twig',   
                array(
                    'main_filename' => $this->getParameter("js_filename"),
                    'javascript_vendor_path' => $this->getParameter("javascript_vendor_path"),
                    'locale'        => $locale,
                    'domains'       => $this->getParameter('translation_domains'),
                    'files'         => $this->getParameter('compiled_files'),
                ));        
    }
    
    private function compiledHeaders($locale)
    {
        return $this->render('HatimeriaExtJSBundle:Javascript:headers/compiled.html.twig', array('locale' => $locale));
    }
    
    /**
     * Dynamic javascript file
     *
     * @return string
     */
    public function dynamicAction()
    {
        return $this->render('HatimeriaExtJSBundle:Javascript:dynamic.js.twig', 
                array(
                    'disable_caching' => $this->getParameter('loader_disable_caching'),
                    'paths' => $this->getParameter('loader')
                ));
    }
    
    /**
     * Variables for javascript
     * 
     * @return string
     */
    public function variablesAction()
    {
        return $this->render('HatimeriaExtJSBundle:Javascript:variables.html.twig', 
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
     * Load default view which extends base or admin layout 
     * with javascript which creates provided class
     */    
    public function testClassAction($class)
    {
        $adminLayout = $this->getRequest()->query->has('admin');
        
        return $this->render('HatimeriaExtJSBundle:Javascript:test.html.twig', 
                array('class' => $class, 'adminLayout' => $this->getRequest()->query->has('admin'))
            );
    }
    
    /**
     * Load default view which extends base or admin layout 
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
        
        $adminLayout = $this->getRequest()->query->has('admin');
        
        return $this->render('HatimeriaExtJSBundle:Javascript:module.html.twig', array(
            'class' => $class, 'adminLayout' => $adminLayout));
    }
}