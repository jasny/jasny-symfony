<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\VanillaBundle\Command;

use Jasny\GeneratorBundle\Command\GenerateFormCommand;

/**
 * Generates a CRUD for a Doctrine entity with support for Vanilla.
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateVanillaFormCommand extends GenerateFormCommand
{
    public function getResourcesDir()
    {
        return __DIR__.'/../Resources';
    }

    public function getBundleName()
    {
        return 'vanilla';
    }
}
