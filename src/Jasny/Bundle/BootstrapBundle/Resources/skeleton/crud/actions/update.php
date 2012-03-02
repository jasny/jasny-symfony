
    /**
     * Edits an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}.update")
     * @Method("post")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }} entity.');
        }

        $request = $this->getRequest();
        
        $form = $this->createForm(new {{ entity_class }}Type(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

{% if stringable %}
            $this->get('session')->setFlash('success', "{{ "Saved %s% '$entity'"|trans({'%s%': entity_desc.singular}) }}");
{% else %}
            $this->get('session')->setFlash('success', "{{ "Saved the %s%"|trans({'%s%': entity_desc.singular}) }}");
{% endif %}
        } else {
{% if 'new' in actions %}
            return $this->displayEditView($entity, $form);
{% else %}
            $this->get('session')->setFlash('error', "{{ "Failed to save the %s%"|trans({'%s%': entity_desc.singular})|capitalize }}");
{% endif %}
        }

{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.show', array('id' => $entity->getId())));
{% elseif 'index' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
{% else %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.edit', array('id' => $entity->getId())));
{% endif %}
    }
