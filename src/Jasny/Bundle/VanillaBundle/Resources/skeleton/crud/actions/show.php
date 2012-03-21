
    /**
     * Finds and displays a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}", name="{{ route_name_prefix }}.show")
     * @Template()
{% endif %}
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if (!$entity) throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }}.');

        $form = $this->createForm(new {{ entity_class }}Type(), $entity, array('read_only'=>true));
        
{% if ('delete' in actions) %}
        $deleteForm = $this->createDeleteForm($id);

{% endif %}
{% if 'index' not in actions %}
        $list = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findAll();

{% endif %}
{% if 'annotation' == format %}
        return array(
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:show.html.twig', array(
{% endif %}
            'entity'      => $entity,
            'form'        => $form->createView(),
{% if ('delete' in actions) %}
            'delete_form' => $deleteForm->createView(),
{% endif %}
{% if 'index' not in actions %}
            'list'   => $list,
{% endif %}
        {{ 'annotation' == format ? ')' : '))' }};
    }
