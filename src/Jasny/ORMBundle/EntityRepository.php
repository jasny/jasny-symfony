<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle;

use Doctrine\ORM\EntityRepository as DoctrineRepository;
use Doctrine\ORM\Mapping;
use Doctrine\DBAL\LockMode;

/**
 * EntityRepository.
 *
 * This class add commonly used functionality to the Doctrine ORM repository.
 */
class EntityRepository extends DoctrineRepository
{
    private $reference_field;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        if ($class->getReflectionClass()->implementsInterface(__NAMESPACE__ . '\Referenceable')) $this->initReferenceable($class);
    }

    /**
     * Initialise Referenceable entity
     * 
     * @param Mapping\ClassMetadata $class
     */
    private function initReferenceable(Mapping\ClassMetadata $class)
    {
        if (isset($class->fieldMappings['reference'])) {
            if (!$class->fieldMappings['reference']['unique']) trigger_error("Reference field for " . $this->_entityName . " entity should be unique.", E_USER_NOTICE);
            $this->reference_field = 'reference';
            return;
        } 
        
        foreach ($class->fieldMappings as $fieldname => $props) {
            if ($props['unique']) {
                $this->reference_field = $fieldname;
                return;
            }
        }

        trigger_error($this->_entityName . " entity incorrectly implements Referencable. I could not find a unique field", E_USER_WARNING);
    }
    
    /**
     * Build a simple query statement.
     * 
     * @param string $select
     * @param array  $criteria
     * @param int    $limit
     * @param int    $offset
     * @return Doctrine\ORM\Query
     */
    protected function buildQuery($select, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()->select($select)->from($this->getEntityName(), 'e');
        
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
     * Finds an entity by its primary key or reference.
     *
     * @param $id The identifier or reference.
     * @return object The entity.
     */
    public function load($id)
    {
        return isset($this->reference_field) ?
            parent::findOneBy(array($this->reference_field => $id)) :
            $this->find($id, $lockMode, $lockVersion);
    }
    
    /**
     * Finds first entity in the repository.
     *
     * @param array|null $orderBy
     * @return object The entity.
     */
    public function findFirst(array $orderBy = null)
    {
        return $this->findOneBy(array(), $orderBy);
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @return object The entity.
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        // Standard behaviour
        if (!isset($orderBy)) {
            return parent::findOneBy($criteria);
        }
        
        // With order by
        return $this->buildQuery('e', $criteria, $orderBy, 1)->getQuery()->getSingleResult();
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
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = null)
    {
        return $this->buildQuery('COUNT(e)', $criteria)->getQuery()->getSingleScalarResult();
    }
}
