<?php

namespace Hatimeria\ExtJSBundle\Doctrine;

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\SecurityContext;

class Pager
{
	/**
	 * Constructor.
	 *
	 * @param EntityManager           $em
	 */
	public function __construct(EntityManager $em, SecurityContext $s)
	{
		$this->em = $em;
		$this->security = $s;
	}

	/**
	 * Paginated resultset in ext direct format
	 *
	 * @param Query $query
	 *
	 * @return array data in ext direct format
	 */
	public function getResults($entity, array $params = array(), array $mapping = array(), $filter = null)
	{
		$qb = $this->em->createQueryBuilder();
		$qb->add('select','e');
		$qb->add('from', $entity.' e');

		if($filter != null) {
			$filter($qb);
		}

		if(isset($params['sort'])) {
			$sort = $params['sort'][0];

			// change birthday_at to birthdayAt
			// @todo move to util class
			$column = lcfirst(preg_replace('/(^|_|-)+(.)/e',"strtoupper('\\2')", $sort['property']));

			if(isset($mapping[$column]))
			{
				$column = $mapping[$column];
			}

			$qb->add('orderBy', 'e.'.$column.' '.$sort['direction']);
		}

		$query = $qb->getQuery();
		$limit = 10;
		if(isset($params['limit']))
		{
			$limit = $params['limit'];
		}

		if(isset($params['page'])) {
			$offset = ($params['page'] - 1) * $limit;
		} else {
			$offset = 0;
		}

		$count = Paginate::getTotalQueryResults($query);
		$paginateQuery = Paginate::getPaginateQuery($query, $offset, $limit);
		$entities = $paginateQuery->getResult();

		return $this->collectionToArray($entities, $count, $limit);
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
	public function collectionToArray($entities, $count = null, $limit = null)
	{
		$records = array();

		
		foreach($entities as $entity) {
			$records[] = $entity->toStoreArray($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_USER'));
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
