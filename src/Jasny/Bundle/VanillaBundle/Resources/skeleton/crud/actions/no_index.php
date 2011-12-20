
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

        $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->findOneBy(array());

        if (!$entity) {
{% if 'new' in actions %}
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}_new'));
{% else %}
            throw $this->createNotFoundException('Unable to find any {{ entity_desc.plural }}.');
{% endif %}
        }
        
{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_show', array('id' => $entity->getId())));
{% elseif 'edit' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => $entity->getId())));
{% endif %}
    }
