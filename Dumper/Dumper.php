<?php

namespace Hatimeria\ExtJSBundle\Dumper;

use Hatimeria\ExtJSBundle\Exception\ExtJSException;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * Collection or Pager to Array conversion
 *
 * @author Michal Wujas
 */
class Dumper
{
    /**
     * Entity Manager
     *
     * @var EntityManager
     */
    private $em;
    /**
     * Signed user is an admin ?
     *
     * @var bool
     */
    private $isAdmin;
    /**
     * Camelizer
     *
     * @var Camelizer
     */
    private $camelizer;
    /**
     * Reflection class objects
     *
     * @var array of \ReflectionClass Objects
     */
    private $reflections;
    /**
     * Map of access methods for object and property (getter, isser or property)
     *
     * @var array
     */
    private $accessMethods;
    /**
     * Configured mappings for classes
     *
     * @var array
     */
    private $mappings;
    /**
     * Admin group key
     */
    const ADMIN_FIELD_GROUP = 'admin';
    /**
     * Default group key
     */    
    const DEFAULT_FIELD_GROUP = 'default';

    public function __construct($em, $security, $camelizer, $mappings)
    {
        $this->isAdmin = $security->getToken() ? $security->isGranted('ROLE_ADMIN') : false;
        $this->em = $em;
        $this->camelizer = $camelizer;
        $this->mappings = $mappings;
    }

    private function hasMapping($entityName)
    {
        return isset($this->mappings[$entityName]);
    }
    
    public function dumpObject($object, $fields = array())
    {
        $class = $this->getClass($object);
        
        if($this->hasMapping($class)) {
            return $this->getValues($object, $fields);
        } else {
            if(is_callable(array($object, 'toArray'))) {
                return $object->toArray();
            }
        }
        
        throw new ExtJSException(sprintf("No mapping information or object method toArray exists for %s", $class));
    }
    
    /**
     *
     * @param type $entityName
     * @return type 
     */
    private function getMappingFields($entityName)
    {
        $fields = array();
        
        if ($this->isAdmin) {
            // add admin fields if configuration has them
           if (isset($this->mappings[$entityName]['fields'][self::ADMIN_FIELD_GROUP])) {
               $fields += $this->getGroupMappingFields($entityName, self::ADMIN_FIELD_GROUP);
           }
        }
        
        $fields += $this->getGroupMappingFields($entityName, self::DEFAULT_FIELD_GROUP);
        
        return $fields;
    }
    
    private function getGroupMappingFields($entityName, $groupName) 
    {
        return $this->mappings[$entityName]['fields'][$groupName];
    }
    
    public function getObjectMappingFields($object)
    {
        $class = $this->getClass($object);

        if (!$this->hasMapping($class)) {
            throw new ExtJSException(sprintf("No dumper method for: %s", $class));
        }

        return $this->getMappingFields($class);
    }    

    /**
     * Convert array or array collection to ext js array used for store source
     *
     * @param array Array collection or array of entities $entities
     * @param int $count
     * @param int $limit
     *
     * @return array
     */
    public function dumpPager(Pager $pager)
    {
        if ($pager->hasToStoreFunction()) {
            return $this->dump($pager->getEntities(), $pager->getCount(), $pager->getLimit(), $pager->getToStoreFunction());
        }
        
        $fields = $pager->getFields();
        $records = array();

        foreach ($pager->getEntities() as $entity) {
            $records[] = $this->getValues($entity, $fields);
        }

        return $this->getResult($records, $pager->getCount(), $pager->getLimit());
    }

    /**
     * Dumps collection without limit
     *
     * @param array $entities
     * @param Closure $toStoreFunction
     * @return array
     */
    public function dumpCollection($entities, $toStoreFunction = null)
    {
        return $this->dump($entities, count($entities), 0, $toStoreFunction);
    }

    /**
     * Dumps collection with limit
     *
     * @param array $entities
     * @param int $count
     * @param int $limit
     * @param Closure $toStoreFunction
     * 
     * @return array
     */
    private function dump($entities, $count = null, $limit = null, $toStoreFunction = null)
    {
        $records = array();

        if (!empty($entities)) {
            foreach ($entities as $entity) {
                if (null === $toStoreFunction) {
                    $records[] = $this->dumpObject($entity);
                } else {
                    $records[] = $toStoreFunction($entity);
                }
            }
        }

        return $this->getResult($records, $count, $limit);
    }

