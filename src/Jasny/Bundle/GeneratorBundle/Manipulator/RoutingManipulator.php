<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\GeneratorBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;

/**
 * Changes the PHP code of a YAML routing file.
 * {@internal Fork of Sensio RoutingManipulator }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingManipulator extends Manipulator
{
    private $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML routing file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a routing resource at the top of the existing ones.
     *
     * @param string $bundle
     * @param string $format
     * @param string $prefix
     * @param string $path
     *
     * @return Boolean true if it worked, false otherwise
     *
     * @throws \RuntimeException If bundle is already imported
     */
    public function addResource($bundle, $format, $prefix = '/', $path = 'routing')
    {
        $key = $bundle.('/' !== $prefix ? '_'.str_replace('/', '_', substr($prefix, 1)) : '');
        
        $current = '';
        if (file_exists($this->file)) {
            $current = file_get_contents($this->file);

            // Don't add same bundle twice
            if (false !== strpos($current, $key)) {
                throw new \RuntimeException(sprintf('Route "%s" is already imported.', $key));
            }
        } elseif (!is_dir($dir = dirname($this->file))) {
            mkdir($dir, 0777, true);
        }

        $code = sprintf("%s:\n", $bundle.('/' !== $prefix ? '_'.str_replace('/', '_', substr($prefix, 1)) : ''));
        if ('annotation' == $format) {
            $code .= sprintf("    resource: \"@%s/Controller/%s\"\n    type:     annotation\n", $bundle, $path);
        } else {
            $code .= sprintf("    resource: \"@%s/Resources/config/%s.%s\"\n", $bundle, $path, $format);
        }
        $code .= sprintf("    prefix:   %s\n", $prefix);
        $code = $current . "\n" . $code;

        if (false === file_put_contents($this->file, $code)) {
            return false;
        }

        return true;
    }
    
    /**
     * Get the default format for specified bundle
     * 
     * @param object $bundle
     * @param string $default
     * @return string 
     */
    public static function getDefaultFormat($bundle=null, $default='annotation')
    {
        if (!$bundle) return $default;
        
        $files = glob($bundle->getPath() . '/Resources/config/routing.*');
        if (!$files) return $default;
        
        $ext = strtolower(pathinfo($files[0], PATHINFO_EXTENSION));
        $contents = file_get_contents($files[0]);
        
        if (preg_match('/\btype:\s*annotation\b/i', $contents)) return 'annotation';
        if (preg_match('/\bresource:\s*".*\.' . $ext . '"/i', $contents)) return $ext;
        return $default;
    }
    
}
