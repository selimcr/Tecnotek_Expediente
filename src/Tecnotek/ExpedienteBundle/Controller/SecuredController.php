<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/secured")
 */
class SecuredController extends Controller
{
    /**
     * @Route("/login", name="_expediente_login")
     * @Template()
     */
    public function loginAction()
    {
        if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        );
    }

    /**
     * @Route("/login_check", name="_expediente_security_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="_expediente_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/hello", defaults={"name"="World"}),
     * @Route("/hello/{name}", name="_expediente_secured_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

    /**
     * @Route("/admin/{name}", name="_expediente_secured_index_admin")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function indexadminAction($name)
    {
        return array('name' => $name);
    }

    public function accessPageAction(){

        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_COORDINADOR' order by users.firstname, users.lastname";
        $query = $em->createQuery($dql);
        $users = $query->getResult();

        $dql = "SELECT e FROM TecnotekExpedienteBundle:ActionMenu e WHERE e.parent is null order by e.sortOrder";
        $query = $em->createQuery($dql);
        $permisos = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Users/access.html.twig', array('menuIndex' => 1,
            'users' => $users, 'permisos' => $permisos));
    }
}
