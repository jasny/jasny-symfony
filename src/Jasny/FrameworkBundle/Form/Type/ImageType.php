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

use Symfony\Component\DependencyInjection\ContainerInterface;

class ImageType extends AbstractType
{
    protected $assets_helper;
    
    /**
     * Set Assets helper service
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->assets_helper = $container->get('templating.helper.assets');
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('preview_size', $options['preview_size']);
        $builder->setAttribute('default_preview', $options['default_preview']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $size = $form->getAttribute('preview_size');
        $view->set('preview', $view->get('filebinding') ? $view->get('filebinding')->getAsset($size ? preg_replace('/.*?(\d+x\d+.*)$/', '$1', $size) : null) : $view->get('url'));
        $view->set('preview_size', $size && preg_match('/(max)?\W*(\d+)x(\d+)/', $size, $match) ? array("max" => (boolean)$match[1], "width" => $match[2], "height" => $match[3]) : null);
        
        $default = $form->getAttribute('default_preview');
        if ($default) $default = $this->assets_helper->getUrl($default);
        $view->set('default_preview', $default);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'preview_size' => null,
            'default_preview' => null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image';
    }
}
