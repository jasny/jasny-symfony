<?php

namespace Jasny\FrameworkBundle\DependencyInjection;

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

        // Change default Twig form view
        if ($container->getParameter('twig.form.resources') == array('form_div_layout.html.twig')) {
            $container->setParameter('twig.form.resources', array('JasnyFrameworkBundle:Form:form_layout.html.twig'));
        }
    }
}
