<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\DoctrineBundle\Mapping\MetadataFactory;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;

/**
 * Generates a form type class for a given Doctrine entity.
 * {@internal Fork of GenerateDoctrineFormCommand }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Hugo Hamon <hugo.hamon@sensio.com>
 */
abstract class GenerateFormCommand extends GenerateDoctrineCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $command = 'generate:' . strtolower($this->getBundleName()) . ':form';
        
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            ))
            ->setDescription('Generates a ' . $this->getBundleName() . ' form type class based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>$command</info> command generates a {$this->getBundleName()} form class based on a Doctrine entity.

<info>php app/console $command AcmeBlogBundle:Post</info>
EOT
            )
            ->setName($command)
            ->setAliases(array('generate:' . strtolower($this->getBundleName()) . ':form'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);

        $generator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'),  $this->getResourcesDir().'/skeleton/form');
        $generator->generate($bundle, $entity, $metadata[0]);

        $output->writeln(sprintf(
            'The new %s.php class file has been created under %s.',
            $generator->getClassName(),
            $generator->getClassPath()
        ));
    }

    abstract public function getResourcesDir();
    
    abstract public function getBundleName();
}
