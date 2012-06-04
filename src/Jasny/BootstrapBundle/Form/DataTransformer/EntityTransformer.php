<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\BootstrapBundle\Form\DataTransformer;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

/**
 * Transform an ID to an entity without a choice list
 */
class EntityTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager 
     */
    private $em;
    
    private $class;
    
    private $addable;
    
    public function __construct(EntityManager $em, $class, $addable = false)
    {
        $this->em = $em;
        $this->class = $class;
        $this->addable = $addable;
    }

    /**
     * Do nothing.
     *
     * @param Collection|object $entity A collection of entities, a single entity or NULL
     *
     * @return Collection|object
     */
    public function transform($entity)
    {
        return $entity;
    }

    /**
     * Get entity for id.
     *
     * @param  mixed $id   An array of ids, a single id or NULL
     *
     * @return Collection|object  A collection of entities, a single entity or NULL
     */
    public function reverseTransform($id)
    {
        if ('' === $id || null === $id) {
            return null;
        }

        if (is_object($id) || (is_array($id) && is_string(key($id)))) {
            if (!$this->addable) throw new UnexpectedTypeException($id, 'scalar');
            
            $entity = new $this->class;
            foreach ($id as $key=>$value) {
                $fn = 'set' . str_replace('_', '', $key);
                if (method_exists($entity, $fn)) $entity->$fn($value);
            }
            
            return $entity;
        }
        
        $entity = $this->em->getRepository($this->class)->find($id);
        if (!$entity) throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $id));

        return $entity;
    }
}
