<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\HttpFoundation\Response;

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

    public function saveAccessAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $userId = $request->get('userId');
                $access = $request->get('access');

                $translator = $this->get("translator");

                if( isset($userId) && isset($access) ) {

                    $em = $this->getDoctrine()->getEntityManager();

                    $user = $em->getRepository("TecnotekExpedienteBundle:User")->find($userId);

                    $currentPrivileges = $user->getPrivileges();
                    $newPrivileges = explode(",", $access);

                    $tempNew = array();

                    $found = false;
                    //Remove already saved
                    foreach( $newPrivileges as $privilege )
                    {
                        $logger->err("Checking: " . $privilege);
                        $found = false;
                        foreach( $currentPrivileges as $currentPrivilege )
                        {
                            if($currentPrivilege->getActionMenu()->getId() == $privilege){
                                $found = true;
                                break;
                            }
                        }
                        if($found == false){
                            array_push($tempNew, $privilege);
                        }
                    }

                    $actionMenuRepository = $em->getRepository("TecnotekExpedienteBundle:ActionMenu");
                    $privilegeRepository = $em->getRepository("TecnotekExpedienteBundle:UserPrivilege");
                    foreach( $tempNew as $temp )
                    {
                        $newPrivilege = new \Tecnotek\ExpedienteBundle\Entity\UserPrivilege();
                        $newPrivilege->setUser($user);
                        $newPrivilege->setActionMenu($actionMenuRepository->find($temp));
                        $em->persist($newPrivilege);
                    }


                    $tempToDelete = array();
                    //To delete
                    foreach( $currentPrivileges as $currentPrivilege )
                    {
                        $found = false;
                        foreach( $newPrivileges as $privilege )
                        {
                            if($currentPrivilege->getActionMenu()->getId() == $privilege){
                                $found = true;
                                break;
                            }
                        }
                        if($found == false){
                            array_push($tempToDelete, $currentPrivilege);
                        }
                    }

                    foreach( $tempToDelete as $temp )
                    {                    ;
                        $em->remove($temp);
                    }

                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::createEntryAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadPrivilegesAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $userId = $request->get('userId');

                $translator = $this->get("translator");

                if( isset($userId) ) {

                    $em = $this->getDoctrine()->getEntityManager();

                    $user = $em->getRepository("TecnotekExpedienteBundle:User")->find($userId);

                    $currentPrivileges = $user->getPrivileges();

                    $privileges = array();

                    foreach( $currentPrivileges as $privilege )
                    {
                        if( sizeof($privilege->getActionMenu()->getChildrens()) == 0)
                            array_push($privileges, $privilege->getActionMenu()->getId());
                    }

                    return new Response(json_encode(array('error' => false, 'privileges' => $privileges)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::createEntryAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function showMenuAction(){

        $user= $this->get('security.context')->getToken()->getUser();

        //Get Current User Privileges
        $sql = 'SELECT m.label, m.route, m.parent_id, f.label as "father_label", f.route as "father_route"'
                . ' FROM tek_users_privileges p'
                . ' JOIN tek_actions_menu m ON m.id = p.action_menu_id'
                . ' JOIN tek_actions_menu f ON f.id = m.parent_id'
                . ' WHERE p.user_id = ' . $user->getId() . ' AND m.parent_id is not null order by f.sort_order, m.sort_order;';

        $em = $this->getDoctrine()->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $privileges = $stmt->fetchAll();

        $html = "";
        $parentId = 0;

        foreach($privileges as $privilege){
            if( $privilege['parent_id'] == $parentId) {
                    $html .= '      <li><a style="text-align: left;" href="' . ($privilege["route"] == "#"? "#":$this->generateUrl($privilege['route'])) . '">' . $privilege['label'] . '</a></li>';
            } else {
                if($parentId == 0){//First Menu
                    $html .= '<li><a href="' . ($privilege["father_route"] == "#"? "#":$this->generateUrl($privilege['father_route'])) . '"><em>' . $privilege['father_label'] . '</em><strong></strong></a>';
                    $html .= '  <ul>';
                    $html .= '      <li><a style="text-align: left;" href="' . ($privilege["route"] == "#"? "#":$this->generateUrl($privilege['route'])) . '">' . $privilege['label'] . '</a></li>';
                } else {
                    $html .= '  </ul>';
                    $html .= '</li>';

                    $html .= '<li><a href="' . ($privilege["father_route"] == "#"? "#":$this->generateUrl($privilege['father_route'])) . '"><em>' . $privilege['father_label'] . '</em><strong></strong></a>';
                    $html .= '  <ul>';
                    $html .= '      <li><a style="text-align: left;" href="' . ($privilege["route"] == "#"? "#":$this->generateUrl($privilege['route'])) . '">' . $privilege['label'] . '</a></li>';
                }
            }
            $parentId = $privilege['parent_id'];
        }

        if($html != ""){
            $html .= '  </ul>';
            $html .= '</li>';
        }

        return new Response($html);
    }
}
