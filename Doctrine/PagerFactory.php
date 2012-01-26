<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Hatimeria\ExtJSBundle\Exception\ExtJSException; 

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

/**
 * Creates pager instance form different sources
 * 
 * @author Michal Wujas
 */
class PagerFactory
{
    /**
     * Doctrine
     *
     * @var Doctrine
     */
    private $doctrine;
    /**
     * Dumper
     *
     * @var Dumper
     */
    private $dumper;
    /**
     * Camelizer
     *
     * @var Camelizer
     */
    private $camelizer;

    /**
     * Constructor
     *
     * @param EntityManager           $em
     * @param Dumper $dumper
     * @param Camelizer $camelizer
     */
    public function __construct($doctrine, $dumper, $camelizer)
    {
        $this->doctrine  = $doctrine;
        $this->dumper    = $dumper;
        $this->camelizer = $camelizer;
    }
    
    /**
     * Pager creation
     *
     * @param ParameterBag $params
     * 
     * @return Pager 
     */
    private function getNewPager(ParameterBag $params, $em)
    {
        return new Pager($em, $params, $this->dumper, $this->camelizer);
    }

    /**
     * Creates pager from entity class
     *
     * @param string $entity Full entity class path
     * @param ParameterBag $params
     * 
     * @return Pager
     */
    public function fromEntity($entityClass, ParameterBag $params)
    {
        return $this->getNewPager($params, $this->doctrine->getEntityManagerForClass($entityClass))->setEntityClass($entityClass);
    }
    
    /**
     * Creates pager from query builder or query
     *
     * @param QueryBuilder $qb
     * @param ParameterBag $params
     * 
     * @return Pager
     */
    public function fromQuery(QueryBuilder $qb, ParameterBag $params)
    {
        return $this->getNewPager($params, $this->doctrine->getEntityManager())->setQueryBuilder($qb);
    }
}
