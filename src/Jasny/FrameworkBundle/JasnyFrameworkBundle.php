<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\FrameworkBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * JasnyFrameworkBundle.
 *
 * @author Arnold Daniels <arnold@jasny.net>
 */
class JasnyFrameworkBundle extends Bundle
{
    public function getParent()
    {
        return 'FrameworkBundle';
    }    
}
