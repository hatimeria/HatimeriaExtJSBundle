<?php

namespace Hatimeria\ExtJSBundle\Tests;

/**
 * Example entity class
 *
 * @author Michal Wujas
 */
class EntityChild
{
    private $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}