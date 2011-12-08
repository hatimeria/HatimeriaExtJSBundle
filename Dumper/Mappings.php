<?php

namespace Hatimeria\ExtJSBundle\Dumper;

use Hatimeria\ExtJSBundle\Dumper\MappingsProvider;

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
     * Collection of mappings providers
     * @var array
     */
    protected $providers = array();

    protected $inited = false;
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
        $this->init();

        return isset($this->config[$class]['fields'][$group]);
    }
    
    /**
     *
     * @param type $entityName
     * @return type 
     */
    public function get($class, $isAdmin)
    {
        $this->init();

        $fields = array();
        
        if ($isAdmin) {
            // add admin fields if configuration has them
           if ($this->has($class, self::ADMIN_FIELD_GROUP)) {
               $fields = array_merge($fields, $this->getGroup($class, self::ADMIN_FIELD_GROUP));
           }
        }
        
        $fields = array_merge($fields, $this->getGroup($class, self::DEFAULT_FIELD_GROUP));
        
        return $fields;
    }
    
    private function getGroup($class, $group) 
    {
        return $this->config[$class]['fields'][$group];
    }

    /**
     * Adds mapping provider
     *
     * @param MappingsProvider $provider
     */
    public function addMappingsProvider($provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Mappings providers initialisation.
     */
    protected function init()
    {
        if ($this->inited) {
            return;
        }
        $this->inited = true;
        foreach ($this->providers as $provider) {
            /* @var \Hatimeria\ExtJSBundle\Dumper\MappingsProvider $provider */
            $config = $provider->getMappings();
            if (!is_array($config)) {
                continue;
            }
            foreach ($config as $class => $groups) {

                if (!isset($groups['fields'])) {
                    continue;
                }
                foreach ($groups['fields'] as $group => $fields) {
                    $this->mergeConfig($class, $group, $fields);
                }
            }
        }
    }

    /**
     * Merges fields of class and group into main configuration
     *
     * @param string $class
     * @param string $group
     * @param array $fields
     */
    protected function mergeConfig($class, $group, $fields)
    {
        $base = (isset($this->config[$class]['fields'][$group])) ? $this->config[$class]['fields'][$group] : array();

        if (!is_array($fields)) {
            $fields = array($fields);
        }

        $this->config[$class]['fields'][$group] = array_merge($base, $fields);
    }

}
