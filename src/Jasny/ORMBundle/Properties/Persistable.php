<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Properties;

/**
 * Interface to indicate that property should be persisted when entity is persisted.
 */
interface Persistable
{
	/**
	 * Persist object.
	 */
	public function persist();

	/**
	 * Delete object.
	 */
	public function persistRemove();
}
