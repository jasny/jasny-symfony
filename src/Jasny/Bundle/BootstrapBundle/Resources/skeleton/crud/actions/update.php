
    /**
     * Edits an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}_update")
     * @Method("post")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }} entity.');
        }

        $request = $this->getRequest();
        
        $form = $this->createForm(new {{ entity_class }}Type(), $entity);
        $form->bindRequest($request);

        if (!$form->isValid()) {
            return $this->displayEditView($entity, $form);
        }
        
{%if triggerable %}
        $entity->onUpdate($this->get('security.context')->getToken()->getUser());

{% endif %}
        $em->persist($entity);
        $em->flush();

{% if stringable %}
        $this->get('session')->setFlash('success', "{{ "Saved %s '$entity'"|trans({'%s': entity_desc.singular}) }}");
{% else %}
        $this->get('session')->setFlash('success', "{{ "Saved %s"|trans({'%s': entity_desc.singular}) }}");
{% endif %}

{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_show', array('id' => $entity->getId())));
{% elseif 'index' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
{% else %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => $entity->getId())));
{% endif %}
    }
