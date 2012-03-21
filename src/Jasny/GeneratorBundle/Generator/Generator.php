<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator as BaseGenerator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     * 
     *
     * @param ClassMetadataInfo $metadata
     * @return array $fields
     */
    protected function getFieldsFromMetadata(ClassMetadataInfo $metadata, $excludeType = array())
    {
        $fields = $metadata->fieldMappings;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            unset($fields[reset($metadata->identifier)]);
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[$fieldName] = $relation;
            }
        }
       
        if ($excludeType) {
            foreach ($fields as $key=>&$field) {
                if (in_array($field['type'], $excludeType)) unset($fields[$key]);
            }
        }
        
        unset($fields['created_at'], $fields['updated_at'], $fields['created_by'], $fields['updated_by']);

        return $fields;
    }
}
