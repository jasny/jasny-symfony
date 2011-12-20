
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}{% if 'show' in actions %}/edit{% endif %}", name="{{ route_name_prefix }}_edit")
     * @Template()
{% endif %}
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }}.');
        }

        $form = $this->createForm(new {{ entity_class }}Type(), $entity);
        
        return $this->displayEditView($entity, $form);
    }
    
    private function displayEditView($entity, $form)
    {
{% if ('delete' in actions) and ('show' not in actions) %}
        $deleteForm = $this->createDeleteForm($entity->getId());

{%   if 'annotation' == format %}
        return array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        );
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
{%   endif %}
{% else %}
{%   if 'annotation' == format %}
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
{%   endif %}
{% endif %}
    }
