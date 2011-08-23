<?php

namespace Hatimeria\ExtJSBundle\Dumper;

/**
 * Object mappings for dumper
 *
 * @author Michal Wujas
 */
class Mappings
{
    /**
     * Mappings config
     *
     * @var array
     */
    private $config;
    /**
     * Admin group key
     */
    const ADMIN_FIELD_GROUP = 'admin';
    /**
     * Default group key
     */
    const DEFAULT_FIELD_GROUP = 'default';
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function has($class, $group = self::DEFAULT_FIELD_GROUP)
    {
        return isset($this->config[$class]['fields'][$group]);
    }
    
    /**
     *
     * @param type $entityName
     * @return type 
     */
    public function get($class, $isAdmin)
    {
        $fields = array();
        
        if ($isAdmin) {
            // add admin fields if configuration has them
           if ($this->hasMapping($class, self::ADMIN_FIELD_GROUP)) {
               $fields += $this->getGroup($class, self::ADMIN_FIELD_GROUP);
           }
        }
        
        $fields += $this->getGroup($class, self::DEFAULT_FIELD_GROUP);
        
        return $fields;
    }
    
    private function getGroup($class, $group) 
    {
        return $this->config[$class]['fields'][$group];
    }
    
}
