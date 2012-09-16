<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\User;
use Tecnotek\ExpedienteBundle\Entity\Route;
use Tecnotek\ExpedienteBundle\Entity\Buseta;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminController extends Controller
{
    
    public function indexAction($name = "John Doe")
    {
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:index.html.twig', array('name' => $name));
    }
    
    public function administradorListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_ADMIN'";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(users) FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_ADMIN'";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Administrador/list.html.twig', array(
            'pagination' => $pagination, 'isTeacher' => true, 'rowsPerPage' => $rowsPerPage
        ));
    }
    
    public function administradorCreateAction()
    {
        $entity = new User();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);        
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Administrador/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function administradorShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Administrador/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function administradorSaveAction(){        
        $entity  = new User();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        $form->bindRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $role = $em->getRepository('TecnotekExpedienteBundle:Role')->
                findOneBy(array('role' => 'ROLE_ADMIN'));
            $entity->getUserRoles()->add($role);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_administrador',
                            array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Administrador/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView()
            ));
        }
    }

    public function administradorDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_administrador'));
    }

    public function administradorUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $request->get('userId'));

        if ( isset($entity) ) {
            $entity->setFirstname($request->get('firstname'));
            $entity->setLastname($request->get('lastname'));
            $entity->setUsername($request->get('username'));
            $entity->setEmail($request->get('email'));
            $entity->setActive(($request->get('active') == "on"));
            $em->persist($entity);
            $em->flush();
            $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Administrador/show.html.twig', array('entity' => $entity,
                'form'   => $form->createView()));
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_administrador'));
        }
    }

    /* Metodos para CRUD de Coordinador */
    public function coordinadorListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_COORDINADOR'";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(users) FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_COORDINADOR'";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Coordinador/list.html.twig', array(
            'pagination' => $pagination, 'isTeacher' => true, 'rowsPerPage' => $rowsPerPage
        ));
    }

    public function coordinadorCreateAction()
    {
        $entity = new User();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Coordinador/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function coordinadorShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Coordinador/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function coordinadorSaveAction(){
        $entity  = new User();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $role = $em->getRepository('TecnotekExpedienteBundle:Role')->
                findOneBy(array('role' => 'ROLE_COORDINADOR'));
            $entity->getUserRoles()->add($role);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_coordinador',
                array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Coordinador/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView()
            ));
        }
    }

    public function coordinadorDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_coordinador'));
    }

    public function coordinadorUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $request->get('userId'));

        if ( isset($entity) ) {
            $entity->setFirstname($request->get('firstname'));
            $entity->setLastname($request->get('lastname'));
            $entity->setUsername($request->get('username'));
            $entity->setEmail($request->get('email'));
            $entity->setActive(($request->get('active') == "on"));
            $em->persist($entity);
            $em->flush();
            $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Coordinador/show.html.twig', array('entity' => $entity,
                'form'   => $form->createView()));
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_coordinador'));
        }
    }
    /* Final de los metodos para CRUD de Coordinador*/

    /* Metodos para CRUD de Profesor */
    public function profesorListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_PROFESOR'";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(users) FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_PROFESOR'";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Profesor/list.html.twig', array(
            'pagination' => $pagination, 'isTeacher' => true, 'rowsPerPage' => $rowsPerPage
        ));
    }

    public function profesorCreateAction()
    {
        $entity = new User();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Profesor/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function profesorShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Profesor/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView()));
    }

    public function profesorSaveAction(){
        $entity  = new User();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $role = $em->getRepository('TecnotekExpedienteBundle:Role')->
                findOneBy(array('role' => 'ROLE_PROFESOR'));
            $entity->getUserRoles()->add($role);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_profesor',
                array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Profesor/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView()
            ));
        }
    }

    public function profesorDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_profesor'));
    }

    public function profesorUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:User")->find( $request->get('userId'));

        if ( isset($entity) ) {
            $entity->setFirstname($request->get('firstname'));
            $entity->setLastname($request->get('lastname'));
            $entity->setUsername($request->get('username'));
            $entity->setEmail($request->get('email'));
            $entity->setActive(($request->get('active') == "on"));
            $em->persist($entity);
            $em->flush();
            $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\UserFormType(), $entity);
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Profesor/show.html.twig', array('entity' => $entity,
                'form'   => $form->createView()));
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_profesor'));
        }
    }
    /* Final de los metodos para CRUD de Profesor*/

    /* Metodos para CRUD de routes */
    public function routeListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT routes FROM TecnotekExpedienteBundle:Route routes";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(routes) FROM TecnotekExpedienteBundle:Route routes";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 2
        ));
    }

    public function routeCreateAction()
    {
        $entity = new Route();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\RouteFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function routeShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Route")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\RouteFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function routeSaveAction(){
        $entity  = new Route();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\RouteFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_route', array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/new.html.twig', array(
                'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 2
            ));
        }
    }

    public function routeDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Route")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_route'));
    }

    public function routeEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Route")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\RouteFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function routeUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Route")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\RouteFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_route_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 2
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_route'));
        }

    }
    /* Final de los metodos para CRUD de routes*/

    /* Metodos para CRUD de buses */
    public function busListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT buses FROM TecnotekExpedienteBundle:Buseta buses";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(buses) FROM TecnotekExpedienteBundle:Buseta buses";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 2
        ));
    }

    public function busCreateAction()
    {
        $entity = new Buseta();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\BusFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function busShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Buseta")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\BusFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function busEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Buseta")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\BusFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2));
    }

    public function busSaveAction(){
        $entity  = new Buseta();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\BusFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_bus',
                array('id' => $entity->getId(), 'menuIndex' => 2)));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 2
            ));
        }
    }

    public function busDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Buseta")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_bus'));
    }

    public function busUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Buseta")->find($request->get('id'));
        if ( isset($entity) ) {
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\BusFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_bus_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Bus/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'updateRejected' => true, 'menuIndex' => 2
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_bus'));
        }
    }
    /* Final de los metodos para CRUD de buses*/

    public function getPaginationPage($dql, $currentPage, $rowsPerPage){
        if(isset($currentPage) == false || $currentPage <= 1){
            return 1;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery($dql);
            $total = $query->getSingleScalarResult();
            //Check if current page has Results
            if( $total > (($currentPage - 1) * $rowsPerPage)){//the page has results
                return $currentPage;
            } else {
                $x = intval($total / $rowsPerPage);
                if($x == 0){
                    return 1;
                } else {
                    if( $total % ($x * $rowsPerPage) > 0){
                        return $x+1;
                    } else {
                        return $x;
                    }
                }
            }
        }
    }

    public function changeUserPasswordAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $newPassword = $request->get('newPassword');
                $confirmPassword = $request->get('confirmPassword');
                $userId = $request->get('userId');
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository("TecnotekExpedienteBundle:User")->find($userId);
                $translator = $this->get("translator");
                if ( isset($user) ) {
                    $defaultController = new DefaultController();
                    $error = $defaultController->validateUserPassword($newPassword, $confirmPassword, $translator);
                    if ( isset($error) ) {
                        return new Response(json_encode(array('error' => true, 'message' => $error)));
                    } else {
                        $user->setPassword($newPassword);
                        $em->persist($user);
                        return new Response(json_encode(array('error' => false, 'message' =>$translator->trans("messages.confirmation.password.change"))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.entity.not.found"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::changeUserPasswordAction [' . $info . "]");
                return new Response($info);
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

}
