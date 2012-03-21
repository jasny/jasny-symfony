
    /**
     * Creates a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/new", name="{{ route_name_prefix }}.create")
     * @Method("post")
{%   if 'new' in actions %}
     * @Template("{{ bundle }}:{{ entity }}:new.html.twig")
{%   endif %}
{% endif %}
     */
    public function createAction()
    {
        $entity  = new {{ entity_class }}();
        $request = $this->getRequest();
        $form    = $this->createForm(new {{ entity_class }}Type(), $entity);
        $form->bindRequest($request);

{% if 'new' in actions %}
        if (!$form->isValid()) {
            return $this->displayNewView($entity, $form);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($entity);
        $em->flush();
        
{%   if stringable %}
        $this->get('session')->setFlash('success', "{{ "Saved %s% '$entity'"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   else %}
        $this->get('session')->setFlash('success', "{{ "Saved the %s%"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   endif %}
{% else %}        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

{%   if stringable %}
            $this->get('session')->setFlash('success', "{{ "Saved %s% '$entity'"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   else %}
            $this->get('session')->setFlash('success', "{{ "Saved the %s%"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   endif %}
        } else {
{%   if stringable %}
            $this->get('session')->setFlash('error', "{{ "Failed to save %s% '$entity'"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   else %}
            $this->get('session')->setFlash('error', "{{ "Failed to save the %s%"|trans({'%s%': entity_desc.singular})|capitalize }}");
{%   endif %}
        }
{% endif %}

{% if 'show' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.show', array('id' => $entity->get{{ id|capitalize }}())));
{% elseif 'index' in actions %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
{% else %}
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}.edit', array('id' => $entity->get{{ id|capitalize }}())));
{% endif %}
    }
