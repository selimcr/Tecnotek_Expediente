<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tecnotek\ExpedienteBundle\Entity\Absence;
use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\StudentExtraTest;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
use Tecnotek\ExpedienteBundle\Entity\StudentPenalty;
use Tecnotek\ExpedienteBundle\Entity\StudentToRoute;
use Tecnotek\ExpedienteBundle\Entity\StudentTutorVisit;
use Tecnotek\ExpedienteBundle\Entity\Ticket;
use Tecnotek\ExpedienteBundle\Entity\StudentExtraPoints;
use Tecnotek\ExpedienteBundle\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class StudentController extends Controller
{
    public function studentTutorVisitSaveAction(){
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) {// Is not the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            $request = $this->get('request')->request;
            $visitId = $request->get('visitId');
            $studentId = $request->get('studentId');
            $date = $request->get('date');
            $people = $request->get('people');
            $observations = $request->get('observations');
            $comments = $request->get('comments');
            $visit = new StudentTutorVisit();
            $currentUser = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getEntityManager();
            if ($visitId != 0) { // It's editing a visit
                $visit = $em->getRepository("TecnotekExpedienteBundle:StudentTutorVisit")->find($visitId);
            } else {
                $visit->setCreator($currentUser);
                $visit->setStudent($em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId));
            }
            if ($visit->getCreator()->getId() == $currentUser->getId()) {
                $visit->setComments($comments);
                $format = 'd/m/Y';
                $date = \DateTime::createFromFormat($format, $date);
                $visit->setDate($date);
                $visit->setObservations($observations);
                $visit->setPeople($people);
                $em->persist($visit);
                $em->flush();
                return new Response(json_encode(array('error' => false)));
            } else {
                return new Response(json_encode(array('error' => true, 'message' => 'Las visitas sólo pueden ser editados por quien las creó')));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Student::studentTutorVisitSaveAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    public function searchStudentTutorVisitAction($rowsPerPage = 10) {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) {// Is not the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            $request = $this->get('request')->request;
            $text = $request->get('text');
            $sortBy = $request->get('sortBy');
            $order = $request->get('order');
            $page = $request->get('page');
            $studentId = $request->get('studentId');
            $offset = ($page-1) * $rowsPerPage;
            $em = $this->getDoctrine()->getEntityManager();
            $words = explode(" ", trim($text));
            $where = "v.student_id = $studentId";
            foreach ($words as $word) {
                $where .= " AND (v.people like '%" . $word . "%')";
            }
            $sql = "SELECT SUM($where) as filtered,"
                . " COUNT(*) as total FROM tek_student_tutor_visit v WHERE $where;";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $filtered = 0;
            $total = 0;
            $result = $stmt->fetchAll();
            foreach($result as $row) {
                $filtered = $row['filtered'];
                $total = $row['total'];
            }

            $sql = "SELECT v.id, date_format(v.date,'%d/%m/%Y') as 'date', v.student_id, v.comments, v.observations, v.people, v.user_id, "
                . " concat(u.firstname, ' ', u.lastname) as 'creator'"
                . " FROM tek_student_tutor_visit v"
                . " JOIN tek_users u ON u.id = v.user_id"
                . " WHERE $where"
                . " ORDER BY $sortBy $order"
                . " LIMIT $rowsPerPage OFFSET $offset";
            $stmt2 = $em->getConnection()->prepare($sql);
            $stmt2->execute();
            $visits = $stmt2->fetchAll();
            return new Response(json_encode(array('error' => false,
                'filtered' => $filtered,
                'total' => $total,
                'visits' => $visits)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Student::searchStudentTutorVisitAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /* Metodos para CRUD de Students */
    public function studentListAction($rowsPerPage = 30)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $text = $this->get('request')->query->get('text');
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/list.html.twig', array(
            'menuIndex' => 3, 'text' => $text
        ));
    }

    public function searchStudentsAction($rowsPerPage = 30) {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $text = $request->get('text');
                $sortBy = $request->get('sortBy');
                $order = $request->get('order');
                $page = $request->get('page');
                $offset = ($page-1) * $rowsPerPage;
                $em = $this->getDoctrine()->getEntityManager();
                $words = explode(" ", trim($text));
                $where = "";
                foreach ($words as $word) {
                    $where .= $where == ""? "":" AND ";
                    $where .= "(std.firstname like '%" . $word . "%' OR std.lastname like '%" . $word .
                        "%' OR std.carne like '%" . $word . "%')";
                }
                $sql = "SELECT SUM($where) as filtered,"
                    . " COUNT(*) as total FROM tek_students std;";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $filtered = 0;
                $total = 0;
                $result = $stmt->fetchAll();
                foreach($result as $row) {
                    $filtered = $row['filtered'];
                    $total = $row['total'];
                }

                $sql = "SELECT std.id, std.lastname, std.firstname, std.groupyear, std.gender, std.carne"
                    . " FROM tek_students std"
                    . " WHERE $where"
                    . " ORDER BY std.$sortBy $order"
                    . " LIMIT $rowsPerPage OFFSET $offset";
                $stmt2 = $em->getConnection()->prepare($sql);
                $stmt2->execute();
                $students = $stmt2->fetchAll();

                return new Response(json_encode(array('error' => false,
                    'filtered' => $filtered,
                    'total' => $total,
                    'students' => $students)));
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::searchStudentsAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function searchContactsAction($rowsPerPage = 30) {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $text = $request->get('text');
                $sortBy = $request->get('sortBy');
                $order = $request->get('order');
                $page = $request->get('page');
                $offset = ($page-1) * $rowsPerPage;
                $em = $this->getDoctrine()->getEntityManager();
                $words = explode(" ", trim($text));
                $where = "";
                foreach ($words as $word) {
                    $where .= $where == ""? "":" AND ";
                    $where .= "(ctd.firstname like '%" . $word . "%' OR ctd.lastname like '%" . $word .
                        "%' OR ctd.identification like '%" . $word . "%' OR ctd.email like '%" . $word . "%'
                        OR ctd.phonec like '%" . $word . "%' OR ctd.phonew like '%" . $word . "%'
                        OR ctd.phoneh like '%" . $word . "%')";
                }
                $sql = "SELECT SUM($where) as filtered,"
                    . " COUNT(*) as total FROM tek_contacts ctd;";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $filtered = 0;
                $total = 0;
                $result = $stmt->fetchAll();
                foreach($result as $row) {
                    $filtered = $row['filtered'];
                    $total = $row['total'];
                }

                $sql = "SELECT ctd.id, ctd.lastname, ctd.firstname, ctd.identification"
                    . " FROM tek_contacts ctd"
                    . " WHERE $where"
                    . " ORDER BY ctd.$sortBy $order"
                    . " LIMIT $rowsPerPage OFFSET $offset";
                $stmt2 = $em->getConnection()->prepare($sql);
                $stmt2->execute();
                $contacts = $stmt2->fetchAll();

                return new Response(json_encode(array('error' => false,
                    'filtered' => $filtered,
                    'total' => $total,
                    'contacts' => $contacts)));
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::searchContactsAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
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

    public function studentReligionEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentReligionFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/religion.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function studentEmergencyEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentEmergencyFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/emergency.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }


    public function studentEmergencyUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentEmergencyFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/emergency.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
        }

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

    public function studentReligionUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentReligionFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/religion.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
        }

    }

    public function contactListAction($rowsPerPage = 30)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $text = $this->get('request')->query->get('text');
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/contact.html.twig', array(
            'menuIndex' => 3, 'text' => $text
        ));
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

$currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
                $currentPeriodId = $currentPeriod->getId();

                $sql = "SELECT std.id, std.lastname, std.firstname, std.groupyear "
                    . " FROM tek_students std, tek_students_year stdy"
                    . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%')"
                    . " AND std.id NOT IN (SELECT cs.student_id FROM club_student cs WHERE cs.club_id = " . $clubId . ")"
                    . " AND std.id = stdy.student_id AND stdy.period_Id =".$currentPeriodId 
                    . " ORDER BY std.lastname, std.firstname";
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

    public function loadGroupsOfPeriodAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');

                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();

                $sql = "SELECT g.id, gr.name , g.name as name_group"
                    . " FROM tek_groups g, tek_grades gr"
                    . " WHERE g.period_id = " . $periodId
                    . " AND g.institution_id in (" . $user->getInstitutionsIdsStr() . ")"
                    . " AND g.grade_id = gr.id"
                    . " ORDER BY gr.id";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $groups = $stmt->fetchAll();

                if ( isset($groups) ) {
                    return new Response(json_encode(array('error' => false, 'groups' => $groups)));
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

    public function getListRouteAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $text = $request->get('text');
                $routeId = $request->get('routeId');

                $em = $this->getDoctrine()->getEntityManager();
                $route = $em->getRepository("TecnotekExpedienteBundle:Route")->find($routeId);
                $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
                $currentPeriodId = $currentPeriod->getId();
                if($route->getRouteType() == 1){
                    $sql = "SELECT std.id, std.lastname, std.firstname, std.carne, std.groupyear, std.routeType, std.in_route_id "
                        . " FROM tek_students std, tek_students_year stdy"
                        . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%')"
                        . " AND (std.route_id is null Or std.route_id <> $routeId)"
                        . " AND std.id = stdy.student_id AND stdy.period_Id =".$currentPeriodId
                        . " ORDER BY std.lastname, std.firstname";
                } else {
                    /*$sql = "SELECT std.id, std.lastname, std.firstname "
                        . " FROM tek_students std, tek_students_year stdy"
                        . " LEFT JOIN tek_students_to_routes stdToRoute ON stdToRoute.student_id = std.id"
                        . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%')"
                        . " AND (stdToRoute.id is null Or stdToRoute.route_id <> $routeId)"
                        . " AND std.id = stdy.student_id"
                        . " ORDER BY std.lastname, std.firstname";*/
                    $sql = "SELECT std.id, std.lastname, std.firstname, std.carne, std.groupyear "
                        . " FROM tek_students std, tek_students_year stdy"
                        . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%')"
                        . " AND NOT EXISTS"
                        . " (SELECT  null"
                        . " FROM    tek_students_to_routes stdToRoute"
                        . " WHERE   stdToRoute.student_id = std.id and stdToRoute.route_id = $routeId)"
                        . " AND std.id = stdy.student_id"
                        . " AND std.groupyear != 'NULL' AND stdy.period_Id =".$currentPeriodId
                        . " ORDER BY std.lastname, std.firstname";
                }
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

    function associateStudentToRouteAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $routeId = $request->get('routeId');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                $route = $em->getRepository("TecnotekExpedienteBundle:Route")->find($routeId);
                if($route->getRouteType() == 1){//Is a normal route
                    $student->setRoute($route);
                    $em->persist($student);
                } else {//Is a club route
                    $studentToRoute = new StudentToRoute();
                    $studentToRoute->setStudent($student);
                    $studentToRoute->setRoute($route);
                    $em->persist($studentToRoute);
                }
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

    public function removeStudentFromRouteAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $routeType = $request->get('routeType');
                $routeId = $request->get('routeId');
                $em = $this->getDoctrine()->getEntityManager();

                $logger->err("---> " . $studentId . " :: " . $routeId . " :: " . $routeType);
                if($routeType == 1){
                    $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                    $student->removeRoute();
                    $em->persist($student);
                } else {//Club route
                    $studentToRoute = $em->getRepository("TecnotekExpedienteBundle:StudentToRoute")->findOneBy(array('student' => $studentId, 'route' => $routeId));
                    $em->remove($studentToRoute);
                }
                $em->flush();
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

    public function changeRouteInInfoAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $routeIn = $request->get('routeIn');
                $routeType = $request->get('routeType');
                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                if ($routeIn == 0) {
                    $student->removeRouteIn();
                } else {
                    $student->setRouteIn($em->getRepository("TecnotekExpedienteBundle:Route")->find($routeIn));
                }
                $student->setRouteType($routeType);
                $em->flush();
                return new Response(json_encode(array('error' => false)));
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::changeRouteInInfo [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        } else {
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
                $phoneh = $request->get('tecnotek_expediente_contactformtype[phoneh]');
                $phonew = $request->get('tecnotek_expediente_contactformtype[phonew]');
                $phonec = $request->get('tecnotek_expediente_contactformtype[phonec]');
                $workplace = $request->get('tecnotek_expediente_contactformtype[workplace]');
                $email = $request->get('tecnotek_expediente_contactformtype[email]');
                $adress = $request->get('tecnotek_expediente_contactformtype[adress]');
                $restriction = $request->get('tecnotek_expediente_contactformtype[restriction]');
                $kinship = $request->get('kinship');
                $detail = $request->get('detail');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                if ( isset($student) ) {
                    $contact = new Contact();
                    $contact->setFirstname($firstname);
                    $contact->setLastname($lastname);
                    $contact->setIdentification($identification);
                    $contact->setPhoneh($phoneh);
                    $contact->setPhonew($phonew);
                    $contact->setPhonec($phonec);
                    $contact->setWorkplace($workplace);
                    $contact->setEmail($email);
                    $contact->setAdress($adress);
                    $contact->setRestriction($restriction);

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

                            return new Response(json_encode(array('error' => false, 'id' => $relative->getId(), 'idc' => $contact->getId())));
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

    public function associateContactAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $kinship = $request->get('kinship');
                $contactId = $request->get('contactId');
                $detail= $request->get('detail');

                $em = $this->getDoctrine()->getEntityManager();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);
                $contact = $em->getRepository("TecnotekExpedienteBundle:Contact")->find($contactId);
                if ( isset($student) && isset($contact) ) {
                    $relative = new Relative();
                    $relative->setContact($contact);
                    $relative->setStudent($student);
                    $relative->setKinship($kinship);
                    $relative->setDescription($detail);
                    $em->persist($relative);
                    $em->flush();
                    return new Response(json_encode(array('error' => false, 'id' => $relative->getId(), 'idc' => $contact->getId())));

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

    public function getRelativeInfoAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $relativeId = $request->get('relativeId');

                $em = $this->getDoctrine()->getEntityManager();
                $relative = new Relative();
                $relative = $em->getRepository("TecnotekExpedienteBundle:Relative")->find($relativeId);
                if ( isset($relative) ) {
                    $html  = '<div class="fieldRow"><label>Nombre:</label><span>' . $relative->getContact()->getFirstname() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Identificaci&oacute;n:</label><span>' . $relative->getContact()->getIdentification() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Celular:</label><span>' . $relative->getContact()->getPhonec() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Trabajo:</label><span>' . $relative->getContact()->getPhonew() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Casa:</label><span>' . $relative->getContact()->getPhoneh() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Lugar de trabajo:</label><span>' . $relative->getContact()->getWorkplace() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Email:</label><span></span>' . $relative->getContact()->getEmail() . '</div>';
                    $html .= '<div class="fieldRow"><label>Direcci&oacute;n:</label><span>' . $relative->getContact()->getAdress() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Restricci&oacute;n:</label><span>' . $relative->getContact()->getRestriction() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Relaci&oacute;n:</label><span>' . $relative->getDescription() . '</span></div>';

                    //firstname (lastname no), identification, phonec (telefono celular), phonew (telefono trabajo), phoneh (telefono casa),
                    //workplace, email, adress, restriction, relacion
                    return new Response(json_encode(array('error' => false, 'html' => $html)));
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


    public function getInfoRelativesAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');

                $em = $this->getDoctrine()->getEntityManager();
                $student = new Student();
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);




                if ( isset($student) ) {
                    $html  = '<div class="fieldRow"><label>Nombre:</label><span>' . $student->getFirstname() . ' ' . $student->getLastname() . '</span></div><div style="float: right;"><p><img src="../../images/alumnos/'.$student->getCarne().'.JPG"></p></div>';
                    $html .= '<div class="fieldRow"><label>Identificaci&oacute;n:</label><span>' . $student->getIdentification() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Fecha de Nacimiento:</label><span></span>' . $student->getBirthday() . '</div>';
                    $html .= '<div class="fieldRow"><label>Tel. Padre:</label><span>' . $student->getFatherPhone() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Madre:</label><span>' . $student->getMotherPhone() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Email:</label><span></span>' . $student->getEmail() . '</div>';
                    $html .= '<div class="fieldRow"><label>Direcci&oacute;n:</label><span>' . $student->getAddress() . '</span></div>';

                    //$relative = new Relative();
                    $relatives = $em->getRepository("TecnotekExpedienteBundle:Relative")->findByStudent($studentId);
                    $html .='<hr>';
                    $html .= '<div><h3><label>Contactos:</label><span></h3></span></div>';
                    foreach($relatives as $relative){
                        $html .='<hr>';
                        $html .= '<div class="fieldRow"><label>Nombre:</label><span>' . $relative->getContact()->getFirstname() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Tel. Celular:</label><span>' . $relative->getContact()->getPhonec() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Tel. Trabajo:</label><span>' . $relative->getContact()->getPhonew() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Tel. Casa:</label><span>' . $relative->getContact()->getPhoneh() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Email:</label><span></span>' . $relative->getContact()->getEmail() . '</div>';
                        $html .= '<div class="fieldRow"><label>Relaci&oacute;n:</label><span>' . $relative->getDescription() . '</span></div>';
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "No se encontro información.")));
                }

                /*$repo = $em->getRepository("TecnotekExpedienteBundle:Contact");
                foreach($institutions as $institution){
                    $institution->setStudents($repo->findAllStudentsByLastname($institution->getId(), $currentPeriod->getId()));
                    $html  = '<div class="fieldRow"><label>Nombre:</label><span>' . $relative->getContact()->getFirstname() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Identificaci&oacute;n:</label><span>' . $relative->getContact()->getIdentification() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Celular:</label><span>' . $relative->getContact()->getPhonec() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Trabajo:</label><span>' . $relative->getContact()->getPhonew() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Casa:</label><span>' . $relative->getContact()->getPhoneh() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Lugar de trabajo:</label><span>' . $relative->getContact()->getWorkplace() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Email:</label><span></span>' . $relative->getContact()->getEmail() . '</div>';
                    $html .= '<div class="fieldRow"><label>Direcci&oacute;n:</label><span>' . $relative->getContact()->getAdress() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Restricci&oacute;n:</label><span>' . $relative->getContact()->getRestriction() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Relaci&oacute;n:</label><span>' . $relative->getDescription() . '</span></div>';

                }*/


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

    public function getInfoRelativesFullAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $contactId = $request->get('contactId');

                $em = $this->getDoctrine()->getEntityManager();
                $student = new Student();
                $contact = $em->getRepository("TecnotekExpedienteBundle:Contact")->find($contactId);




                if ( isset($contact) ) {
                    $html  = '<div class="fieldRow"><label>Nombre:</label><span>' . $contact->getFirstname() . ' ' . $student->getLastname() . '</span></div><div style="float: right;"><p><img src="../../images/alumnos/'.$student->getCarne().'.JPG"></p></div>';
                    $html .= '<div class="fieldRow"><label>Identificaci&oacute;n:</label><span>' . $contact->getIdentification() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Fecha de Nacimiento:</label><span></span>' . $contact->getBirthday() . '</div>';
                    $html .= '<div class="fieldRow"><label>Tel. Cel:</label><span>' . $contact->getPhonec() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Tel. Casa:</label><span>' . $contact->getPhoneh() . '</span></div>';
                    $html .= '<div class="fieldRow"><label>Email:</label><span></span>' . $contact->getEmail() . '</div>';
                    $html .= '<div class="fieldRow"><label>Direcci&oacute;n:</label><span>' . $contact->getAdress() . '</span></div>';

                    //$relative = new Relative();
                    $relatives = $em->getRepository("TecnotekExpedienteBundle:Relative")->findByContact($contactId);
                    $html .='<hr>';
                    $html .= '<div><h3><label>Estudiantes Asociados:</label><span></h3></span></div>';
                    foreach($relatives as $relative){
                        $html .='<hr>';
                        $html .= '<div class="fieldRow"><label>Nombre:</label><span>' . $relative->getStudent()->getFirstname() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Grupo:</label><span>' . $relative->getStudent()->getGroupyear() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Carne:</label><span>' . $relative->getStudent()->getCarne() . '</span></div>';
                        $html .= '<div class="fieldRow"><label>Relaci&oacute;n:</label><span>' . $relative->getDescription() . '</span></div>';
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "No se encontro información.")));
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
                    . " ORDER BY c.lastname, c.firstname";

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
                $groupId = $request->get('groupId');
                $searchType = $request->get('searchType');

                $em = $this->getDoctrine()->getEntityManager();

                $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
                $currentPeriodId = $currentPeriod->getId();

                if( isset($searchType) && isset($groupId) && $searchType == 1 ){
                    $sql = "SELECT stdy.id, CONCAT(e.lastname, ' ', e.firstname) as 'name'  , stdy.group_id"
                        . " FROM tek_students e, tek_students_year stdy"
                        . " WHERE stdy.group_id = $groupId AND stdy.student_id = e.id"
                        . " ORDER BY e.lastname, e.firstname";

                } else {
                    $sql = "SELECT e.id, e.lastname, e.firstname, e.carne, e.groupyear , stdy.group_id, g.institution_id"
                        . " FROM tek_students e, tek_students_year stdy, tek_groups g"
                        . " WHERE (e.firstname like '%" . $text . "%' OR e.lastname like '%" . $text . "%')"
                        . " AND e.id = stdy.student_id AND e.groupyear != 'NULL' AND stdy.period_Id =".$currentPeriodId
                        . " AND stdy.group_id = g.id"
                        . " ORDER BY e.lastname, e.firstname";
                }

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

    /* Metodos para editar contactos de estudiante*/

    public function relativesEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Contact")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ContactFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/editrelative.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function relativesUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $logger = $this->get('logger');

        $entity = $em->getRepository("TecnotekExpedienteBundle:Contact")->find( $request->get('id'));
        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ContactFormType(), $entity);
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                //$student_id = $entity->getId()->;
                return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/editrelative.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_student'));
        }

    }

    /*Final metodos para editar contactos de estudiante*/
    /* Metodos para CRUD de Absences*/
    public function absencesIndexAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $today = new \DateTime();
        $start = $today->format('Y-m-d');
        $end = $today->format('Y-m-d');

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'absences')
            ->add('from', 'TecnotekExpedienteBundle:Absence absences')
            ->leftJoin("absences.studentYear", "stdY")
            ->leftJoin("stdY.student", "std")
            ->add('where', "absences.date between :start and :end")
            ->add('orderBy', 'std.lastname ASC')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $dql = "SELECT students FROM TecnotekExpedienteBundle:Student students ORDER BY students.lastname, students.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();

        $absenceTypes = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/index.html.twig', array('menuIndex' => 3, 'currentPeriod' => $currentPeriodId,
            'entities' => $entities, 'dateFrom' => $start, "dateTo" => $end, 'status' => "-1", "students" => $students, "absenceTypes" => $absenceTypes, 'student' => ""
        ));
    }

    public function absencesSearchAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $start = $request->get('from') ;
        $end = $request->get('to');

        $status = $request->get('status');
        $statusQuery = "";
        if($status != "-1")
            $statusQuery = " AND absences.justify = " . $status;

        $qb = $em->createQueryBuilder();

        $student = $request->get('student');
        $studentQuery = "";
        if( isset($student) && trim($student) != ""){
            $student = trim($student);
            $studentQuery = " AND (std.firstname like '%" . $student . "%' or std.lastname like '%" . $student . "%')";
        } else {
            $student = "";
        }

        $qb->add('select', 'absences')
            ->add('from', 'TecnotekExpedienteBundle:Absence absences')
            ->leftJoin("absences.studentYear", "stdY")
            ->leftJoin("stdY.student", "std")
            ->add('where', "absences.date between :start and :end " . $statusQuery . $studentQuery)
            ->add('orderBy', 'std.lastname ASC, std.firstname ASC, absences.date')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");


        $query = $qb->getQuery();

        $entities = $query->getResult();

        $dql = "SELECT students FROM TecnotekExpedienteBundle:Student students ORDER BY students.lastname, students.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();

        $absenceTypes = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/index.html.twig', array('menuIndex' => 3,
            'entities' => $entities, "students" => $students, "absenceTypes" => $absenceTypes, 'student' => $student,
            'dateFrom' => $start, "dateTo" => $end, 'status' => $status, 'currentPeriod' => $currentPeriodId
        ));
    }

    public function absenceSaveAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $date = $request->get('date');
                $type = $request->get('type');
                $justify = $request->get('justify');
                $comments = $request->get('comments');
                $periodId = $request->get('periodId');

                //TODO saveAbsenceAjax

                $em = $this->getDoctrine()->getEntityManager();

                $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->findOneBy(array('period' => $periodId, 'student' => $studentId));

                $logger->err("Period: " . $periodId . ", Student: " . $studentId);

                if( isset($studentYear) && $studentYear->getGroup()!= null ){
                    $entity  = new Absence();
                    $entity->setComments($comments);
                    $entity->setDate(new \DateTime($date));
                    $entity->setJustify(($justify == "true"));
                    $entity->setStudentYear($studentYear);
                    $entity->setType($em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find($type));

                    $em->persist($entity);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "El estudiante debe ingresar a un grupo antes de guardar.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::absenceSaveAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function absenceCreateAction()
    {
        $entity = new Absence();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function absenceShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Absence")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/show.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function absenceDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Absence")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_absences'));
    }

    public function absenceEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Absence")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function absenceUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $mode = $request->get('selectMode');
        $absence = $em->getRepository("TecnotekExpedienteBundle:Absence")->find( $request->get('id'));
        if ( isset($absence) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $absence);
            $form->bindRequest($request);
            if ($form->isValid()) {
                if($mode == '1') { // If type is 1 update only the use absence
                    $em->persist($absence);
                    $em->flush();
                } else { // If type 2 or 3 must update all related absences
                    $typeQuery = "";
                    if($mode == '2'){ /// ausencias del mismo dia y tipo
                        $typeQuery =  " AND abs.type = " . $absence->getTypeId();
                    }
                    $mainDate =  $absence->getDate()->format('Y-m-d');
                    $updateAllAbsencesDQL = "UPDATE TecnotekExpedienteBundle:Absence abs"
                        . " SET abs.justify = " . ($absence->getJustify()? 1:0)
                        . " , abs.comments = '" . $absence->getComments() . "'"
                        . " , abs.date='" . $absence->getDate()->format('Y-m-d') . "'"
                        . " WHERE abs.studentYear = " . $absence->getStudentYearId()
                        . " AND abs.date = '".$mainDate."'"
                        . $typeQuery;
                    $updateQuery = $em->createQuery($updateAllAbsencesDQL);
                    $updateQuery->execute();
                }
                return $this->redirect($this->generateUrl('_expediente_sysadmin_absence_show_simple') . "/" .
                    $absence->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/edit.html.twig', array(
                    'entity' => $absence, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_absences'));
        }
    }

    public function enterAbsenceByGroupAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        $absencesTypes = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/enterByGroup.html.twig', array(
            'menuIndex' => 3, 'periods' => $periods, 'absencesTypes' => $absencesTypes
        ));
    }

    public function saveGroupAbsencesAction(){
        $logger = $this->get("logger");
        $request = $this->get('request')->request;
        $em = $this->getDoctrine()->getEntityManager();
        $studentsYearIds = explode(" ", trim($request->get('studentsIds')));
        $date = $request->get('absencesDate');

        foreach( $studentsYearIds as $studentYearId  ){
            //studentYear, comments, justify, date, typeId
            if( trim($studentYearId) != ""){
                $comments = $request->get('comments_' . $studentYearId);
                $typeId = $request->get('type_' . $studentYearId);
                $justify = $request->get('justify_' . $studentYearId);
                $number = $request->get('number_' . $studentYearId);
                //$logger->err("Save absence with stdId:  " . $studentYearId . ", type: " . $typeId . ", justify: " . $justify . ":" . ($justify == "on") . ", comments: " . $comments);

                for ($i=0; $i < $number; $i++){
                    $absence = new Absence();
                    $absence->setDate(new \DateTime($date));
                    $absence->setComments($comments);
                    $absence->setJustify(($justify == "on"));
                    $absence->setStudentYear($em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($studentYearId));
                    $absence->setType($em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find($typeId));
                    $em->persist($absence);
                }
            }
        }

        $em->flush();

        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        $absencesTypes = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/enterByGroup.html.twig', array(
            'menuIndex' => 3, 'periods' => $periods, 'absencesTypes' => $absencesTypes
        ));
    }
    /* Final de los metodos para CRUD de Absences*/

    /* Metodos para CRUD de Penalties*/
    public function penaltiesIndexAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $today = new \DateTime();
        $start = $today->format('Y-m-d');
        $end = $today->format('Y-m-d');

        $logger = $this->get('logger');

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'penalties')
            ->add('from', 'TecnotekExpedienteBundle:StudentPenalty penalties')
            ->leftJoin("penalties.studentYear", "stdy")
            ->leftJoin("stdy.student", "std")
            ->add('where', "penalties.date between :start and :end")
            ->add('orderBy', 'std.lastname ASC')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
            " WHERE stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

        $query = $em->createQuery($dql);

        $students = $query->getResult();

        $penalties = $em->getRepository("TecnotekExpedienteBundle:Penalty")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Penalty/index.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'dateFrom' => $start, "dateTo" => $end, 'student' => "",
            'period' => "-1", 'students' => $students, "penalties" => $penalties, "currentPeriod" => $currentPeriodId
        ));
    }

    public function penaltiesSearchAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $start = $request->get('from') ;
        $end = $request->get('to');

        $period = $request->get('period');
        $periodQuery = "";
        if($period != "-1")
            $periodQuery = " AND absences.justify = " . $period;

        $logger = $this->get('logger');

        $student = $request->get('student');
        $studentQuery = "";
        if( isset($student) && trim($student) != ""){
            $student = trim($student);
            $studentQuery = " AND (std.firstname like '%" . $student . "%' or std.lastname like '%" . $student . "%')";
        } else {
            $student = "";
        }

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'penalties')
            ->add('from', 'TecnotekExpedienteBundle:StudentPenalty penalties')
            ->leftJoin("penalties.studentYear", "stdy")
            ->leftJoin("stdy.student", "std")
            ->add('where', "penalties.date between :start and :end " . $periodQuery . $studentQuery)
            ->add('orderBy', 'std.lastname ASC')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
            " WHERE stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

        $query = $em->createQuery($dql);
        $students = $query->getResult();

        $penalties = $em->getRepository("TecnotekExpedienteBundle:Penalty")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Penalty/index.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'dateFrom' => $start, "dateTo" => $end, 'student' => $student,
            'period' => $period, 'students' => $students, "penalties" => $penalties, "currentPeriod" => $currentPeriodId
        ));
    }

    public function penaltiesSaveAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $date = $request->get('date');
                $type = $request->get('type');
                $comments = $request->get('comments');
                $periodId = $request->get('periodId');
                $pointsPenalty = $request->get('pointsPenalty');

                $em = $this->getDoctrine()->getEntityManager();

                $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->findOneBy(array('period' => $periodId, 'student' => $studentId));

                if( isset($studentYear) && $studentYear->getGroup()!= null ){
                    $entity  = new StudentPenalty();
                    $entity->setComments($comments);
                    $entity->setPointsPenalty($pointsPenalty);
                    $entity->setDate(new \DateTime($date));
                    $entity->setStudentYear($studentYear);
                    $entity->setPenalty($em->getRepository("TecnotekExpedienteBundle:Penalty")->find($type));

                    $em->persist($entity);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "El estudiante debe pertenecer a un grupo antes de guardar.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::penaltiesSaveAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function penaltyDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentPenalty")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_penalties'));
    }

    public function penaltiesEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentPenalty")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentPenaltyFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Penalty/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function penaltiesUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $logger = $this->get('logger');

        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentPenalty")->find( $request->get('id'));
        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentPenaltyFormType(), $entity);
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_penalties'));
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Penalty/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_penalties'));
        }

    }
    /*
        public function absenceCreateAction()
        {
            $entity = new Absence();
            $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/new.html.twig', array('entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 3));
        }

        public function absenceShowAction($id)
        {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository("TecnotekExpedienteBundle:Absence")->find($id);
            $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\AbsenceFormType(), $entity);
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Absence/show.html.twig', array('entity' => $entity,
                'form'   => $form->createView(), 'menuIndex' => 3));
        }

        public function absenceDeleteAction($id){
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository("TecnotekExpedienteBundle:Absence")->find( $id );
            if ( isset($entity) ) {
                $em->remove($entity);
                $em->flush();
            }
            return $this->redirect($this->generateUrl('_expediente_absences'));
        }

        /* Final de los metodos para CRUD de Penalties*/

    /* Metodos para CRUD de Extra Points*/
    public function extrapIndexAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $request = $this->get('request')->request;
        $periodId = $request->get('periodId');

        //$currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));

        if($periodId != "")
            $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('period' => $periodId));
        $currentPeriodId = 0;

        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }else{
            $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
            if( isset($currentPeriod) ){
                $currentPeriodId = $currentPeriod->getId();
            }
        }

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'extrapoints')
            ->add('from', 'TecnotekExpedienteBundle:StudentExtraPoints extrapoints')
            ->leftJoin("extrapoints.studentYear", "stdy")
            ->leftJoin("stdy.student", "std")
            ->add('where', "stdy.period = " . $currentPeriodId)
            ->add('orderBy', 'std.lastname ASC');
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
            " WHERE stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

        $query = $em->createQuery($dql);

        $students = $query->getResult();

        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:ExtraPoints/index.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'student' => "",
            'period' => "-1", 'students' => $students, "currentPeriod" => $currentPeriodId, 'periods' => $periods
        ));
    }

    public function extrapSearchUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $request = $this->get('request')->request;
        $periodId = $request->get('periodId');

        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //$currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
                $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('id' => $periodId));

                $currentPeriodId = 0;
                if( isset($currentPeriod) ){
                    $currentPeriodId = $currentPeriod->getId();
                }

                $sql = "SELECT concat(s.lastname, ' ',s.firstname) as name, ex.id, ex.points, ex.typepoints, ex.course_id, c.name as coursename, ex.course_id as courseid"
                    . " FROM tek_students s, tek_students_year std, tek_student_extra_points ex LEFT JOIN tek_courses c ON c.id = ex.course_id"
                    . " WHERE s.id = std.student_id and std.id = ex.student_year_id and std.period_id = " . $currentPeriodId
                    . " ORDER BY s.lastname";
                $stmt2 = $em->getConnection()->prepare($sql);
                $stmt2->execute();
                $entities = $stmt2->fetchAll();


                $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
                    " WHERE stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

                $query = $em->createQuery($dql);

                $students = $query->getResult();

                $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();

                return new Response(json_encode(array('error' => false,
                    'students' => $students,
                    'currentPeriod' => $currentPeriodId,
                    'entity' => $entities)));
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::extrapSearchUpdateAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function extrapSaveAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $type = $request->get('type');
                $periodId = $request->get('periodId');
                $courseId = $request->get('course');
                $pointsExtrap = $request->get('pointsExtrap');

                $em = $this->getDoctrine()->getEntityManager();

                $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->findOneBy(array('period' => $periodId, 'student' => $studentId));
                $course = $em->getRepository("TecnotekExpedienteBundle:Course")->findOneBy(array('id' => $courseId));

                if( isset($studentYear) && $studentYear->getGroup()!= null ){
                    $entity  = new StudentExtraPoints();
                    if( isset($course))
                        $entity->setCourse($course);
                    $entity->setPoints($pointsExtrap);
                    $entity->setTypePoints($type);
                    $entity->setStudentYear($studentYear);
                    $em->persist($entity);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "El estudiante debe pertenecer a un grupo antes de guardar.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::extrapSaveAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function extrapDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentExtraPoints")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_extrap'));
    }

    public function extrapEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentExtraPoints")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentExtraPointsFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:ExtraPoints/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function extrapUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $logger = $this->get('logger');

        $entity = $em->getRepository("TecnotekExpedienteBundle:StudentExtraPoints")->find( $request->get('id'));
        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\StudentExtraPointsFormType(), $entity);
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_extrap'));
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:ExtraPoints/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_extrap'));
        }

    }

        /* Final de los metodos para CRUD de Extra Points*/

    public function getListStudentsOfGroupAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $groupId = $request->get('groupId');

                $em = $this->getDoctrine()->getEntityManager();
                $sql = "SELECT stdY.id, std.id as 'studentId', std.firstname, std.lastname "
                    . " FROM tek_students_year stdY"
                    . " JOIN tek_students std ON std.id = stdY.student_id"
                    . " WHERE stdY.group_id = " . $groupId
                    . " ORDER BY std.lastname, std.firstname";
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

    public function getListStudentsForGroupAction(){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $groupId = $request->get('groupId');
                $periodId = $request->get('periodId');
                $text = $request->get('text');
                $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true)); //2016-II

                $em = $this->getDoctrine()->getEntityManager();
                $sql = "SELECT std.id, std.firstname, std.lastname, std.carne "
                    . " FROM tek_students std"
                    . " LEFT JOIN tek_students_year stdy ON stdy.period_id = " . $currentPeriod->getId() . " AND stdy.student_id = std.id"
                    . " WHERE (std.firstname like '%" . $text . "%' OR std.lastname like '%" . $text . "%') "
                    . " AND (stdy.id is null or (stdy.period_id = " . $periodId . " AND stdy.group_id <> " . $groupId . " OR stdy.group_id is null))"
                    . " GROUP BY std.id"
                    . " ORDER BY std.lastname, std.firstname";
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
                $logger->err('Student::getListStudentsForGroupAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function addStudentToGroupAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $groupId = $request->get('groupId');
                $studentId = $request->get('studentId');
                $periodId = $request->get('periodId');

                $translator = $this->get("translator");

                if( isset($groupId) && isset($studentId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);

                    $dql = "SELECT studentYears FROM TecnotekExpedienteBundle:StudentYear studentYears WHERE studentYears.period = :period AND studentYears.student = :student";

                    $query = $em->createQuery($dql)
                        ->setParameter('period', $periodId)
                        ->setParameter('student', $studentId);
                    $results = $query->getResult();
                    $studentYear = null;
                    foreach ($results as $result) {
                        $studentYear = $result;
                    }

                    $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($studentId);

                    if( isset($studentYear) ){
                        $studentYear->setGroup($group);
                    } else {

                        $period = $em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId);
                        $studentYear = new \Tecnotek\ExpedienteBundle\Entity\StudentYear();
                        $studentYear->setGroup($group);
                        $studentYear->setStudent($student);
                        $studentYear->setPeriod($period);
                    }

                    /*UPDATE tek_students SET
                    groupyear =
                        (   SELECT CONCAT( g.grade_id, '-', g.name ) AS groupyear
                            FROM tek_groups g, tek_students_year sy
                            WHERE tek_students.id = sy.student_id AND sy.group_id = g.id );*/

                    $student->setGroupyear($group->getGrade()->getNumber() . "-" . $group->getName());
                    $em->persist($studentYear);
                    $em->flush();

                    return new Response(json_encode(array('error' => false, 'id' => $studentYear->getId())));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::addStudentToGroupAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeStudentFromGroupAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $studentId = $request->get('studentId');
                $periodId = $request->get('periodId');

                $translator = $this->get("translator");

                if( isset($studentId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    /*$dql = "SELECT studentYears FROM TecnotekExpedienteBundle:StudentYear studentYears WHERE studentYears.period = :period AND studentYears.student = :student";

                    $query = $em->createQuery($dql)
                        ->setParameter('period', $periodId)
                        ->setParameter('student', $studentId);
                    $results = $query->getResult();
                    $studentYear = null;
                    foreach ($results as $result) {
                        $studentYear = $result;
                    }*/

                    $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($studentId);

                    $studentYear->removeFromGroup();

                    $student = $studentYear->getStudent();
                    $student->setGroupyear("");

                    $em->persist($student);
                    $em->persist($studentYear);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('SuperAdmin::removeStudentFromGroupAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function ticketShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ticket = $em->getRepository("TecnotekExpedienteBundle:Ticket")->find($id);

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        $logger = $this->get('logger');

        $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->findOneBy(
            array('period' => $currentPeriodId, 'student' => $ticket->getStudent()->getId() ));

        $header = "encabezadoDefault.png";
        $text = "";
        if(isset($studentYear)){
            $institution = $studentYear->getGroup()->getInstitution();
            if(isset($institution)){
                //Find Properties
                $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                    array('institution' => $institution->getId(), 'code' => "TICKETS_IMAGE" ));

                if(isset($property)){
                    $header = $property->getValue();
                }

                $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                    array('institution' => $institution->getId(), 'code' => "TICKETS_TEXT" ));

                if(isset($property)){
                    $text = $property->getValue();
                }
            }
        }



        //$relatives = $em->getRepository("TecnotekExpedienteBundle:Relative")->findByStudent($id);
        //$contact = new \Tecnotek\ExpedienteBundle\Entity\Contact();
        //$form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ContactFormType(), $contact);

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ticket/show.html.twig',
            array('ticket' => $ticket, 'header' => $header, 'text' => $text));

        //return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/show.html.twig', array('entity' => $entity,
        //  'form'   => $form->createView(), 'menuIndex' => 3, 'relatives' => $relatives));
    }

    public function ticketIndex2Action()
    {

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ticket/index.html.twig');
    }

    public function removeTicketAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $id = $request->get('id');

                $em = $this->getDoctrine()->getEntityManager();
                $ticket = $em->getRepository("TecnotekExpedienteBundle:Ticket")->find($id);
                if ( isset($ticket) ) {
                    $em->remove($ticket);
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' => "Ticket not found.")));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::removeTicketAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function ticketsIndexAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $today = new \DateTime();
        $start = $today->format('Y-m-d');
        $end = $today->format('Y-m-d');

        $logger = $this->get('logger');

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'tickets')
            ->add('from', 'TecnotekExpedienteBundle:Ticket tickets')
            ->leftJoin("tickets.student", "std")
            ->add('where', "tickets.date between :start and :end")
            ->add('orderBy', 'std.lastname ASC')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
            " WHERE stdY.student = std.id AND stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

        $query = $em->createQuery($dql);

        $students = $query->getResult();

        $tickets = $em->getRepository("TecnotekExpedienteBundle:Ticket")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ticket/indexticket.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'dateFrom' => $start, "dateTo" => $end, 'student' => "",
            'period' => "-1", 'students' => $students, "tickets" => $tickets, "currentPeriod" => $currentPeriodId
        ));
    }

    public function ticketsSearchAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $start = $request->get('from') ;
        $end = $request->get('to');

        $period = $request->get('period');
        $periodQuery = "";
        if($period != "-1")
            $periodQuery = " AND tickets.justify = " . $period;

        $logger = $this->get('logger');

        $student = $request->get('student');
        $studentQuery = "";
        if( isset($student) && trim($student) != ""){
            $student = trim($student);
            $studentQuery = " AND (std.firstname like '%" . $student . "%' or std.lastname like '%" . $student . "%')";
        } else {
            $student = "";
        }

        $qb = $em->createQueryBuilder();
        $qb->add('select', 'tickets')
            ->add('from', 'TecnotekExpedienteBundle:Ticket tickets')
            ->leftJoin("tickets.student", "std")
            ->add('where', "tickets.date between :start and :end " . $periodQuery . $studentQuery)
            ->add('orderBy', 'std.lastname ASC')
            ->setParameter('start', $start . " 00:00:00")
            ->setParameter('end', $end . " 23:59:59");
        $query = $qb->getQuery();

        $entities = $query->getResult();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        $dql = "SELECT std FROM TecnotekExpedienteBundle:StudentYear stdY, TecnotekExpedienteBundle:Student std" .
            " WHERE stdY.student = std.id AND stdY.period = $currentPeriodId AND stdY.student = std ORDER BY std.lastname, std.firstname";

        $query = $em->createQuery($dql);
        $students = $query->getResult();

        $tickets = $em->getRepository("TecnotekExpedienteBundle:Ticket")->findAll();

        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
        $currentPeriodId = 0;
        if( isset($currentPeriod) ){
            $currentPeriodId = $currentPeriod->getId();
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Ticket/indexticket.html.twig', array('menuIndex' => 3,
            'entities' => $entities, 'dateFrom' => $start, "dateTo" => $end, 'student' => $student,
            'period' => $period, 'students' => $students, "tickets" => $tickets, "currentPeriod" => $currentPeriodId
        ));
    }

    public function enterConvocatoriasAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        $years = array();

        foreach($periods as $period){
            if (!array_key_exists($period->getYear(), $years)) {
                $years[$period->getYear()] = $period->getYear();
            }
        }
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/enterConvocatorias.html.twig', array('menuIndex' => 3,
            'years' => $years
        ));
    }

    public function getStudentsListWithConvocatoriasAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            $translator = $this->get("translator");
            try {
                $request = $this->get('request')->request;
                $year = $request->get('year');
                $groupId = $request->get('groupId');
                $courseId = $request->get('courseId');

                $em = $this->getDoctrine()->getEntityManager();

                //$currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));
                //$currentPeriodId = $currentPeriod->getId();

                if( isset($courseId) && isset($groupId) ){

                    $keywords = preg_split("/[\s-]+/", $groupId);
                    $groupId = $keywords[0];
                    $gradeId = $keywords[1];

                    $sql = "SELECT stdy.id, CONCAT(e.lastname, ' ', e.firstname) as 'name'  , stdy.group_id,"
                        . "(select et.qualification from tek_student_extra_tests et where et.student_year_id = stdy.id and et.course_id = $courseId and et.number = 1) as nota1,"
                        . "(select et.qualification from tek_student_extra_tests et where et.student_year_id = stdy.id and et.course_id = $courseId and et.number = 2) as nota2"
                        . " FROM tek_students e, tek_students_year stdy"
                        . " WHERE stdy.group_id = $groupId AND stdy.student_id = e.id"
                        . " ORDER BY e.lastname, e.firstname";
                    $logger->err("--> " . $sql);
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $students = $stmt->fetchAll();

                    if ( isset($students) ) {
                        return new Response(json_encode(array('error' => false, 'students' => $students)));
                    } else {
                        return new Response(json_encode(array('error' => true, 'message' => "Data not found.")));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
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

    public function saveConvocatoriaAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            $translator = $this->get("translator");
            try {
                $request = $this->get('request')->request;
                $stdYear = $request->get('stdYear');
                $course = $request->get('course');
                $number = $request->get('number');
                $nota = $request->get('nota');

                $em = $this->getDoctrine()->getEntityManager();

                if( isset($stdYear) && isset($number) && isset($nota) && isset($course) ){

                    $studentExtraTest = $em->getRepository("TecnotekExpedienteBundle:StudentExtraTest")->findOneBy(
                        array('studentYear' => $stdYear, 'course' => $course, 'number' => $number));

                    if( isset($studentExtraTest) ){
                        if($nota != -1){
                            $studentExtraTest->setQualification($nota);
                        } else {
                            $em->remove($studentExtraTest);
                        }
                    } else {
                        if($nota != -1){
                            $studentExtraTest = new StudentExtraTest();
                            $studentExtraTest->setCourse($em->getRepository("TecnotekExpedienteBundle:Course")->find($course));
                            $studentExtraTest->setNumber($number);
                            $studentExtraTest->setQualification($nota);
                            $studentExtraTest->setStudentYear($em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($stdYear));
                            $em->persist($studentExtraTest);
                        }
                    }
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
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

    public function studentPsicoProfileAction($id)
    {
        return $this->studentPsicoProfileGroupAction($id, 0);
    }

    public function studentPsicoProfileGroupAction($id, $groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $groups = $em->getRepository("TecnotekExpedienteBundle:QuestionnaireGroup")->findAll();
        $group = null;
        if($groupId == 0){ //Get the first Group
            $group = $groups[0];
        } else {
            $group = $em->getRepository("TecnotekExpedienteBundle:QuestionnaireGroup")->find($groupId);
        }
        $forms = $em->getRepository("TecnotekExpedienteBundle:Questionnaire")->findPsicoQuestionnairesOfGroup($group,
        false, $entity);

        $answersResult = $em->getRepository("TecnotekExpedienteBundle:Questionnaire")
            ->findPsicoQuestionnairesAnswersOfStudentByGroup($id, $group);
        $answers = array();
        foreach ($answersResult as $answer) {
            $answers[$answer->getQuestion()->getId()] = $answer;
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/psicoProfile.html.twig',
            array('entity' => $entity,'forms'   => $forms, 'menuIndex' => 3, 'answers' => $answers,
                'groups' => $groups, 'currentGroup' => $groupId));
    }

    public function studentPsicoLogAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Student")->find($id);
        $visits = $em->getRepository("TecnotekExpedienteBundle:StudentTutorVisit")->findByStudent($id);

        /*$group = null;
        if($groupId == 0){ //Get the first Group
            $group = $groups[0];
        } else {
            $group = $em->getRepository("TecnotekExpedienteBundle:QuestionnaireGroup")->find($groupId);
        }
        $forms = $em->getRepository("TecnotekExpedienteBundle:Questionnaire")->findPsicoQuestionnairesOfGroup($group,
            false, $entity);

        $answersResult = $em->getRepository("TecnotekExpedienteBundle:Questionnaire")
            ->findPsicoQuestionnairesAnswersOfStudentByGroup($id, $group);
        $answers = array();
        foreach ($answersResult as $answer) {
            $answers[$answer->getQuestion()->getId()] = $answer;
        }*/

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/psicoLog.html.twig',
            array('entity' => $entity, 'menuIndex' => 3, 'visits' => $visits));
    }

    public function savePsicoFormAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            $translator = $this->get("translator");
            try {
                $request = $this->get('request')->request;
                $stdId = $request->get('studentId');

                $em = $this->getDoctrine()->getEntityManager();

                if( isset($stdId) ){
                    $student = $em->getRepository("TecnotekExpedienteBundle:Student")->find($stdId);
                    $parameters = $request->all();
                    foreach ($parameters as $key => $value) {
                        $logger->err("Parameter-> " . $key . ":" . $value);
                    }
                    foreach ($parameters as $key => $value) {
                        if( $this->startswith($key, 'q-') ){
                            $objs = explode('-', $key); //0: q, 1: questionId, 2: type
                            $answer = new \Tecnotek\ExpedienteBundle\Entity\QuestionnaireAnswer();
                            $answer = $em->getRepository("TecnotekExpedienteBundle:Questionnaire")
                                ->findStudentQuestion($stdId,$objs[1]);
                            $answer->setStudent($student);
                            switch($objs[2]){
                                //SimpleInput, DateInput, TextAreaInput and YesNoSelectionSimple, Just save the answer
                                case 1:
                                case 2:
                                case 5:
                                case 6:
                                case 10:
                                    $answer->setMainText($value);
                                    $answer->setSecondText("");
                                    break;
                                case 3://YesNoSelectionWithExplain: Must get the explanation also
                                    $answer->setMainText($value);
                                    $answer->setSecondText($request->get('qaux-' . $objs[1]));
                                    break;
                                case 4://Must get all the
                                    break;
                                case 8://Three Columns Table
                                    $answer->setMainText($request->get('qaux1-' . $objs[1]) . '-*-' .
                                        $request->get('qaux2-' . $objs[1]) . '-*-'
                                        . $request->get('qaux3-' . $objs[1]));
                                    $answer->setSecondText("");
                                    break;
                                default:
                                    return new Response(json_encode(array('error' => true,
                                        'message' => 'Tipo Incorrecto: ' . $objs[2])));
                                    break;
                            }
                            $em->persist($answer);
                        } else {
                            //$logger->err("---> Omitiendo porque no es una pregunta... " . $key);
                        }
                    }
                    $em->flush();
                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::savePsicoFormAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    private function startswith($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    public function studentListPsicoEscAction($rowsPerPage = 35)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $text = $this->get('request')->query->get('text');
        $sqlText = "";
        ;
        if(isset($text) && $text != "") {
            $sqlText = " and (s.firstname like '%$text%' or s.lastname like '%$text%' or s.carne like '%$text%' or s.groupyear like '%$text%')";
        }

        $dql = "SELECT s FROM TecnotekExpedienteBundle:Student s where (s.groupyear = '1-A' or s.groupyear = '1-B' or s.groupyear = '1-C' or
        s.groupyear = '2-A' or s.groupyear = '2-B' or s.groupyear = '2-C' or
        s.groupyear = '3-A' or s.groupyear = '3-B' or s.groupyear = '3-C' or
        s.groupyear = '4-A' or s.groupyear = '4-B' or s.groupyear = '4-C' or
        s.groupyear = '5-A' or s.groupyear = '5-B' or s.groupyear = '5-C' or
        s.groupyear = '6-A' or s.groupyear = '6-B' or s.groupyear = '6-C') " . $sqlText;
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');



        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(s) FROM TecnotekExpedienteBundle:Student s where (s.groupyear = '1-A' or s.groupyear = '1-B' or s.groupyear = '1-C' or
        s.groupyear = '2-A' or s.groupyear = '2-B' or s.groupyear = '2-C' or
        s.groupyear = '3-A' or s.groupyear = '3-B' or s.groupyear = '3-C' or
        s.groupyear = '4-A' or s.groupyear = '4-B' or s.groupyear = '4-C' or
        s.groupyear = '5-A' or s.groupyear = '5-B' or s.groupyear = '5-C' or
        s.groupyear = '6-A' or s.groupyear = '6-B' or s.groupyear = '6-C') " . $sqlText;
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/listpsicoesc.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3, 'text' => $text
        ));
    }

    /**
     * This method will render the page for the generation of the email lists of the
     * students
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function emailsAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Student/emails.html.twig', array(
            'menuIndex' => 3, 'periods' => $periods
        ));
    } // End of emailsAction

    public function emailsLoadAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $levelId = $request->get('levelId');
                $groupId = $request->get('groupId');
                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];

                //$logger->err("Period: " . $periodId . ", Level: " . $levelId . ", Group: " . $groupId);
                $em = $this->getDoctrine()->getEntityManager();

                $sql = " SELECT distinct con.email
                FROM tek_students_year stdY
                INNER JOIN tek_students std ON std.id = stdY.student_id
                INNER JOIN tek_groups g ON g.id = stdY.group_id
                INNER JOIN tek_relatives rel ON rel.student_id = std.id
                INNER JOIN tek_contacts con ON con.id = rel.contact_id
                WHERE g.period_id = " . $periodId;

                if($levelId != 0){
                    $sql .= " AND g.grade_id = " . $levelId;
                }

                if($groupId != 0){
                    $sql .= " AND g.id = " . $groupId;
                }

                $sql .= " AND con.email is not null AND con.email <> ''
                ORDER BY con.email;";

                //$logger->err("--> " . $sql);
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $contactsEmails = $stmt->fetchAll();

                $emailsFinal = array();
                foreach ($contactsEmails as $email) {
                    if($email[0] != ""){
                        $contactEmails = explode(';', $email[0]);
                        foreach ($contactEmails as $contactEmail) {
                            if(!in_array(strtolower($contactEmail), $emailsFinal)){
                                array_push($emailsFinal, strtolower($contactEmail));
                                //$logger->err("Adding: " . strtolower($contactEmail));
                            }
                        }
                    }
                }

                //$logger->err('Count of contacts emails [' . sizeof($emailsFinal) . "]");

                $sql = "SELECT distinct std.email
                FROM tek_students_year stdY
                INNER JOIN tek_students std ON std.id = stdY.student_id
                INNER JOIN tek_groups g ON g.id = stdY.group_id
                WHERE g.period_id = " . $periodId;

                if($levelId != 0){
                    $sql .= " AND g.grade_id = " . $levelId;
                }

                if($groupId != 0){
                    $sql .= " AND g.id = " . $groupId;
                }

                $sql .= " AND std.email is not null AND std.email <> ''
                ORDER BY std.email;";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $emailsStudents = $stmt->fetchAll();

                foreach ($emailsStudents as $email) {
                    $studentEmails = explode(';', $email[0]);
                    foreach ($studentEmails as $studentEmail) {
                        if(!in_array(strtolower($studentEmail), $emailsFinal)){
                            array_push($emailsFinal, strtolower($studentEmail));
                            //$logger->err("Adding: " . strtolower($studentEmail));
                        }
                    }
                }

                //$logger->err('Count with students emails [' . count($emailsFinal) . "]");
                sort($emailsFinal);
                $emailsStr = "";
                if(sizeof($emailsFinal) > 0){
                    $emailsStr = $emailsFinal[0];
                    for($i = 1; $i < sizeof($emailsFinal); $i++){
                        $emailsStr .= ";" . $emailsFinal[$i];
                    }
                }

                if ( isset($emailsStr) ) {
                    return new Response(json_encode(array('error' => false, 'emails' => $emailsStr)));
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
    }// End of emailsLoadAction

    public function sendEmailsAction(){
        $logger = $this->get('logger');
        $translator = $this->get("translator");
        if ($this->get('request')->isXmlHttpRequest()) {
            $error = false;
            $msg = "";
            try {
                $request = $this->get('request');
                $attachment = $request->files->get("attachment-0");
                $fullPath = "";
                if ( isset($attachment) ) {
                    if ($attachment instanceof UploadedFile && ($attachment->getError() == '0')) {
                        if ($attachment->getSize() <= 5 * 1024 * 1024) { // 8MB
                            $originalName = $attachment->getClientOriginalName();
                            $name_array = explode('.', $originalName);
                            $file_type = $name_array[sizeof($name_array)-1];
                            $valid_file_types = array('jpg', 'jpeg', 'bpm', 'png','pdf','doc','docx','xls','xlsx');
                            if (in_array(strtolower($file_type), $valid_file_types)) {
                                $fullPath = $this->saveFile($attachment);
                            } else {
                                $error = true;
                                $msg = $translator->trans("email.send.error.file.type");
                            }
                        } else {
                            $error = true;
                            $msg = $translator->trans("email.send.error.size");
                        }
                    } else {
                        $error = true;
                        $msg = $translator->trans("email.send.error.invalid");
                    }
                }
                if ($error == false) {
                    $emails = $request->request->get('emails');
                    $body = $request->request->get('body');
                    $subject = $request->request->get('subject');
                    $extraEmails = $request->request->get('extraEmails');
                    if ( trim($emails) == "") {
                        $emails .= $extraEmails;
                    } else {
                        $emails .= ";" . $extraEmails;
                    }
                    $bbcEmails = explode(";",$emails);
                    $message = \Swift_Message::newInstance()
                        ->setSubject($subject)
                        ->setFrom(array('stmmail@stmcr.com' => 'Saint Michael'))
                        ->setBcc($bbcEmails)
                        ->setBody($body, 'text/html');
                    if($fullPath != "") {
                        $message->attach(\Swift_Attachment::fromPath($fullPath));
                    }
                    $this->get('mailer')->send($message);
                    if ($fullPath != "" && file_exists($fullPath)) unlink($fullPath);
                    $msg = $translator->trans("email.send.success");
                }
                return new Response(json_encode(array('error' => $error, 'message' => $msg)));
            }catch (Exception $e) {
                $info = toString($e);
                $logger->err('Student::sendEmailsAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        } else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadStudentRelatives(){
        $em = $this->getDoctrine()->getEntityManager();
        $id = 1;
        $relatives = $em->getRepository("TecnotekExpedienteBundle:Relative")->findByStudent($id);
    }

    public function saveFile($uploaded_file) {
        $uploaded_file_info = pathinfo($uploaded_file->getClientOriginalName());
        $filename = md5(uniqid()) . "." .$uploaded_file_info['extension'];
        $attachmentPath = $this->container->getParameter('kernel.root_dir').'/../web/uploads/attachments';
        $uploaded_file->move($attachmentPath, $uploaded_file->getClientOriginalName());
        return $attachmentPath . "/" . $uploaded_file->getClientOriginalName();
    }
}