
    /**
     * Deletes a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
     * @Method("post")
{% endif %}
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }}.');
            }

            $em->remove($entity);
            $em->flush();
            
{% if stringable %}
        $this->get('session')->setFlash('notice', "{{ "Deleted %s% '$entity'"|trans({'%s%': entity_desc.singular})|capitalize }}");
{% else %}
        $this->get('session')->setFlash('notice', "{{ "Deleted %s%"|trans({'%s%': entity_desc.singular})|capitalize }}");
{% endif %}
        }

        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
{% if ('index' in actions) and ('show' not in actions) and ('edit' not in actions) %}
    private function createDeleteTokens($entities)
    {
        $tokens = array();
        
        foreach ($entities as $entity) {
            $fields = $this->createDeleteForm($entity->getId())->getChildren();
            $tokens[$entity->getId()] = $fields['_token']->getData();
        }
        
        return $tokens;
    }
{% endif %}
    