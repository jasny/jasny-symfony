<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\FrameworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\DateType as BaseType;

class DateType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if (isset($options['pattern'])) $options['format'] = $options['pattern'];

        $pattern = is_string($options['format']) ? $options['format'] : datefmt_get_pattern(datefmt_create(\Locale::getDefault(), $options['format'], \IntlDateFormatter::NONE, \DateTimeZone::UTC, \IntlDateFormatter::GREGORIAN));
        $pattern = strtolower(preg_replace(array('/\bd\b/', '/\bM\b/', '/\by{1,2}\b/', '/\by{3,}\b/'), array('dd', 'mm', 'yy', 'yyyy'), $pattern));
        
        $builder->setAttribute('inputmask', $options['inputmask'] === true ? preg_replace('/\w/', '9', $pattern) : $options['inputmask']);
        $builder->setAttribute('placeholder', $options['placeholder'] === true ? $pattern : $options['placeholder']);

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('inputmask', $form->getAttribute('inputmask'));
        $view->set('placeholder', $form->getAttribute('placeholder'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $date_pattern = preg_replace('/\byy?\b/', 'yyyy', datefmt_get_pattern(datefmt_create(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, \DateTimeZone::UTC, \IntlDateFormatter::GREGORIAN)));

        return array(
            'widget' => 'single_text',
            'format' => \IntlDateFormatter::LONG,
            'pattern' => $date_pattern,
            'inputmask' => true,
            'placeholder' => true,
        ) + parent::getDefaultOptions($options);
    }
}
