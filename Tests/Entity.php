<?php

namespace Hatimeria\ExtJSBundle\Tests;

/**
 * Example entity class
 *
 * @author Michal Wujas
 */
class Entity
{
    private $name;
    public $createdAt;
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->createdAt = new \DateTime("2011-01-01");
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getChild()
    {
        return new EntityChild('Bar');
    }
    
    public function isEnabled()
    {
        return true;
    }
}