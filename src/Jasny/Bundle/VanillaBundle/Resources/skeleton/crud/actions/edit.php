
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}{% if 'show' in actions %}/edit{% endif %}", name="{{ route_name_prefix }}.edit")
     * @Method("get")
     * @Template()
{% endif %}
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if (!$entity) throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }}.');

        $form = $this->createForm(new {{ entity_class }}Type(), $entity);

        return $this->displayEditView($entity, $form);
    }
    
{%- include 'actions/update.php' -%}

    private function displayEditView($entity, $form)
    {
{% if 'index' not in actions %}
        $em = $this->getDoctrine()->getEntityManager();
        $list = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findAll();
        
{% endif %}    
{% if ('delete' in actions) and ('show' not in actions) %}
        $deleteForm = $this->createDeleteForm($entity->getId());

{% endif %}
{% if 'annotation' == format %}
        return array(
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', array(
{% endif %}
            'entity'      => $entity,
            'form'        => $form->createView(),
{%   if ('delete' in actions) and ('show' not in actions) %}
            'delete_form' => $deleteForm->createView(),
{%   endif %}
{%   if 'index' not in actions %}
            'list'        => $list,
{%   endif %}
        {{ 'annotation' == format ? ')' : '))' }};
    }
