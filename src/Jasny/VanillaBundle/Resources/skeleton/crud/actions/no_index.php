
    /**
     * Redirect to first {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Template()
{% endif %}
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $entity = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findFirst();
{% if 'new' not in actions %}
        if (!$entity) throw $this->createNotFoundException('Unable to find any {{ entity_desc.plural }}.');
{% else %}

        if (!$entity) {
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}.new'));
        }
{% endif %}
        
{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.show', array('id' => $entity->get{{ id|capitalize }}())));
{% elseif 'edit' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.edit', array('id' => $entity->get{{ id|capitalize }}())));
{% endif %}
    }
