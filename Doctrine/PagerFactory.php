<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Doctrine\Pager;
use Hatimeria\ExtJSBundle\Exception\ExtJSException;

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
        $this->isAdmin  = $security->isGranted('ROLE_ADMIN');
    }

    /**
     * Paginated resultset in ext direct format
     *
     * @param Query $query
     *
     * @return array data in ext direct format
     */
    public function create($entity, ParameterBag $params)
    {
        return new Pager($this->em, $entity, $params, $this);
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
            
            $dumper       = function($entity) { return $entity->toStoreArray(); };
            $customDumper = null !== $toStoreFunction;
            $firstEntity  = $entities[0];
            
            // admin dumper method has precedence
            if (!$customDumper && $this->isAdmin && is_callable(array($firstEntity, 'toAdminStoreArray'))) {
                $dumper = function($entity) { return $entity->toAdminStoreArray(); };
            } elseif ($customDumper) {
                $dumper = $customDumper;
            } else {
                if (!is_callable(array($firstEntity, 'toStoreArray'))) {
                    throw new ExtJSException(
                            sprintf("method toStoreArray in %s entity class doesn't exists", get_class($firstEntity)));
                }
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
