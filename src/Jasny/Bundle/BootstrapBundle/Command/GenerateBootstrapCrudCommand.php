<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\BootstrapBundle\Command;

use Jasny\Bundle\GeneratorBundle\Command\GenerateCrudCommand;

/**
 * Generates a CRUD for a Doctrine entity with support for Bootstrap.
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateBootstrapCrudCommand extends GenerateCrudCommand
{
    public function configure()
    {
        parent::configure();

        $command = $this->getName();
        
        $this
            ->setHelp(<<<EOT
The <info>$command</info> command generates a CRUD based on a Doctrine entity.
The views will be formatted to have a clean look and feel out of the box, using the Bootstrap CSS library.

<info>php app/console $command --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>
EOT
        );
    }
    
    public function getResourcesDir()
    {
        return __DIR__.'/../Resources';
    }

    public function getBundleName()
    {
        return 'Bootstrap';
    }
    
    protected function getDefaultActions()
    {
        return 'all';
    }

    protected function getFormActions()
    {
        return array('show', 'new', 'edit');
    }    
}
