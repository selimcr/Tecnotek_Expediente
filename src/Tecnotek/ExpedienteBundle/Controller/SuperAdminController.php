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
use Tecnotek\ExpedienteBundle\Entity\StudentQualification;
use Tecnotek\ExpedienteBundle\Entity\SubCourseEntry;
use Tecnotek\ExpedienteBundle\Entity\AssignedTeacher;

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
        $routes = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ruta/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 2, 'students' => $students, 'routes' => $routes));
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

    public function loadAvailableCoursesGroupsAction(){   //2016 - 4
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                //$gradeId = $request->get('gradeId');

                $translator = $this->get("translator");

                if(isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    //Get Courses
                    $sql = "SELECT c.id, c.name"
                        . " FROM tek_courses c"
                        //. " WHERE c.id not in (select cc.course_id from tek_course_class cc where cc.period_id = " . $periodId . " AND cc.grade_id = " . $gradeId . ")"
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
                $logger->err('SuperAdmin::loadAvailableCoursesGroupsAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadAvailableCourseClassAction(){   //2016 -4
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $groupId = $request->get('groupId');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    //Get Courses
                    $sql = "SELECT cc.id as courseclass, c.id as course, c.name as name"
                        . " FROM tek_courses c, tek_course_class cc"
                        . " WHERE cc.course_id = c.id and cc.period_id = " . $periodId . " AND cc.grade_id = " . $gradeId . " "
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
                $logger->err('SuperAdmin::loadAvailableCourseClassAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadCoursesExtraPointsAction(){   //2016 -5
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');

                /*$keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];*/

                $translator = $this->get("translator");

                if( isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    //Get Courses
                    $sql = "SELECT c.id, c.name as name"
                        . " FROM tek_courses c"
                        //. " WHERE cc.course_id = c.id and cc.period_id = " . $periodId . " AND cc.grade_id = " . $gradeId . " "
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
                $logger->err('SuperAdmin::loadAvailableCourseClassAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadCoursesByTeacherAction(){ //2016 - 4
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $teacherId = $request->get('teacherId');

                $translator = $this->get("translator");

                if( isset($periodId) && isset($teacherId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    /* $dql = "SELECT a FROM TecnotekExpedienteBundle:AssignedTeacher a WHERE a.period = $periodId AND a.user = $teacherId";
                     $query = $em->createQuery($dql);
                     $entries = $query->getResult();
 */

                    $stmt = $this->getDoctrine()->getEntityManager()
                        ->getConnection()
                        ->prepare('SELECT t.id as id, t.group_id, c.id as course, c.name as name, cc.id as courseclass, concat(g.grade_id,"-",g.name)  as groupname
                                    FROM `tek_assigned_teachers` t, tek_courses c, tek_course_class cc, tek_groups g
                                    where cc.course_id = c.id and t.course_class_id = cc.id and g.id = t.group_id and cc.period_id = "'.$periodId.'" and t.user_id = "'.$teacherId.'"');
                    $stmt->execute();
                    $entity = $stmt->fetchAll();

                    $colors = array(
                        "one" => "#38255c",
                        "two" => "#04D0E6"
                    );
                    $html = "";
                    $groupOptions = "";

                    foreach( $entity as $entry ){
                        $html .= '<div id="courseTeacherRows_' . $entry['id'] . '" class="row userRow tableRowOdd">';
                        $html .= '    <div id="entryNameField_' . $entry['courseclass'] . '" name="entryNameField_' . $entry['courseclass'] . '" class="option_width" style="float: left; width: 150px;">' . $entry['name'] . '</div>';
                        $html .= '    <div id="entryCodeField_' . $entry['group_id'] . '" name="entryCodeField_' . $entry['group_id'] . '" class="option_width" style="float: left; width: 100px;">' . $entry['groupname'] . '</div>';
                        $html .= '    <div class="right imageButton deleteButton deleteTeacherAssigned" style="height: 16px;" title="Eliminar"  rel="' . $entry['id'] . '"></div>';
                        $html .= '    <div class="clear"></div>';
                        $html .= '</div>';

                    }

                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g WHERE g.period = $periodId";
                    $query = $em->createQuery($dql);
                    $results = $query->getResult();
                    $courseClassId = 0;
                    $groupOptions .= '<option value="0"></option>';
                    foreach( $results as $result ){
                        $groupOptions .= '<option value="' . $result->getId() . '-' . $result->getGrade()->getId() . '">'. $result->getGrade() . '-'. $result->getName() . '</option>';
                    }

                    return new Response(json_encode(array('error' => false, 'groupOptions' => $groupOptions, 'entriesHtml' => $html)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::loadCoursesByTeacherAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeTeacherAssignedAction(){    /// 2016 - 4

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $teacherAssignedId = $request->get('teacherAssignedId');
                $translator = $this->get("translator");

                if( isset($teacherAssignedId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $entity = $em->getRepository("TecnotekExpedienteBundle:AssignedTeacher")->find( $teacherAssignedId );
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
                $logger->err('SuperAdmin::removeTeacherAssignedAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function createTeacherAssignedAction(){ //2016 - 4 temp
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $teacherId = $request->get('teacherId');
                $courseClassId = $request->get('courseClassId');
                $groupId = $request->get('groupId');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];

                $translator = $this->get("translator");

                if( isset($courseClassId) && isset($groupId) && isset($teacherId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $assignedTeacher = new AssignedTeacher();
                    $assignedTeacher->setCourseClass($em->getRepository("TecnotekExpedienteBundle:CourseClass")->find($courseClassId));
                    $assignedTeacher->setGroup($em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId));
                    $assignedTeacher->setTeacher($em->getRepository("TecnotekExpedienteBundle:User")->find($teacherId));

                    $em->persist($assignedTeacher);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::createTeacherAssignedAction [' . $info . "]");
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

        $dql = "SELECT p FROM TecnotekExpedienteBundle:Period p ORDER BY p.year";
        $query = $em->createQuery($dql);
        $periods = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Qualification/index.html.twig', array('periods' => $periods,
            'menuIndex' => 5));
    }

    public  function enterQualificationssssAction(){
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

    /******************************* Funciones para la administracion de las calificaciones *******************************/
    public function loadLevelsOfPeriodAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $translator = $this->get("translator");

                if( isset($periodId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //Get Groups
                    $sql = "SELECT grade.id as 'id', grade.name as 'name'
                            FROM tek_groups g, tek_grades grade
                            WHERE g.period_id = " . $periodId . " AND g.grade_id = grade.id
                            GROUP BY grade.id
                            ORDER BY grade.id, g.name;";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $levels = $stmt->fetchAll();

                    return new Response(json_encode(array('error' => false, 'levels' => $levels)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadGroupsOfPeriodAndLevelAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $levelId = $request->get('levelId');
                $translator = $this->get("translator");

                if( isset($periodId) && isset($levelId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //Get Groups
                    $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                        " FROM tek_groups g, tek_grades grade" .
                        " WHERE g.period_id = " . $periodId  . " AND g.grade_id = grade.id";

                    if($levelId != 0){
                        $sql .= " AND grade.id = " . $levelId;
                    }

                    $sql .+
                        " GROUP BY g.id" .
                        " ORDER BY grade.id, g.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $groups = $stmt->fetchAll();

                    return new Response(json_encode(array('error' => false, 'groups' => $groups)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadGroupsOfPeriodAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $translator = $this->get("translator");

                if( isset($periodId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $user = $this->get('security.context')->getToken()->getUser();

                    //Get Groups
                    $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                        " FROM tek_groups g, tek_grades grade" .
                        " WHERE g.period_id = " . $periodId  . " AND g.institution_id in ("
                        . $user->getInstitutionsIdsStr() . ")"
                        . " AND g.grade_id = grade.id" .
                        " GROUP BY g.id" .
                        " ORDER BY grade.id, g.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $groups = $stmt->fetchAll();

                    return new Response(json_encode(array('error' => false, 'groups' => $groups)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadCourseOfGroupByTeacherAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $keywords = preg_split("/[\s-]+/", $request->get('groupId'));
                $groupId = $keywords[0];

                $translator = $this->get("translator");

                if( isset($groupId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //Get Courses
                    $sql = "SELECT course.id, course.name " .
                        " FROM tek_assigned_teachers tat, tek_course_class tcc, tek_courses course " .
                        " WHERE tat.group_id = " . $groupId . " AND tat.course_class_id =  tcc.id AND tcc.course_id = course.id" .
                        " ORDER BY course.name ";

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
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadPrintableGroupQualificationsAction(){
        $logger = $this->get('logger');
        $translator = $this->get("translator");

        try {
            $request = $this->get('request');
            $periodId = $request->get('periodId');
            $groupId = $request->get('groupId');
            $courseId = $request->get('courseId');

            if( !isset($courseId) || !isset($groupId) || !isset($periodId)) {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }

            $keywords = preg_split("/[\s-]+/", $groupId);
            $groupId = $keywords[0];
            $gradeId = $keywords[1];

            if( isset($courseId) && isset($groupId) && isset($periodId)) {
                $em = $this->getDoctrine()->getEntityManager();

                $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find( $groupId );
                $grade = $group->getGrade();
                $course = $em->getRepository("TecnotekExpedienteBundle:Course")->find( $courseId );

                $title = "Calificaciones del grupo: " . $group->getGrade() . "-" . $group . " en la materia: " . $course. " en el Periodo: " . $periodId;

                $dql = "SELECT ce FROM TecnotekExpedienteBundle:CourseEntry ce "
                    . " JOIN ce.courseClass cc"
                    . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                    . " AND cc.course = " . $courseId
                    . " ORDER BY ce.sortOrder";
                $query = $em->createQuery($dql);
                $entries = $query->getResult();
                $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
                $html =  '<tr  style="height: 75px; line-height: 0px;"><td class="celesteOscuro" style="min-width: 75px; font-size: 12px; height: 75px;">Carne</td>';
                $html .=  '<td class="celesteClaro bold" style="min-width: 300px; font-size: 12px; height: 75px;">Estudiante</td>';
                $html .= '<td class="azul" style="vertical-align: bottom; padding: 1.5625em 0.625em; height: 75px;"><div class="verticalText" style="color: #000;">Promedio</div></td>';

                $marginLeft = 48;
                $marginLeftCode = 62;
                $htmlCodes =  '<tr  style="height: 25px;"><td class="celesteOscuro" style="width: 75px; font-size: 10px;"></td>';
                $htmlCodes .=  '<td class="celesteClaro bold" style="width: 300px; font-size: 8px;"></td>';
                $htmlCodes .= '<td class="azul" style="color: #000;"></td>';
                $jumpRight = 46;
                $width = 44;

                $html3 =  '<tr style="height: 25px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="min-width: 75px; font-size: 12px;">Carne</td>';
                $html3 .=  '<td class="celesteClaro bold headcolnombre" style="min-width: 300px; font-size: 12px;">Estudiante</td>';
                $html3 .= '<td class="azul headcoltrim" style="color: #000;">TRIM1111</td>';
                $studentRow = '';
                $studentsHeader = '';
                $colors = array(
                    "one" => "#38255c",
                    "two" => "#04D0E6"
                );

                $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
                    . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
                    . " ORDER BY std.lastname, std.firstname";
                $query = $em->createQuery($dql);
                $students = $query->getResult();

                $studentsCount = sizeof($students);
                $rowIndex = 1;
                $colsCounter = 1;

                $specialCounter = 1;

                foreach( $entries as $entry )
                {
                    $temp = $entry;
                    $childrens = $temp->getChildrens();
                    $size = sizeof($childrens);
                    if($size == 0){//No child
                        //Find SubEntries
                        $dql = "SELECT ce FROM TecnotekExpedienteBundle:SubCourseEntry ce "
                            . " WHERE ce.parent = " . $temp->getId()  . " AND ce.group = " . $groupId
                            . " ORDER BY ce.sortOrder";
                        $query = $em->createQuery($dql);
                        $subentries = $query->getResult();

                        $size = sizeof($subentries);

                        if($size > 1){
                            foreach( $subentries as $subentry )
                            {

                                //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                $studentRow .= '<td class="celesteClaro noPrint"><div><input disabled="disabled" tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                                $colsCounter++;
                                $htmlCodes .= '<td class="celesteClaro noPrint"></td>';
                                $specialCounter++;
                                $html .= '<td class="celesteClaro noPrint"><div class="verticalText">' . $subentry->getCode() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;
                            }

                            //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            $studentRow .= '<td class="celesteOscuro noPrint" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            $htmlCodes .= '<td class="celesteOscuro noPrint"></td>';
                            $specialCounter++;
                            $html .= '<td class="celesteOscuro noPrint"><div class="verticalText">Promedio ' . $temp->getCode() . ' </div></td>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                            $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                            $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                            $specialCounter++;
                            $html .= '<td class="morado" style="padding: 1.5625em 0.625em; vertical-align: bottom;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getCode() . '</div></td>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getCode() . '</div>';
                            $html3 .= '<td class="celesteClaro noPrint" colspan="' . (sizeof($subentries)+2) . '">' . $temp->getCode() . '</td>';
                        } else {
                            if($size == 1){
                                foreach( $subentries as $subentry )
                                {
                                    //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                    $studentRow .= '<td class="celesteClaro noPrint"><div style="height: 15px;"><input disabled="disabled" tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                                    $colsCounter++;
                                    $htmlCodes .= '<td class="celesteClaro noPrint"></td>';
                                    $specialCounter++;
                                    $html .= '<td class="celesteClaro noPrint"><div class="verticalText">' . $subentry->getCode() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                                $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                                $specialCounter++;
                                $html .= '<td class="morado"  style="padding: 1.5625em 0.625em; vertical-align: bottom;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getCode() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;
                                $html3 .= '<td class="celesteClaro noPrint" colspan="' . (sizeof($subentries)+1) . '">' . $temp->getName() . '</td>';
                            }
                        }
                    } else {
                    }
                }

                $htmlCodes .= "</tr>";
                $html .= "</tr>";
                $html3 .= "</tr>";
                $html = '<table class="tableQualifications" style="border-spacing: 0px; border-collapse: collapse;">' . $htmlCodes . $html;

                $studentRowIndex = 0;
                foreach($students as $stdy){
                    $html .=  '<tr style="height: 25px; line-height: 0px;">';
                    $studentRowIndex++;
                    $html .=  '<td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;">' . $stdy->getStudent()->getCarne() . '</td>';
                    $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 300px; font-size: 12px;">' . $stdy->getStudent() . '</td>';

                    $row = str_replace("stdId", $stdy->getStudent()->getId(), $studentRow);
                    $row = str_replace("stdyIdd", $stdy->getId(), $row);

                    //tabIndexColXx
                    for ($i = 1; $i <= $colsCounter; $i++) {
                        $indexVar = "tabIndexCol" . $i . "x";
                        $row = str_replace($indexVar, "" . ($studentRowIndex + (($i - 1) * $studentsCount)), $row);
                    }

                    $dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
                        . " WHERE qua.studentYear = " . $stdy->getId();
                    $query = $em->createQuery($dql);
                    $qualifications = $query->getResult();
                    foreach($qualifications as $qualification){
                        $row = str_replace("val_" . $stdy->getStudent()->getId() . "_" .
                            $qualification->getSubCourseEntry()->getId() . "_", $qualification->getQualification(), $row);
                    }
                    $html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #000;">-</td>' . $row . "</tr>";
                }

                $html .= "</table>";

                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Qualification/courseGroupQualification.html.twig',
                    array('table' => $html, 'studentsCounter' => $studentsCount,
                            "codesCounter" => $specialCounter, 'menuIndex' => 5, 'title' => $title,
                            "notaMin" => $grade->getNotaMin()));
            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        }
        catch (Exception $e) {
            $info = toString($e);
            $logger->err('Teacher::loadEntriesByCourseAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    public function loadGroupQualificationsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $groupId = $request->get('groupId');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];
                $courseId = $request->get('courseId');

                $translator = $this->get("translator");

                if( isset($courseId) && isset($groupId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $dql = "SELECT ce FROM TecnotekExpedienteBundle:CourseEntry ce "
                        . " JOIN ce.courseClass cc"
                        . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                        . " AND cc.course = " . $courseId
                        . " ORDER BY ce.sortOrder";
                    $query = $em->createQuery($dql);
                    $entries = $query->getResult();
                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
                    $html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
                    $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';
                    $html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';

                    $marginLeft = 48;
                    $marginLeftCode = 62;
                    $htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
                    $htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
                    $htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;">SCIE</td>';
                    $jumpRight = 46;
                    $width = 44;

                    $html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="width: 75px; font-size: 12px;">Carne</td>';
                    $html3 .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">Estudiante</td>';
                    $html3 .= '<td class="azul headcoltrim" style="color: #fff;">TRIM</td>';
                    $studentRow = '';
                    $studentsHeader = '';
                    $colors = array(
                        "one" => "#38255c",
                        "two" => "#04D0E6"
                    );

                    $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
                        . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
                        . " ORDER BY std.lastname, std.firstname";
                    $query = $em->createQuery($dql);
                    $students = $query->getResult();

                    $studentsCount = sizeof($students);
                    $rowIndex = 1;
                    $colsCounter = 1;

                    $specialCounter = 1;

                    foreach( $entries as $entry )
                    {
                        $temp = $entry;
                        $childrens = $temp->getChildrens();
                        $size = sizeof($childrens);
                        if($size == 0){//No child
                            //Find SubEntries
                            $dql = "SELECT ce FROM TecnotekExpedienteBundle:SubCourseEntry ce "
                                . " WHERE ce.parent = " . $temp->getId()  . " AND ce.group = " . $groupId
                                . " ORDER BY ce.sortOrder";
                            $query = $em->createQuery($dql);
                            $subentries = $query->getResult();

                            $size = sizeof($subentries);

                            if($size > 1){
                                foreach( $subentries as $subentry )
                                {

                                    //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                    $studentRow .= '<td class="celesteClaro"><div><input style="background-color: #A4D2FD;" disabled="disabled" tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                    $colsCounter++;
                                    $htmlCodes .= '<td class="celesteClaro"></td>';
                                    $specialCounter++;
                                    $html .= '<td class="celesteClaro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                                $studentRow .= '<td class="celesteOscuro" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                                $htmlCodes .= '<td class="celesteOscuro"></td>';
                                $specialCounter++;
                                $html .= '<td class="celesteOscuro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Promedio ' . $temp->getName() . ' </div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                                $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                                $specialCounter++;
                                $html .= '<td class="morado" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                                $html3 .= '<td class="celesteClaro" colspan="' . (sizeof($subentries)+2) . '">' . $temp->getName() . '</td>';
                            } else {
                                if($size == 1){
                                    foreach( $subentries as $subentry )
                                    {
                                        //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                        $studentRow .= '<td class="celesteClaro"><div><input style="background-color: #A4D2FD;" disabled="disabled" tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                        $colsCounter++;
                                        $htmlCodes .= '<td class="celesteClaro"></td>';
                                        $specialCounter++;
                                        $html .= '<td class="celesteClaro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                        $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    }

                                    //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                    $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                                    $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                                    $specialCounter++;
                                    $html .= '<td class="morado" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    $html3 .= '<td class="celesteClaro" colspan="' . (sizeof($subentries)+1) . '">' . $temp->getName() . '</td>';
                                }
                            }


                        } else {
                        }
                    }

                    $htmlCodes .= "</tr>";
                    $html .= "</tr>";
                    $html3 .= "</tr>";
                    $html = '<table class="tableQualifications">' . $htmlCodes . $html . $html3;

                    $studentRowIndex = 0;
                    foreach($students as $stdy){
                        $html .=  '<tr style="height: 30px; line-height: 0px;">';
                        $studentRowIndex++;
                        $html .=  '<td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;">' . $stdy->getStudent()->getCarne() . '</td>';
                        $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">' . $stdy->getStudent() . '</td>';

                        $row = str_replace("stdId", $stdy->getStudent()->getId(), $studentRow);
                        $row = str_replace("stdyIdd", $stdy->getId(), $row);

                        //tabIndexColXx
                        for ($i = 1; $i <= $colsCounter; $i++) {
                            $indexVar = "tabIndexCol" . $i . "x";
                            $row = str_replace($indexVar, "" . ($studentRowIndex + (($i - 1) * $studentsCount)), $row);
                        }

                        $dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
                            . " WHERE qua.studentYear = " . $stdy->getId();
                        $query = $em->createQuery($dql);
                        $qualifications = $query->getResult();
                        foreach($qualifications as $qualification){
                            $row = str_replace("val_" . $stdy->getStudent()->getId() . "_" . $qualification->getSubCourseEntry()->getId() . "_", "" . $qualification->getQualification(), $row);
                        }
                        $html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>' . $row . "</tr>";
                    }

                    $html .= "</table>";

                    return new Response(json_encode(array('error' => false, 'html' => $html, "studentsCounter" => $studentsCount, "codesCounter" => $specialCounter)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Teacher::loadEntriesByCourseAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function saveStudentQualificationAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $subentryId = $request->get('subentryId');
                $studentYearId = $request->get('studentYearId');
                $qualification = $request->get('qualification');
                $translator = $this->get("translator");
                $logger->err('--> ' . $subentryId . " :: " . $studentYearId . " :: " . $qualification);
                if( !isset($qualification) || $qualification == ""){
                    $qualification = -1;
                }
                if( isset($subentryId) || isset($studentYearId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $studentQ = $em->getRepository("TecnotekExpedienteBundle:StudentQualification")->findOneBy(array('subCourseEntry' => $subentryId, 'studentYear' => $studentYearId));

                    if ( isset($studentQ) ) {
                        $studentQ->setQualification($qualification);
                    } else {
                        $studentQ = new StudentQualification();
                        $studentQ->setSubCourseEntry($em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->find( $subentryId ));
                        $studentQ->setStudentYear($em->getRepository("TecnotekExpedienteBundle:StudentYear")->find( $studentYearId ));
                        $studentQ->setQualification($qualification);
                    }
                    $em->persist($studentQ);
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::saveStudentQualificationAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function generateExcelAction(){

        $excelService = $this->get('export.excel')->setNameOfSheet("Notas");

        //$this->get('export.excel')->createSheet();
        $filepath = "export/excel/groupNotes";

        $request = $this->get('request')->request;
        //$periodId = $request->get('periodId');
        $periodId = 1;
        //$groupId = $request->get('groupId');
        $groupId = "1-1";
        $keywords = preg_split("/[\s-]+/", $groupId);
        $groupId = $keywords[0];
        $gradeId = $keywords[1];
        //$courseId = $request->get('courseId');
        $courseId = "42";

        $translator = $this->get("translator");

        if( isset($courseId) && isset($groupId) && isset($periodId)) {
            $em = $this->getDoctrine()->getEntityManager();

            $dql = "SELECT ce FROM TecnotekExpedienteBundle:CourseEntry ce "
                . " JOIN ce.courseClass cc"
                . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                . " AND cc.course = " . $courseId
                . " ORDER BY ce.sortOrder";
            $query = $em->createQuery($dql);
            $entries = $query->getResult();
            $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
            //$html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
            //$html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';

            $excelService->writeCellByPositionWithOptions(2,2,"Promedio Trimestral",
                array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => '2b34ee', 'color' => 'ffffff', 'bold' => true));
            //$html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';

            $marginLeft = 48;
            $marginLeftCode = 62;
            //$htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
            //$htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
            //$htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;">SCIE</td>';
            $excelService->writeCellByPositionWithOptions(1,2,"SCIE",
                array('height' => 15, 'width' => 5, 'backgroundColor' => '2b34ee', 'color' => 'ffffff', 'bold' => true));
            $jumpRight = 46;
            $width = 44;

            $excelService->writeCellByPositionWithOptions(1,0,"",
                array('height' => 15, 'width' => 5, 'backgroundColor' => '82c0fd', 'bold' => true));
            $excelService->writeCellByPositionWithOptions(1,1,"",
                array('height' => 15, 'width' => 5, 'backgroundColor' => 'A4D2FD', 'bold' => true));

            $excelService->writeCellByPositionWithOptions(2,0,"",
                array('height' => 195, 'width' => 5, 'backgroundColor' => '82c0fd', 'bold' => true));
            $excelService->writeCellByPositionWithOptions(2,1,"",
                array('height' => 195, 'width' => 5, 'backgroundColor' => 'A4D2FD', 'bold' => true));

            $excelService->writeCellByPositionWithOptions(3,0,"Carne",
                array('height' => 15, 'width' => 5, 'backgroundColor' => '82c0fd', 'bold' => true));
            $excelService->writeCellByPositionWithOptions(3,1,"Estudiante",
                array('height' => 15, 'width' => 5, 'backgroundColor' => 'A4D2FD', 'bold' => true));
            $excelService->writeCellByPositionWithOptions(3,2,"TRIM",
                array('height' => 15, 'width' => 5, 'backgroundColor' => '2b34ee', 'color' => 'ffffff', 'bold' => true));

            //$html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="width: 75px; font-size: 12px;">Carne</td>';
            //$html3 .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">Estudiante</td>';
            //$html3 .= '<td class="azul headcoltrim" style="color: #fff;">TRIM</td>';
            $studentRow = '';
            $studentsHeader = '';
            $colors = array(
                "one" => "#38255c",
                "two" => "#04D0E6"
            );

            $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
                . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
                . " ORDER BY std.lastname, std.firstname";
            $query = $em->createQuery($dql);
            $students = $query->getResult();

            $studentsCount = sizeof($students);
            $rowIndex = 1;
            $colsCounter = 1;

            $specialCounter = 1;

            $colsIndex = 3;

            foreach( $entries as $entry )
            {
                $temp = $entry;
                $childrens = $temp->getChildrens();
                $size = sizeof($childrens);
                if($size == 0){//No child
                    //Find SubEntries
                    $dql = "SELECT ce FROM TecnotekExpedienteBundle:SubCourseEntry ce "
                        . " WHERE ce.parent = " . $temp->getId()  . " AND ce.group = " . $groupId
                        . " ORDER BY ce.sortOrder";
                    $query = $em->createQuery($dql);
                    $subentries = $query->getResult();

                    $size = sizeof($subentries);


                    if($size > 1){
                        foreach( $subentries as $subentry )
                        {
                            $excelService->writeCellByPositionWithOptions(1,$colsIndex,"",
                                array('height' => 15, 'width' => 5, 'backgroundColor' => 'A4D2FD'));
                            $excelService->writeCellByPositionWithOptions(2,$colsIndex,$subentry->getName(),
                                array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => 'A4D2FD'));
                            $colsIndex += 1;
                            //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                            /*$studentRow .= '<td class="celesteClaro"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                            $colsCounter++;
                            $htmlCodes .= '<td class="celesteClaro"></td>';
                            $specialCounter++;
                            $html .= '<td class="celesteClaro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';*/
                            $marginLeft += $jumpRight; $marginLeftCode += 25;
                        }

                        $excelService->writeCellByPositionWithOptions(1,$colsIndex,"",
                            array('height' => 15, 'width' => 5, 'backgroundColor' => '5F96E7'));
                        $excelService->writeCellByPositionWithOptions(2,$colsIndex,'Promedio ' . $temp->getName(),
                            array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => '5F96E7'));
                        $colsIndex += 1;

                        //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                        /*$studentRow .= '<td class="celesteOscuro" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                        $htmlCodes .= '<td class="celesteOscuro"></td>';
                        $specialCounter++;
                        $html .= '<td class="celesteOscuro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Promedio ' . $temp->getName() . ' </div></td>';
                        $marginLeft += $jumpRight; $marginLeftCode += 25;*/

                        //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                        /*$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                        $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                        $specialCounter++;
                        $html .= '<td class="morado" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                        $marginLeft += $jumpRight; $marginLeftCode += 25;

                        // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                        $html3 .= '<td class="celesteClaro" colspan="' . (sizeof($subentries)+2) . '">' . $temp->getName() . '</td>';*/

                        $excelService->writeCellByPositionWithOptions(1,$colsIndex,$temp->getCode(),
                            array('height' => 15, 'width' => 5, 'backgroundColor' => 'B698EE'));
                        $excelService->writeCellByPositionWithOptions(2,$colsIndex,$temp->getPercentage() . '% ' . $temp->getName(),
                            array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => 'B698EE'));
                        $colsIndex += 1;
                    } else {
                        if($size == 1){
                            foreach( $subentries as $subentry )
                            {
                                //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                /*$studentRow .= '<td class="celesteClaro"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                                $colsCounter++;
                                $htmlCodes .= '<td class="celesteClaro"></td>';
                                $specialCounter++;
                                $html .= '<td class="celesteClaro" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;*/


                                $excelService->writeCellByPositionWithOptions(1,$colsIndex,"",
                                    array('height' => 15, 'width' => 5, 'backgroundColor' => 'A4D2FD'));
                                $excelService->writeCellByPositionWithOptions(2,$colsIndex,$temp->getPercentage() . '% ' . $temp->getName(),
                                    array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => 'A4D2FD'));
                                $colsIndex += 1;
                            }

                            //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                            /*$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                            $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                            $specialCounter++;
                            $html .= '<td class="morado" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;
                            $html3 .= '<td class="celesteClaro" colspan="' . (sizeof($subentries)+1) . '">' . $temp->getName() . '</td>';*/

                            $excelService->writeCellByPositionWithOptions(1,$colsIndex,$temp->getCode(),
                                array('height' => 15, 'width' => 5, 'backgroundColor' => 'B698EE'));
                            $excelService->writeCellByPositionWithOptions(2,$colsIndex,$temp->getPercentage() . '% ' . $temp->getName(),
                                array('rotation' => 90, 'height' => 195, 'width' => 5, 'backgroundColor' => 'B698EE'));
                            $colsIndex += 1;
                        }
                    }


                } else {
                }
            }

           /* $htmlCodes .= "</tr>";
            $html .= "</tr>";
            $html3 .= "</tr>";
            $html = '<table class="tableQualifications">' . $htmlCodes . $html . $html3;*/

            $studentRowIndex = 4;
            foreach($students as $stdy){
                //$html .=  '<tr style="height: 30px; line-height: 0px;">';

                $excelService->writeCellByPositionWithOptions($studentRowIndex,0,$stdy->getStudent()->getCarne(),
                    array('height' => 15, 'width' => 10, 'backgroundColor' => '82c0fd'));
                $excelService->writeCellByPositionWithOptions($studentRowIndex,1,$stdy->getStudent(),
                    array('height' => 15, 'width' => 40, 'backgroundColor' => 'A4D2FD'));

                $studentRowIndex++;
                /*$html .=  '<td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;">' . $stdy->getStudent()->getCarne() . '</td>';
                $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;">' . $stdy->getStudent() . '</td>';

                $row = str_replace("stdId", $stdy->getStudent()->getId(), $studentRow);
                $row = str_replace("stdyIdd", $stdy->getId(), $row);

                //tabIndexColXx
                for ($i = 1; $i <= $colsCounter; $i++) {
                    $indexVar = "tabIndexCol" . $i . "x";
                    $row = str_replace($indexVar, "" . ($studentRowIndex + (($i - 1) * $studentsCount)), $row);
                }

                $dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
                    . " WHERE qua.studentYear = " . $stdy->getId();
                $query = $em->createQuery($dql);
                $qualifications = $query->getResult();
                foreach($qualifications as $qualification){
                    $row = str_replace("val_" . $stdy->getStudent()->getId() . "_" . $qualification->getSubCourseEntry()->getId() . "_", "" . $qualification->getQualification(), $row);
                }
                $html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>' . $row . "</tr>";*/
            }

            $excelService->applyBorderByRange(0, 1, $colsIndex - 1, $studentRowIndex - 1);

            //$html .= "</table>";
        }
        /*$excelService->writeCellByPosition(1,1,"probando 1");
        $excelService->writeCellByPosition(1,2,"probando 2");
        $excelService->writeCellByPosition(2,1,"probando 3");*/

        //$excelService->writeCellByPosition(row,col,"");

        $excelService->writeExport($filepath);

        /*$response = new Response();
        $response->setContent("<html><body>OK!!!</body></html>");
        $response->setStatusCode(200);
        /*$response->headers->add('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->add('Content-Disposition', 'attachment;filename=stdream2.xls');*/

        //return $response;

        return new Response(json_encode(array('error' => false)));

        //create the response
        /*$response = $excelService->getResponse();
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=stdream2.xls');

        // If you are using a https connection, you have to set those two headers for compatibility with IE <9
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;*/
    }

    public function loadGroupsOfYearAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $year = $request->get('year');
                $loadOnlyBachelors = $request->get('loadOnlyBachelors');
                if ( !isset($loadOnlyBachelors) ) {
                    $loadOnlyBachelors = false;
                } else {
                    $loadOnlyBachelors = ($loadOnlyBachelors == "true");
                }
                $translator = $this->get("translator");

                if( isset($year) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $user = $this->get('security.context')->getToken()->getUser();
                    //Get Groups
                    $sqlOnlyBachelors = $loadOnlyBachelors? " AND grade.number = 11 ":"";
                    $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                        " FROM tek_groups g, tek_periods p, tek_grades grade" .
                        " WHERE p.orderInYear = 3 AND g.period_id = p.id AND p.year = " .  $year . " AND g.grade_id = grade.id" .
                        "" . $sqlOnlyBachelors . " AND g.institution_id in (" . $user->getInstitutionsIdsStr() . ")" .
                        " GROUP BY CONCAT(grade.name, ' :: ', g.name)" .
                        " ORDER BY g.id";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $groups = $stmt->fetchAll();

                    return new Response(json_encode(array('error' => false, 'groups' => $groups)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Admin::loadGroupsOfYearAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadCoursesOfGroupAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $keywords = preg_split("/[\s-]+/", $request->get('groupId'));
                $groupId = $keywords[0];

                $translator = $this->get("translator");

                if( isset($groupId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //Get Courses
                    $sql = "SELECT course.id, course.name " .
                        " FROM tek_assigned_teachers tat, tek_course_class tcc, tek_courses course " .
                        " WHERE tat.group_id = " . $groupId . " AND tat.course_class_id =  tcc.id AND tcc.course_id = course.id" .
                        " GROUP BY course.id" .
                        " ORDER BY course.name ";

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
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function questionnairesAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT q FROM TecnotekExpedienteBundle:Questionnaire q";
        $query = $em->createQuery($dql);
        $questionnaires = $query->getResult();
        $groups = $em->getRepository('TecnotekExpedienteBundle:QuestionnaireGroup')->findAll();
        $institutions = $em->getRepository('TecnotekExpedienteBundle:Institution')->findAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Questionnaires/list.html.twig', array(
            'questionnaires' => $questionnaires, 'groups' => $groups, 'institutions' => $institutions
        ));
    }

    public function saveQuestionnaireConfigAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $questionnaireId = $request->get('q');
                $field = $request->get('field');
                $val = $request->get('val');

                $translator = $this->get("translator");

                if( isset($questionnaireId) && isset($field) && isset($val) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $q = new \Tecnotek\ExpedienteBundle\Entity\Questionnaire();
                    $q = $em->getRepository('TecnotekExpedienteBundle:Questionnaire')->find($questionnaireId);

                    switch($field){
                        case 'group':
                            $qGroup = $em->getRepository('TecnotekExpedienteBundle:QuestionnaireGroup')->find($val);
                            $q->setGroup($qGroup);
                            break;
                        case 'teacher':
                            $q->setEnabledForTeacher($val == 1);
                            break;
                        case 'institution':
                            $values = preg_split("/[\s-]+/", $val);
                            $institution =
                                $em->getRepository('TecnotekExpedienteBundle:Institution')->find($values[0]);
                            if($values[1] == 0){
                                $q->getInstitutions()->removeElement($institution);
                            } else {
                                $q->getInstitutions()->add($institution);
                            }
                            break;
                        default:
                            break;
                    }
                    $em->persist($q);
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Admin::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }
}
