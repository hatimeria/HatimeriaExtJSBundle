<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Hatimeria\ExtJSBundle\Exception\ExtJSException; 

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class PagerFactory
{
    private $isAdmin, $em;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     */
    public function __construct(EntityManager $em, $security)
    {
        $this->em       = $em;
        $this->isAdmin  = $security->getToken() ? $security->isGranted('ROLE_ADMIN') : false;
    }

    // shortcut, depracated
    public function create($entity, ParameterBag $params)
    {
        return $this->fromEntity($entity, $params);
    }
    
    public function fromEntity($entity, ParameterBag $params)
    {
        return $this->getNewPager($params)->setEntityName($entity);
    }
    
    private function getNewPager(ParameterBag $params)
    {
        return new Pager($this->em, $params, $this);
    }
    
    public function fromQuery(QueryBuilder $qb, ParameterBag $params)
    {
        return $this->getNewPager($params)->setQueryBuilder($qb);
    }
    
    private function getDumper($entity)
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
        
        return function($entity) use($method) { return $entity->$method(); };
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
    public function collectionToArray($entities, $count = null, $limit = null, $toStoreFunction = null)
    {
        $records = array();
        
        if(!empty($entities)) {
            $hasCustomDumper = null !== $toStoreFunction;
            
            if($hasCustomDumper) {
                $dumper = $toStoreFunction;
            } else {
                $dumper = $this->getDumper($entities[0]);
            }
            
            foreach ($entities as $entity) {
                $records[] = $dumper($entity);
            }
        }
        
        if ($count == null) {
            $count = count($records);
        }        

        return array(
            'records' => $records,
            'success' => true,
            'total' => $count,
            'start' => 0,
            'limit' => $limit ? $limit : 0
        );
    }    
}
