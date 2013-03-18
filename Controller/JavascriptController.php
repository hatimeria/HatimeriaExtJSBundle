<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Hatimeria\ExtJSBundle\DependencyInjection\HatimeriaExtJSExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use Hatimeria\ExtJSBundle\Annotation\Remote;
use Hatimeria\ExtJSBundle\Annotation\Form;

/**
 * Javascript controller
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
        // take first allowed locales if current is not in allowed ones
        if(!in_array($locale, $allowedLocales)) {
            $locale = $allowedLocales[0];
        }
        
        if($this->getParameter("compiled")) {
            return $this->compiledHeaders($locale);
        } else {
            return $this->allHeaders($locale);
        }
    }
    
    /**
     * Get all headers - not compiled version
     *
     * @param string $locale Locale
     * @return string
     */
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
    
    /**
     * Compiled headers
     *
     * @param string $locale Locale
     * @return string
     */
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
                    'paths' => $this->getParameter('loader'),
                    'compiled' => $this->getParameter('compiled')
                ));
    }
    
    /**
     * Variables for javascript
     * 
     * @return string
     */
    public function variablesAction()
    {
        $userData = array('roles' => array());
        $user = null;
        $sc = $this->get("security.context");
        
        if($sc->getToken() && is_object($sc->getToken()->getUser())) {
            $user = $sc->getToken()->getUser();
            $userData['roles'] = $user->getRoles();
            $userData['username'] = $user->getUsername();
            $userData['is_switched'] = $sc->isGranted("ROLE_PREVIOUS_ADMIN");
        }
        
        return $this->render('HatimeriaExtJSBundle:Javascript:variables.html.twig', 
                array(
                    'dev_mode' => $this->container->getParameter("kernel.debug"),
                    'user' => $userData
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
        
        return $this->render('HatimeriaExtJSBundle:Javascript:module.html.twig', array('class' => $class));
    }
    
    /**
     * Testing forms
     */
    public function formsTestAction()
    {
        return $this->render('HatimeriaExtJSBundle:Javascript:formsTest.html.twig');
    }
    
    /**
     * Receive data
     * @remote
     * @form
     * @param \Symfony\Component\HttpFoundation\ParameterBag $params
     */
    public function receiveTestDataAction($params)
    {
        return new \Hatimeria\ExtJSBundle\Response\Success();
    }
}
