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

use Jasny\ORMBundle\Properties\Persistable;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * Service to automatically persist a Persistable property on entity persist.
 */
class PropertyPersister implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('postPersist', 'postUpdate', 'postRemove');
    }
    
    /**
     * Function for postPersist event.
     * 
     * @param LifecycleEventArgs $ea
     */
	public function postPersist(LifecycleEventArgs $ea)
	{
        $this->persistProperties($ea->getEntity());
    }

    /**
     * Function for postUpdate event.
     * 
     * @param LifecycleEventArgs $ea
     */
	public function postUpdate(LifecycleEventArgs $ea)
	{
        $this->persistProperties($ea->getEntity());
    }
    
    /**
     * Function for postUpdate event.
     * 
     * @param LifecycleEventArgs $ea
     */
	public function postRemove(LifecycleEventArgs $ea)
	{
        $this->removeProperties($ea->getEntity());
    }


    /**
     * Persist all Persistable properties of the entity.
     * 
     * @param object $entity
     */
	protected function persistProperties($entity)
	{
        // We're walking through all public and private properties, not using getters
        foreach ((array)$entity as $value) {
            if ($value instanceof Persistable) {
                $value->persist();
            }
        }
	}

    /**
     * Delete all Persistable properties of the entity.
     * 
     * @param object $entity
     */
	protected function removeProperties($entity)
	{
        // We're walking through all public and private properties, not using getters
        foreach ((array)$entity as $key => $value) {
            if ($value instanceof Persistable) {
                $value->persistRemove();
            }
        }
	}
}
