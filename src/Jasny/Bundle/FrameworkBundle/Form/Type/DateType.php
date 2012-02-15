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

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\DateType as BaseType;

class DateType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if (isset($options['pattern'])) $options['format'] = $options['pattern'];
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $date_pattern = preg_replace(array('/d+/', '/M+/', '/y+/'), array('dd', 'MM', 'yyyy'), datefmt_get_pattern(datefmt_create(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, \DateTimeZone::UTC, \IntlDateFormatter::GREGORIAN)));
        $date_mask = preg_replace('/\w/', '9', $date_pattern);

        return array(
           'widget' => 'single_text',
           'format' => \IntlDateFormatter::LONG,
           'pattern' => $date_pattern,
           'attr' => array('class' => 'datepicker', 'data-mask' => strtolower($date_mask), 'placeholder' => strtolower($date_pattern))
        ) + parent::getDefaultOptions($options);
    }
}
