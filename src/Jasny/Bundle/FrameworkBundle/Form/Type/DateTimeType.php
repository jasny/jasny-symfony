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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as BaseType;

use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataTransformerChain;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ArrayToPartsTransformer;

class DateTimeType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if (isset($options['date_pattern'])) $options['date_format'] = $options['date_pattern'];
            
        if ($options['widget'] !== 'single_text') {
            parent::buildForm($builder, $options);
            return;
        }
        
        $builder->appendClientTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $format, \IntlDateFormatter::NONE, \IntlDateFormatter::GREGORIAN, $pattern));
        
        if ($options['input'] === 'string') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], $format)
            ));
        } else if ($options['input'] === 'timestamp') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } else if ($options['input'] === 'array') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], $parts)
            ));
        }

        $builder->setAttribute('widget', $options['widget']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $date_pattern = preg_replace(array('/d+/', '/M+/', '/y+/'), array('dd', 'MM', 'yyyy'), datefmt_get_pattern(datefmt_create(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, \DateTimeZone::UTC, \IntlDateFormatter::GREGORIAN)));
        $date_mask = preg_replace('/\w/', '9', $date_pattern);

        $time_pattern = "hh:mm" . (empty($options['with_seconds']) ? '' : ':ss');
        $time_mask = "99:99" . (empty($options['with_seconds']) ? '' : ':99');
        
        return array(
           'widget' => 'single_text',
           'date_format' => \IntlDateFormatter::LONG,
           'date_pattern' => $date_pattern,
        ) + parent::getDefaultOptions($options);
    }
}