    /**
     * Pager dump result in ExtJS format
     *
     * @param array $records
     * @param int $count
     * @param int $limit
     * @return array 
     */
    private function getResult($records, $count, $limit)
    {
        return array(
            'records' => $records,
            'success' => true,
            'total' => $count,
            'start' => 0,
            'limit' => $limit
        );
    }

    /**
     * How is accessed object property? by getter, isser or public property
     * Code from Symfony Form Component used - class PropertyPath
     *
     * @param Object $object
     * @param string $name
     * @return mixed array(methodName) or propertyName
     */
    private function getPropertyAccessMethod($object, $name)
    {
        $class = get_class($object);
        if (!isset($this->reflections[$class])) {
            $this->reflections[$class] = new \ReflectionClass($object);
        }

        $reflClass = $this->reflections[$class];
        $camelProp = $this->camelizer->camelize($name);
        $property = $camelProp;
        $getter = 'get' . $camelProp;
        $isser = 'is' . $camelProp;

        if ($reflClass->hasMethod($getter)) {
            if (!$reflClass->getMethod($getter)->isPublic()) {
                throw new ExtJSException(sprintf('Method "%s()" is not public in class "%s"', $getter, $reflClass->getName()));
            }

            return array($getter);
        } else if ($reflClass->hasMethod($isser)) {
            if (!$reflClass->getMethod($isser)->isPublic()) {
                throw new ExtJSException(sprintf('Method "%s()" is not public in class "%s"', $isser, $reflClass->getName()));
            }

            return array($isser);
        } else if ($reflClass->hasMethod('__get')) {
            // needed to support magic method __get
            return $object->$property;
        } else if ($reflClass->hasProperty($property)) {
            if (!$reflClass->getProperty($property)->isPublic()) {
                throw new ExtJSException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "%s()" or "%s()"?', $property, $reflClass->getName(), $getter, $isser));
            }

            return $property;
        } else if (property_exists($object, $property)) {
            // needed to support \stdClass instances
            return $property;
        }

        throw new ExtJSException(sprintf('Neither property "%s" nor method "%s()" nor method "%s()" exists in class "%s"', $property, $getter, $isser, $reflClass->getName()));
    }

    /**
     * Property value for given property name
     *
     * @example getPropertyValue($user, name)
     *  
     * @param Object $object
     * @param string $name
     * @return mixed 
     */
    private function getPropertyValue($object, $name)
    {
        $key = get_class($object) . $name;

        if (!isset($this->accessMethods[$key])) {
            $this->accessMethods[$key] = $this->getPropertyAccessMethod($object, $name);
        }

        $method = $this->accessMethods[$key];

        if (is_array($method)) {
            return $object->$method[0]();
        } else {
            return $object->$method;
        }
    }

    /**
     * Object value for given path 
     *
     * @param Object $object 
     * @param string $path
     * @return mixed
     */
    private function getPathValue($object, $path)
    {
        if (strpos($path, '.')) {
            $names = explode('.', $path);
            $property = array_shift($names);
            $value = $this->getPropertyValue($object, $property);

            return $this->getPathValue($value, implode('.', $names));
        }

        return $this->getPropertyValue($object, $path);
    }

    /**
     * Get object class
     * ignores doctrine proxy class
     *
     * @param Object $object
     * 
     * @return string Class name
     */
    private function getClass($object)
    {
        if ($object instanceof \Doctrine\ORM\Proxy\Proxy) {
            return get_parent_class($object);
        } else {
            return get_class($object);
        }
    }

    /**
     * Object values for properties paths
     *
     * @param Object $object
     * @param array $paths
     * 
     * @return array
     */
    public function getValues($object, $paths = array())
    {
        $values = array();

        if (count($paths) == 0) {
            $paths = $this->getObjectMappingFields($object);
        }

        foreach ($paths as $path) {
            $value = $this->getPathValue($object, $path);

            if (is_object($value)) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d');
                } else if ($value instanceof ArrayCollection) {
                    $records = array();

                    foreach ($value as $entity) {
                        $records[] = $this->getValues($entity);
                    }

                    $value = $records;
                } else {    
                    $value = $this->getValues($value);
                }
            }

            $values[$path] = $value;
        }

        return $values;
    }

}