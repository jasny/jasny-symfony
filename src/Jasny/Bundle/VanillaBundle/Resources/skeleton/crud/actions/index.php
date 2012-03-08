
    /**
     * Lists all {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Template()
{% endif %}
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('{{ entity_bundle }}:{{ entity }}')->findAll();
{% if ('delete' in actions) and ('show' not in actions) and ('edit' not in actions) %}
        $deleteTokens = $this->createDeleteTokens($entities);
{% endif %}
        
{% if 'annotation' == format %}
        return array(
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig', array(
{% endif %}
            'entities' => $entities,
{% if ('delete' in actions) and ('show' not in actions) and ('edit' not in actions) %}
            'delete_tokens' => $deleteTokens,
{% endif %}
        ){% if 'annotation' != format %}){% endif %};
    }
