<?php
/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORM;

use Doctrine\ORM\EntityRepository as DoctrineRepository;

/**
 * EntityRepository.
 *
 * This class add commonly used functionality to the Doctrine ORM repository.
 */
class EntityRepository extends DoctrineRepository
{
    /**
     * Build a simple query statement.
     * 
     * @param array  $criteria
     * @param int    $limit
     * @param int    $offset
     * @return Doctrine\ORM\QueryBuilder
     */
    protected function buildQuery(array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()->select('e')->from($this->getEntityName(), 'e');
        
        if (!empty($criteria)) {
            $where = array();
            foreach (array_keys($criteria) as $i => $field) {
                $where[] = $qb->expr()->eq($field, '?' . $i);
            }
            $qb->where(call_user_func_array(array($db->expr(), 'andx'), $where));
            $qb->setParameters(array_values($criteria));
        }
        
        if (!empty($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->orderBy($sort, $order);
            }
        }
        
        if (isset($limit)) $qb->setMaxResults($limit);
        if (isset($offset)) $qb->setFirstResult($offset);
        
        return $qb;
    }
    
    /**
     * Finds all entities in the repository.
     *
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array The entities.
     */
    public function findAll(array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->findBy(array(), $orderBy, $limit, $offset);
    }
    
    /**
     * Count all entities in the repository.
     *
     * @return int
     */
    public function count(array $criteria = null)
    {
        return $this->buildQuery($criteria)->getMaxResults();
    }
}
