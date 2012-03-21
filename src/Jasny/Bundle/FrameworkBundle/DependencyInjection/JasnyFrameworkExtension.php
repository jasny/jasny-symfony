<?php

namespace Jasny\Bundle\FrameworkBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * This is the class that loads and manages the Framework bundle configuration
 */
class JasnyFrameworkExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $loader->load('form.yml');
        $loader->load('twig.yml');
        
        $this->setORMParameters($container);
    }
    
    /**
     * Add Doctrine ORM types and functions.
     * 
     * @param ContainerBuilder $container 
     */
    protected function setORMParameters(ContainerBuilder $container)
    {
        // Types
        $orm_types = array(
            'reference' => 'Jasny\Bundle\FrameworkBundle\ORM\Types\ReferenceType',
            'point' => 'Jasny\Bundle\FrameworkBundle\ORM\Types\PointType',
        ) + $container->getParameter('doctrine.dbal.connection_factory.types');
        
        $container->setParameter('doctrine.dbal.connection_factory.types', $orm_types);
        
        // Functions
        $ormConfigDef = $container->setDefinition('jasny.orm.configuration', new DefinitionDecorator('doctrine.orm.configuration'));
        
        $ormConfigDef->addMethodCall('addCustomNumericFunction', array('geom', 'Jasny\Bundle\FrameworkBundle\ORM\Functions\Geom'));
    }
}
