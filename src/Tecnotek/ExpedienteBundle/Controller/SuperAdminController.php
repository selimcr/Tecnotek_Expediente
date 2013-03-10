<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\User;
use Tecnotek\ExpedienteBundle\Entity\Route;
use Tecnotek\ExpedienteBundle\Entity\Buseta;
use Tecnotek\ExpedienteBundle\Entity\Period;
use Tecnotek\ExpedienteBundle\Entity\Grade;
use Tecnotek\ExpedienteBundle\Entity\Course;
use Tecnotek\ExpedienteBundle\Entity\CourseEntry;
use Tecnotek\ExpedienteBundle\Entity\CourseClass;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminController extends Controller
{
    
    public function indexAction($name = "John Doe")
    {
        return $this->render('TecnotekExpedienteBundle::index.html.twig', array('name' => $name));
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
        if($entity->getRouteType() == 1) {
            $students = $entity->getStudents();
        } else {
            //Get Students From Other Table
            $students = $em->getRepository("TecnotekExpedienteBundle:StudentToRoute")->findByRoute($id);
        }
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2, 'students' => $students));
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

    /* Metodos para CRUD de periods */
    public function periodListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT period FROM TecnotekExpedienteBundle:Period period";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(periods) FROM TecnotekExpedienteBundle:Period periods";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 5
        ));
    }

    public function periodCreateAction()
    {
        $entity = new Period();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\PeriodFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function periodShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Period")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\PeriodFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function periodEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Period")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\PeriodFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function periodSaveAction(){
        $entity  = new Period();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\PeriodFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_period',
                array('id' => $entity->getId(), 'menuIndex' => 5)));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 5
            ));
        }
    }

    public function periodDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Period")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_period'));
    }

    public function periodUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Period")->find($request->get('id'));
        if ( isset($entity) ) {
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\PeriodFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_period_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'updateRejected' => true, 'menuIndex' => 5
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_period'));
        }
    }
    /* Final de los metodos para CRUD de periods */

    /* Metodos para CRUD de grades */
    public function gradeListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT entity FROM TecnotekExpedienteBundle:Grade entity";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(entity) FROM TecnotekExpedienteBundle:Grade entity";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 5
        ));
    }

    public function gradeCreateAction()
    {
        $entity = new Grade();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\GradeFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function gradeShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Grade")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\GradeFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function gradeEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Grade")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\GradeFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function gradeSaveAction(){
        $entity  = new Grade();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\GradeFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_grade',
                array('id' => $entity->getId(), 'menuIndex' => 5)));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 5
            ));
        }
    }

    public function gradeDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Grade")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_grade'));
    }

    public function gradeUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Grade")->find($request->get('id'));
        if ( isset($entity) ) {
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\GradeFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_grade_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Grade/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'updateRejected' => true, 'menuIndex' => 5
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_grade'));
        }
    }
    /* Final de los metodos para CRUD de Grades */

    /* Metodos para CRUD de courses */
    public function courseListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT entity FROM TecnotekExpedienteBundle:Course entity";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(entity) FROM TecnotekExpedienteBundle:Course entity";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 5
        ));
    }

    public function courseCreateAction()
    {
        $entity = new Course();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CourseFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function courseShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Course")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CourseFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function courseEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Course")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CourseFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 5));
    }

    public function courseSaveAction(){
        $entity  = new Course();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CourseFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_course',
                array('id' => $entity->getId(), 'menuIndex' => 5)));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 5
            ));
        }
    }

    public function courseDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Course")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_course'));
    }

    public function courseUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Course")->find($request->get('id'));
        if ( isset($entity) ) {
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CourseFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_course_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Course/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'updateRejected' => true, 'menuIndex' => 5
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_course'));
        }
    }
    /* Final de los metodos para CRUD de Courses */

    public function adminPeriodAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Period")->find($id);
        $grades = $em->getRepository("TecnotekExpedienteBundle:Grade")->findAll();
        $institutions = $em->getRepository("TecnotekExpedienteBundle:Institution")->findAll();

        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_PROFESOR' ORDER BY users.firstname";
        $query = $em->createQuery($dql);
        $teachers = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/admin.html.twig', array('entity' => $entity,
            'grades' => $grades, 'teachers' => $teachers, 'institutions' => $institutions,
            'menuIndex' => 5));
    }

    public function createEditGroupAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $name = $request->get('name');
                $teacherId = $request->get('teacherId');
                $groupId = $request->get('groupId');
                $periodId = $request->get('periodId');
                $gradeId = $request->get('gradeId');
                $institutionId = $request->get('institutionId');
                $translator = $this->get("translator");

                if( isset($name) && isset($teacherId) && isset($groupId)
                    && isset($gradeId) && isset($periodId)) {
                    //Validate Parameters
                    if( strlen(trim($name)) < 1) {
                        return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                    } else {
                        $em = $this->getDoctrine()->getEntityManager();
                        if($groupId == 0) {//New Group
                            $group = new \Tecnotek\ExpedienteBundle\Entity\Group();
                            $group->setPeriod($em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId));
                            $group->setGrade($em->getRepository("TecnotekExpedienteBundle:Grade")->find($gradeId));
                        } else {//Edit Group
                            $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);
                        }
                        $group->setName($name);
                        $teacher = $em->getRepository("TecnotekExpedienteBundle:User")->find($teacherId);
                        $group->setTeacher($teacher);
                        $institution = $em->getRepository("TecnotekExpedienteBundle:Institution")->find($institutionId);
                        $group->setInstitution($institution);
                        $em->persist($group);

                        if($groupId == 0) {//New Group
                            //Get groups of period-grade to assigned teacher
                            $dql = "SELECT g FROM TecnotekExpedienteBundle:CourseClass g WHERE g.period = " . $periodId . " AND g.grade = " . $gradeId;
                            $query = $em->createQuery($dql);
                            $courses = $query->getResult();
                            foreach( $courses as $courseClass )
                            {
                                $assignedTeacher =  new \Tecnotek\ExpedienteBundle\Entity\AssignedTeacher();
                                $assignedTeacher->setCourseClass($courseClass);
                                $assignedTeacher->setGroup($group);
                                $assignedTeacher->setTeacher($teacher);
                                $em->persist($assignedTeacher);
                            }
                        }


                        $em->flush();
                        return new Response(json_encode(array('error' => false, 'message' =>$translator->trans("messages.confirmation.password.change")
                            ,'groupId' => $group->getId() )));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::changeUserPasswordAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadPeriodInfoAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $gradeId = $request->get('gradeId');

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    //Get Groups
                    $sql = "SELECT g.id, g.name, g.user_id as 'teacherId', CONCAT(u.firstname,' ',u.lastname) as 'teacherName', institution.name as 'institutionName', institution.id as 'institutionId'"
                        . " FROM tek_groups g"
                        . " JOIN tek_users u ON u.id = g.user_id"
                        . " LEFT JOIN tek_institutions institution ON institution.id = g.institution_id"
                        . " WHERE g.period_id = " . $periodId . " AND g.grade_id = " . $gradeId
                        . " ORDER BY g.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $groups = $stmt->fetchAll();

                    //Get Courses
                    $sql = "SELECT cc.id, c.name, cc.user_id as 'teacherId', (CONCAT(u.firstname, ' ', u.lastname)) as 'teacherName', c.id as 'courseId' "
                        . " FROM tek_courses c, tek_course_class cc, tek_users u"
                        . " WHERE cc.period_id = " . $periodId . " AND cc.grade_id = " . $gradeId . " AND cc.course_id = c.id AND u.id = cc.user_id"
                        . " ORDER BY c.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $courses = $stmt->fetchAll();

                    return new Response(json_encode(array('error' => false, 'groups' => $groups, 'courses' => $courses)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::changeUserPasswordAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeGroupAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $groupId = $request->get('groupId');
                $translator = $this->get("translator");

                if( isset($groupId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $entity = $em->getRepository("TecnotekExpedienteBundle:Group")->find( $groupId );
                    if ( isset($entity) ) {
                        $em->remove($entity);
                        $em->flush();
                    }
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::removeGroupAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeEntryAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $entryId = $request->get('entryId');
                $translator = $this->get("translator");

                if( isset($entryId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $entity = $em->getRepository("TecnotekExpedienteBundle:CourseEntry")->find( $entryId );
                    if ( isset($entity) ) {
                        $em->remove($entity);
                        $em->flush();
                    }
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::removeEntryAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function courseAssociationRemoveAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $associationId = $request->get('associationId');
                $translator = $this->get("translator");

                if( isset($associationId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $entity = $em->getRepository("TecnotekExpedienteBundle:CourseClass")->find( $associationId );
                    if ( isset($entity) ) {
                        $em->remove($entity);
                        $em->flush();
                    }
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::removeGroupAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadAvailableCoursesAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $gradeId = $request->get('gradeId');

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    //Get Courses
                    $sql = "SELECT c.id, c.name"
                        . " FROM tek_courses c"
                        . " WHERE c.id not in (select cc.course_id from tek_course_class cc where cc.period_id = " . $periodId . " AND cc.grade_id = " . $gradeId . ")"
                        . " ORDER BY c.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $courses = $stmt->fetchAll();
                    return new Response(json_encode(array('error' => false, 'courses' => $courses)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::loadAvailableCoursesAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function associateCourseAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $gradeId = $request->get('gradeId');
                $courseId = $request->get('courseId');
                $teacherId = $request->get('teacherId');

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId) && isset($courseId) && isset($teacherId)) {

                    $courseClass = new \Tecnotek\ExpedienteBundle\Entity\CourseClass();
                    $em = $this->getDoctrine()->getEntityManager();
                    $teacher = $em->getRepository("TecnotekExpedienteBundle:User")->find($teacherId);
                    $courseClass->setPeriod($em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId));
                    $courseClass->setGrade($em->getRepository("TecnotekExpedienteBundle:Grade")->find($gradeId));
                    $courseClass->setTeacher($teacher);
                    $courseClass->setCourse($em->getRepository("TecnotekExpedienteBundle:Course")->find($courseId));
                    $em->persist($courseClass);

                    //Get groups of period-grade to assigned teacher
                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g WHERE g.period = " . $periodId . " AND g.grade = " . $gradeId;
                    $query = $em->createQuery($dql);
                    $groups = $query->getResult();
                    foreach( $groups as $group )
                    {
                        $assignedTeacher =  new \Tecnotek\ExpedienteBundle\Entity\AssignedTeacher();
                        $assignedTeacher->setCourseClass($courseClass);
                        $assignedTeacher->setGroup($group);
                        $assignedTeacher->setTeacher($teacher);
                        $em->persist($assignedTeacher);
                    }

                    $em->flush();

                    return new Response(json_encode(array('error' => false, 'courseClass' => $courseClass->getId())));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::loadAvailableCoursesAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }
    /**/

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
                        $em->flush();
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

    public  function enterQualificationsAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();

        $dql = "SELECT e FROM TecnotekExpedienteBundle:CourseEntry e WHERE e.parent IS NULL ORDER BY e.sortOrder";
        $query = $em->createQuery($dql);
        $logger->err("-----> SQL: " . $query->getSQL());
        $entries = $query->getResult();
        $logger->err("-----> ENTRIES: " . sizeof($entries));
        $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
        $html = '<div class="itemPromedioPeriodo itemHeader" style="margin-left: 0px; color: #fff;">Promedio Trimestral</div>';
        /*
            <div class="itemHeader itemNota" style="margin-left: 125px;">Tarea 2</div>
            <div class="itemHeader itemPromedio" style="margin-left:150px;">Promedio Tareas </div>
            <div class="itemHeader itemPorcentage" style="margin-left: 175px;">10 % Tarea</div>

        <div class="itemHeaderCode itemNota" style="margin-left: 0px;"></div>
        */
        $marginLeft = 34;
        $marginLeftCode = 62;
        $htmlCodes = '<div class="itemPromedioPeriodo itemHeaderCode" style="color: #fff;">SCIE</div>';
        $jumpRight = 34;
        $width = 32;
        $html3 = '<div class="itemHeader2 itemPromedioPeriodo" style="width: 32px; color: #fff;">TRIM</div>';
        $studentRow = "";
        $studentsHeader = '';
        $colors = array(
            "one" => "#38255c",
            "two" => "#04D0E6"
        );
        foreach( $entries as $entry )
        {
            $temp = $entry;
            $childrens = $temp->getChildrens();
            $size = sizeof($childrens);
            $logger->err("-----> Childrens of " . $temp->getName() . ": " . sizeof($childrens));

            if($size == 0){//No child
                $studentRow .= '<input type="text" class="textField itemNota" tipo="1" rel="total_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '" std="stdId" >';
                $htmlCodes .= '<div class="itemHeaderCode itemNota"></div>';
                $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getName() . '</div>';
                $marginLeft += $jumpRight; $marginLeftCode += 25;

                $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage">' . $temp->getCode() . '</div>';
                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                $marginLeft += $jumpRight; $marginLeftCode += 25;

                $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * 2) + 2) . 'px">' . $temp->getName() . '</div>';
            } else {
            if($size == 1){//one child
                foreach ( $childrens as $child){
                    $htmlCodes .= '<div class="itemHeaderCode itemNota"></div>';
                    $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $child->getName() . '</div>';
                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                }
                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage"></div>';
                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                $marginLeft += $jumpRight; $marginLeftCode += 25;
            } else {//two or more
                foreach ( $childrens as $child){
                    //$studentRow .= '<input type="text" class="textField itemNota">';
                    $studentRow .= '<input type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '" std="stdId" >';
                    $htmlCodes .= '<div class="itemHeaderCode itemNota">' . $child->getCode() . '</div>';
                    $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $child->getName() . '</div>';
                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                }
                $studentRow .= '<div class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</div>';
                $htmlCodes .= '<div class="itemHeaderCode itemPromedio"></div>';
                $html .= '<div class="itemHeader itemPromedio" style="margin-left:' . $marginLeft . 'px;">Promedio ' . $temp->getName() . ' </div>';
                $marginLeft += $jumpRight; $marginLeftCode += 25;

                //$studentRow .= '<div class="itemHeaderCode itemPorcentage">-</div>';
                $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage">' . $temp->getCode() . '</div>';
                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                $marginLeft += $jumpRight; $marginLeftCode += 25;

                $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * ($size + 2)) + (($size + 1) * 2)) . 'px">' . $temp->getName() . '</div>';
            }
            }
            /*$assignedTeacher =  new \Tecnotek\ExpedienteBundle\Entity\AssignedTeacher();
            $assignedTeacher->setCourseClass($courseClass);
            $assignedTeacher->setGroup($group);
            $assignedTeacher->setTeacher($teacher);
            $em->persist($assignedTeacher);*/
        }

        $html = $htmlCodes . '<div class="clear"></div>' .
            '<div style="position: relative; height: 152px; margin-left: -59px;">' . $html . '</div>' . '<div class="clear"></div>' .
            $html3;

        $students = $em->getRepository("TecnotekExpedienteBundle:Student")->findAll();
        foreach($students as $student){
            $studentsHeader .= '<div class="itemCarne">' . $student->getCarne() . '</div><div class="itemEstudiante">' . $student . '</div><div class="clear"></div>';
            $row = str_replace("stdId", $student->getId(), $studentRow);
            $html .=  '<div class="clear"></div><div id="total_trim_' . $student->getId() . '" class="itemHeaderCode itemPromedioPeriodo"style="color: #fff;">-</div>' . $row;
        }

        //'<div class="clear"></div>' . $studentRow . '<div class="clear"></div>' . $studentRow . '<div class="clear"></div>' . $studentRow;

       /* $entries = $em->getRepository("TecnotekExpedienteBundle:CourseEntry")->findAll();

        $dql = "SELECT users FROM TecnotekExpedienteBundle:User users JOIN users.roles r WHERE r.role = 'ROLE_PROFESOR' ORDER BY users.firstname";
        $query = $em->createQuery($dql);
        $teachers = $query->getResult();*/

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Qualification/index.html.twig', array('table' => $html,
            'studentsHeader' => $studentsHeader, 'menuIndex' => 5));
    }

    public function loadEntriesByCourseAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $gradeId = $request->get('gradeId');
                $courseId = $request->get('courseId');

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId) && isset($courseId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $dql = "SELECT e FROM TecnotekExpedienteBundle:CourseEntry e, TecnotekExpedienteBundle:CourseClass cc WHERE e.parent IS NULL AND e.courseClass = cc AND cc.period = $periodId AND cc.grade = $gradeId And cc.course = $courseId ORDER BY e.sortOrder";
                    $query = $em->createQuery($dql);
                    $entries = $query->getResult();

                    $colors = array(
                        "one" => "#38255c",
                        "two" => "#04D0E6"
                    );
                    $html = "";
                    $entriesOptions = "";
                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();

                    $dql = "SELECT cc FROM TecnotekExpedienteBundle:CourseClass cc WHERE cc.period = $periodId AND cc.grade = $gradeId And cc.course = $courseId";
                    $query = $em->createQuery($dql);
                    $results = $query->getResult();
                    $courseClassId = 0;
                    foreach( $results as $result ){
                        $courseClassId = $result->getId();
                    }


                    foreach( $entries as $entry ){
                        $temp = $entry;
                        $courseClassId = $temp->getCourseClass()->getId();
                        $childrens = $temp->getChildrens();
                        $size = sizeof($childrens);

                        $entriesOptions .= '<option value="' . $entry->getId() . '">' . $entry->getName() . '</option>';
                        $html .= '<div id="entryRow_' . $entry->getId() . '" class="row userRow tableRowOdd">';
                        $html .= '    <div id="entryNameField_' . $entry->getId() . '" name="entryNameField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getName() . '</div>';
                        $html .= '    <div id="entryCodeField_' . $entry->getId() . '" name="entryCodeField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getCode() . '</div>';
                        $html .= '    <div id="entryPercentageField_' . $entry->getId() . '" name="entryPercentageField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getPercentage() . '</div>';
                        $html .= '    <div id="entryMaxValueField_' . $entry->getId() . '" name="entryMaxValueField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getMaxValue() . '</div>';
                        $html .= '    <div id="entryOrderField_' . $entry->getId() . '" name="entryOrderField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getSortOrder() . '</div>';
                        $html .= '    <div id="entryParentField_' . $entry->getId() . '" name="entryParentField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getParent() . '</div>';

                        $html .= '    <div class="right imageButton deleteButton deleteEntry" style="height: 16px;" title="Eliminar"  rel="' . $entry->getId() . '"></div>';
                        $html .= '    <div class="right imageButton editButton editEntry" title="Editar" rel="' . $entry->getId() . '" entryParent="0"></div>';
                        $html .= '    <div class="clear"></div>';
                        $html .= '</div>';

                        foreach ( $childrens as $child){
                            $html .= '<div id="entryRow_' . $child->getId() . '" class="row userRow tableRowOdd">';
                            $html .= '    <div id="entryNameField_' . $child->getId() . '" name="entryNameField_' . $child->getId() . '" class="option_width" style="float: left; width: 150px;">' . $child->getName() . '</div>';
                            $html .= '    <div id="entryCodeField_' . $child->getId() . '" name="entryCodeField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getCode() . '</div>';
                            $html .= '    <div id="entryPercentageField_' . $child->getId() . '" name="entryPercentageField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getPercentage() . '</div>';
                            $html .= '    <div id="entryMaxValueField_' . $child->getId() . '" name="entryMaxValueField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getMaxValue() . '</div>';
                            $html .= '    <div id="entryOrderField_' . $child->getId() . '" name="entryOrderField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getSortOrder() . '</div>';
                            $html .= '    <div id="entryParentField_' . $child->getId() . '" name="entryParentField_' . $child->getId() . '" class="option_width" style="float: left; width: 150px;">' . $child->getParent() . '</div>';

                            $html .= '    <div class="right imageButton deleteButton deleteEntry" style="height: 16px;" title="Eliminar"  rel="' . $child->getId() . '"></div>';
                            $html .= '    <div class="right imageButton editButton editEntry" title="Editar" rel="' . $child->getId() . '" entryParent="' . $entry->getId() . '"></div>';
                            $html .= '    <div class="clear"></div>';
                            $html .= '</div>';
                        }

                        /*if($size == 0){//No child
                            $studentRow .= '<input type="text" class="textField itemNota" tipo="1" rel="total_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '" std="stdId" >';
                            $htmlCodes .= '<div class="itemHeaderCode itemNota"></div>';
                            $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getName() . '</div>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                            $htmlCodes .= '<div class="itemHeaderCode itemPorcentage">' . $temp->getCode() . '</div>';
                            $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * 2) + 2) . 'px">' . $temp->getName() . '</div>';
                        } else {
                            if($size == 1){//one child
                                foreach ( $childrens as $child){
                                    $htmlCodes .= '<div class="itemHeaderCode itemNota"></div>';
                                    $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $child->getName() . '</div>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }
                                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage"></div>';
                                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;
                            } else {//two or more
                                foreach ( $childrens as $child){
                                    //$studentRow .= '<input type="text" class="textField itemNota">';
                                    $studentRow .= '<input type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '" std="stdId" >';
                                    $htmlCodes .= '<div class="itemHeaderCode itemNota">' . $child->getCode() . '</div>';
                                    $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $child->getName() . '</div>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }
                                $studentRow .= '<div class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</div>';
                                $htmlCodes .= '<div class="itemHeaderCode itemPromedio"></div>';
                                $html .= '<div class="itemHeader itemPromedio" style="margin-left:' . $marginLeft . 'px;">Promedio ' . $temp->getName() . ' </div>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                //$studentRow .= '<div class="itemHeaderCode itemPorcentage">-</div>';
                                $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage">' . $temp->getCode() . '</div>';
                                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * ($size + 2)) + (($size + 1) * 2)) . 'px">' . $temp->getName() . '</div>';
                            }
                        }*/
                    }

                    return new Response(json_encode(array('error' => false, 'entries' => $entriesOptions, 'entriesHtml' => $html, 'courseClassId' => $courseClassId)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::loadEntriesByCourseAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function createEntryAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $parentId = $request->get('parentId');
                $name = $request->get('name');
                $code = $request->get('code');
                $maxValue = $request->get('maxValue');
                $percentage = $request->get('percentage');
                $sortOrder = $request->get('sortOrder');
                $courseClassId = $request->get('courseClassId');
                $entryId = $request->get('entryId');

                $translator = $this->get("translator");

                if( isset($parentId) && isset($name) && isset($code) && isset($maxValue) && isset($percentage)
                    && isset($sortOrder) && isset($courseClassId) && isset($entryId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    if($entryId == 0){//Is new
                        $courseEntry = new CourseEntry();
                        $courseEntry->setCourseClass($em->getRepository("TecnotekExpedienteBundle:CourseClass")->find($courseClassId));
                    } else {//Is editing
                        $courseEntry = $em->getRepository("TecnotekExpedienteBundle:CourseEntry")->find($entryId);
                    }

                    $courseEntry->setName($name);
                    $courseEntry->setCode($code);
                    $courseEntry->setMaxValue($maxValue);
                    $courseEntry->setPercentage($percentage);
                    $courseEntry->setSortOrder($sortOrder);

                    if($parentId == 0){
                        $courseEntry->removeParent();
                    }else {
                        $parent = $em->getRepository("TecnotekExpedienteBundle:CourseEntry")->find($parentId);
                        if(isset($parent)) $courseEntry->setParent($parent);
                    }

                    $em->persist($courseEntry);
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
}
