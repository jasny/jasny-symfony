
    /**
     * Finds and displays a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}", name="{{ route_name_prefix }}_show")
     * @Template()
{% endif %}
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity_desc.singular }}.');
        }

        $form = $this->createForm(new {{ entity_class }}Type(), $entity, array('read_only'=>true));
        
{% if 'index' not in actions %}
        $list = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findAll();

{% endif %}    
{% if ('delete' in actions) %}
        $deleteForm = $this->createDeleteForm($id);

{%   if 'annotation' == format %}
        return array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
{%     if 'index' not in actions %}
            'list'   => $list,
{%     endif %}
        );
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:show.html.twig', array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
{%     if 'index' not in actions %}
            'list'   => $list,
{%     endif %}
        ));
{%   endif %}
{% else %}
{%   if 'annotation' == format %} 
        return array(
            'entity' => $entity,
            'form'   => $editForm->createView(),
{%     if 'index' not in actions %}
            'list'   => $list,
{%     endif %}
        );
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:show.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
{%     if 'index' not in actions %}
            'list'   => $list,
{%     endif %}
        ));
{%   endif %}
{% endif %}
    }
