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
        $path = $this->getParameter("js_filename");
        
        return $this->render('HatimeriaExtJSBundle:Default:headers.html.twig', 
                array(
                    'js_filename' => $path,
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