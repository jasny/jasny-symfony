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


class LinkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('linkify', $options['linkify']);
        $builder->setAttribute('multiple', $options['multiple']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $linkify = $form->getAttribute('linkify');
        $linkify += array('protocols' => array('http', 'mail'), 'attr' => array(), 'mode' => 'all');
        $view->set('linkify', $linkify);
        
        $view->set('multiple', $form->getAttribute('multiple'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'linkify' => array(),
            'multiple' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return $options['multiple'] ? 'textarea' : 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'link';
    }
}
