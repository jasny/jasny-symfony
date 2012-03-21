<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

{% if ('index' in actions) or ('show' in actions) or ('edit' in actions) %}
$collection->add('{{ route_name_prefix }}', new Route(
    '/',
    array('_controller' => '{{ bundle }}:{{ entity }}:index')
));

{% endif %}
{% if 'new' in actions %}
$collection->add('{{ route_name_prefix }}.new', new Route(
    '/new',
    array('_controller' => '{{ bundle }}:{{ entity }}:new'),
    array('_method' => 'get'),
));

{% endif %}
{% if 'new' in actions or 'create' in actions %}
$collection->add('{{ route_name_prefix }}.create', new Route(
    '/new',
    array('_controller' => '{{ bundle }}:{{ entity }}:create'),
    array('_method' => 'post')
));

{% endif %}
{% if 'show' in actions %}
$collection->add('{{ route_name_prefix }}.show', new Route(
    '/{id}/',
    array('_controller' => '{{ bundle }}:{{ entity }}:show'),
));

{% endif %}
{% if 'edit' in actions %}
$collection->add('{{ route_name_prefix }}.edit', new Route(
    {% if 'show' in actions %}'/{id}/edit'{% else %}'/{id}'{% endif %},
    array('_controller' => '{{ bundle }}:{{ entity }}:edit'),
    array('_method' => 'get')
));

{% endif %}
{% if 'edit' in actions or 'update' in actions %}
$collection->add('{{ route_name_prefix }}.update', new Route(
    {% if 'show' in actions %}'/{id}/edit'{% else %}'/{id}'{% endif %},
    array('_controller' => '{{ bundle }}:{{ entity }}:update'),
    array('_method' => 'post')
));

{% endif %}
{% if 'delete' in actions %}
$collection->add('{{ route_name_prefix }}.delete', new Route(
    '/{id}/delete',
    array('_controller' => '{{ bundle }}:{{ entity }}:delete'),
    array('_method' => 'post')
));

{% endif %}
return $collection;
