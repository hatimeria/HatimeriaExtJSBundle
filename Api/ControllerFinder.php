<?php
namespace Neton\DirectBundle\Api;

use Symfony\Component\Finder\Finder;
/**
 * Controller Finder find all controllers from a Bundle.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class ControllerFinder
{
    /**
     * Find all controllers from a bundle.
     * 
     * @param  \Symfony\HttpKernel\Bundle\Bundle $bundle
     * @return Mixed
     */
    public function getControllers($bundle)
    {
        $dir = $bundle->getPath()."\\Controller";
        $controllers = array();
        
        if (is_dir($dir)) {
            $finder = new Finder();            
            $finder->files()->in($dir)->name('*Controller.php');
            
            foreach ($finder as $file) {

                $name = explode('.',$file->getFileName());
                $class = $bundle->getNamespace()."\\Controller\\".$name[0];
                $controllers[] = $class;
            }
        }

        return $controllers;
    }
}
