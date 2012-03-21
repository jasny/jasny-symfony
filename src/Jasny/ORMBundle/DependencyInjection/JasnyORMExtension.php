<?php

namespace Jasny\ORMBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * This is the class that loads and manages the ORM bundle configuration
 */
class JasnyORMExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('jasny_orm.slugger.space', $config['slugger']['space']);
        $container->setParameter('jasny_orm.slugger.glue', $config['slugger']['glue']);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
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
            'point' => 'Jasny\ORMBundle\Types\PointType',
        ) + $container->getParameter('doctrine.dbal.connection_factory.types');
        
        $container->setParameter('doctrine.dbal.connection_factory.types', $orm_types);
        
        // Functions
        
        /* TODO: I want these functions to be added automatically, but how?
        $ormConfigDef->addMethodCall('addCustomNumericFunction', array('distance', 'Jasny\ORMBundle\Functions\Distance'));
        $ormConfigDef->addMethodCall('addCustomNumericFunction', array('geom', 'Jasny\ORMBundle\Functions\Geom'));
        */
    }
}
