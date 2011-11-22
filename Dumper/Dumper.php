<?php

namespace Hatimeria\ExtJSBundle\Dumper;

use Hatimeria\ExtJSBundle\Exception\ExtJSException;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Hatimeria\ExtJSBundle\Dumper\Mappings;
use Hatimeria\ExtJSBundle\Response\Records;
use Hatimeria\ExtJSBundle\Response\Success;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

use \DateTime;

/**
 * Collection, Pager or Object to Array conversion
 * Configurable paths for object properties
 *
 * @author Michal Wujas
 */
class Dumper
{
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
     * @var Mappings
     */
    private $mappings;

    public function __construct($security, $camelizer, $mappings)
    {
        $this->isAdmin   = is_object($security->getToken()) ? $security->isGranted('ROLE_ADMIN') : false;
        $this->camelizer = $camelizer;
        $this->mappings  = $mappings;
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
    
    private function getMappings($object)
    {
        $class = $this->getClass($object);

        if (!$this->mappings->has($class)) {
            throw new ExtJSException(sprintf("No dumper method for: %s", $class));
        }

        return $this->mappings->get($class, $this->isAdmin);
    }        
    
    private function dumpObject($object, $fields = array())
    {
        $class = $this->getClass($object);
        
        if($this->mappings->has($class)) {
            return $this->getValues($object, $fields);
        } else {
            if(is_callable(array($object, 'toArray'))) {
                return $object->toArray();
            }
        }
        
        throw new ExtJSException(sprintf("No mapping information or object method toArray exists for %s", $class));
    }

    /**
     * Dump resource
     * Supported: array of objects, object, instance of Pager
     *
     * @param mixed $resource
     * 
     * @return Response
     */
    public function dump($resource)
    {
        $isPager = $resource instanceof Pager;
        $isArray = is_array($resource);
        
        if($isPager || $isArray) {
            
            $r = new Records();
            $toStoreFunction = null;
            
            if($isPager) {
                $entities = $resource->getEntities();
                $toStoreFunction = $resource->getToStoreFunction();
            } else {
                $entities = $resource;
            }
            
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
            
            $r->result($records);
            
            if($isPager) {
                $r->limit($resource->getLimit());
                $r->total($resource->getCount());
                $r->start($resource->getStart());
            }
            
            return $r;
        } elseif ($resource instanceof ArrayCollection || $resource instanceof PersistentCollection) {
            $records = array();

            foreach ($resource as $element) {
                $records[] = $this->dumpObject($element);
            }
            $r = new Success();
            $r->set("record", $records);
        } elseif (is_object($resource)) {
            $r = new Success();
            $r->set("record", $this->dumpObject($resource));
            // for BasicForm api load function compability
            $r->set("data", $this->dumpObject($resource));
        } else {
            $r = new Success();
            $r->set("record", $resource);
        }
        
        return $r;
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
            $paths = $this->getMappings($object);
        }

        foreach ($paths as $path) {
            $value = $this->getPathValue($object, $path);

            if (is_object($value)) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                } else if ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
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