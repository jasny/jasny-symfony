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

use Jasny\Bundle\GeneratorBundle\Generator\BaseViewGenerator;
use Jasny\Bundle\GeneratorBundle\Generator\CrudGenerator;
use Jasny\Bundle\GeneratorBundle\Generator\FormGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Jasny\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Doctrine\ORM\Mapping\MappingException;

/**
 * Generates a CRUD for a Doctrine entity.
 * {@internal Fork of GenerateDoctrineCrudCommand }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class GenerateCrudCommand extends GenerateDoctrineCommand
{
    private $baseviewGenerator;
    private $generator;
    private $formGenerator;
    
    protected function configure()
    {
        $command = strtolower($this->getBundleName()) . ':generate:crud';
        
        $this
            ->setDefinition(array(
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('bundle', '', InputOption::VALUE_REQUIRED, 'The target bundle to generate the controller and views in (shortcut notation)'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('actions', '', InputOption::VALUE_REQUIRED, 'Which actions to create (options: list, show, new, edit and delete) ', $this->getDefaultActions()),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('lang', '', InputOption::VALUE_REQUIRED, 'Translate the template to this language'),
                new InputOption('singular', '', InputOption::VALUE_REQUIRED, 'The description for a single entity'),
                new InputOption('plural', '', InputOption::VALUE_REQUIRED, 'The description for multiple entities'),
            ))
            ->setDescription('Generates a ' . $this->getBundleName() . ' CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>$command</info> command generates a {$this->getBundleName()} CRUD based on a Doctrine entity.

<info>php app/console $command --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>
EOT
            )
            ->setName($command)
            ->setAliases(array('generate:' . strtolower($this->getBundleName()) . ':crud'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $bundle = $input->getOption('bundle');
        
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($entityBundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        
        $language = $input->getOption('lang');
        if (!$language) $language = $this->getContainer()->get('session')->getLocale();
        
        $entityDesc = array('singular'=>$input->getOption('singular'), 'plural'=>$input->getOption('plural'));
        
        $actions = $input->getOption('actions');
        $actions = $actions == 'all' ? array('list', 'show', 'new', 'edit', 'delete') : array_intersect(preg_split('/\W+/', $actions), array('list', 'show', 'new', 'edit', 'delete'));
        foreach ($actions as &$action) {
            if ($action == 'list') $action = 'index';
        }

        if (empty($actions)) {
            $output->writeln('<error>No actions selected</error>');
            return 1;
        }

        $entityClass  = $this->getContainer()->get('doctrine')->getEntityNamespace($entityBundle).'\\'.$entity;
        $metadata     = $this->getEntityMetadata($entityClass);
        $bundle       = $this->getContainer()->get('kernel')->getBundle($bundle);
        $entityBundle = $this->getContainer()->get('kernel')->getBundle($entityBundle);
        
        // base view
        $baseviewGenerator = $this->getBaseViewGenerator();
        if (!$baseviewGenerator->baseExists($bundle)) {
            $dialog->writeSection($output, 'Base view generation');
            
            $output->writeln(array(
                'It looks like this is the first CRUD you\'re creating in this bundle.',
                'I\'ll generate the base view, which will be used for all screens.',
                '',
            ));
            
            $sitename = $input->isInteractive() ? $dialog->ask($output, $dialog->getQuestion('What is the name of this website', null, '?')) : null;
            
            $baseviewGenerator->generate($bundle, $language, compact('sitename'));
            $output->writeln('Generating base view: <info>OK</info>');
        }

        $dialog->writeSection($output, 'CRUD generation');

        // CRUD
        $generator = $this->getGenerator();
        $generator->generate($bundle, $entityBundle, $entity, $metadata[0], $format, $prefix, $actions, $entityDesc, $language);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // form
        if (array_intersect($actions, array('show', 'new', 'edit'))) {
            $this->generateForm($bundle, $entity, $metadata);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        // routing
        if ('annotation' != $format) {
            try {
                $runner($this->updateRouting($dialog, $input, $output, $bundle, $format, $entity, $prefix));
            } catch (\RuntimeException $e) {
                $output->writeln('<fg=red>SKIPPED</>');
                $this->outputWarning($output, $e->getMessage());
            }
        }
        
        // navigation
        if (!$input->isInteractive() || $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the navigation', 'yes', '?'), true)) {
            $output->write('Update navigation: ');
            try {
                $baseviewGenerator->addToNavigation($bundle, $prefix, $entityDesc['plural']);
                $output->writeln('<info>OK</info>');
            } catch (\RuntimeException $e) {
                $output->writeln('<fg=red>SKIPPED</>');
                $this->outputWarning($output, $e->getMessage());
            }
        }
        
        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Jasny ' . $this->getBundleName() . ' CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('entity'));
        $input->setOption('entity', $entity);
        list($entityBundle, $entity) = $this->parseShortcutNotation($entity);

        // Entity exists?
        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($entityBundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);

        // Bundle
        $bundle = $input->getOption('bundle') ?: $entityBundle;
        $output->writeln(array(
            '',
            'Normally, the controller and views are generated in the same bundle as',
            'the entity. However you may specify a target bundle, which is useful to',
            'create a seperate back and front end.',
            '',
        ));
        $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('Target bundle', $bundle, ':'), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName'), false, $bundle);
        $input->setOption('bundle', $bundle);
        
        // Bundle exists?
        $this->getContainer()->get('kernel')->getBundle($bundle);
                
        // show and write?
        if ($this->getDefaultActions() == 'all') {
            $output->writeln(array(
                '',
                'By default, the all actions are generated: list, show, new, edit and delete.',
                'You may specify to only generate some actions (eg. list,show,delete).',
                'Omiting actions will influence views of other actions.',
                '',
            ));
        } else {
            $output->writeln(array(
                '',
                'By default only the ' . $this->getDefaultActions() . (false === strpos($this->getDefaultActions(), ',') ? ' action is' : ' actions are') . ' generated.',
                'However, you may specify which actions to generate (list, show, new, edit and delete).',
                'Adding or omiting actions will influence views of other actions.',
                '',
            ));
        }
        
        $actions = $input->getOption('actions') ?: 'all';
        if ($actions != 'all') $actions = join(',', array_intersect(array('list', 'show', 'new', 'edit', 'delete'), preg_split('/\W+/', $actions)));
        $actions = $dialog->ask($output, $dialog->getQuestion('Which actions do you want to generate', $actions, '?'), $actions);
        $input->setOption('actions', $actions);

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ));
        $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $format);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $dialog->ask($output, $dialog->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix);
        $input->setOption('route-prefix', $prefix);

        // single and plural entity description
        $output->writeln(array(
            '',
            'Please specify the singular and plural description of the entity.',
            '',
        ));
        $singularDesc = $input->getOption('singular') ?: strtolower(preg_replace('~^.*([a-z])(?=[A-Z])|\\\\|_~', '', $entity));
        $singularDesc = $dialog->ask($output, $dialog->getQuestion('How do you call a single entity', $singularDesc, '?'), $singularDesc);
        $input->setOption('singular', $singularDesc);

        $pluralDesc = $input->getOption('plural') ?: $singularDesc . 's';
        $pluralDesc = $dialog->ask($output, $dialog->getQuestion('How do you call a multiple entities', $pluralDesc, '?'), $pluralDesc);
        $input->setOption('plural', $pluralDesc);
        
        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a CRUD controller for \"<info>%s:%s</info>\"", $entityBundle, $entity),
            ($entityBundle != $bundle ? sprintf("in \"<info>%s</info>\" ", $bundle) : '') . sprintf("using the \"<info>%s</info>\" format.", $format),
            '',
        ));
    }    
    
    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator()->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e ) {
            // form already exists
        }
    }

    protected function updateRouting($dialog, InputInterface $input, OutputInterface $output, $bundle, $format, $entity, $prefix)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
        }

        $output->write('Importing the CRUD routes: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $ret = false;
        
        if (!$auto) {
            if (!isset($todomsg)) $todomsg = '- Import the entity\'s routing resource in the bundle routing file';
            $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing/%s.%s\"</comment>\n", $bundle->getName(), strtolower(str_replace('\\', '_', $entity)), $format);
            $help .= sprintf("        <comment>prefix:   /%s</comment>\n", $prefix);
            
            return array(
                $todomsg,
                sprintf('  (%s).', $bundle->getPath().'/Resources/config/routing.yml'),
                '',
                sprintf('    <comment>%s:</comment>', $bundle->getName().('' !== $prefix ? '_'.str_replace('/', '_', $prefix) : '')),
                $help,
                '',
            );
        }

        $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/routing.yml');
        $routing->addResource($bundle->getName(), $format, '/'.$prefix, 'routing/'.strtolower(str_replace('\\', '_', $entity)));
        
        return true;
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    /**
     * @return BaseViewGenerator
     */
    protected function getBaseViewGenerator()
    {
        if (null === $this->generator) {
            $this->baseviewGenerator = new BaseViewGenerator($this->getContainer()->get('filesystem'), $this->getResourcesDir() . '/skeleton/base');
        }

        return $this->baseviewGenerator;
    }

    public function setBaseViewGenerator(BaseViewGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return CrudGenerator
     */
    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new CrudGenerator($this->getContainer()->get('filesystem'), $this->getResourcesDir() . '/skeleton/crud');
        }

        return $this->generator;
    }

    public function setGenerator(CrudGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return FormGenerator
     */
    protected function getFormGenerator()
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new FormGenerator($this->getContainer()->get('filesystem'),  $this->getResourcesDir() . '/skeleton/form');;
        }

        return $this->formGenerator;
    }

    public function setFormGenerator(FormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }

    protected function outputWarning($output, $msg)
    {
        $output->writeln(array(
          "",
          "<bg=yellow>  " . str_repeat(" ", strlen($msg)) . "  </>",
          "<bg=yellow>  " . $msg . "  </>",
          "<bg=yellow>  " . str_repeat(" ", strlen($msg)) . "  </>",
          ""
        ));
    }
    
    abstract public function getResourcesDir();
    
    abstract public function getBundleName();
    
    abstract protected function getDefaultActions();
}
