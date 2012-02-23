<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\CKEditorBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class KCFinder
{
    /**
     * Relative path to kcfinder assets in web directory
     */
    protected $path = 'bundles/jasnyckeditor/kcfinder';
    
    /**
     * Session configuration
     * @var array
     */
    protected $config;
    
    /**
     * Set Assets helper service
     * 
     * @param ContainerInterface $container
     * @param string             $upload_dir
     */
    public function __construct(ContainerInterface $container, $upload_dir)
    {
        $this->path = $container->get('templating.helper.assets')->getUrl($this->path);
        $this->config =& $_SESSION['KCFINDER'];
        
        $this->config['uploadURL'] = $container->get('templating.helper.assets')->getUrl($upload_dir);
    }
    
    /**
     * Enable KCFinder
     */
    public function enable()
    {
        $this->config['disabled'] = false;
    }
    
    /**
     * Set KCFinder configuration option
     * 
     * @param string $name
     * @param string $value 
     */
    public function setOption($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * Get configuration for CKEditor
     * 
     * @return array
     */
    public function getCKEditorConfig()
    {
        return array(
            'filebrowserBrowseUrl'      => "{$this->path}/browse.php?type=files",
            'filebrowserImageBrowseUr'  => "{$this->path}/browse.php?type=images",
            'filebrowserFlashBrowseUrl' => "{$this->path}/browse.php?type=flash",
            'filebrowserUploadUrl'      => "{$this->path}/upload.php?type=files",
            'filebrowserImageUploadUrl' => "{$this->path}/upload.php?type=images",
            'filebrowserFlashUploadUrl' => "{$this->path}/upload.php?type=flash",

            'filebrowserWindowWidth'  => 800,
            'filebrowserWindowHeight' => 600,
        );
    }
}
