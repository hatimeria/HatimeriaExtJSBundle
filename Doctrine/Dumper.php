<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

/**
 * Object Collection to Array conversion
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

    public function __construct($em, $security, $camelizer)
    {
        $this->isAdmin = $security->getToken() ? $security->isGranted('ROLE_ADMIN') : false;
        $this->em = $em;
        $this->camelizer = $camelizer;
    }

    private function getEntityToStoreFunction($entity)
    {
        $adminMethod = 'toAdminStoreArray';
        $defaultMethod = 'toStoreArray';

        // admin dumper method has precedence
        if ($this->isAdmin && is_callable(array($entity, $adminMethod))) {
            $method = $adminMethod;
        } else {
            if (!is_callable(array($entity, $defaultMethod))) {
                throw new ExtJSException(
                        sprintf("method %s in %s entity class doesn't exists", $defaultMethod, get_class($entity)));
            }

            $method = $defaultMethod;
        }

        return function($entity) use($method) {
                    return $entity->$method();
                };
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

    public function dumpCollection($entities, $toStoreFunction = null)
    {
        return $this->dump($entities, count($entities), 0, $toStoreFunction);
    }

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
                throw new \Exception(sprintf('Method "%s()" is not public in class "%s"', $getter, $reflClass->getName()));
            }

            return array($getter);
        } else if ($reflClass->hasMethod($isser)) {
            if (!$reflClass->getMethod($isser)->isPublic()) {
                throw new \Exception(sprintf('Method "%s()" is not public in class "%s"', $isser, $reflClass->getName()));
            }

            return array($isser);
        } else if ($reflClass->hasMethod('__get')) {
            // needed to support magic method __get
            return $object->$property;
        } else if ($reflClass->hasProperty($property)) {
            if (!$reflClass->getProperty($property)->isPublic()) {
                throw new \Exception(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "%s()" or "%s()"?', $property, $reflClass->getName(), $getter, $isser));
            }

            return $property;
        } else if (property_exists($object, $property)) {
            // needed to support \stdClass instances
            return $property;
        }

        throw new \Exception(sprintf('Neither property "%s" nor method "%s()" nor method "%s()" exists in class "%s"', $property, $getter, $isser, $reflClass->getName()));
    }

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

    public function getValues($entity, $fields)
    {
        $values = array();

        foreach ($fields as $fieldName) {
            $value = $this->getPathValue($entity, $fieldName);

            if (is_object($value)) {
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d');
                } 
                
                throw new \Exception(sprintf("Unknown object: %s", get_class($value)));
            }

            $values[$fieldName] = $value;
        }

        return $values;
    }

}