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
     * Entity Manager
     *
     * @var EntityManager
     */
    private $em;
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
    public function __construct(EntityManager $em, $dumper, $camelizer)
    {
        $this->em        = $em;
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
    private function getNewPager(ParameterBag $params)
    {
        return new Pager($this->em, $params, $this->dumper, $this->camelizer);
    }

    /**
     * Creates pager from entity class
     *
     * @param string $entity Full entity class path
     * @param ParameterBag $params
     * 
     * @return Pager
     */
    public function fromEntity($entity, ParameterBag $params)
    {
        return $this->getNewPager($params)->setEntityName($entity);
    }
    
    /**
     * Creates pager from query builder
     *
     * @param QueryBuilder $qb
     * @param ParameterBag $params
     * 
     * @return Pager
     */
    public function fromQuery(QueryBuilder $qb, ParameterBag $params)
    {
        return $this->getNewPager($params)->setQueryBuilder($qb);
    }
}
