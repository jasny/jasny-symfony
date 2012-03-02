<?php

namespace {{ namespace }}\Controller{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
{% if 'annotation' == format -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{%- endif %}

use {{ entity_full_class }};
{% if ('show' in actions) or ('new' in actions) or ('edit' in actions) %}
use {{ namespace }}\Form\{{ entity }}Type;
{% endif %}

/**
 * {{ entity }} controller.
 *
 */
class {{ entity_class }}Controller extends Controller
{

    {%- if 'index' in actions -%}
        {%- include 'actions/index.php' -%}
    {%- elseif ('show' in actions) or ('edit' in actions) -%}
        {%- include 'actions/no_index.php' -%}
    {%- endif -%}

    {%- if 'new' in actions -%}
        {%- include 'actions/new.php' -%}
    {%- elseif 'create' in actions -%}
        {%- include 'actions/create.php' -%}
    {%- endif -%}

    {%- if 'show' in actions -%}
        {%- include 'actions/show.php' -%}
    {%- endif -%}

    {%- if 'edit' in actions -%}
        {%- include 'actions/edit.php' -%}
    {%- elseif 'update' in actions -%}
        {%- include 'actions/update.php' -%}
    {%- endif -%}

    {%- if 'delete' in actions -%}
        {%- include 'actions/delete.php' -%}
    {%- endif -%}

}
