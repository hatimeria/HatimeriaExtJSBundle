<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;
use Hatimeria\ExtJSBundle\Exception\ExtJSException;

/**
 * Collection or Pager to Array conversion
 *
 * @author Michal Wujas
 */
class Dumper
{
    private $em;
    private $isAdmin;
    private $camelizer;
    private $reflections;
    private $accessMethods;
    private $mappings;

    public function __construct($em, $security, $camelizer, $mappings)
    {
        $this->isAdmin = $security->getToken() ? $security->isGranted('ROLE_ADMIN') : false;
        $this->em = $em;
        $this->camelizer = $camelizer;
        $this->mappings  = $mappings;
    }

    /**
     * Which function use to dump every object in collection
     * Use admin method if he is signed in or default to store method
     * 
     * @param Object $entity
     */
    private function getEntityToStoreFunction($object)
    {
        $adminMethod = 'toAdminStoreArray';
        $defaultMethod = 'toStoreArray';

        // admin dumper method has precedence
        if ($this->isAdmin && is_callable(array($object, $adminMethod))) {
            $method = $adminMethod;
        } else {
            if (!is_callable(array($object, $defaultMethod))) {
                throw new ExtJSException(
                        sprintf("method %s in %s entity class doesn't exists", $defaultMethod, get_class($object)));
            }

            $method = $defaultMethod;
        }

        return function($object) use($method) { return $object->$method(); };
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
        $time_start = microtime(true);
        if ($pager->hasFields()) {

            $fields = $pager->getFields();
            $records = array();

            foreach ($pager->getEntities() as $entity) {
                $records[] = $this->getValues($entity, $fields);
            }
            
            return $this->getResult($records, $pager->getCount(), $pager->getLimit());
        } 

        return $this->dump($pager->getEntities(), $pager->getCount(), $pager->getLimit(), $pager->getToStoreFunction());
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
            $hasCustomDumper = null !== $toStoreFunction;

            if ($hasCustomDumper) {
                $dumper = $toStoreFunction;
            } else {
                $dumper = $this->getEntityToStoreFunction($entities[0]);
            }

            foreach ($entities as $entity) {
                $records[] = $dumper($entity);
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
     * Object values for list of properties (fields)
     *
     * @param Object $entity
     * @param array $fields
     * @return array
     */
    public function getValues($entity, $fields)
    {
        $values = array();

        foreach ($fields as $fieldName) {
            $value = $this->getPathValue($entity, $fieldName);

            if (is_object($value)) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d');
                } else if ($value instanceof Doctrine\Common\Collections\ArrayCollection) {
                    $records = array();
                    
                    foreach($value as $entity) {
                        $records[] = $entity->toArray();
                    }
                    
                    $value = $records;
                } else {
                    
                    $class = get_class($value);
                    
                    if(isset($this->mappings[$class])) {
                        $value = $this->getValues($value, $this->mappings[$class]['fields']);
                    } else {
                        throw new ExtJSException(sprintf("Unknown object: %s", $class));
                    }
                }
            }

            $values[$fieldName] = $value;
        }

        return $values;
    }

}