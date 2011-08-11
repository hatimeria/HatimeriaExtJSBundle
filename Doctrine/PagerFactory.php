<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Hatimeria\ExtJSBundle\Exception\ExtJSException; 

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class PagerFactory
{
    private $em;
    
    private $dumper;
    
    private $camelizer;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     */
    public function __construct(EntityManager $em, $dumper, $camelizer)
    {
        $this->em        = $em;
        $this->dumper    = $dumper;
        $this->camelizer = $camelizer;
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
        return new Pager($this->em, $params, $this->dumper, $this->camelizer);
    }
    
    public function fromQuery(QueryBuilder $qb, ParameterBag $params)
    {
        return $this->getNewPager($params)->setQueryBuilder($qb);
    }
}
