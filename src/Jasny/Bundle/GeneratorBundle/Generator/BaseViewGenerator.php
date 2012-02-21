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
 * Generates a base view with Bootstrap support.
 * {@internal Fork of DoctrineCrudGenerator }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BaseViewGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
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
     * Check if the base view already exists
     * 
     * @param BundleInterface $bundle 
     */
    public function baseExists(BundleInterface $bundle)
    {
        $dir = sprintf('%s/Resources/views', $bundle->getPath());
        return file_exists($dir . '/base.html.twig');
    }
    
    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface $bundle A bundle object
     * @param string $language Translation language
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $language, $parameters=array())
    {
        $this->language = $language;
        
        $dir = sprintf('%s/Resources/views', $bundle->getPath());
        
        if (file_exists($dir . '/base.html.twig')) {
            throw new \RuntimeException("Base view already exists");
        }
        
        $files = glob($this->skeletonDir . '/*.html.twig');
        
        foreach ($files as $file) {
            $file = basename($file);
            
            $this->renderFile($this->skeletonDir, $file, $dir.'/'.$file, $parameters + array(
                'dir'               => $this->skeletonDir,
                'bundle'            => $bundle->getName(),
            ));
        }
    }
    
    public function addToNavigation($bundle, $routePrefix, $title)
    {
        $route = str_replace('/', '_', $routePrefix);
        $dir = sprintf('%s/Resources/views', $bundle->getPath());
        
        if (file_exists($dir.'/nav.html.twig')) {
            $current = file_get_contents($dir.'/nav.html.twig');
            
            // Don't add same route twice
            if (false !== strpos($current, "'$route'")) {
                throw new \RuntimeException("Route '$route' has already been added to the navigation");
            }
        }
        
        $line = "<li class=\"{% if app.request.attributes.get('_route') == '$route' %}active{% endif %}\"><a href=\"{{ path('$route') }}\">" . ucwords($title) . "</a></li>\n";
        file_put_contents($dir.'/nav.html.twig', $line, FILE_APPEND);
        
        return true;
    }
    
    protected function getLanguage()
    {
        return $this->language;
    }
}
