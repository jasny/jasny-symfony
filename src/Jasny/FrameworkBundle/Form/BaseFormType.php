<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\DateType as BaseType;

class BaseFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('mode', isset($options['mode']) ? $options['mode'] : null);
        $builder->setAttribute('dirty', !empty($options['dirty']));
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('dirty', $form->getAttribute('dirty'));
        $view->set('mode', $form->getAttribute('mode'));
    }
        
    /**
     * {@inheritdoc}
     */
    public function getDetaultOptions(array $options)
    {
        return array(
            'mode'  => null,
            'dirty' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'base_form';
    }
}
