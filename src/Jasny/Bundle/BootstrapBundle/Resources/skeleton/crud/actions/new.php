
    /**
     * Displays a form to create a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/new", name="{{ route_name_prefix }}.new")
     * @Method("get")
     * @Template()
{% endif %}
     */
    public function newAction()
    {
        $entity = new {{ entity_class }}();
        $form   = $this->createForm(new {{ entity_class }}Type(), $entity);
        
        return $this->displayNewView($entity, $form);
    }
    
{%- include 'actions/create.php' -%}
    
    private function displayNewView($entity, $form)
    {
{% if 'index' not in actions %}
        $em = $this->getDoctrine()->getEntityManager();
        $list = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findAll();
        
{% endif %}    
{% if 'annotation' == format %}
        return array(
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:new.html.twig', array(
{% endif %}
            'entity' => $entity,
            'form'   => $form->createView(),
{% if 'index' not in actions %}
            'list'   => $list,
{% endif %}
        {{ 'annotation' == format ? ')' : '))' }};
    }
