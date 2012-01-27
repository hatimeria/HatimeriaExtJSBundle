<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Hatimeria\ExtJSBundle\Response\Response;
use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Util\Camelizer;
use Closure;

class Pager implements Response
{
    /**
     * Entity Manager
     * 
     * @var EntityManager
     */
    private $em;
    /**
     * Parameters
     *
     * @var ParameterBag
     */
    private $params;
    /**
     * Column name mapping
     *
     * @var array
     */
    private $mapping = array();
    /**
     * Column name => sorting closure
     *
     * @var array
     */
    private $sortFunctions = array();
    /**
     * Closure
     *
     * @var Closure
     */
    private $toStoreFunction = null;
    /**
     * Camelizer
     *
     * @var Camelizer
     */
    private $camelizer;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     */
    public function __construct(EntityManager $em, ParameterBag $params, $dumper, $camelizer)
    {
        $this->em        = $em;
        $this->params    = $params;
        $this->dumper    = $dumper;
        $this->camelizer = $camelizer;
    }
    
    public function setEntityClass($entityClass)
    {
        $this->qb = $this->em->createQueryBuilder();
        $this->qb->add('select', 'e');
        $this->qb->add('from', $entityClass . ' e');

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
    
    public function hasToStoreFunction()
    {
        return $this->toStoreFunction !== null;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
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
        
        $column = $this->camelizer->camelize($sort['property']);

        if (isset($this->sortFunctions[$column])) {
            return $this->sortFunctions[$column]($this->qb, $sort['direction']);
        }
        if (isset($this->mapping[$column])) {
            $column = $this->mapping[$column];
        }

        $this->qb->add('orderBy', 'e.' . $column . ' ' . $sort['direction']);
    }

    public function setSortFunction($column, $function)
    {
        $this->sortFunctions[$column] = $function;
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

        $this->limit = $this->params->getInt('limit', 10);
        $this->start = $this->params->getInt('start', 0);

        if ($this->params->has('page') && $this->params->get('page') > 0) {
            $offset = ($this->params->get('page') - 1) * $this->limit;
        } else {
            $offset = 0;
        }

        $query = $this->qb->getQuery();
        if($this->limit != 0) {
            $this->count = Paginate::getTotalQueryResults($query);
            $paginateQuery = Paginate::getPaginateQuery($query, $offset, $this->limit);
            $this->entities = $paginateQuery->getResult();
        } else {
            $this->entities = $query->getResult();
            $this->count = count($this->entities);
        }

        return $this->dumper->dump($this)->toArray();
    }
    
    public function getToStoreFunction()
    {
        return $this->toStoreFunction;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function getCount()
    {
        return $this->count;
    }
    
    public function getEntities()
    {
        return $this->entities;
    }

    public function getStart()
    {
        return $this->start;
    }

}
