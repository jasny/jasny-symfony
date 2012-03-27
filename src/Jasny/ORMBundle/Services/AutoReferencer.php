<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Services;

use Jasny\ORMBundle\AutoReferencing;
use Jasny\ORMBundle\Helpers\AutoReferenceGenerator;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Service for AutoReference entities.
 */
class AutoReferencer
{
    /**
     * @var AutoReferenceGenerator
     */
    protected $generator;

    /**
     * @param generator $generator
     */
    public function __construct(AutoReferenceGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Set reference before persist.
     * 
     * @param LifecycleEventArgs $ea
     */
	public function prePersist(LifecycleEventArgs $ea)
	{
		if ($ea->getEntity() instanceof AutoReferencing) {
			$entity = $ea->getEntity();
			$repository = $ea->getEntityManager()->getRepository(get_class($entity));

			$reference = $this->generateReference($entity, $repository, $entity->getReference());
        	$entity->setReference($reference);
		}
	}

    /**
     * Fix modified reference before persist.
     * 
     * @param LifecycleEventArgs $ea
     */
	public function preUpdate(PreUpdateEventArgs $ea)
	{
		if ($ea->getEntity() instanceof AutoReferencing) {
			$entity = $ea->getEntity();
			$repository = $ea->getEntityManager()->getRepository(get_class($entity));
            
            if ($ea->hasChangedField('reference')) {
                $reference = $this->generateReference($entity, $repository, $ea->getNewValue('reference'));
                $ea->setNewValue('reference', $reference);
                $entity->setReference($reference);
            }
		}
	}
    

    /**
     * generateReference a unique reference for the entity.
     * 
     * @param AutoReferenceInterface $entity
     * @param EntityRepository $repository
     * @param string $value
     */
	public function generateReference(AutoReferencing $entity, EntityRepository $repository, $value=null)
	{
		// Find a reference
        $eliminated = array(); // Our prior eliminated references
        $found = false;
        
        $call = empty($value) && method_exists($entity, 'getReferenceSource');
        if (empty($value) && !$call) $value = (string)$entity;
        $i = 0;
        
		do {
            // If entity is passed get reference source
            if ($call) $value = $entity->getReferenceSource($i++);
            
            // Obtain our reference
            $reference = $this->generator->generate($value, $eliminated);

            // See if it is in our collection
            $result = $repository->findOneBy(array('reference' => $reference));

            // Check to see if we have found a reference that matches
            if (!empty($result) && $result !== $entity) {
                $eliminated[] = $reference;
            } else {
                // We have found a reference for this element
                $found = true;
            }
		} while ($found === false);

        return $reference;
	}
}
