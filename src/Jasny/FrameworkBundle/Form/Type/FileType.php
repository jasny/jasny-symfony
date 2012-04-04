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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\FileType as BaseType;

use Jasny\ORMBundle\Properties\FileBinding;
use Symfony\Component\HttpFoundation\File\File;

class FileType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $this->setFile($view, $form);
        parent::buildView($view, $form);
    }

    /**
     * Set file attribute
     * 
     * @param FormView $view
     * @param FormInterface $form
     */
    protected function setFile(FormView $view, FormInterface $form)
    {
        $view->set('url', null);
        $view->set('filename', null);
        $view->set('exists', false);
        
        $value = $view->get('value');
        if (!$value) return;

        if ($value instanceof FileBinding) {
            $view->set('filebinding', $value);
            if ($value->exists()) {
                $view->set('exists', true);
                $view->set('url', $value->getAsset());
                $view->set('filename', $value->getFile()->getBasename());
            }
        } elseif ($value instanceof File) {
            $view->set('exists', true);
            $view->set('filename', $value->getBasename());
            
            $root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
            $path = $file->getPathname();
            if (substr($path, 0, strlen($root) + 1) != $_SERVER['DOCUMENT_ROOT'] . '/') $view->set('url', substr($path, strlen($root) + 1));
        }
    }
}
