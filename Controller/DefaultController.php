<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Hatimeria\ExtJSBundle\DependencyInjection\HatimeriaExtJSExtension;

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
                    'locale'      => $this->container->getParameter("locale")
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
}