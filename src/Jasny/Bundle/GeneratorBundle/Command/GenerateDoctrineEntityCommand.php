<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand as BaseCommand;

use Jasny\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;

use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\DBAL\Types\Type;

/**
 * Initializes a Doctrine entity inside a bundle.
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineEntityCommand extends BaseCommand
{
    private $generator;
    
    protected function configure()
    {
        parent::configure();
        
        $this->setHelp($this->getHelp() . "\n\n-> Jasny Bootstrap customized <-");
    }
    
    public function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
        }

        return $this->generator;
    }
    
    public function setGenerator(BaseDoctrineEntityGenerator $generator)
    {
        $this->generator = $generator;
    }
}
