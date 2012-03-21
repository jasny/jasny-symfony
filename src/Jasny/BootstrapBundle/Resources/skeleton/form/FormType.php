<?php

namespace {{ namespace }}\Form{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class {{ form_class }} extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
{% for field, metadata in fields %}
{%   if metadata.type == 'boolean' %}
            ->add('{{ field }}', 'choice', array('choices' => array(0 => '{{ "No"|trans }}', 1 => '{{ "Yes"|trans }}')))
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

    public function getParent(array $options)
    {
        return 'base_form';
    }
}
