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

use Symfony\Component\Form\Extension\Core\Type\FieldType as BaseType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FieldType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder
            ->setAttribute('class', $options['class'])
            ->setAttribute('placeholder', $options['placeholder'])
            ->setAttribute('inputmask', $options['inputmask'])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        parent::buildView($view, $form);
        
        $view
            ->set('class', join(" ", (array)$form->getAttribute('class')))
            ->set('placeholder', $form->getAttribute('placeholder'))
            ->set('inputmask', $form->getAttribute('inputmask'))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'class' => '',
            'required' => false,
            'placeholder' => null,
            'inputmask' => null,
        ) + parent::getDefaultOptions($options);
    }
}
