<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\BootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Jasny\BootstrapBundle\Form\DataTransformer\EntityTransformer;

class TypeAheadType extends AbstractType
{
    protected $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $source = $options['source'];
        if (isset($source) && !is_scalar($source)) $source = str_replace("\n", "", json_encode($source));
        
        $builder
            ->setAttribute('provide', $options['provide'])
            ->setAttribute('source', $source)
            ->setAttribute('strict', $options['strict'])
            ->setAttribute('class', $options['class'])
        ;
        
        if ($options['class']) {
            $em = $this->registry->getEntityManager($options['em']);
            $builder->prependNormTransformer(new EntityTransformer($em, $options['class'], $options['addable']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $value = $view->get('value');
        $id = is_object($value) && method_exists($value, 'getId') ? $value->getId() : $value;

        $view->set('text', (string)$value);
        $view->set('value', $id);
        if (is_object($value)) $view->set('object', $value);
        $view->set('object_class', $form->getAttribute('class'));
        
        if (!$view->get('read_only')) {
            $attr = $view->get('attr');
            if ($form->getAttribute('provide')) $attr['data-provide'] = $form->getAttribute('provide');
            if ($form->getAttribute('source')) $attr['data-source'] = $form->getAttribute('source');
            if (!is_null($form->getAttribute('strict'))) $attr['data-strict'] = $form->getAttribute('strict');
            $view->set('attr', $attr);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'em'                => null,
            'class'             => null,
            'strict'            => null,
            'addable'           => false,
            'provide'           => 'typeahead',
            'source'            => null,
            'multiple'          => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'typeahead';
    }
}
