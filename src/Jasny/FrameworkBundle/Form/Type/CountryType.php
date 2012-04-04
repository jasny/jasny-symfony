<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\FrameworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Yaml\Yaml;

class CountryType extends AbstractType
{
    protected $dir;
    
    /**
     * @param string $dir  Directory with YAML translation files
     */
    public function __construct($dir=null)
    {
        $this->dir = $dir ?: __DIR__ . '/../../Resources/translation';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'choices' => $this->getCountries(),
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'country';
    }
    
    protected function getCountries()
    {
        $locale = \Locale::getDefault();
        while ($locale && !file_exists("{$this->dir}/countries.$locale.yml")) {
            $locale = preg_replace('/(^|_)\w+$/', '', $locale);
        }
        
        if (empty($locale)) $locale = 'en';

        if (!file_exists("{$this->dir}/countries.$locale.yml")) throw new Exception("Could not load yaml file with countries.");
        
        return Yaml::parse("{$this->dir}/countries.$locale.yml");
    }
}
