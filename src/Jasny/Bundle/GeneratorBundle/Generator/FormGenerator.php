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
 * Generates a form class with Bootstrap support based on a Doctrine entity
 * {@internal Fork of DoctrineFormGenerator }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Hugo Hamon <hugo.hamon@sensio.com>
 */
class FormGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
    private $className;
    private $classPath;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param BundleInterface $bundle The bundle in which to create the class
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity metadata class
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'Type';
        $dirPath         = $bundle->getPath().'/Form';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'Type.php';

        if (file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s form class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The form generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile($this->skeletonDir, 'FormType.php', $this->classPath, array(
            'dir'              => $this->skeletonDir,
            'fields'           => $this->getFieldsFromMetadata($metadata),
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'form_class'       => $this->className,
            'form_type_name'   => strtolower(implode('_', $parts).($parts ? '_' : '').$this->className),
        ));
    }
}
