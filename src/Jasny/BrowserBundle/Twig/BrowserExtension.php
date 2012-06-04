<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\BrowserBundle\Twig;

use Jasny\BrowserBundle\Services\BrowserDetector;

/**
 * Format a date based on the current locale in Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class BrowserExtension extends \Twig_Extension
{
    protected $detector;
    
    /**
     * @param BrowserDetector $detector 
     */
    public function __construct(BrowserDetector $detector)
    {
        $this->detector = $detector;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array('browser' => $this->detector);
    }
    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'browser';
    }
}
