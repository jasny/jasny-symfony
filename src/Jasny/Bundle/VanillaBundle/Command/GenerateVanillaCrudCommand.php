<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\VanillaBundle\Command;

use Jasny\Bundle\GeneratorBundle\Command\GenerateCrudCommand;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateVanillaCrudCommand extends GenerateCrudCommand
{
    public function getResourcesDir()
    {
        return __DIR__.'/../Resources';
    }

    public function getBundleName()
    {
        return 'vanilla';
    }
    
    protected function getDefaultActions()
    {
        return array('list', 'show');
    }
}
