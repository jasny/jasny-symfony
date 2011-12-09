<?php

namespace {{ namespace }}\Form{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class {{ form_class }} extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $fieldOptions = $options['read_only'] ? $this->getReadOnlyFieldOptions() : $this->getFieldOptions();
        
        $builder
{% for field, metadata in fields %}
{%   if metadata.type == 'date' %}
            ->add('{{ field }}', null, $fieldOptions['date'])
{%   elseif metadata.type == 'time' %}
            ->add('{{ field }}', null, $fieldOptions['time'])
{%   elseif metadata.type == 'datetime' %}
            ->add('{{ field }}', null, $fieldOptions['datetime'])
{%   elseif metadata.type == 'birthday' %}
            ->add('{{ field }}', null, $fieldOptions['birthday'])
{%   elseif metadata.type == 'boolean' %}
            ->add('{{ field }}', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes')))
{%   else %}
            ->add('{{ field }}')
{%   endif %}
{% endfor %}
        ;
    }

    public function getName()
    {
        return '{{ form_type_name }}';
    }
    
    public function getDetaultOptions(array $options)
    {
        return array('single_text'=>true);
    }

    private function getFieldOptions()
    {
        $date_pattern = preg_replace('/\byy\b/', 'yyyy', datefmt_get_pattern(datefmt_create(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, \DateTimeZone::UTC, \IntlDateFormatter::GREGORIAN)));
        $date_mask = preg_replace('/\w/', '9', $date_pattern);
        
        $options['date'] = array('widget' => 'single_text', 'format' => $date_pattern, 'attr' => array('class' => 'datepicker', 'data-mask' => $date_mask, 'placeholder' => strtolower($date_pattern)));
        $options['time'] = array('format' => 'hh:mm', 'attr' => array('data-mask' => '99:99', 'placeholder' => 'hh:mm'));
        $options['datetime'] = array('widget' => 'single_text', 'date_format' => $date_pattern, 'attr' => array('data-mask' => "$date_mask? 99:99", 'placeholder' => strtolower("$date_pattern hh:mm")));
        $options['birthday'] = array('widget' => 'single_text', 'format' => $date_pattern, 'attr' => array('data-mask' => $date_mask, 'placeholder' => strtolower($date_pattern)));
    
        return $options;
    }
    
    private function getReadOnlyFieldOptions()
    {
        $options['date'] = array('widget' => 'single_text', 'format' => \IntlDateFormatter::LONG);
        $options['time'] = array('format' => \IntlDateFormatter::LONG);
        $options['datetime'] = array('widget' => 'single_text', 'date_format' => \IntlDateFormatter::LONG);
        $options['birthday'] = $options['date'];
        
        return $options;
    }
}
