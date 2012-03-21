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
 * Interface to indicate that this is a geometric object
 */
interface Geom
{
    /**
     * Cast object to string (for DQL)
     * 
     * @return string
     */
    public function __toString();
}