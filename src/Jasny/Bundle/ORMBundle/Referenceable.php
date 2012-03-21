<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\ORMBundle;

/**
 * Interface to indicate an entity has a reference.
 * 
 * If the reference field doesn't exist the first unique field can be used as reference.
 */
interface Referenceable
{
    /**
     * Get reference.
     * 
     * @return string
     */
	public function getReference();
}
