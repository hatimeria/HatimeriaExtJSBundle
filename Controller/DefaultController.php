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
    public function javascriptsAction()
    {
        $path = $this->getParameter("js_filename");
        
        return $this->render('HatimeriaExtJSBundle:Default:javascripts.html.twig', 
                array(
                    'js_filename' => $path,
                    'locale'      => $this->container->getParameter("locale")
                ));
    }
    
    private function getParameter($key)
    {
        return $this->container->getParameter(HatimeriaExtJSExtension::CONFIG_NAMESPACE.'.'.$key);
    }    
}