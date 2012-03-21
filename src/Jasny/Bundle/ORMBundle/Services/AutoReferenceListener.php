<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\ORMBundle\Services;

use Jasny\Bundle\ORMBundle\AutoReferencing;
use Jasny\Bundle\ORMBundle\Helpers\AutoReferenceGenerator;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Service for AutoReference entities.
 */
class AutoReferenceListener
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

            $value = $entity->getReference() ?: $entity->getReferenceSource();
			$reference = $this->generateReference($entity, $repository, $value);
            
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
                $value = $ea->getNewValue('reference') ?: $entity->getReferenceSource();
                $reference = $this->generateReference($entity, $repository, $value);
                
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
	public function generateReference(AutoReferencing $entity, EntityRepository $repository, $value)
	{
		// Find a reference
        $eliminated = array(); // Our prior eliminated references
        $found = false;
        
		do {
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
