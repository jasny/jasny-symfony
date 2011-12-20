
    /**
     * Creates a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/create", name="{{ route_name_prefix }}_create")
     * @Method("post")
     * @Template("{{ bundle }}:{{ entity }}:new.html.twig")
{% endif %}
     */
    public function createAction()
    {
        $entity  = new {{ entity_class }}();
        $request = $this->getRequest();
        $form    = $this->createForm(new {{ entity_class }}Type(), $entity);
        $form->bindRequest($request);

        if (!$form->isValid()) {
            return $this->displayNewView($entity, $form);
        }
        
{%if triggerable %}
        $entity->onCreate($this->get('security.context')->getToken()->getUser());

{% endif %}
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($entity);
        $em->flush();

{% if stringable %}
        $this->get('session')->setFlash('success', "{{ "Saved %s% '$entity'"|trans({'%s%': entity_desc.singular}) }}");
{% else %}
        $this->get('session')->setFlash('success', "{{ "Saved %s%"|trans({'%s%': entity_desc.singular}) }}");
{% endif %}

{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_show', array('id' => $entity->getId())));
{% elseif 'index' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
{% else %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => $entity->getId())));
{% endif %}
    }
