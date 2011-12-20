
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
        
{%   if 'annotation' == format %}
        return array('entities' => $entities, 'delete_tokens' => $delete_tokens);
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig', array(
            'entities' => $entities,
            'delete_tokens' => $deleteTokens,
        ));
{%   endif %}
{% else %}
{%   if 'annotation' == format %}
        return array('entities' => $entities);
{%   else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig', array(
            'entities' => $entities,
        ));
{%   endif %}
{% endif %}
    }
