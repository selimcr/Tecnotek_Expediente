<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Absence;
use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
use Tecnotek\ExpedienteBundle\Entity\Ticket;
use Tecnotek\ExpedienteBundle\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{
    /* Metodos para CRUD de Students */
    public function studentListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT students FROM TecnotekExpedienteBundle:Student students";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(students) FROM TecnotekExpedienteBundle:Student students";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3
        ));
    }

    public function studentCreateAction()
    {
        $entity = new Student();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function studentShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $relatives = $em->getRepository("TecnotekExpedienteBundle:Relative")->findByStudent($id);
        $contact = new \Tecnotek\ExpedienteBundle\Entity\Contact();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ContactFormType(), $contact);

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3, 'relatives' => $relatives));
    }

    public function studentSaveAction(){
        $entity  = new Student();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_student', array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/new.html.twig', array(
                'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
            ));
        }
    }

    public function studentDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
    }

    public function studentEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function studentUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_student_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
        }

    }
    /* Final de los metodos para CRUD de students*/

    /* Metodos para CRUD de Clubs */
    public function clubListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT clubs FROM TecnotekExpedienteBundle:Club clubs";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(clubs) FROM TecnotekExpedienteBundle:Club clubs";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3
        ));
    }

    public function clubCreateAction()
    {
        $entity = new Club();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function clubShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function clubSaveAction(){
        $entity  = new Club();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_club', array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/new.html.twig', array(
                'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
            ));
        }
    }

    public function clubDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_club'));
    }

    public function clubEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function clubUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_club_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_club'));
        }

    }
    /* Final de los metodos para CRUD de clubs*/

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

    public function getListAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $type = $request->get('type');
                $text = $request->get('text');
                $clubId = $request->get('clubId');

                $em = $this->getDoctrine()->getEntityManager();
                $sql = "SELECT std.id, std.firstname, std.lastname "
                    . " FROM tek_students std"
                    . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%')"
                    . " AND std.id NOT IN (SELECT cs.student_id FROM club_student cs WHERE cs.club_id = " . $clubId . ")"
                    . " ORDER BY std.firstname, std.lastname";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $students = $stmt->fetchAll();

                if ( isset($students) ) {
                    return new Response(json_encode(array('error' => false, 'students' => $students)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Data not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::getListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    function associateStudentToClubAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $clubId = $request->get('clubId');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                $club = $em->getRepository("TecnotekExpedienteBundle:Club")->find($clubId);
                $club->getStudents()->add($student);
                $em->persist($club);
                $em->flush();

                return new Response(json_encode(array('error' => false)));
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::getListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeStudentFromClubAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $clubId = $request->get('clubId');
                $em = $this->getDoctrine()->getEntityManager();
                $sql = "DELETE FROM club_student WHERE club_id = " . $clubId .
                    " AND student_id = " . $studentId. ";";
                $logger->err($sql);
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();

                return new Response(json_encode(array('error' => false)));
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::removeStudentFromClub [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function createContactAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $firstname = $request->get('tecnotek_expediente_contactformtype[firstname]');
                $lastname = $request->get('tecnotek_expediente_contactformtype[lastname]');
                $identification = $request->get('tecnotek_expediente_contactformtype[identification]');
                $kinship = $request->get('kinship');
                $detail = $request->get('detail');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                if ( isset($student) ) {
                    $contact = new Contact();
                    $contact->setFirstname($firstname);
                    $contact->setLastname($lastname);
                    $contact->setIdentification($identification);

                    $form = $this->createForm(new ContactFormType(), $contact);
                    $form->bindRequest($this->getRequest());
                    if ($form->isValid()) {
                        $em->persist($contact);

                        $relative = new Relative();
                        $relative->setContact($contact);
                        $relative->setStudent($student);
                        $relative->setKinship($kinship);
                        $relative->setDescription($detail);
                        $em->persist($relative);

                        $em->flush();

                        return new Response(json_encode(array('error' => false, 'id' => $contact->getId())));
                    } else {
                        $errors = $this->get('validator')->validate( $contact );
                        $result = '';

                        foreach( $errors as $error )
                        {
                            $result .= "[" . $error->getPropertyPath() . ": " . $error->getMessage() . "]\n";
                        }

                        if($result == ""){
                            $em->persist($contact);

                            $relative = new Relative();
                            $relative->setContact($contact);
                            $relative->setStudent($student);
                            $relative->setKinship($kinship);
                            $relative->setDescription($detail);
                            $em->persist($relative);

                            $em->flush();

                            return new Response(json_encode(array('error' => false, 'id' => $relative->getId())));
                        } else {
                            return new Response(json_encode(array('error' => true, 'message' => $result)));
                        }
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Student not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::createContactAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeRelativeAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $relativeId = $request->get('relativeId');

                $em = $this->getDoctrine()->getEntityManager();
                $relative = $em->getRepository("TecnotekExpedienteBundle:Relative")->find($relativeId);
                if ( isset($relative) ) {
                    $em->remove($relative);
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Relative not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::createContactAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getContactListAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $text = $request->get('text');
                $studentId = $request->get('studentId');

                $em = $this->getDoctrine()->getEntityManager();
                $sql = "SELECT c.id, c.firstname, c.lastname "
                    . " FROM tek_contacts c"
                    . " WHERE (c.firstname like '%" . $text . "%' OR c.lastname like '%" . $text . "%')"
                    . " AND c.id NOT IN (SELECT r.contact_id FROM tek_relatives r WHERE r.student_id = " . $studentId . ")"
                    . " ORDER BY c.firstname, c.lastname";

                $logger->err($sql);
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $contacts = $stmt->fetchAll();

                if ( isset($contacts) ) {
                    return new Response(json_encode(array('error' => false, 'contacts' => $contacts)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Data not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::getContactList [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /* Metodos para CRUD de Tickets */
    public function ticketIndexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT tickets FROM TecnotekExpedienteBundle:Ticket tickets WHERE tickets.date BETWEEN :initial AND :final";
        $today = new \DateTime();
        $query = $em->createQuery($dql)
            ->setParameter('initial', $today->format('Y-m-d') . " 00:00:00")
            ->setParameter('final', $today->format('Y-m-d') . " 23:59:59");
        $tickets = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ticket/index.html.twig', array(
            'tickets' => $tickets, 'menuIndex' => 3
        ));
    }

    public function ticketSaveAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $relativeId = $request->get('relativeId');
                $comments = $request->get('comments');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                $relative = $em->getRepository("TecnotekExpedienteBundle:Relative")->find($relativeId);

                if ( isset($student) && isset($relative) ) {
                    $entity = new Ticket();
                    $entity->setStudent($student);
                    $entity->setRelative($relative);
                    $entity->setComments($comments);
                    $entity->setDate(new \DateTime());
                    $em->persist($entity);
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Student or Relative not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::ticketSaveAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }



    /*
    public function ticketListAction($rowsPerPage = 10)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT clubs FROM TecnotekExpedienteBundle:Club clubs";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(clubs) FROM TecnotekExpedienteBundle:Club clubs";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            $rowsPerPage
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3
        ));
    }

    public function ticketCreateAction()
    {
        $entity = new Club();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function ticketShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }



    public function ticketDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_club'));
    }

    public function ticketEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function ticketUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Club")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ClubFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_club_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Club/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_club'));
        }

    }*/

    public function getStudentListAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $text = $request->get('text');

                $em = $this->getDoctrine()->getEntityManager();
                $sql = "SELECT e.id, e.firstname, e.lastname "
                    . " FROM tek_students e"
                    . " WHERE (e.firstname like '%" . $text . "%' OR e.lastname like '%" . $text . "%')"
                    . " ORDER BY e.firstname, e.lastname";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $students = $stmt->fetchAll();

                if ( isset($students) ) {
                    return new Response(json_encode(array('error' => false, 'students' => $students)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Data not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::getStudentListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getRelativesListAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                $result = array();
                $counter = 0;
                foreach($student->getRelatives() as $relative){
                    $obj = array( 'id' => $relative->getId(),
                        'contact' => $relative->getContact()->getFirstname() . " " . $relative->getContact()->getLastname(),
                        'kinship' => $relative->getDescription()
                    );
                    $result[$counter] = $obj;
                    $counter++;
                }
                if ( isset($student) ) {
                    return new Response(json_encode(array('error' => false, 'relatives' => $result)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Data not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::getRelativesListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }
    /* Final de los metodos para CRUD de tickets*/


    /* Metodos para CRUD de Absences*/
    public function absencesIndexAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Absence")->findAll();

        $entity = new Absence();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/index.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'entity' => $entity, 'form'   => $form->createView()
        ));
    }

    public function absenceSaveAction(){
        $entity  = new Absence();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
        $form->bindRequest($request);

        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Absence")->findAll();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_absences',
                array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/index.html.twig', array('menuIndex' => 3,
                'entities' => $entities, 'entity' => $entity, 'form'   => $form->createView()
            ));
        }
    }
    /* Final de los metodos para CRUD de Absences*/
}
