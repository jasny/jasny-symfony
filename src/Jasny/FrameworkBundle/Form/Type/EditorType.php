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


class EditorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('linkify', $options['linkify']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $linkify = $form->getAttribute('linkify');
        if ($linkify && !is_array($linkify)) $linkify = array();
        $linkify += array('protocols' => array('http', 'mail'), 'attributes' => array(), 'mode' => 'normal');
        $view->set('linkify', $linkify);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'class' => 'editor',
            'linkify' => true,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'editor';
    }
}
