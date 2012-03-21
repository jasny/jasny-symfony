<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Helpers;

/**
 * Interface for objects that can create a reference.
 */
interface AutoReferenceGenerator
{
	/**
	 * Return a slug, ensuring it does not appear in exclude (prior collisions).
     * 
	 * @param array|string $value
	 * @param array        $exclude  List of slugs to exclude
	 */
	public function generate($value, array $exclude = array());
}
