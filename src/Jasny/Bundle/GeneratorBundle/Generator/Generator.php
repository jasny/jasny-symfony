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

use Sensio\Bundle\GeneratorBundle\Generator\Generator as BaseGenerator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

/**
 * Generator is the base class, with translation functionality.
 * {@internal Fork of Sensio Generator }}
 *
 * @author Arnold Daniels <arnold@jasny.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Generator extends BaseGenerator
{
    private $translator;
    
    protected function getTranslator()
    {
        if (!isset($this->translator)) {
            $this->translator = new Translator($this->getLanguage(), new MessageSelector());
            $this->translator->addLoader('yaml', new YamlFileLoader());
            
            foreach (glob(dirname(__DIR__) . '/Resources/translation/*.yml') as $file) {
                if (!preg_match('/^(.+)\.(\w+)\.yml$/', basename($file), $matches)) continue;
                $this->translator->addResource('yaml', $file, $matches[2], $matches[1]);
            }
        }
        
        return $this->translator;
    }
    
    protected function renderFile($skeletonDir, $template, $target, $parameters)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDir), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'autoescape'       => false,
        ));
        $twig->addExtension(new TranslationExtension($this->getTranslator()));
        
        file_put_contents($target, $twig->render($template, $parameters));
    }
    
    protected function getLanguage()
    {
        return null;
    }
}
