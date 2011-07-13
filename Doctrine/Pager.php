<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Closure;

class Pager
{
    private $entity, $em;
    /**
     * Parameters
     *
     * @var ParameterBag
     */
    private $params;
    
    private $mapping = array();
    
    private $toStoreFunction = null;
    
    private $factory;
    
    /**
     * Constructor.
     *
     * @param EntityManager           $em
     */
    public function __construct(EntityManager $em, $params, $factory)
    {
        $this->em      = $em;
        $this->params  = $params;
        $this->factory = $factory;
    }
    
    public function setEntityName($entity) 
    {
        $this->entity  = $entity;        
        $this->qb      = $this->em->createQueryBuilder();
        $this->qb->add('select', 'e');
        $this->qb->add('from', $this->entity . ' e');
        
        return $this;
    }
    
    public function addColumnAlias($column, $alias)
    {
        $this->mapping[$column] = $alias;
    }
    
    public function setToStoreFunction($function)
    {
        $this->toStoreFunction = $function;
    }
    
    public function getQueryBuilder()
    {
        return $this->qb;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    private function addSort()
    {
        $sort = $this->params['sort'][0];

        // change birthday_at to birthdayAt
        // @todo move to util class
        $column = lcfirst(preg_replace('/(^|_|-)+(.)/e', "strtoupper('\\2')", $sort['property']));

        if (isset($this->mapping[$column])) {
            $column = $this->mapping[$column];
        }

        $this->qb->add('orderBy', 'e.' . $column . ' ' . $sort['direction']);        
    }
    
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;
        
        return $this;
    }    

    /**
     * Paginated resultset in ext direct format
     *
     * @param Query $query
     *
     * @return array data in ext direct format
     */
    public function toArray()
    {
        if ($this->params->has('sort')) {
            $this->addSort();
        }

        $limit = $this->params->getInt('limit', 10);

        if ($this->params->has('page')) {
            $offset = ($this->params->get('page') - 1) * $limit;
        } else {
            $offset = 0;
        }

        $query = $this->qb->getQuery();
        $count = Paginate::getTotalQueryResults($query);
        $paginateQuery = Paginate::getPaginateQuery($query, $offset, $limit);
        $entities = $paginateQuery->getResult();

        return $this->factory->collectionToArray($entities, $count, $limit, $this->toStoreFunction);
    }
}
