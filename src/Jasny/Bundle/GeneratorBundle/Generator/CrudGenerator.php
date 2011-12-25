<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\GeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Util\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Generates a CRUD controller with Bootstrap support.
 * {@internal Fork of DoctrineCrudGenerator }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CrudGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
    private $routePrefix;
    private $routeNamePrefix;
    private $bundle;
    private $entityBundle;
    private $entity;
    private $metadata;
    private $format;
    private $actions;
    private $customForm;
    private $entityDesc;
    private $stringable;
    private $language;
    
    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     * @param string $skeletonDir Path to the skeleton directory
     */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem  = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface $bundle A bundle object
     * @param BundleInterface $entityBundle The bundle that contains the entity
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity class metadata
     * @param string $format The configuration format (xml, yaml, annotation)
     * @param string $routePrefix The route name prefix
     * @param array $actions Array of actions ('index', 'show', 'new', 'edit', 'delete')
     * @param array $entityDesc Array with singular and plural description
     * @param string $language Translation language
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, BundleInterface $entityBundle, $entity, ClassMetadataInfo $metadata, $format, $routePrefix, $actions, $customForm, $entityDesc, $language)
    {
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = str_replace('/', '_', $routePrefix);
        $this->actions = $actions;
        $this->customForm = $customForm;

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The CRUD generator does not support entity classes with multiple primary keys.');
        }

        if (!in_array('id', $metadata->identifier)) {
            throw new \RuntimeException('The CRUD generator expects the entity object has a primary key field named "id" with a getId() method.');
        }
        
        $this->bundle       = $bundle;
        $this->entityBundle = $entityBundle;
        $this->entity       = $entity;
        $this->metadata     = $metadata;
        $this->entityDesc   = $entityDesc;
        $this->setFormat($format);

        $refl = $metadata->getReflectionClass();
        $this->stringable  = $refl->hasMethod('__toString');
        $this->language = $language;
        
        $this->generateControllerClass();

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $this->entity));

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }
        
        if ($this->customForm) {
            if (!file_exists("$dir/includes")) $this->filesystem->mkdir("$dir/includes", 0777);
            $this->generateCustomFormView($dir);
        }

        if (in_array('index', $this->actions)) {
            $this->generateIndexView($dir);
        }

        if (in_array('show', $this->actions)) {
            $this->generateShowView($dir);
        }

        if (in_array('new', $this->actions)) {
            $this->generateNewView($dir);
        }

        if (in_array('edit', $this->actions)) {
            $this->generateEditView($dir);
        }

        $this->generateTestClass();
        $this->generateConfiguration();
    }

    /**
     * Sets the configuration format.
     *
     * @param string $format The configuration format
     */
    private function setFormat($format)
    {
        switch ($format) {
            case 'yml':
            case 'xml':
            case 'php':
            case 'annotation':
                $this->format = $format;
                break;
            default:
                $this->format = 'yml';
                break;
        }
    }

    /**
     * Generates the routing configuration.
     *
     */
    private function generateConfiguration()
    {
        if (!in_array($this->format, array('yml', 'xml', 'php'))) {
            return;
        }

        $target = sprintf(
            '%s/Resources/config/routing/%s.%s', 
            $this->bundle->getPath(),
            strtolower(str_replace('\\', '_', $this->entity)),
            $this->format
        );

        $this->renderFile($this->skeletonDir, 'config/routing.'.$this->format, $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
        ));
    }

    /**
     * Generates the controller class only.
     *
     */
    private function generateControllerClass()
    {
        $dir = $this->bundle->getPath();

        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $this->renderFile($this->skeletonDir, 'controller.php', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'dir'               => $this->skeletonDir,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'entity_full_class' => $this->entityBundle->getNamespace() . '\\Entity\\' . $this->entity,
            'entity_bundle'     => $this->entityBundle->getName(),
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'format'            => $this->format,
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
        ));
    }

    /**
     * Generates the functional test class only.
     *
     */
    private function generateTestClass()
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir    = $this->bundle->getPath() .'/Tests/Controller';
        $target = $dir .'/'. str_replace('\\', '/', $entityNamespace).'/'. $entityClass .'ControllerTest.php';

        $this->renderFile($this->skeletonDir, 'tests/test.php', $target, array(
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'entity_full_class' => $this->entityBundle->getNamespace() . '\\Entity\\' . $this->entity,
            'entity_bundle'     => $this->entityBundle->getName(),
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'actions'           => $this->actions,
            'dir'               => $this->skeletonDir,
        ));
    }

    /**
     * Generates the include/form.html.twig template, which is included in the new and edit view.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateCustomFormView($dir)
    {
        $this->renderFile($this->skeletonDir, 'views/includes/form.html.twig', $dir.'/includes/form.html.twig', array(
            'dir'               => $this->skeletonDir,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'fields'            => $this->getFieldsFromMetadata($this->metadata),
            'actions'           => $this->actions,
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
        ));
    }
    
    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateIndexView($dir)
    {
        $this->renderFile($this->skeletonDir, 'views/index.html.twig', $dir.'/index.html.twig', array(
            'dir'               => $this->skeletonDir,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'fields'            => $this->getFieldsFromMetadata($this->metadata, array('text', 'object')),
            'actions'           => $this->actions,
            'custom_form'       => $this->customForm,
            'record_actions'    => $this->getRecordActions(),
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
            'pagination'        => false
        ));
    }

    /**
     * Generates the show.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateShowView($dir)
    {
        $this->renderFile($this->skeletonDir, 'views/show.html.twig', $dir.'/show.html.twig', array(
            'dir'               => $this->skeletonDir,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'fields'            => $this->getFieldsFromMetadata($this->metadata),
            'actions'           => $this->actions,
            'custom_form'       => $this->customForm,
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
        ));
    }

    /**
     * Generates the new.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateNewView($dir)
    {
        $this->renderFile($this->skeletonDir, 'views/new.html.twig', $dir.'/new.html.twig', array(
            'dir'               => $this->skeletonDir,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'actions'           => $this->actions,
            'custom_form'       => $this->customForm,
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
        ));
    }

    /**
     * Generates the edit.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateEditView($dir)
    {
        $this->renderFile($this->skeletonDir, 'views/edit.html.twig', $dir.'/edit.html.twig', array(
            'dir'               => $this->skeletonDir,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'actions'           => $this->actions,
            'custom_form'       => $this->customForm,
            'entity_desc'       => $this->entityDesc,
            'stringable'        => $this->stringable,
        ));
    }

    /**
     * Returns an array of record actions to generate (edit, show).
     *
     * @return array
     */
    private function getRecordActions()
    {
        return array_filter($this->actions, function($item) {
            return in_array($item, array('show', 'edit'));
        });
    }

    protected function getLanguage()
    {
        return $this->language;
    }
}
