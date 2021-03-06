<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\CourseClass;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
use Tecnotek\ExpedienteBundle\Entity\StudentYear;
use Tecnotek\ExpedienteBundle\Entity\StudentYearCourseQualification;
use Tecnotek\ExpedienteBundle\Entity\Ticket;
use Tecnotek\ExpedienteBundle\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{

    public function reportBusAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Buseta")->findAll();
        $routes = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/bus.html.twig', array('menuIndex' => 4,
            'entities' => $entities, 'routes' => $routes
        ));
    }

    public function searchBusAction(){
        $logger = $this->get("logger");
        $request = $this->get('request')->request;
        $name = $request->get('name');
        $capacity = $request->get('capacity');
        $licensePlate = $request->get('licensePlate');
        $driver = $request->get('driver');
        $color = $request->get('color');
        $route = $request->get('route');

        $where = "";
        if( $name != ""){ $where .= ($where=="")? " e.name like '%$name%'":" AND e.name like '%$name%'"; }
        if( $capacity != ""){ $where .= ($where=="")? " e.capacity = $capacity":" AND e.capacity = $capacity"; }
        if( $licensePlate != ""){ $where .= ($where=="")? " e.licensePlate like '%$licensePlate%'":" AND e.licensePlate like '%$licensePlate%'"; }
        if( $driver != ""){ $where .= ($where=="")? " e.driver like '%$driver%'":" AND e.driver like '%$driver%'"; }
        if( $color != ""){ $where .= ($where=="")? " e.color like '%$color%'":" AND e.color like '%$color%'"; }
        /*if( $route != 0){
            $where .= ($where=="")? " e.route = $route":" AND e.route = $route";
        }*/

        $logger->err("Parametros de busqueda de busetas: " . $name . "-" . $licensePlate . "-" . $driver. "-" . $color. "-" . $capacity. "-" . $route . "<-");
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT e FROM TecnotekExpedienteBundle:Buseta e";
        $dql .= ($where == "")? " ORDER BY e.name ASC" : " WHERE $where ORDER BY e.name ASC";
        $query = $em->createQuery($dql);
        $entities = $query->getResult();
        $routes = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/bus.html.twig', array('menuIndex' => 4,
            'entities' => $entities, 'routes' => $routes
        ));
    }

    public function reportRouteAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/routes.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }

    public function reportRouteClubAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/routes_club.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }

    public function reportZonesAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Zone")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/zone.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }

    public function reportStudentByRouteAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }

    public function reportStudentInRouteByGroupAction(){  //nuevo 2016-II

        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();
        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));

        $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
        $query = $em->createQuery($dql);
        $groups = $query->getResult();
        $groupRepo = $em->getRepository("TecnotekExpedienteBundle:Group");
        foreach($groups as $group){
            $group->setStudents($groupRepo->findAllStudentsByLastname($group->getId()));
        }

        $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
        $query = $em->createQuery($dql);
        $groupsT = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_in_route_by_group.html.twig', array('menuIndex' => 4,
            'groups' => $groups, 'groupsT' => $groupsT
        ));
    }

    public function reportStudentByRouteFilter2Action(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route_filter2.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }


    public function reportStudentDailyByRouteFilter2Action(){
        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteFilter2Action [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteFilter2Action [Error runing sp: ' . $errorMessage . "]");
        }

        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();

        $html = "";

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/daily_by_route_filter2.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }



    public function reportStudentAbsencesByRouteFilter2Action(){
        $em = $this->getDoctrine()->getEntityManager();
        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteFilter2Action [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteFilter2Action [Error runing sp: ' . $errorMessage . "]");
        }

        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/absences_by_route_filter2.html.twig', array('menuIndex' => 4,
            'entities' => $entities, 'errorMessage' => $errorMessage
        ));
    }

    public function reportStudentByRouteClubAction(){

        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $text = $this->get('request')->query->get('text');
        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE r.name like '%$text%'";
        }

        $dql = "SELECT r FROM TecnotekExpedienteBundle:Route r" . $sqlText;
        $query = $em->createQuery($dql);

        $entity = $query->getResult();
        /*$entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route_club.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));*/

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route_club.html.twig', array(
            'menuIndex' => 4, 'text' => $text, 'entities' => $entity
        ));
    }

    public function reportStudentByRouteClubLevelAction(){

        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $text = $this->get('request')->query->get('text');
        $day = $this->get('request')->query->get('day');
        $nday= "";
        if($day == '1'){$nday='LUNES';}
        if($day == '2'){$nday='MARTES';}
        if($day == '3'){$nday='MIERCOLES';}
        if($day == '4'){$nday='JUEVES';}
        if($day == '5'){$nday='VIERNES';}
        if($day == '6'){$nday='SABADO';}
        if($day == '7'){$nday='DOMINGO';}


        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE r.name like '%$text%'";
        }

        $stmt = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('select r.name as route, c.day, c.name, s.carne, concat (s.lastname," ",s.firstname) as student, s.groupyear
            from tek_students s, tek_clubs c, club_student cs, tek_route r, tek_students_to_routes tr
            where tr.student_id = s.id and tr.route_id = r.id and s.id = cs.student_id and c.id = cs.club_id and day = "'.$day.'"
and r.name like "%'.$nday.'%"
order by c.day, s.groupyear');
        $stmt->execute();
        $entity = $stmt->fetchAll();




        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route_club_level.html.twig', array(
            'menuIndex' => 4, 'text' => $text, 'day' => $day, 'entities' => $entity
        ));
    }

    public function reportStudentByClubLevelAction(){

        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $text = $this->get('request')->query->get('text');
        $day = $this->get('request')->query->get('day');

        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE r.name like '%$text%'";
        }

        $stmt = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('select c.day, c.name, s.carne, concat (s.lastname," ",s.firstname) as student, s.groupyear
            from tek_students s, tek_clubs c, club_student cs
            where s.id = cs.student_id and c.id = cs.club_id and day = "'.$day.'"
            order by c.day, s.groupyear');
        $stmt->execute();
        $entity = $stmt->fetchAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_club_level.html.twig', array(
            'menuIndex' => 4, 'text' => $text, 'day' => $day, 'entities' => $entity
        ));
    }

    public function reportStudentByRouteClubRouteAction(){

        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $text = $this->get('request')->query->get('text');
        $day = $this->get('request')->query->get('day');
        $dayname = "";
        switch($day){
            case "1":   $dayname = "Lunes";
                break;
            case "2":   $dayname = "Martes";
                break;
            case "3":   $dayname = "Miercoles";
                break;
            case "4":   $dayname = "Jueves";
                break;
            case "5":   $dayname = "Viernes";
                break;
            case "6":   $dayname = "Sabado";
                break;
            case "7":   $dayname = "Domingo";
                break;
            default:    $dayname = "";
            break;
        }
        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE r.name like '%$text%'";
        }

        $stmt = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('select r.name as route, c.day, c.name as club, s.carne, concat (s.lastname," ",s.firstname) as student, s.groupyear
            from tek_students s, tek_clubs c, club_student cs, tek_route r, tek_students_to_routes sr
            where cs.student_id = sr.student_id and r.id = sr.route_id and sr.student_id = s.id and s.id = cs.student_id and c.id = cs.club_id
            and day = "'.$day.'"  and r.name like "%'.$dayname.'%"
            order by r.name, s.groupyear, s.lastname');
        $stmt->execute();
        $entity = $stmt->fetchAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_by_route_club_route.html.twig', array(
            'menuIndex' => 4, 'text' => $text, 'day' => $day, 'entities' => $entity
        ));
    }

    public function reportStudentRoutesAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $text = $this->get('request')->query->get('text');
        $text2 = $this->get('request')->query->get('text2');
        $sqlText = "";
        if((isset($text) && $text != "")||(isset($text2) && $text2 != "")) {
            $sqlText = " AND s.lastname like '%$text2%' AND s.firstname like '%$text%'";
            //$sqlText = " AND concat(s.firstname,' ', s.lastname) like '%$text%'";
            //$sqlText = " AND (s.lastname like '%$text2%' AND s.firstname like '%$text%)";
        }

        $dql = "SELECT s FROM TecnotekExpedienteBundle:Student s where s.route != 'NULL'" . $sqlText ."ORDER BY s.lastname";
        $query = $em->createQuery($dql);

        $entity = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students_routes.html.twig', array(
            'menuIndex' => 4, 'text' => $text, 'text2' => $text2, 'entities' => $entity
        ));
    }

    public function reportStudentAbsencesByRouteAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/absences_by_route.html.twig', array('menuIndex' => 4,
            'entities' => $entities, 'errorMessage' => $errorMessage
        ));
    }

    public function reportStudentDailyByRouteAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');
        $errorMessage = "";
        try{
            $stmt = $em->getConnection()->prepare("CALL setStudentsDailyStatus()");
            $stmt->execute();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $logger->err('Report::reportStudentAbsencesByRouteAction [Error runing sp: ' . $errorMessage . "]");
        }

        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();

        $html = "";

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/daily_by_route.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }

    public function reportStudentsAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();
        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));

        $logger->err("--> CurrentPeriod: " . $currentPeriod);
        $request = $this->get('request')->request;
        $tipo = $request->get('tipo');

        $iSpecial = $request->get('iSpecial');
        $iSpecialTiq = $request->get('iSpecialTiq');
        $autoTiq = $request->get('autoTiq');
        $carneTiq = $request->get('carneTiq');

        $gender = $request->get('gender');
        $age = $request->get('age');
        $address = $request->get('address');
        $birthday = $request->get('birthday');
        $identification = $request->get('identification');
        $laterality = $request->get('laterality');
        $route = $request->get('route');  //nuevo

        $groups = null;
        $grades = null;
        $institutions = null;

        $groupsT = null;
        $gradesT = null;
        $institutionsT = null;

        if( !isset($tipo)){
            $tipo = 0;
        } else {
            if($tipo == 1){
                $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $groups = $query->getResult();
                $groupRepo = $em->getRepository("TecnotekExpedienteBundle:Group");
                foreach($groups as $group){
                    $group->setStudents($groupRepo->findAllStudentsByLastname($group->getId()));
                }

                $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $groupsT = $query->getResult();

                $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $gradesT = $query->getResult();

                $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                $query = $em->createQuery($dql);
                $institutionsT = $query->getResult();

            } else {
                if($tipo == 2){
                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $grades = $query->getResult();
                    $gradeRepo = $em->getRepository("TecnotekExpedienteBundle:Grade");
                    foreach($grades as $grade){
                        $grade->setStudents($gradeRepo->findAllStudentsByLastname($grade->getId(), $currentPeriod->getId()));
                    }

                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $groupsT = $query->getResult();

                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $gradesT = $query->getResult();

                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutionsT = $query->getResult();

                } else {
                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutions = $query->getResult();
                    $repo = $em->getRepository("TecnotekExpedienteBundle:Institution");
                    foreach($institutions as $institution){
                        $institution->setStudents($repo->findAllStudentsByLastname($institution->getId(), $currentPeriod->getId()));
                    }

                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $groupsT = $query->getResult();

                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $gradesT = $query->getResult();

                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutionsT = $query->getResult();

                }
            }

            //$groups = $em->getRepository("TecnotekExpedienteBundle:Group")->findBy(array('period' => $currentPeriod));
        }
        $logger->err("--> groups: " . sizeof($groups) );
        $logger->err("--> groups: " . sizeof($grades) );
        $typeLabel = "";
        switch($tipo){
            case 1: $typeLabel = "Grupo"; break;
            case 2: $typeLabel = "Nivel"; break;
            case 3: $typeLabel = "Institucion"; break;
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students.html.twig', array('menuIndex' => 4,
            'tipo' => $tipo, 'typeLabel' => $typeLabel, 'groups' => $groups,
            'grades' => $grades, 'institutions' => $institutions, 'iSpecial' => $iSpecial, 'iSpecialTiq' => $iSpecialTiq,'autoTiq' => $autoTiq,'carneTiq' => $carneTiq,
            'age' => $age, 'gender' => $gender, 'address' => $address, 'identification' => $identification,'birthday' => $birthday, 'laterality' => $laterality, 'route' => $route,
            'groupsT' => $groupsT, 'institutionsT' => $institutionsT,'gradesT' => $gradesT
        ));
    }

    public function reportEmergencyStudentsAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();
        $currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));

        $logger->err("--> CurrentPeriod: " . $currentPeriod);
        $request = $this->get('request')->request;
        $tipo = $request->get('tipo');

        $iSpecial = $request->get('iSpecial');
        $iSpecialTiq = $request->get('iSpecialTiq');
        $autoTiq = $request->get('autoTiq');
        $carneTiq = $request->get('carneTiq');

        $fatherPhone = $request->get('fatherPhone');
        $motherPhone = $request->get('motherPhone');
        $address = $request->get('address');
        $birthday = $request->get('birthday');
        $identification = $request->get('identification');
        $laterality = $request->get('laterality');

        $emergencyout = $request->get('emergencyout');
        $emergencyoutinst = $request->get('emergencyoutinst');
        $brethren = $request->get('brethren');
        $familiars = $request->get('familiars');
        $emergencyinfo = $request->get('emergencyinfo');

        $groups = null;
        $grades = null;
        $institutions = null;

        $groupsT = null;
        $gradesT = null;
        $institutionsT = null;

        $tipo = 1;
        if( !isset($tipo)){
            $tipo = 0;
        } else {
            if($tipo == 1){
                $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $groups = $query->getResult();
                $groupRepo = $em->getRepository("TecnotekExpedienteBundle:Group");
                foreach($groups as $group){
                    $group->setStudents($groupRepo->findAllStudentsByLastname($group->getId()));
                }

                $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $groupsT = $query->getResult();

                $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $gradesT = $query->getResult();

                $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                $query = $em->createQuery($dql);
                $institutionsT = $query->getResult();

            } else {
                if($tipo == 2){
                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $grades = $query->getResult();
                    $gradeRepo = $em->getRepository("TecnotekExpedienteBundle:Grade");
                    foreach($grades as $grade){
                        $grade->setStudents($gradeRepo->findAllStudentsByLastname($grade->getId(), $currentPeriod->getId()));
                    }

                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $groupsT = $query->getResult();

                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $gradesT = $query->getResult();

                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutionsT = $query->getResult();

                } else {
                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutions = $query->getResult();
                    $repo = $em->getRepository("TecnotekExpedienteBundle:Institution");
                    foreach($institutions as $institution){
                        $institution->setStudents($repo->findAllStudentsByLastname($institution->getId(), $currentPeriod->getId()));
                    }

                    $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $groupsT = $query->getResult();

                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $gradesT = $query->getResult();

                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutionsT = $query->getResult();

                }
            }

            //$groups = $em->getRepository("TecnotekExpedienteBundle:Group")->findBy(array('period' => $currentPeriod));
        }
        $logger->err("--> groups: " . sizeof($groups) );
        $logger->err("--> groups: " . sizeof($grades) );
        $typeLabel = "";
        switch($tipo){
            case 1: $typeLabel = "Grupo"; break;
            case 2: $typeLabel = "Nivel"; break;
            case 3: $typeLabel = "Institucion"; break;
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/studentsEmergency.html.twig', array('menuIndex' => 4,
            'tipo' => $tipo, 'typeLabel' => $typeLabel, 'groups' => $groups,
            'grades' => $grades, 'institutions' => $institutions, 'iSpecial' => $iSpecial, 'iSpecialTiq' => $iSpecialTiq,'autoTiq' => $autoTiq,'carneTiq' => $carneTiq,
            'fatherPhone' => $fatherPhone, 'motherPhone' => $motherPhone, 'address' => $address, 'identification' => $identification,'birthday' => $birthday, 'laterality' => $laterality,
            'groupsT' => $groupsT, 'institutionsT' => $institutionsT,'gradesT' => $gradesT,'emergencyout' => $emergencyout, 'emergencyoutinst' => $emergencyoutinst,
            'brethren' => $brethren, 'familiars' => $familiars,'emergencyinfo' => $emergencyinfo
        ));
    }

    public function reportClubsAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();

        $clubs = $em->getRepository("TecnotekExpedienteBundle:Club")->findAll();

        //$currentPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('isActual' => true));

        $request = $this->get('request')->request;
        $withStudents = $request->get('withStudents');
        $tipo = $request->get('tipo');
        $gender = $request->get('gender');
        $age = $request->get('age');
        $telephone = $request->get('telephone');
        $identification = $request->get('identification');
        $emails = $request->get('emails');

        /*if( !isset($tipo)){
            $tipo = 0;
        } else {
            if($tipo == 1){
                $dql = "SELECT g FROM TecnotekExpedienteBundle:Group g JOIN g.grade grade WHERE g.period = " . $currentPeriod->getId() . " ORDER BY grade.number";
                $query = $em->createQuery($dql);
                $groups = $query->getResult();
                $groupRepo = $em->getRepository("TecnotekExpedienteBundle:Group");
                foreach($groups as $group){
                    $group->setStudents($groupRepo->findAllStudentsByLastname($group->getId()));
                }
            } else {
                if($tipo == 2){
                    $dql = "SELECT grade FROM TecnotekExpedienteBundle:Grade grade ORDER BY grade.number";
                    $query = $em->createQuery($dql);
                    $grades = $query->getResult();
                    $gradeRepo = $em->getRepository("TecnotekExpedienteBundle:Grade");
                    foreach($grades as $grade){
                        $grade->setStudents($gradeRepo->findAllStudentsByLastname($grade->getId(), $currentPeriod->getId()));
                    }
                } else {
                    $dql = "SELECT institution FROM TecnotekExpedienteBundle:Institution institution ORDER BY institution.id";
                    $query = $em->createQuery($dql);
                    $institutions = $query->getResult();
                    $repo = $em->getRepository("TecnotekExpedienteBundle:Institution");
                    foreach($institutions as $institution){
                        $institution->setStudents($repo->findAllStudentsByLastname($institution->getId(), $currentPeriod->getId()));
                    }
                }
            }

            //$groups = $em->getRepository("TecnotekExpedienteBundle:Group")->findBy(array('period' => $currentPeriod));
        }
        $logger->err("--> groups: " . sizeof($groups) );
        $logger->err("--> groups: " . sizeof($grades) );
        $typeLabel = "";
        switch($tipo){
            case 1: $typeLabel = "Grupo"; break;
            case 2: $typeLabel = "Nivel"; break;
            case 3: $typeLabel = "Institucion"; break;
        }

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/students.html.twig', array('menuIndex' => 4,
            'tipo' => $tipo, 'typeLabel' => $typeLabel, 'groups' => $groups,
            'grades' => $grades, 'institutions' => $institutions,
            'age' => $age, 'gender' => $gender
        ));*/

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/clubs.html.twig', array('menuIndex' => 4,
            'tipo' => 0, 'typeLabel' => "aaa", 'withStudents' => $withStudents, 'clubs' => $clubs,
            'age' => $age, 'gender' => $gender, 'telephone' => $telephone, 'identification' => $identification, 'emails' => $emails, 'tipo' => $tipo
        ));
    }

    public function qualificationsOfPeriodAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        //$routes = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/periodGroupQualifications.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function averagesOfPeriodAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/periodBestAverages.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function averagesOfGroupAction(){   //mejores promedios 2016-II
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            $em = $this->getDoctrine()->getEntityManager();
            $logger = $this->get('logger');
            $errorMessage = "";

            $request = $this->get('request')->request;
            $periodId = $request->get('periodId');
            $levelId = $request->get('levelId');
            $groupId = $request->get('groupId');

            $keywords = preg_split("/[\s-]+/", $groupId);
            $groupId = $keywords[0];
            $gradeId = $keywords[1];

            $stmt = $this->getDoctrine()->getEntityManager()
                ->getConnection()
                ->prepare('SELECT g.grade_id as grade, g.name as `group`, s.carne as carne, concat(s.lastname, s.firstname) as name, st.periodAverageScore as average, st.periodhonor as honor, st.conducta as conducta
                FROM tek_students_year st, tek_students s, tek_groups g
                WHERE g.id = "'.$groupId.'" and st.student_id = s.id and g.id = st.group_id order by st.periodAverageScore desc, s.lastname asc');
            $stmt->execute();
            $entity = $stmt->fetchAll();


            /*$entity = 'SELECT g.grade_id as grade, g.name as `group`, s.carne as carne, concat(s.lastname, s.firstname) as name, st.periodAverageScore as average, st.periodhonor as honor, st.conducta as conducta
                FROM tek_students_year st, tek_students s, tek_groups g
                WHERE g.id = "'.$groupId.'" and st.student_id = s.id and g.id = st.group_id order by st.group_id ASC, s.lastname asc';
*/
            return new Response(json_encode(array('error' => false, 'entity' => $entity, 'periodId' => $periodId)));

        }// endif this is an ajax request
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");

        }
    }

    public function absencesOfPeriodAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/absences_by_group.html.twig', array('menuIndex' => 5,
            'periods' => $periods
        ));
    }

    public function absencesOfGroupAction(){   //ausencias de un grupo  2016-4
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            $em = $this->getDoctrine()->getEntityManager();
            $logger = $this->get('logger');
            $errorMessage = "";

            $request = $this->get('request')->request;
            $periodId = $request->get('periodId');
            $levelId = $request->get('levelId');
            $groupId = $request->get('groupId');

            $keywords = preg_split("/[\s-]+/", $groupId);
            $groupId = $keywords[0];
            $gradeId = $keywords[1];

            $stmt = $this->getDoctrine()->getEntityManager()
                ->getConnection()
                ->prepare('SELECT DISTINCT g.grade_id as grade, g.name as `group`, s.carne as carne, concat(s.lastname, s.firstname) as name, a.date, at.name as absence, a.comments, a.justify
                FROM tek_students_year st, tek_students s, tek_groups g, tek_absences a, tek_absence_types at
                WHERE a.studentYear_id = st.id and at.id = a.type_id and g.id = "'.$groupId.'" and st.student_id = s.id and g.id = st.group_id ORDER BY `s`.`lastname` ASC');
            $stmt->execute();
            $entity = $stmt->fetchAll();


            /*$entity = 'SELECT g.grade_id as grade, g.name as `group`, s.carne as carne, concat(s.lastname, s.firstname) as name, st.periodAverageScore as average, st.periodhonor as honor, st.conducta as conducta
                FROM tek_students_year st, tek_students s, tek_groups g
                WHERE g.id = "'.$groupId.'" and st.student_id = s.id and g.id = st.group_id order by st.group_id ASC, s.lastname asc';
*/
            return new Response(json_encode(array('error' => false, 'entity' => $entity, 'periodId' => $periodId)));

        }// endif this is an ajax request
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");

        }
    }

    public function penaltiesOfPeriodAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        //$routes = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/penaltyStudentReport.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function loadGroupQualificationsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //ToDo: volver a poner el request de post
                $request = $this->get('request')->request;
                //$request = $this->get('request');
                $periodId = $request->get('periodId');
//                $periodId = 3;
                $groupId = $request->get('groupId');
                $convocatoria = $request->get('conv');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];
$kinder1 = 0;
                $referenceId = $request->get('referenceId');

                $translator = $this->get("translator");

                if( isset($referenceId) && isset($groupId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //$period = $em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId);

                    $carne = "";
                    $teacherGroup = "";
                    $studentName = "";

                    $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);
                    $grade = $em->getRepository("TecnotekExpedienteBundle:Grade")->find($gradeId);
                    $teacher = $group->getTeacher();
                    $imgHeader = "encabezadoDefault.png";
                    $teacherGroup = $teacher->getFirstname() . " " . $teacher->getLastname();
                    $director = "Indefinido";
                    $institution = $group->getInstitution();
                    if(isset($institution)){
                        //Find Properties
                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "TICKETS_IMAGE" ));

                        if(isset($property)){
                            $imgHeader = $property->getValue();
                        }

                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "DIRECTOR" ));

                        if(isset($property)){
                            $director = $property->getValue();
                        }

                        /*$property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "TICKETS_TEXT" ));

                        if(isset($property)){
                            $text = $property->getValue();
                        }*/
                    }

                    //$grade = new \Tecnotek\ExpedienteBundle\Entity\Grade();
                    if($grade->getIsSpecial()){
                        if($referenceId == 0){
                            //$html = $this->getGroupHTMLQualifications($periodId, $gradeId, $groupId);
                            $html = "Trabajando....";
                        } else {
                            $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($referenceId);
                            $student = $studentYear->getStudent();
                            $carne = $student->getCarne();
                            $kinder1 = 1;
                            $studentName = "" . $student;
                            $html = $this->getStudentSpecialQualificationHTMLQualifications($periodId, $gradeId, $groupId,
                                $referenceId, $studentYear, $director, $institution, $convocatoria);
                        }
                    } else {
                        if($referenceId == 0){
                            $html = $this->getGroupHTMLQualifications($periodId, $gradeId, $groupId);
                        } else {
                            $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($referenceId);
                            $student = $studentYear->getStudent();
                            $carne = $student->getCarne();
                            $studentName = "" . $student;
                            $html = $this->getStudentByPeriodHTMLQualifications($periodId, $gradeId, $groupId, $referenceId, $studentYear, $director, $institution, $convocatoria);
                        }
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html, 'carne' => $carne, 'teacherGroup' => $teacherGroup, "studentName" => $studentName, "imgHeader" => $imgHeader, "kinder1" => $kinder1)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getGroupHTMLQualifications($periodId, $gradeId, $groupId){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
///aca voy
        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc "
            . " WHERE cc.grade = " . $gradeId . " AND cc.course = c AND cc.period = " . $periodId
            . " ORDER BY c.name";

        $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);
        $institution = $group->getInstitution();

        $query = $em->createQuery($dql);
        $courses = $query->getResult();

        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 145px;">';
        $headersRow .=  '        <th style="width: 75px; text-align: center;">Carne</th>';
        $headersRow .=  '        <th style="width: 250px; text-align: center;">Estudiante</th>';

        $studentRow = '';

        $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
            . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
            . " ORDER BY std.lastname, std.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();

        $headersRow .=  '        <th style="vertical-align: bottom; padding: 0.5625em 0.625em;">P</th>';
        $headersRow .=  '        <th style="vertical-align: bottom; padding: 0.5625em 0.625em;">C</th>';



        foreach( $courses as $course )
        {
            $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $course->getCourse()->getName() . '</div></th>';
            $studentRow .= '<td>Nota_' . $course->getId() . '_</td>';
        }


        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';
        $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $grupo = $em->getRepository("TecnotekExpedienteBundle:Group")->findOneBy(array('id' => $groupId));
        $html .=  'Grupo: '.$grupo->getGrade().'-'. $grupo->getName();
        $studentRowIndex = 0;
 //$html .= "entro";
        foreach($students as $stdy){
 //$html .= "entro";
            $this->calculateStudentYearQualification($periodId, $stdy->getId(), $stdy);


            $total = 0;
            $counter = 0;

            $html .=  '<tr class="rowNotas" style="height: 25px;">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';

            $row = $studentRow;
            /***** Obtener Notas del Estudiante Inicio *****/
            foreach( $courses as $course )
            {
                $notaMin = $em->getRepository("TecnotekExpedienteBundle:Grade")->findOneBy(array('id' => $gradeId));
                $notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")->findOneBy(array('courseClass' => $course->getId(), 'studentYear' => $stdy->getId()));

                $typeC = $course->getCourse()->getType();
                if( $typeC==1){



                    if(isset($notaFinal)){//Si existe

                        //// nuevo
                        if($notaFinal->getQualification() != '0' || $notaFinal->getQualification() != '-1'){
                            $total += $notaFinal->getQualification();
                            $counter += 1;
                        }
                        //// nuevo

                        if($notaFinal->getQualification() < $notaMin->getNotaMin()){
                            $row = str_replace("Nota_" . $course->getId() . "_", "* " .  $notaFinal->getQualification(), $row);
                        } else {
                            $row = str_replace("Nota_" . $course->getId() . "_", $notaFinal->getQualification(), $row);
                        }
                    } else {
                        $row = str_replace("Nota_" . $course->getId() . "_", "-", $row);
                    }

                }
                else{
                    if(isset($notaFinal)){//Si existe
                        $valorNota =  $notaFinal->getQualification();
                        if($valorNota == 99)
                            $valorNota = "Exc";
                        if($valorNota == 74)
                            $valorNota = "V.Good";
                        if($valorNota == 50)
                            $valorNota = "Good";
                        if($valorNota == 25)
                            $valorNota = "N.I.";
                        if($valorNota == 4)
                            $valorNota = "Exc";
                        if($valorNota == 3)
                            $valorNota = "V.Good";
                        if($valorNota == 2)
                            $valorNota = "Good";
                        if($valorNota == 1)
                            $valorNota = "N.I.";
                        $row = str_replace("Nota_" . $course->getId() . "_", $valorNota, $row);
                    }
                }
            }

            //Revisar Ausencias y Calcular Nota de Conducta

            if($institution->getId() == '3'){
                $sql = "select at.name, count(a.id) as 'total', sum(atp.points) as 'puntos'"
                    . " from tek_absence_types at"
                    . " join tek_absence_types_points atp on at.id = atp.absence_type_id and atp.institution_id = " . $institution->getId()
                    . " left join tek_absences a on a.type_id = at.id and a.justify = 0 and a.studentYear_id = " . $stdy->getId()
                    . " group by at.id;";
            }
            if($institution->getId() == '2'){
                $sql = "select at.name, count(a.id) as 'total', sum(atp.points) as 'puntos'"
                    . " from tek_absences a "
                    . " join tek_absence_types at on at.id = a.type_id"
                    . " join tek_absence_types_points atp on at.id = atp.absence_type_id and atp.institution_id = " . $institution->getId()
                    . " where a.studentYear_id =  " . $stdy->getId()." AND a.justify = 0"
                    . " group by a.type_id;";
            }

            $absences = $em->getConnection()->executeQuery($sql);
            $conducta = 100;
            $logger->err("-----> Arranca con 100");
            foreach($absences as $absenceType){
                if($absenceType["total"] > 0) {
                    $conducta -= $absenceType["puntos"];
                }
            }

            $sql = 'SELECT COUNT(id) as "total",SUM(pointsPenalty) as "puntos" FROM tek_student_penalties where student_year_id = ' . 	$stdy->getId();
            $puntosPorSancion = $em->getConnection()->executeQuery($sql);
            foreach($puntosPorSancion as $pa){
                if(isset($pa["puntos"]) && $pa["puntos"] != "null"){
                    $conducta -= $pa["puntos"];
                }
            }

            if($conducta < 0) {
                $conducta = 0;
            }
            $total += $conducta;
            $counter += 1;

            $promedioPeriodo = 0;
            $promedioPeriodo = $total / $counter;

            $html .=  '<td>'.number_format($promedioPeriodo, 2, '.', '').'</td>';
            $html .=  '<td>'.number_format($conducta, 2, '.', '').'</td>';
            /***** Obtener Notas del Estudiante Final *****/
            $html .=  $row . "</tr>";
        }

        $html .= "</table>";

        return $html;
    }

    private function printSpecialQualificationOptions($type, $stdyId, $sq, $responses, $periodNumber){
        $html = "";
        $option = (key_exists($stdyId . '-' . $sq . '-' . $periodNumber, $responses))? $responses[$stdyId . '-' . $sq . "-" .
            $periodNumber]:0;
        $option = $option*1;
        switch($type){
            case 1:
                $html = '<select class="sq-option" stdyId="' . $stdyId . '" sq="' . $sq . '" pn="' . $periodNumber .
                    '">';
                $html .= '<option value="0"' . (($option==0)? ' selected':'') . '>&nbsp;</option>';
                $html .= '<option value="1"' . (($option==1)? ' selected':'') . '>Domina el contenido</option>';
                $html .= '<option value="2"' . (($option==2)? ' selected':'') . '>No domina el contenido</option>';
                $html .= '<option value="3"' . (($option==3)? ' selected':'') . '>Necesita mejorar</option>';
                $html .= '<option value="4"' . (($option==4)? ' selected':'') . '>Algunas Veces</option>';
                $html .= '</select>';
                break;
            case 2:
                $html = '<select class="sq-option" stdyId="' . $stdyId . '" sq="' . $sq . '" pn="' . $periodNumber .
                    '">';
                $html .= '<option value="0"' . (($option==0)? ' selected':'') . '>&nbsp;</option>';
                $html .= '<option value="1"' . (($option==1)? ' selected':'') . '>Bueno</option>';
                $html .= '<option value="2"' . (($option==2)? ' selected':'') . '>MB - Muy bueno</option>';
                $html .= '<option value="3"' . (($option==3)? ' selected':'') . '>Exc - Excelente</option>';
                $html .= '</select>';
                break;
            default:
                break;
        }
        return $html;
    }

    private function getSpecialQualificationValue(
            \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm $form,
        $q, $questionType){

        $result = "";
        //switch($form->getEntryType()){
        switch($questionType){
            case 1:
                switch($q){
                    case '1': $result = "Domina el contenido"; break;
                    case '2': $result = "Habilidad en proceso"; break;
                    case '3': $result = "Necesita refuerzo"; break;
                    case '4': $result = "Algunas Veces"; break;
                    default: $result = "-"; break;
                }
                break;
            case 2:
                switch($q){
                    case '1': $result = "Excellent"; break;
                    case '2': $result = "Very Good"; break;
                    case '3': $result = "In Process"; break;
                    case '4': $result = "Needs Reteaching"; break;
                    case '5': $result = "Good"; break;
                    default: $result = "-"; break;
                }
                break;
            case 3:
                switch($q){
                    case '1': $result = "Domina el contenido"; break;
                    case '2': $result = "Habilidad en proceso"; break;
                    case '3': $result = "Necesita mejorar"; break;
                    case '4': $result = "Algunas veces"; break;
                    default: $result = "-"; break;
                }
                break;
            case 4:
                switch($q){
                    case '1': $result = "Excelente"; break;
                    case '2': $result = "Muy Bueno"; break;
                    case '3': $result = "Bueno"; break;
                    default: $result = "-"; break;
                }
                break;
            case 5:
                switch($q){
                    case '1': $result = "Lo logra"; break;
                    case '2': $result = "Habilidad en proceso"; break;
                    default: $result = "-"; break;
                }
                break;
            case 9:
                switch($q){
                    case '1': $result = "1"; break;
                    case '2': $result = "2"; break;
                    case '3': $result = "3"; break;
                    case '4': $result = "4"; break;
                    case '5': $result = "5"; break;
                    case '6': $result = "6"; break;
                    case '7': $result = "7"; break;
                    case '8': $result = "8"; break;
                    case '9': $result = "9"; break;
                    case '10': $result = "10"; break;
                    case '11': $result = "11"; break;
                    case '12': $result = "12"; break;
                    case '13': $result = "13"; break;
                    case '14': $result = "14"; break;
                    case '15': $result = "15"; break;
                    case '16': $result = "Excelente"; break;
                    case '17': $result = "Muy Bueno"; break;
                    case '18': $result = "Bueno"; break;
                    default: $result = "-"; break;
                }
                break;
            default:
                $result = '-';
                break;
        }
        return $result;
    }

    private function printSpecialFormByStudent(\Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm $form,
                                               $responses, $periodNumber){
        $html = "";
        $html .= '<table class="student-special-form" style="width: 100%">';
        $html .= '<tr class="header"><td colspan="2">' . $form->getName() . '</td></tr>';
        $q = new \Tecnotek\ExpedienteBundle\Entity\SpecialQualification();
        foreach($form->getQuestions() as $q){
            $html .= '<tr>';
            $html .= '<td>' . $q->getMainText();
            $html .= '</td>';
            if(key_exists($q->getId() . "-q-" . $periodNumber, $responses)){
                $html .= '<td>' . $this->getSpecialQualificationValue($form, $responses[$q->getId() . "-q-" . $periodNumber],
                    $q->getType()) . '</td>';
            } else  {
                $html .= '<td> - </td>';
            }

            $html .= '</tr>';
        }
        $i = 0;
        

        if($form->getMustIncludeComments() && key_exists($form->getId() . "-c", $responses)  && ($form->getName() == 'Unit Assessment' || $form->getName() == 'Unit Assessment II' || $form->getName() == 'Unit Assessment III')){
            $html .='</table>';
            $html .= '<table class="student-special-form" style="width: 100%">';
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            if ($responses[$form->getId() . '-c']!= '')
                $html .= '<b>Comments:</b> '. $responses[$form->getId() . '-c'];
            $html .= '</br></br>Signature: _______________</td></tr>';
            $i = 1;
            $html .= '';
        }
        else {
          if($form->getMustIncludeComments() && key_exists($form->getId() . "-c", $responses)){
            $html .='</table>';
            $html .= '<table class="student-special-form" style="width: 100%">';
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            if ($responses[$form->getId() . '-c']!= '')
                $html .= '<b>Comentarios:</b> '. $responses[$form->getId() . '-c'];
            $html .= '</br></br>Firma: _______________</td></tr>';
            $i = 1;
            $html .= '';
          }
        }

        if($form->getMustIncludeComments() && $i == 0 && ($form->getName() == 'Unit Assessment' || $form->getName() == 'Unit Assessment II' || $form->getName() == 'Unit Assessment III')){
            $html .='</table>';
            $html .= '<table class="student-special-form" style="width: 100%">';
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            $html .= '</br></br>Signature: _______________</td></tr>';
            $html .= '';
        }
        else {
          if($form->getMustIncludeComments() && $i == 0){
            $html .='</table>';
            $html .= '<table class="student-special-form" style="width: 100%">';
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            $html .= '</br></br>Firma: _______________</td></tr>';
            $html .= '';
          }
        }
        




        $html .= '';
        $html .='</table>';
        return $html;
    }

    private function printSpecialFormByStudent2(\Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm $form,
                                                $responses){
        $logger = $this->get('logger');
        $html = "";
        $html .= '<table class="student-special-form" style="width: 100%">';
        $html .= '<tr class="header"><td>' . $form->getName() . '</td><td>I</td><td>II</td><td>III</td></tr>';
        $q = new \Tecnotek\ExpedienteBundle\Entity\SpecialQualification();
        foreach($form->getQuestions() as $q){
            $html .= '<tr>';
            $html .= '<td>' . $q->getMainText();
            $html .= '</td>';

            //Primer Periodo
            if(key_exists($q->getId() . "-q-1", $responses)){
                $html .= '<td>' . $this->getSpecialQualificationValue2($form, $responses[$q->getId() . "-q-1"],
                    $q->getType()) . '</td>';
            } else  {
                $html .= '<td> - </td>';
            }
            //Segundo Periodo
            if(key_exists($q->getId() . "-q-2", $responses)){
                $html .= '<td>' . $this->getSpecialQualificationValue2($form, $responses[$q->getId() . "-q-2"],
                    $q->getType()) . '</td>';
            } else  {
                $html .= '<td> - </td>';
            }
            //Tercer Periodo
            if(key_exists($q->getId() . "-q-3", $responses)){
                $html .= '<td>' . $this->getSpecialQualificationValue2($form, $responses[$q->getId() . "-q-3"],
                    $q->getType()) . '</td>';
            } else  {
                $html .= '<td> - </td>';
            }

            $html .= '</tr>';
        }
        //$html .= 'blabla';
        $i = 0;
        if($form->getMustIncludeComments() && key_exists($form->getId() . "-c", $responses)){
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            if ($responses[$form->getId() . '-c']!= '')
                $html .= '<b>Comentarios:</b> '. $responses[$form->getId() . '-c'];
            $html .= '</br></br>Firma: _______________</td></tr>';
            $i = 1;
        }

        if($form->getMustIncludeComments() && $i == 0){
            $html .= '<tr><td colspan="3" style="background-color: rgb(187, 214, 188);">';
            $html .= '</br></br>Firma: _______________</td></tr>';
        }



        $html .= '';
        $html .='</table>';
        return $html;
    }

    private function getSpecialQualificationValue2(
        \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm $form,
        $q, $questionType){

        $result = "";
        //switch($form->getEntryType()){
        switch($questionType){
            case 1:
                switch($q){
                    case '1': $result = "DC"; break;
                    case '2': $result = "HP"; break;
                    case '3': $result = "NR"; break;
                    case '4': $result = "AV"; break;
                    default: $result = "-"; break;
                }
                break;

            case 3:
                switch($q){
                    case '1': $result = "DC"; break;
                    case '2': $result = "HP"; break;
                    case '3': $result = "NM"; break;
                    case '4': $result = "AV"; break;
                    default: $result = "-"; break;
                }
                break;
            case 4:
                switch($q){
                    case '1': $result = "EX"; break;
                    case '2': $result = "MB"; break;
                    case '3': $result = "B"; break;
                    default: $result = "-"; break;
                }
                break;
            case 9:
                switch($q){
                    case '1': $result = "1"; break;
                    case '2': $result = "2"; break;
                    case '3': $result = "3"; break;
                    case '4': $result = "4"; break;
                    case '5': $result = "5"; break;
                    case '6': $result = "6"; break;
                    case '7': $result = "7"; break;
                    case '8': $result = "8"; break;
                    case '9': $result = "9"; break;
                    case '10': $result = "10"; break;
                    case '11': $result = "11"; break;
                    case '12': $result = "12"; break;
                    case '13': $result = "13"; break;
                    case '14': $result = "14"; break;
                    case '15': $result = "15"; break;
                    case '16': $result = "EX"; break;
                    case '17': $result = "MB"; break;
                    case '18': $result = "B"; break;
                    default: $result = "-"; break;
                }
                break;
            default:
                $result = '-';
                break;
        }
        return $result;
    }

    private function getStudentSpecialQualificationHTMLQualifications($periodId, $gradeId, $groupId, $studentId,
                                                                      $studentYear, $director, $institution, $convocatoria){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $period = $em->getRepository("TecnotekExpedienteBundle:Period")
            ->find($periodId);
        $year = $period->getYear(); ///para filtro de año
        $periodNumber = $period->getOrderInYear();

        $responses = array();
        $periodsArray = array();
        $sqr = null;

        //Para el tercer periodo debe cargar los del primer y segundo tambien
        if($periodNumber == 3){
            $loadPeriods = array(1, 2, 3);
            $periodOne = $em->getRepository("TecnotekExpedienteBundle:Period")
                ->findOneBy(array('year' => $period->getYear(), 'orderInYear' => 1));
            $periodTwo = $em->getRepository("TecnotekExpedienteBundle:Period")
                ->findOneBy(array('year' => $period->getYear(), 'orderInYear' => 2));
            $periodsArray[1] = $periodOne;
            $periodsArray[2] = $periodTwo;
        } else {//Si no es tercer periodo solo cargar el periodo normalmente
            $loadPeriods = array($periodNumber);
        }
        foreach ($loadPeriods as $periodN) {
            $stdYearId = 0;
            if($periodN == $periodNumber){
                $stdYearId = $studentYear->getId();
            } else {
                $stdYearRecord = $em->getRepository("TecnotekExpedienteBundle:StudentYear")
                    ->findOneBy(array('student' => $studentYear->getStudent()->getId(),
                                      'period' => $periodsArray[$periodN]->getId()));

                $stdYearId = $stdYearRecord->getId();
            }
            $dql = "SELECT obs FROM TecnotekExpedienteBundle:SpecialQualificationResult obs"
                . " WHERE obs.studentYear = " . $stdYearId . " AND obs.periodNumber = " . $periodN;
            $query = $em->createQuery($dql);
            $results = $query->getResult();
            foreach($results as $r){
                $responses[$r->getSpecialQualification()->getId() . "-q-" . $periodN]
                    = $r->getMainText();
            }
        }

        $dql = "SELECT obs FROM TecnotekExpedienteBundle:SpecialQualificationFormComment obs"
            . " WHERE obs.studentYear = " . $studentYear->getId();
        $query = $em->createQuery($dql);
        $results = $query->getResult();
        $sqr = null;
        foreach($results as $r){
            $responses[$r->getSpecialQualificationsForm()->getId() . "-c"]
                = $r->getMainText();
        }
        //Obtener Formularios del Grade
        //y filtro del año
        $formularios = $em->getRepository("TecnotekExpedienteBundle:SpecialQualificationsForm")
            ->findBy(array('grade' => $gradeId, 'year' => $year), array('sortOrder' => 'ASC'));

        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 30px;">';
        $headersRow .=  '        <th style="width: 50%; text-align: left;"></th>';
        $headersRow .=  '        <th style="width: 50%; text-align: center;"></th>';
        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';

        $html = '<table class="tableQualificationsSpecial" cellSpacing="0" cellPadding="0">' .
            $headersRow;
        $html .= '<tr>';
        $html .= '<td>';
        //Pintar los de la columna 1
        $form = new \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm();
        foreach($formularios as $form){
            if($form->getColumnNumber() == 1){
               switch($periodNumber){
                    case 1: if($form->getshowsOnPeriodOne() == 1 ){
                                $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                            }
                            break;
                    case 2: if($form->getshowsOnPeriodTwo() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                    case 3: if($form->getShowsOnPeriodThree() == 1 ){
                        $html .= $this->printSpecialFormByStudent2($form, $responses);
                    }
                        break;
                }
            }
        }
if($periodNumber == 3){
$html .= '<table class="student-special-form"><tr class="header"><td colspan="2">Simbología</td></tr><tr><td>Domina el contenido</td><td>DC</td></tr><tr><td>No domina el contenido</td><td>NDC</td><tr><td>Habilidad en proceso</td><td>HP</td></tr></tr><tr><td>Necesita mejorar</td><td>NM</td></tr><tr><td>Necesita refuerzo</td><td>NR</td></tr><tr><td>Algunas veces</td><td>AV</td></tr><tr><td>Excelente</td><td>EX</td></tr><tr><td>Muy Buena</td><td>MB</td></tr><tr><td>Buena</td><td>B</td></tr></table>';}
        $html .= '</td>';
        $html .= '<td>';
        //Pintar los de la columna 2
        foreach($formularios as $form){
            if($form->getColumnNumber() == 2){
                switch($periodNumber){
                    case 1: if($form->getshowsOnPeriodOne() == 1 ){
                                $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                            }
                            break;
                    case 2: if($form->getshowsOnPeriodTwo() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                    case 3: if($form->getShowsOnPeriodThree() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                }
            }
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '';
        $html .= "</table>";

        //Second Page
        $html .= '<div class="pageBreak"> </div>';

        $html .= '<table class="tableQualificationsSpecial" cellSpacing="0" cellPadding="0">' .
            $headersRow;
        $html .= '<tr>';
        $html .= '<td>';
        //Pintar los de la columna 1
$html .= '<img width="200" height="100" src="/expediente/web/images/kinderpictittle.png" allign= "center" alt="" class="image-hover">';
        $form = new \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm();
        foreach($formularios as $form){
            if($form->getColumnNumber() == 3){
                switch($periodNumber){
                    case 1: if($form->getshowsOnPeriodOne() == 1 ){
                                $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                            }
                            break;
                    case 2: if($form->getshowsOnPeriodTwo() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                    case 3: if($form->getShowsOnPeriodThree() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                }
            }
        }
        $html .= '</td>';
        $html .= '<td>';
        //Pintar los de la columna 2
$html .= '<img width="250" height="120" src="/expediente/web/images/kinderpictittle2.png" allign= "center" alt="" class="image-hover">';
        foreach($formularios as $form){
            if($form->getColumnNumber() == 4){
                switch($periodNumber){
                    case 1: if($form->getshowsOnPeriodOne() == 1 ){
                                $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                            }
                            break;
                    case 2: if($form->getshowsOnPeriodTwo() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                    case 3: if($form->getShowsOnPeriodThree() == 1 ){
                        $html .= $this->printSpecialFormByStudent($form, $responses, $periodNumber);
                    }
                        break;
                }
            }
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '';
        $html .= "</table>";

        return $html;
    }

    public function getStudentByPeriodHTMLQualifications($periodId, $gradeId, $groupId, $studentId, $studentYear, $director, $institution, $convocatoria){


        $numCols = 5;
        if($convocatoria == 1){
            $numCols = 6;
        }
        if($convocatoria == 2){
            $numCols = 7;
        }

        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 30px;">';
        $headersRow .=  '        <th style="width: 350px; text-align: left;">MATERIAS</th>';
        $headersRow .=  '        <th style="width: 100px; text-align: center;">I TRIM.</th>';
        $headersRow .=  '        <th style="width: 100px; text-align: center;">II TRIM.</th>';
        $headersRow .=  '        <th style="width: 100px; text-align: center;">III TRIM</th>';
        if($convocatoria == 1){
            $headersRow .= '<th style="width: 100px; text-align: center;">CONV I</th>';
        }
        if($convocatoria == 2){
            $headersRow .= '<th style="width: 100px; text-align: center;">CONV I</th>';
            $headersRow .= '<th style="width: 100px; text-align: center;">CONV II</th>';
        }
        $headersRow .=  '        <th style="width: 150px; text-align: center;">PROMEDIO</th>';
        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';

        $this->calculateStudentYearQualification($periodId, $studentId, $studentYear);

        $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $courseRow = '';
        $courseRow .=  '<tr class="rowNotas">';
        $courseRow .= '<td style="text-align: left; font-size:16px;">courseName</td>';
        $courseRow .= '<td style="text-align: center; font-size:16px;">courseRowNota1</td>';
        $courseRow .= '<td style="text-align: center; font-size:16px;">courseRowNota2</td>';
        $courseRow .= '<td style="text-align: center; font-size:16px;">courseRowNota3</td>';
        if($convocatoria == 1){
            $courseRow .= '<td style="text-align: center; font-size:16px;">convo1</td>';
        }
if($convocatoria == 2){
             $courseRow .= '<td style="text-align: center; font-size:16px;">convo1</td>';
             $courseRow .= '<td style="text-align: center; font-size:16px;">convo2</td>';
        }
        $courseRow .= '<td style="text-align: center; font-size:16px;">courseRowNotaProm</td>';
        $courseRow .=  "</tr>";

        $promedioRow = '';
        $promedioRow .=  '<tr class="rowNotas" style="background-color: rgb(189, 176, 176);">';
        $promedioRow .= '<td style="text-align: left; font-size:16px;">Promedio General</td>';
        $promedioRow .= '<td  style="text-align: center; font-size:16px;">promedio1</td>';
        $promedioRow .= '<td style="text-align: center; font-size:16px;">promedio2</td>';
        $promedioRow .= '<td style="text-align: center; font-size:16px;">promedio3</td>';
        if($convocatoria == 1){
            $promedioRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
        }
if($convocatoria == 2){
            $promedioRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
            $promedioRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
        }
        $promedioRow .= '<td style="text-align: center; font-size:16px;">promedioGeneral</td>';
        $promedioRow .=  "</tr>";

        $condicionRow = '';
        $condicionRow .=  '<tr class="rowNotas" style="background-color: rgb(78, 76, 76);">';
        $condicionRow .= '<td style="text-align: left; font-size:16px; color: white;" colspan="'.$numCols.'">Condici&oacute;n: changeCondicion</td>';
        $condicionRow .=  "</tr>";

        //Revisar Ausencias y Calcular Nota de Conducta
        $absenceRow = '';
        $absenceRow .=  '<tr class="rowNotas">';
        $absenceRow .= '<td style="text-align: left; font-size:16px;">absenceTypeName</td>';
        $absenceRow .= '<td style="text-align: center; font-size:16px;">absenceTypeCount1</td>';
        $absenceRow .= '<td style="text-align: center; font-size:16px;">absenceTypeCount2</td>';
        $absenceRow .= '<td style="text-align: center; font-size:16px;">absenceTypeCount3</td>';
        if($convocatoria == 1){
            $absenceRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
        }
if($convocatoria == 2){
            $absenceRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
            $absenceRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
        } 

        $absenceRow .= '<td style="text-align: center; font-size:16px;">&nbsp;</td>';
        $absenceRow .=  "</tr>";

        $notaMin = $em->getRepository("TecnotekExpedienteBundle:Grade")->findOneBy(array('id' => $gradeId));

        //Get Period entity
        $period = $em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId);

        //Get Periods of the Year order by the field orderInYear
        $dql = "SELECT p "
            . " FROM TecnotekExpedienteBundle:Period p "
            . " WHERE p.year = " . $period->getYear()
            . " AND p.orderInYear <= " . $period->getOrderInYear()
            . " ORDER BY p.orderInYear";

        $query = $em->createQuery($dql);
        $periods = $query->getResult();

        $total = 0;
        $counter = 0;
        $honor = true;

        $stdQualifications = array();
        $absencesArray = array();
        $notasParteFinal = "";
        $numberOfLossCourses = 0;

        foreach( $periods as $period )
        {
            //Get Student Year of the Period related to the student
            $logger->err("Getting stdYear with: " . $studentYear->getStudent()->getId() . " AND " . $period->getId());
            $stdYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->
                findOneBy(array('student' => $studentYear->getStudent()->getId(), 'period' => $period->getId()));

            if(isset($stdYear)){
                /*****************/
                $dql = "SELECT cc "
                    . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc "
                    . " WHERE cc.grade = " . $gradeId . " AND cc.course = c"
                    . " AND cc.period = " . $period->getId() . " "
                    . " ORDER BY c.name";

                $query = $em->createQuery($dql);
                $courses = $query->getResult();

                foreach( $courses as $courseClass )
                {
                    if (!array_key_exists("" . $courseClass->getCourse()->getName(), $stdQualifications)) {
                        $courseQ = array();
                        $courseQ["courseClass"] = $courseClass;
                        $stdQualifications["" . $courseClass->getCourse()->getName()] = $courseQ;
                    }
                }

                $qualifications = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")
                    ->findBy(array('studentYear' => $stdYear->getId()));

                foreach( $qualifications as $q )
                {
                    if (array_key_exists("" . $q->getCourseClass()->getCourse()->getName(), $stdQualifications)) {
                        $courseQ = $stdQualifications["" . $q->getCourseClass()->getCourse()->getName()];
                        $courseQ["nota" . $period->getOrderInYear()] = $q;
                        $stdQualifications["" . $q->getCourseClass()->getCourse()->getName()] = $courseQ;
                    } else {
                        $courseQ = array();
                        $courseQ["courseClass"] = $q->getCourseClass();
                        $courseQ["nota" . $period->getOrderInYear()] = $q;
                        $stdQualifications["" . $q->getCourseClass()->getCourse()->getName()] = $courseQ;
                    }
                }

                //Get Absences Detail
                if($institution->getId() == '3'){
                    $sql = "select at.name, count(a.id) as 'total', (count(a.id) * atp.points) as 'puntos'"
                        . " from tek_absence_types at"
                        . " join tek_absence_types_points atp on at.id = atp.absence_type_id and atp.institution_id = " . $institution->getId()
                        . " left join tek_absences a on a.type_id = at.id and a.justify = 0 and a.studentYear_id = " . $stdYear->getId()
                        . " group by at.id;";

                    /*$sql = "select at.name, count(a.id) as 'total', sum(atp.points) as 'puntos'"
                . " from tek_absences a "
                . " join tek_absence_types at on at.id = a.type_id"
                . " join tek_absence_types_points atp on at.id = atp.absence_type_id and atp.institution_id = " . $institution->getId()
                . " where a.studentYear_id =  " . $stdYear->getId()." AND a.justify = 0"
                . " group by a.type_id;";*/

                }
                if($institution->getId() == '2'){
                    $sql = "select at.name, count(a.id) as 'total', sum(atp.points) as 'puntos'"
                        . " from tek_absences a "
                        . " join tek_absence_types at on at.id = a.type_id"
                        . " join tek_absence_types_points atp on at.id = atp.absence_type_id and atp.institution_id = " . $institution->getId()
                        . " where a.studentYear_id =  " . $stdYear->getId()." AND a.justify = 0"
                        . " group by a.type_id;";
                }

                $htmlAbsence = "";
                $absences = $em->getConnection()->executeQuery($sql);
                $conducta = 100;
                foreach($absences as $absenceType){
                    if($absenceType["total"] > 0) {
                        $absencePoints = $absenceType["total"] . "(" . number_format($absenceType["puntos"], 1, '.', '') . "pts)";
                    } else {
                        $absencePoints = "0";
                    }

                    if (!array_key_exists($absenceType["name"], $absencesArray)) {
                        $absenceDetail = array();
                        $absenceDetail["absence" . $period->getOrderInYear()] = $absencePoints;
                        $absenceDetail["puntos" . $period->getOrderInYear()] = $absenceType["puntos"];
                        $absencesArray[$absenceType["name"]] = $absenceDetail;
                    } else {
                        $absenceDetail = $absencesArray[$absenceType["name"]];
                        $absenceDetail["absence" . $period->getOrderInYear()] = $absencePoints;
                        $absenceDetail["puntos" . $period->getOrderInYear()] = $absenceType["puntos"];
                        $absencesArray[$absenceType["name"]] = $absenceDetail;
                    }
                }

                $sql = 'SELECT COUNT(id) as "total",SUM(pointsPenalty) as "puntos" FROM tek_student_penalties where student_year_id = ' . $stdYear->getId();
                $puntosPorSancion = $em->getConnection()->executeQuery($sql);
                foreach($puntosPorSancion as $pa){
                    //if($institution->getId() == '3'){
                    //$absenceDetail = array();
                    //$absencePoints = $pa["total"] . "(" . number_format($pa["puntos"], 1, '.', '') . "pts)";
                    //$absenceDetail["absence" . $period->getOrderInYear()] = $absencePoints;
                    //$absenceDetail["puntos" . $period->getOrderInYear()] = $pa["puntos"];
                    //$absencesArray["Puntos por Observaciones"] = $absenceDetail;
                    //}
                    if (!array_key_exists("Puntos por Observaciones", $absencesArray)) {
                        $absenceDetail = array();
                        $absencePoints = $pa["total"] . "(" . number_format($pa["puntos"], 1, '.', '') . "pts)";
                        $absenceDetail["absence" . $period->getOrderInYear()] = $absencePoints;
                        $absenceDetail["puntos" . $period->getOrderInYear()] = $pa["puntos"];
                        $absencesArray["Puntos por Observaciones"] = $absenceDetail;
                    } else {
                        $absenceDetail = $absencesArray["Puntos por Observaciones"];
                        $absencePoints = $pa["total"] . "(" . number_format($pa["puntos"], 1, '.', '') . "pts)";
                        $absenceDetail["absence" . $period->getOrderInYear()] = $absencePoints;
                        $absenceDetail["puntos" . $period->getOrderInYear()] = $pa["puntos"];
                        $absencesArray["Puntos por Observaciones"] = $absenceDetail;
                    }
                }
            } else {
                //No hay notas para el periodo buscado
            }
        }

        $separator[0] = '/1./';
        $separator[1] = '/2./';

        $totales = array();
        $totales[1] = 0;
        $totales[2] = 0;
        $totales[3] = 0;

        $counters = array();
        $counters[1] = 0;
        $counters[2] = 0;
        $counters[3] = 0;

        $honor = true;
        foreach ($stdQualifications as $csq => $courseStdQ) {

            $courseClass = $courseStdQ["courseClass"];

            $courseName = preg_replace($separator,"",$courseClass->getCourse()->getName());

            $row = str_replace("courseName", $courseName, $courseRow);
            $row = str_replace("courseId", $courseClass->getId(), $row);

            $typeC = $courseClass->getCourse()->getType();
            $totalForAverage = 0;
            $counterForAverage = 0;
            $tercerTrim = 0;


            /*switch($periodId){
                case 1: $periodIdb=1;
                        break;
                case 2: $periodIdb=2;
                        break;
                case 3: $periodIdb=3;
                        break;
                case 4: $periodIdb=1;
                        break;
                case 5: $periodIdb=2;
                        break;
                case 6: $periodIdb=3;
                        break;
                case 7: $periodIdb=1;
                    break;
                case 8: $periodIdb=2;
                    break;
                case 9: $periodIdb=3;
                    break;
                case 10: $periodIdb=1;
                    break;
                case 11: $periodIdb=2;
                    break;
                case 12: $periodIdb=3;
                    break;
            }*/

            $periodOption = $em->getRepository("TecnotekExpedienteBundle:Period")
                ->find($periodId);
            //$year = $period->getYear(); ///para filtro de año
            $periodIdb = $period->getOrderInYear();

            for($i = 1; $i < 4; $i++){
                if (array_key_exists("nota" . $i, $courseStdQ)) {
                    $notaFinal = $courseStdQ["nota" . $i];
                    if( $typeC==1){
                        $totalForAverage += $notaFinal->getQualification();
                        $counterForAverage++;

                        if($notaFinal->getQualification() != 0){
                            if($notaFinal->getQualification() < $notaMin->getNotaMin()){
                                $row = str_replace("courseRowNota" . $i, "* " . $notaFinal->getQualification(), $row);
                                if($i==3){
                                    $tercerTrim = 1;
                                }
                            } else {
                                $row = str_replace("courseRowNota" . $i, $notaFinal->getQualification(), $row);
                            }
                            $counters[$i] += 1;
                            $totales[$i] += $notaFinal->getQualification();
//desde aca
/*if($notaFinal->getQualification()<'50'){
	$notaFinalAplicada = 50;
}else{
         $notaFinalAplicada = $notaFinal->getQualification(); 
}
                                $row = str_replace("courseRowNota" . $i, "* " . $notaFinalAplicada , $row);
                                if($i==3){
                                    $tercerTrim = 1;
                                }
                                $counters[$i] += 1;
                                $totales[$i] += $notaFinalAplicada;    



                            } else {
                                $row = str_replace("courseRowNota" . $i, $notaFinal->getQualification(), $row);
                                $counters[$i] += 1;
                                $totales[$i] += $notaFinal->getQualification();    
                            } */  /// reemplazado hasta aca

                        }else{
                            $row = str_replace("courseRowNota" . $i, $notaFinal->getQualification(), $row);
                        }
                        if($notaFinal->getQualification()<'90' && $i == $periodIdb && $notaFinal->getQualification()!='0'){
                            $honor = false;
                        }
                    }
                    else{
                        $valorNota =  $notaFinal->getQualification();
                        if($valorNota == 99)
                            $valorNota = "Exc";
                        if($valorNota == 74)
                            $valorNota = "V.Good";
                        if($valorNota == 50)
                            $valorNota = "Good";
                        if($valorNota == 25)
                            $valorNota = "N.I.";
                        if($valorNota == 4)
                            $valorNota = "Exc";
                        if($valorNota == 3)
                            $valorNota = "V.Good";
                        if($valorNota == 2)
                            $valorNota = "Good";
                        if($valorNota == 1)
                            $valorNota = "N.I.";
                        $row = str_replace("courseRowNota" . $i, "" . $valorNota, $row);
                    }
                } else {
                    $row = str_replace("courseRowNota" . $i, "-----", $row);
                }
            }
            if($counterForAverage != 0){
                if($convocatoria != 0){
                    //$logger->err("-----> Course: " . $courseClass->getCourse()->getId() . ", StudentYear: " . $stdYear->getId() . ", number: " . '1');

                    $notaCon = $em->getRepository("TecnotekExpedienteBundle:StudentExtraTest")->findOneBy(array('studentYear' => $stdYear->getId(), 'course' => $courseClass->getCourse()->getId(), 'number' => 1));
                    //$notaCon = null; //quitar
                    if($notaCon != null){

                        $notaConP = $notaCon->getQualification();
                        // remplazar conv1 por nota
                        $row = str_replace("convo1",$notaConP, $row);
                        if(number_format($notaConP, 0, '.', '')< $notaMin->getNotaMin() && $convocatoria != 2){ //sino lo pasa mantener promedio original
                            $row = str_replace("courseRowNotaProm","*".number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }else{ // si lo paso nota del periodo es la minima
                            $row = str_replace("courseRowNotaProm",$notaMin->getNotaMin(), $row);
                            //totales[3] =  /// necesario para actualizar el promedio general
                        }
if($convocatoria == 1){
if($notaConP < $notaMin->getNotaMin() && $convocatoria != 2){//Si se pierde curso igual suma...
                            $numberOfLossCourses = $numberOfLossCourses + 1;
                            
                        }
}
                    }else{ //hace lo de siempre si no hizo examen en la materia
                        // remplazar conv1 por nota
                        $row = str_replace("convo1","", $row);
                        $notaTemp = number_format( ($totalForAverage/$counterForAverage), 0, '.', '');
                        if($notaTemp < $notaMin->getNotaMin()){
                            $row = str_replace("courseRowNotaProm","*".number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }else{
                            $row = str_replace("courseRowNotaProm",number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }
                        if($totalForAverage != 0 && $totalForAverage/$counterForAverage < $notaMin->getNotaMin()){//Si se pierde curso igual suma...
                            $numberOfLossCourses = $numberOfLossCourses + 1;
                            //$logger->err("--> se perdio un curso: " . $courseName );
                        }
                    }


if($convocatoria == 2){


$notaCon = $em->getRepository("TecnotekExpedienteBundle:StudentExtraTest")->findOneBy(array('studentYear' => $stdYear->getId(), 'course' => $courseClass->getCourse()->getId(), 'number' => 2));
                    //$notaCon = null; //quitar
                    if($notaCon != null){

                        $notaConP = $notaCon->getQualification();
                        // remplazar conv2 por nota
                        $row = str_replace("convo2",$notaConP, $row);
                        if(number_format($notaConP, 0, '.', '')< $notaMin->getNotaMin()){ //sino lo pasa mantener promedio original
                            $row = str_replace("courseRowNotaProm","*".number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }else{ // si lo paso nota del periodo es la minima
                            $row = str_replace("courseRowNotaProm",$notaMin->getNotaMin(), $row);
                            //totales[3] =  /// necesario para actualizar el promedio general
                        }

if($notaConP < $notaMin->getNotaMin()){//Si se pierde curso igual suma...
                            $numberOfLossCourses = $numberOfLossCourses + 5;   /// +5 para reprobar no hay mas convocatorias
                            
                        }

                    }else{ //hace lo de siempre si no hizo examen en la materia
                        // remplazar conv2 por nota
                        $row = str_replace("convo2","", $row);
                        $notaTemp = number_format( ($totalForAverage/$counterForAverage), 0, '.', '');
                        if($notaTemp < $notaMin->getNotaMin()){
                            $row = str_replace("courseRowNotaProm","*".number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }else{
                            $row = str_replace("courseRowNotaProm",number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                        }
                        if($totalForAverage != 0 && $totalForAverage/$counterForAverage < $notaMin->getNotaMin()){//Si se pierde curso igual suma...
                            //$numberOfLossCourses = $numberOfLossCourses + 1;
                            //$logger->err("--> se perdio un curso: " . $courseName );
                        }
                    }

 }

                }
                else{ //lo que hacia solo agrega asteristico si es materia aplazada
                    $notaTemp = number_format( ($totalForAverage/$counterForAverage), 0, '.', '');
                    if($notaTemp < $notaMin->getNotaMin()){
                        $row = str_replace("courseRowNotaProm","*".number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                    }else{
                        $row = str_replace("courseRowNotaProm",number_format( ($totalForAverage/$counterForAverage), 2, '.', ''), $row);
                    }
                    if($totalForAverage != 0 && number_format( ($totalForAverage/$counterForAverage), 0, '.', '') < $notaMin->getNotaMin()){//Si se pierde curso igual suma...
                        if(number_format( ($totalForAverage/$counterForAverage), 0, '.', '') != '0.00'){
                            $numberOfLossCourses = $numberOfLossCourses + 1;
                        }
                        //$logger->err("--> se perdio un curso: " . $courseName );
                    }else{
                        if($tercerTrim == 1){
                            $numberOfLossCourses = $numberOfLossCourses + 1;
                            $tercerTrim = 0;
                        }

                    }
                }
            } else {
                $row = str_replace("courseRowNotaProm", "-----", $row);
                if($convocatoria != 0){
                    $row = str_replace("convo1","", $row);
                }
                if($convocatoria != 0){
                    $row = str_replace("convo2","", $row);
                }
            }

            $row = str_replace("courseRowNotaProm", "-----", $row);
            $html .=  $row;
        }

        $totalesConductaMod = array();
        $totalesConductaMod[1] = false;
        $totalesConductaMod[2] = false;
        $totalesConductaMod[3] = false;

        $totalesConducta = array();
        $totalesConducta[1] = 100;
        $totalesConducta[2] = 100;
        $totalesConducta[3] = 100;

        $absencesHtml = "";
        foreach ($absencesArray as $i => $absenceDetail) {
            $row = str_replace("absenceTypeName", $i, $absenceRow);
            for($i = 1; $i < 4; $i++){
                if (array_key_exists("absence" . $i, $absenceDetail)) {
                    $row = str_replace("absenceTypeCount" . $i, $absenceDetail["absence" . $i], $row);
                    //$logger->err($i . " - La nota es de: " . $totalesConducta[$i] . " y debe rebajar " .  floatval($absenceDetail["puntos" . $i]));
                    $totalesConducta[$i] = $totalesConducta[$i] - floatval($absenceDetail["puntos" . $i]);
                    //$logger->err($i . " - La nota es ahora de: " . $totalesConducta[$i]);
                    $totalesConductaMod[$i] = true;
                } else {
                    $row = str_replace("absenceTypeCount" . $i, "-----", $row);
                }
            }
            $absencesHtml .=  $row;
        }

        $row = str_replace("courseName", "CONDUCTA", $courseRow);
        $row = str_replace("courseId", 0, $row);
        $totalConducta = 0;
        for($i = 1; $i <= $period->getOrderInYear(); $i++){
            $conducta = $totalesConducta[$i];
            if($conducta < 0) {
                $conducta = 0;
            }
            $totalConducta += $conducta;
            if($i == $period->getOrderInYear()){
                $studentYear->setConducta($conducta);
            }
            $row = str_replace("courseRowNota" . $i, $conducta, $row);
        }
        if($convocatoria != 0){
            $row = str_replace("convo1", "", $row);
        }
if($convocatoria != 0){
            $row = str_replace("convo2", "", $row);
        }

        if(($totalConducta/$period->getOrderInYear() < $notaMin->getNotaMin()&&($convocatoria != 1)&&($convocatoria != 2))){//Si se pierde conducta igual suma...
            $numberOfLossCourses = $numberOfLossCourses + 1;
            //$logger->err("--> se perdio un curso: " . "Conducta" );
        }
        $row = str_replace("courseRowNotaProm", number_format($totalConducta/$period->getOrderInYear(), 2, '.', ''), $row);
        $row = str_replace("courseRowNota1", "-----", $row);
        $row = str_replace("courseRowNota2", "-----", $row);
        $row = str_replace("courseRowNota3", "-----", $row);

        $promedioGeneral = 0;
        $counter = 0;
        for($i = 1; $i < 4; $i++){

            $promedioPeriodo = 0;
            if($counters[$i] == 0){
                $promedioRow = str_replace("promedio" . $i, "-----", $promedioRow);
            } else {
                if($totalesConductaMod[$i]){
                    $totales[$i] += $totalesConducta[$i];
                    $counters[$i]++;
                }
                $promedioPeriodo = $totales[$i] / $counters[$i];
                $promedioRow = str_replace("promedio" . $i, number_format($promedioPeriodo, 2, '.', ''), $promedioRow);
                if($i == $period->getOrderInYear()){
                    if($promedioPeriodo >= 90){
                        //$honor = true;
                    }
                    $studentYear->setPeriodAverageScore($promedioPeriodo);
                }
                 $promedioGeneral += $promedioPeriodo;
                 $counter++;
            }
        }


        if($counter == 0){
            $promedioRow = str_replace("promedioGeneral", "-----", $promedioRow);
        }else {

            if($convocatoria != 0){
                $promedioRow = str_replace("promedioGeneral", "", $promedioRow);
            }else{
                $promedioRow = str_replace("promedioGeneral", number_format($promedioGeneral/$counter, 2, '.', ''), $promedioRow);
            }
        }

        $html .= $row . $promedioRow . $absencesHtml;

        if($period->getOrderInYear() == 3){
if(($conducta< $notaMin->getNotaMin())&&($convocatoria != 1)&&($convocatoria != 2)){         //agregado caso en que perdio conducta 3er trim   
$condicionRow = str_replace("changeCondicion", "APLAZADO", $condicionRow);
            } else 
            if($numberOfLossCourses == 0){//Aprobado    
                $condicionRow = str_replace("changeCondicion", "APROBADO", $condicionRow);
            } else if ($numberOfLossCourses > 3){//Reprobado
                $condicionRow = str_replace("changeCondicion", "REPROBADO", $condicionRow);
            } else {//Aplazado
                $condicionRow = str_replace("changeCondicion", "APLAZADO", $condicionRow);
            }

            $html .= $condicionRow;
        }


        $html .= "</table>";


        if($conducta<90){
            $honor = false;
        }

        if($honor){
            $studentYear->setPeriodHonor(1);
            $html .= '<div class="notaHonor">CUADRO DE HONOR: ALUMNO DE EXCELENCIA ACADEMICA </div>';
        } else {
            $studentYear->setPeriodHonor(0);
        }

        $em->persist($studentYear);
        $em->flush();

        $sql = "SELECT obs FROM TecnotekExpedienteBundle:Observation obs"
            . " WHERE obs.studentYear = " .  $studentYear->getId();
        //. " AND obs.courseClass = ".$periodId;
        $query = $em->createQuery($sql);
        $observations = $query->getResult();
        $counter2=0;
        if($institution->getId() == '2'){
            foreach($observations as $observation){
                $row =  $observation->getDetail();
                if($counter2 == 0){
                    $html .= '<div style="color: #000; font-size: 16px;">';
                    $html .= 'Observaciones:</br>';
                }
                $html .= '-'. $row  . '</br>';

                $counter2++;
            }
            if($counter2 != 0){
                $html .= '</div>';
            }
        }

        $dia=date("l");

        if ($dia=="Monday") $dia="Lunes";
        if ($dia=="Tuesday") $dia="Martes";
        if ($dia=="Wednesday") $dia="Miércoles";
        if ($dia=="Thursday") $dia="Jueves";
        if ($dia=="Friday") $dia="Viernes";
        if ($dia=="Saturday") $dia="Sabado";
        if ($dia=="Sunday") $dia="Domingo";

        $mes=date("F");

        if ($mes=="January") $mes="Enero";
        if ($mes=="February") $mes="Febrero";
        if ($mes=="March") $mes="Marzo";
        if ($mes=="April") $mes="Abril";
        if ($mes=="May") $mes="Mayo";
        if ($mes=="June") $mes="Junio";
        if ($mes=="July") $mes="Julio";
        if ($mes=="August") $mes="Agosto";
        if ($mes=="September") $mes="Setiembre";
        if ($mes=="October") $mes="Octubre";
        if ($mes=="November") $mes="Noviembre";
        if ($mes=="December") $mes="Diciembre";

        $ano=date("Y");
        $dia2=date("d");

        $html .= '</br></br>';
        $html .= '<div style="color: #000; font-size: 12px;">';
        $html .= '<div style="margin-top: 25px; margin-bottom: 25px;">Desamparados, '. $dia.' '. $dia2 .' de '. $mes .' del '. $ano . '</br></br></div>';

        $html .= '<div class="left" style="width: 250px; text-align: center;"><div style="line-height: 25px;">______________________________</div><div>Profesor Gu&iacute;a</div></div>';
        $html .= '<div class="left" style="width: 250px; text-align: center; margin-left: 300px;"><div style="line-height: 25px;">______________________________</div><div>' . $director . '</div><div>Director</div></div>';
        $html .= '<div class="clear"></div>';

        $html .= '<div style="margin-top: 15px;"><hr></div>';
        $html .= '<div class="left" style="width: 100%; text-align: center;"><div style="line-height: 25px;">Autorizado y Reconocido por el MEP, Acuerdo C.S.E. N. 042-92</div>';
        $html .= '<div style="line-height: 25px;">Afiliado a ANADEC</div><div style="line-height: 25px;">Tel&eacute;fono: ' .
            $this->container->getParameter('corpo_phone') . '&nbsp;&nbsp;&nbsp;Fax: ' .
            $this->container->getParameter('corpo_fax') . '&nbsp;&nbsp;&nbsp;Email: ' .
            $this->container->getParameter('corpo_email') . '</div></div>';

        $html .= '</div>';


        $html .= '';
        return $html;
    }

    public function calculateStudentYearQualification($periodId, $studentYearId, $studentYear){
        $em = $this->getDoctrine()->getEntityManager();

        $logger = $this->get('logger');

        //Obtener todas las notas obtenidas del estudiante en el periodo
        $allQualifications = $em->getRepository("TecnotekExpedienteBundle:StudentQualification")->findBy(array('studentYear' => $studentYearId));

        $notasCursos = array();

        foreach($allQualifications as $qua){

            $subEntry = $qua->getSubCourseEntry();
            $n = $qua->getQualification();
            $m = $subEntry->getMaxValue();
            $p = $subEntry->getPercentage();

            //Calcular porcentage ganado en la entrada
            $pg = $p * $n / $m;

            //Hack para cuando no se definen los porcentajes adecuados...
            $courseEntry = $subEntry->getParent();

            if($subEntry->getParent()->getPercentage() == $subEntry->getPercentage()){
                $childrens = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->findBy(array('parent' => $courseEntry->getId(), 'group' => $studentYear->getGroup()->getId()));
                $size = sizeof($childrens);
                if( $size > 0){
                    $pg = $pg / $size;
                }
            }

            $pg = round( $pg, 2, PHP_ROUND_HALF_UP);

            //Revisar si ya existe la entrada del curso (CourseEntry)
            if( isset($notasCursos[$subEntry->getParent()->getCourseClass()->getId()]) ){
                $pg = $notasCursos[$subEntry->getParent()->getCourseClass()->getId()] + $pg;
            }
            $notasCursos[$subEntry->getParent()->getCourseClass()->getId()] = $pg;
        }

        $con = $em->getConnection();
        $con->beginTransaction();

        $group = $studentYear->getGroup();
        $grade = $group->getGrade();
        $gradeId = $grade->getId();
        $notaMin = $em->getRepository("TecnotekExpedienteBundle:Grade")->findOneBy(array('id' => $gradeId));

        foreach ($notasCursos as $i => $value) {
            //Guardar registro de nota final


            $valuetemp = $value;
            $stexpoints = $em->getRepository("TecnotekExpedienteBundle:StudentExtraPoints")->findBy(array('studentYear' => $studentYearId));

            foreach($stexpoints as $ex){
                //if ( isset($stexpoints) ){//si esta en la lista
                if(round($value, 0, PHP_ROUND_HALF_UP) >= $notaMin->getNotaMin()){

                    $extraPoints = $ex->getPoints();
                    $course = $ex->getCourse();
                    if($course == null){
                        $valuetemp = $valuetemp + $extraPoints;
                    }else{
                        $course_of_cc = $em->getRepository("TecnotekExpedienteBundle:CourseClass")->findOneBy(array('id' => $i));
                        $cour = $course_of_cc->getCourse();
                        if($course == $cour){
                            $valuetemp = $valuetemp + $extraPoints;
                        }
                    }

                    if ($valuetemp > 100){
                        $valuetemp = 100;
                    }

                    
                }

  
            }

                    if ($valuetemp < 0){
                        $valuetemp = 0;
                    }

                    if($studentYear->getGroup()->getInstitution()->getId() == 2){
                        if ($valuetemp < 50 && $valuetemp != 0 && $valuetemp != 1 && $valuetemp != 1 && $valuetemp != 3 && $valuetemp != 4){
                            $valuetemp = 50;
                        }
                    }

            $sql = 'INSERT INTO tek_student_year_course_qualifications (course_class_id,student_year_id, qualification) VALUES (' . $i . ',' . $studentYearId . ', ' . $value . ')'.
                ' ON DUPLICATE KEY UPDATE qualification = ' . $valuetemp . ';';

            $con->executeUpdate($sql);
        }
        $con->commit();
    }

    public function loadGroupQualificationssssAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                /*$periodId = $request->get('periodId');
                $groupId = $request->get('groupId');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];
                $courseId = $request->get('courseId');*/

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

    public function groupQualificationsByRubroAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/groupQualificationsByRubro.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function loadGroupQualificationsByRubroAction(){
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

                $code = $request->get('code');

                $translator = $this->get("translator");

                if( isset($code) && isset($groupId) && isset($periodId)) {

                    $html = $this->getGroupByRubroHTMLQualifications($periodId, $gradeId, $groupId, $code);

                    return new Response(json_encode(array('error' => false, 'html' => $html)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getGroupByRubroHTMLQualifications($periodId, $gradeId, $groupId, $code){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $dql = "SELECT ce "
            . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc, TecnotekExpedienteBundle:CourseEntry ce "
            . " WHERE cc.period = " . $periodId . " AND cc.grade = " . $gradeId . " AND cc.course = c AND ce.courseClass = cc"
            . " AND ce.code = '$code'"
            . " ORDER BY c.name";

        $query = $em->createQuery($dql);

        $courseEntries = $query->getResult();

        if(sizeof($courseEntries) == 0){
            return "";
        } else {
            $headersRow =  '<thead>';
            $headersRow .=  '    <tr style="height: 175px;">';
            $headersRow .=  '        <th colspan=2 style="text-align: center;">NOMBREENTRY</th>';

            $porcRow = '<tr style="background-color: black; background-color: rgb(201, 194, 194); font-weight: bold; font-size: 14px;">';
            $porcRow .=  '        <td style="width: 75px; text-align: center;">Carne</td>';
            $porcRow .=  '        <td style="width: 250px; text-align: center;">Estudiante</td>';

            $studentRow = '';

            $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
                . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
                . " ORDER BY std.lastname, std.firstname";
            $query = $em->createQuery($dql);
            $students = $query->getResult();
            $entryName  = "";
            foreach( $courseEntries as $courseEntry )
            {
                $entryName = $courseEntry->getName();
                $headersRow .=  '<th colspan=2 style="vertical-align: bottom; padding: 15px 30px;"><div class="verticalText">' .
                    $courseEntry->getCourseClass()->getCourse()->getName() . ' ' . $courseEntry->getPercentage() . '%</div></th>';
                $studentRow .= '<td>Nota_' . $courseEntry->getId() . '_</td>' . '<td style=" background-color: rgb(234, 241, 221);">Notap_' . $courseEntry->getId() . '_</td>';
                $porcRow .= '<td>Nota</td><td style=" background-color: rgb(234, 241, 221);">% Gan</td>';

            }

            $headersRow .=  '    </tr>';
            $headersRow .=  '</thead>';
            $headersRow = str_replace("NOMBREENTRY", $entryName, $headersRow);

            $porcRow .= "</tr>";
            $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow . $porcRow;

            $studentRowIndex = 0;

            foreach($students as $stdy){
                $this->calculateStudentYearQualification($periodId, $stdy->getId(), $stdy);

                $html .=  '<tr class="rowNotas">';
                $studentRowIndex++;
                $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
                $html .=  '<td>' . $stdy->getStudent() . '</td>';

                $row = $studentRow;
                /***** Obtener Notas del Estudiante Inicio *****/
                foreach( $courseEntries as $courseEntry )
                {
                    /*$notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")->findOneBy(
                        array('courseClass' => $courseEntry->getCourseClass()->getId(), 'studentYear' => $stdy->getId()));*/

                    $dql = "SELECT stdQ FROM TecnotekExpedienteBundle:StudentQualification stdQ, TecnotekExpedienteBundle:SubCourseEntry subEntry "
                        . " WHERE stdQ.studentYear = " . $stdy->getId() . " AND subEntry.parent = " . $courseEntry->getId()
                        . " AND stdQ.subCourseEntry = subEntry"
                        . " ";
                    // . " ORDER BY std.lastname, std.firstname";
                    $query = $em->createQuery($dql);
                    $qualifications = $query->getResult();
                    $notasCursos = array();
                    foreach( $qualifications as $q )
                    {
                        $subEntry = $q->getSubCourseEntry();
                        $n = $q->getQualification();
                        $m = $subEntry->getMaxValue();
                        $p = $subEntry->getPercentage();

                        //Calcular porcentage ganado en la entrada
                        $pg = $p * $n / $m;

                        //Hack para cuando no se definen los porcentajes adecuados...
                        //$courseEntry = $subEntry->getParent();

                        if($subEntry->getParent()->getPercentage() == $subEntry->getPercentage()){
                            $childrens = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->findBy(array('parent' => $courseEntry->getId(), 'group' => $stdy->getGroup()->getId()));
                            $size = sizeof($childrens);
                            if( $size > 0){
                                $pg = $pg / $size;
                            }
                        }

                        $pg = round( $pg, 2, PHP_ROUND_HALF_UP);

                        //Revisar si ya existe la entrada del curso (CourseEntry)
                        if( isset($notasCursos["p" . $courseEntry->getCourseClass()->getId()]) ){
                            $pg = $notasCursos["p" . $courseEntry->getCourseClass()->getId()] + $pg;
                        }
                        if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                            $n = $notasCursos["n" . $courseEntry->getCourseClass()->getId()] + $n;
                        }
                        $notasCursos["p" . $courseEntry->getCourseClass()->getId()] = $pg;
                        $notasCursos["n" . $courseEntry->getCourseClass()->getId()] = $n;
                    }

                    if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                        $row = str_replace("Nota_" . $courseEntry->getId() . "_", $notasCursos["n" . $courseEntry->getCourseClass()->getId()], $row);
                        $row = str_replace("Notap_" . $courseEntry->getId() . "_", $notasCursos["p" . $courseEntry->getCourseClass()->getId()], $row);
                    } else {
                        $row = str_replace("Nota_" . $courseEntry->getId() . "_", "-", $row);
                        $row = str_replace("Notap_" . $courseEntry->getId() . "_", "-", $row);
                    }
                }
                /***** Obtener Notas del Estudiante Final *****/
                $html .=  $row . "</tr>";
            }

            $html .= "</table>";

            return $html;
        }
    }

    public function studentQualificationsAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/studentQualificationsDetail.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function loadStudentQualificationsAction(){
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

                $referenceId = $request->get('referenceId');

                $translator = $this->get("translator");

                if( isset($referenceId) && isset($groupId) && isset($periodId)) {
                    if($referenceId == 0){
                        $html = $this->getGroupHTMLQualifications($periodId, $gradeId, $groupId);
                    } else {
                        $html = $this->getStudentQualificationsDetailHTMLQualifications($periodId, $gradeId, $groupId, $referenceId);
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadStudentPenaltiesAction(){
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

                $referenceId = $request->get('referenceId');

                $translator = $this->get("translator");

                if( isset($referenceId) && isset($groupId) && isset($periodId)) {
                    if($referenceId == 0){
                        $html = $this->getGroupHTMLPenalties($periodId, $gradeId, $groupId);
                    } else {
                        $html = $this->getStudentHTMLPenalties($periodId, $gradeId, $groupId, $referenceId);
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getStudentQualificationsDetailHTMLQualifications($periodId, $gradeId, $groupId, $studentId){

        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($studentId);

        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc "
            . " WHERE cc.period = " . $periodId . " AND c.type = 1 AND cc.grade = " . $gradeId . " AND cc.course = c"
            . " ORDER BY c.name";

        $query = $em->createQuery($dql);
        $courses = $query->getResult();



        $courseRow = '';

        $this->calculateStudentYearQualification($periodId, $studentId, $studentYear);

        //$html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $courseRow .=  '<tr class="rowNotas">';
        $courseRow .= '<td>courseName</td>';
        $courseRow .= '<td><div id="course_courseId_nota">-</div></td>';
        $courseRow .=  "</tr>";


        $html = '<table class="courseTable">';
        foreach( $courses as $course )
        {
            //$headersRow =  '<thead>';
            $headersRow =  '<tr style="height: 175px;">';
            $headersRow .=  '<th rowspan="3" style="vertical-align: bottom; padding: 15px 30px; background-color: rgb(68, 192, 78);">' .
                '<div class="verticalText" style="font-size: 14px; color: #fff;">' . $course->getCourse()->getName() . '</div></th>';

            $porcRow = '<tr style="background-color: black; background-color: rgb(201, 194, 194); font-weight: bold; font-size: 14px;">';

            $courseRow = '<tr style="background-color: black; background-color: rgb(201, 194, 194); font-weight: bold; font-size: 14px;">';
            $dql = "SELECT ce "
                . " FROM TecnotekExpedienteBundle:CourseEntry ce "
                . " WHERE ce.courseClass = " . $course->getId()
                . " ORDER BY ce.name";

            $query = $em->createQuery($dql);
            $courseEntries = $query->getResult();

            if(sizeof($courseEntries) > 0 ){
                foreach( $courseEntries as $courseEntry )
                {
                    $headersRow .=  '<th colspan=2 style="vertical-align: bottom; padding: 15px 30px;"><div class="verticalText">' .
                        $courseEntry->getName() . ' ' . $courseEntry->getPercentage() . '%</div></th>';
                    //$studentRow .= '<td>Nota_' . $courseEntry->getId() . '_</td>' . '<td style=" background-color: rgb(234, 241, 221);">Notap_' . $courseEntry->getId() . '_</td>';
                    $porcRow .= '<td>Nota</td><td style=" background-color: rgb(234, 241, 221);">% Gan</td>';
                    //$courseRow .= '<td>-</td><td style=" background-color: rgb(234, 241, 221);">-</td>';

                    /*-----------------------------------------------------------------------------------*/
                    $dql = "SELECT stdQ FROM TecnotekExpedienteBundle:StudentQualification stdQ, TecnotekExpedienteBundle:SubCourseEntry subEntry "
                        . " WHERE stdQ.studentYear = " . $studentId . " AND subEntry.parent = " . $courseEntry->getId()
                        . " AND stdQ.subCourseEntry = subEntry"
                        . " ";
                    // . " ORDER BY std.lastname, std.firstname";
                    $query = $em->createQuery($dql);
                    $qualifications = $query->getResult();
                    $notasCursos = array();
                    foreach( $qualifications as $q )
                    {
                        $subEntry = $q->getSubCourseEntry();
                        $n = $q->getQualification();
                        $m = $subEntry->getMaxValue();
                        $p = $subEntry->getPercentage();

                        //Calcular porcentage ganado en la entrada
                        $pg = $p * $n / $m;

                        //Hack para cuando no se definen los porcentajes adecuados...
                        //$courseEntry = $subEntry->getParent();

                        if($subEntry->getParent()->getPercentage() == $subEntry->getPercentage()){
                            $childrens = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->findBy(array('parent' => $courseEntry->getId(), 'group' => $studentYear->getGroup()->getId()));
                            $size = sizeof($childrens);
                            if( $size > 0){
                                $pg = $pg / $size;
                                $n = $n / $size;
                            }
                        }

                        $pg = round( $pg, 2, PHP_ROUND_HALF_UP);
                        $n = round( $n, 2, PHP_ROUND_HALF_UP);

                        //Revisar si ya existe la entrada del curso (CourseEntry)
                        if( isset($notasCursos["p" . $courseEntry->getCourseClass()->getId()]) ){
                            $pg = $notasCursos["p" . $courseEntry->getCourseClass()->getId()] + $pg;
                        }
                        if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                            $n = $notasCursos["n" . $courseEntry->getCourseClass()->getId()] + $n;
                        }
                        $notasCursos["p" . $courseEntry->getCourseClass()->getId()] = $pg;
                        $notasCursos["n" . $courseEntry->getCourseClass()->getId()] = $n;
                    }

                    if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                        $courseRow .= '<td>' . $notasCursos["n" . $courseEntry->getCourseClass()->getId()]
                            . '</td><td style=" background-color: rgb(234, 241, 221);">'
                            . $notasCursos["p" . $courseEntry->getCourseClass()->getId()] . '</td>';
                        /*$row = str_replace("Nota_" . $courseEntry->getId() . "_", $notasCursos["n" . $courseEntry->getCourseClass()->getId()], $row);
                        $row = str_replace("Notap_" . $courseEntry->getId() . "_", $notasCursos["p" . $courseEntry->getCourseClass()->getId()], $row);*/
                    } else {
                        $courseRow .= '<td>-</td><td style=" background-color: rgb(234, 241, 221);">-</td>';
                        /*$row = str_replace("Nota_" . $courseEntry->getId() . "_", "-", $row);
                        $row = str_replace("Notap_" . $courseEntry->getId() . "_", "-", $row);*/
                    }

                }
                /*$row = str_replace("courseName", $course->getCourse()->getName(), $courseRow);
                $row = str_replace("courseId", $course->getId(), $row);

                $notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")->findOneBy(array('courseClass' => $course->getId(), 'studentYear' => $studentYear->getId()));
                if(isset($notaFinal)){//Si existe
                    $row = str_replace("-", $notaFinal->getQualification(), $row);
                }*/

                $porcRow .= '</tr>';
                $courseRow .= '</tr>';
                $headersRow .= "</tr>";
                $courseTable = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">'
                    . $headersRow . $porcRow . $courseRow . '</table>';


                //$html .=  '<tr class="courseTableRow"><td>' . $courseTable . '</td></tr>';

                $html .=  $courseTable;
            }

        }
        //$html .= '</table>';
        /*
      $html = '<table border="1">';
      $html .= '  <tr>';
      $html .= '    <th rowspan="3">Month</th>';
          $html .= '   <th>Savings</th>';
      $html .= '    <th>Savings for holiday!</th>';
          $html .= '  </tr>';
     $html .= '   <tr>';
      $html .= '    <td>January</td>';
     $html .= '     <td>$100</td>';
          $html .= '    </tr>';
      $html .= '  <tr>';
      $html .= '    <td>February</td>';
     $html .= '     <td>$80</td>';
          $html .= '  </tr>';
      $html .= '</table>';*/
        return $html;
    }

    public function getStudentHTMLPenalties($periodId, $gradeId, $groupId, $studentId){

        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($studentId);

        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc "
            . " WHERE cc.grade = " . $gradeId . " AND cc.course = c"
            . " ORDER BY c.name";

        $query = $em->createQuery($dql);
        $courses = $query->getResult();



        $courseRow = '';

        $this->calculateStudentYearQualification($periodId, $studentId, $studentYear);

        $courseRow .=  '<tr class="rowNotas">';
        $courseRow .= '<td>courseName</td>';
        $courseRow .= '<td><div id="course_courseId_nota">-</div></td>';
        $courseRow .=  "</tr>";


        $html = '<table class="courseTable">';
        foreach( $courses as $course )
        {
            //$headersRow =  '<thead>';
            $headersRow =  '<tr style="height: 175px;">';
            $headersRow .=  '<th rowspan="3" style="vertical-align: bottom; padding: 15px 30px; background-color: rgb(68, 192, 78);">' .
                '<div class="verticalText" style="font-size: 14px; color: #fff;">' . $course->getCourse()->getName() . '</div></th>';

            $porcRow = '<tr style="background-color: black; background-color: rgb(201, 194, 194); font-weight: bold; font-size: 14px;">';

            $courseRow = '<tr style="background-color: black; background-color: rgb(201, 194, 194); font-weight: bold; font-size: 14px;">';
            $dql = "SELECT ce "
                . " FROM TecnotekExpedienteBundle:CourseEntry ce "
                . " WHERE ce.courseClass = " . $course->getId()
                . " ORDER BY ce.name";

            $query = $em->createQuery($dql);
            $courseEntries = $query->getResult();

            if(sizeof($courseEntries) > 0 ){
                foreach( $courseEntries as $courseEntry )
                {
                    $headersRow .=  '<th colspan=2 style="vertical-align: bottom; padding: 15px 30px;"><div class="verticalText">' .
                        $courseEntry->getName() . ' ' . $courseEntry->getPercentage() . '%</div></th>';
                    //$studentRow .= '<td>Nota_' . $courseEntry->getId() . '_</td>' . '<td style=" background-color: rgb(234, 241, 221);">Notap_' . $courseEntry->getId() . '_</td>';
                    $porcRow .= '<td>Nota</td><td style=" background-color: rgb(234, 241, 221);">% Gan</td>';
                    //$courseRow .= '<td>-</td><td style=" background-color: rgb(234, 241, 221);">-</td>';

                    /*-----------------------------------------------------------------------------------*/
                    $dql = "SELECT stdQ FROM TecnotekExpedienteBundle:StudentQualification stdQ, TecnotekExpedienteBundle:SubCourseEntry subEntry "
                        . " WHERE stdQ.studentYear = " . $studentId . " AND subEntry.parent = " . $courseEntry->getId()
                        . " AND stdQ.subCourseEntry = subEntry"
                        . " ";
                    // . " ORDER BY std.lastname, std.firstname";
                    $query = $em->createQuery($dql);
                    $qualifications = $query->getResult();
                    $notasCursos = array();
                    foreach( $qualifications as $q )
                    {
                        $subEntry = $q->getSubCourseEntry();
                        $n = $q->getQualification();
                        $m = $subEntry->getMaxValue();
                        $p = $subEntry->getPercentage();

                        //Calcular porcentage ganado en la entrada
                        $pg = $p * $n / $m;

                        //Hack para cuando no se definen los porcentajes adecuados...
                        //$courseEntry = $subEntry->getParent();

                        if($subEntry->getParent()->getPercentage() == $subEntry->getPercentage()){
                            $childrens = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->findBy(array('parent' => $courseEntry->getId(), 'group' => $studentYear->getGroup()->getId()));
                            $size = sizeof($childrens);
                            if( $size > 0){
                                $pg = $pg / $size;
                            }
                        }

                        $pg = round( $pg, 2, PHP_ROUND_HALF_UP);

                        //Revisar si ya existe la entrada del curso (CourseEntry)
                        if( isset($notasCursos["p" . $courseEntry->getCourseClass()->getId()]) ){
                            $pg = $notasCursos["p" . $courseEntry->getCourseClass()->getId()] + $pg;
                        }
                        if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                            $n = $notasCursos["n" . $courseEntry->getCourseClass()->getId()] + $n;
                        }
                        $notasCursos["p" . $courseEntry->getCourseClass()->getId()] = $pg;
                        $notasCursos["n" . $courseEntry->getCourseClass()->getId()] = $n;
                    }

                    if( isset($notasCursos["n" . $courseEntry->getCourseClass()->getId()]) ){
                        $courseRow .= '<td>' . $notasCursos["n" . $courseEntry->getCourseClass()->getId()]
                            . '</td><td style=" background-color: rgb(234, 241, 221);">'
                            . $notasCursos["p" . $courseEntry->getCourseClass()->getId()] . '</td>';
                    } else {
                        $courseRow .= '<td>-</td><td style=" background-color: rgb(234, 241, 221);">-</td>';
                    }

                }


                $porcRow .= '</tr>';
                $courseRow .= '</tr>';
                $headersRow .= "</tr>";
                $courseTable = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">'
                    . $headersRow . $porcRow . $courseRow . '</table>';


                //$html .=  '<tr class="courseTableRow"><td>' . $courseTable . '</td></tr>';

                $html .=  $courseTable;
            }

        }

        return $html;
    }

    public function getGroupHTMLPenalties($periodId, $gradeId, $groupId){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();

        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:Course c, TecnotekExpedienteBundle:CourseClass cc "
            . " WHERE cc.grade = " . $gradeId . " AND cc.course = c"
            . " ORDER BY c.name";

        $query = $em->createQuery($dql);
        $courses = $query->getResult();

        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 175px;">';
        $headersRow .=  '        <th style="width: 75px; text-align: center;">Carne</th>';
        $headersRow .=  '        <th style="width: 250px; text-align: center;">Estudiante</th>';

        $studentRow = '';

        $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
            . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
            . " ORDER BY std.lastname, std.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();

        foreach( $courses as $course )
        {
            $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $course->getCourse()->getName() . '</div></th>';
            $studentRow .= '<td>Nota_' . $course->getId() . '_</td>';
        }

        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';
        $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $studentRowIndex = 0;
        foreach($students as $stdy){
            $this->calculateStudentYearQualification($periodId, $stdy->getId(), $stdy);

            $html .=  '<tr class="rowNotas">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';

            $row = $studentRow;
            /***** Obtener Notas del Estudiante Inicio *****/
            foreach( $courses as $course )
            {
                $notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")->findOneBy(array('courseClass' => $course->getId(), 'studentYear' => $stdy->getId()));
                if(isset($notaFinal)){//Si existe
                    $row = str_replace("Nota_" . $course->getId() . "_", $notaFinal->getQualification(), $row);
                } else {
                    $row = str_replace("Nota_" . $course->getId() . "_", "-", $row);
                }
            }
            /***** Obtener Notas del Estudiante Final *****/
            $html .=  $row . "</tr>";
        }

        $html .= "</table>";

        return $html;
    }

    public function groupByCourseAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        $years = array();

        foreach($periods as $period){
            if (!array_key_exists($period->getYear(), $years)) {
                $years[$period->getYear()] = $period->getYear();
            }
        }
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/groupCourseQualifications.html.twig', array('menuIndex' => 4,
            'years' => $years
        ));
    }

    public function loadGroupQualificationsByCourseAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $year = $request->get('year');
                $groupId = $request->get('groupId');
                $courseId = $request->get('courseId');
                $isMepReport = $request->get('isMepReport');
                if (!isset($isMepReport)) {//Default is false=0
                    $isMepReport = 0;
                }

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];

                $translator = $this->get("translator");

                if( isset($year) && isset($groupId) && isset($courseId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $carne = "";
                    $teacherGroup = "";
                    $studentName = "";
                    $logger->err("Obtener estudiantes del grupo: " . $groupId);
                    $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);
                    $teacher = $group->getTeacher();
                    $imgHeader = "encabezadoDefault.png";
                    $teacherGroup = $teacher->getFirstname() . " " . $teacher->getLastname();
                    $director = "Indefinido";
                    $institution = $group->getInstitution();
                    if(isset($institution)){
                        //Find Properties
                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "TICKETS_IMAGE" ));

                        if(isset($property)){
                            $imgHeader = $property->getValue();
                        }

                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "DIRECTOR" ));

                        if(isset($property)){
                            $director = $property->getValue();
                        }
                    }
                    if ($isMepReport == 0) {
                        $html = $this->getGroupHTMLQualificationsByCourse($group, $courseId);
                    } else {
                        $html = $this->getMepHTMLReport($group, $courseId);
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html, 'teacherGroup' => $teacherGroup, "imgHeader" => $imgHeader)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getGroupHTMLQualificationsByCourse($group, $courseId){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        $myCourse = $em->getRepository("TecnotekExpedienteBundle:Course")->find($courseId);

        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:CourseClass cc "
            . " JOIN cc.period p  "
            . " WHERE cc.course = " . $courseId . " AND cc.grade = " . $group->getGrade()->getId()
            . " ORDER BY p.orderInYear";
        $query = $em->createQuery($dql);
        $courses = $query->getResult();

        $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
            . " WHERE stdy.student = std AND stdy.group = " . $group->getId()
            . " ORDER BY std.lastname, std.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();

        //Agregar los 3 periodos y el promedio
        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 145px;">';
        $headersRow .=  '        <th style="width: 75px; text-align: center;">Carne</th>';
        $headersRow .=  '        <th style="width: 250px; text-align: center;">Estudiante</th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Trimestre I</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Trimestre II</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Trimestre III</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Promedio</div></th>';
        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';

        $studentRow = '';
        $studentRow .= '<td>Nota_Period_1</td>';
        $studentRow .= '<td>Nota_Period_2</td>';
        $studentRow .= '<td>Nota_Period_3</td>';
        $studentRow .= '<td>Nota_Promedio</td>';

        $html =  '<b>Grupo: '.$group->getGrade().'-'. $group->getName() . ", Materia: " . $myCourse->getName() . "</b><br/>";

        $html .= '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;


        $studentRowIndex = 0;

        $periods = array();
        $periods[1] = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('year' => $group->getPeriod()->getYear(), 'orderInYear' => 1));
        $periods[2] = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('year' => $group->getPeriod()->getYear(), 'orderInYear' => 2));
        $periods[3] = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('year' => $group->getPeriod()->getYear(), 'orderInYear' => 3));

        $notaMin = $em->getRepository("TecnotekExpedienteBundle:Grade")->findOneBy(array('id' => $group->getGrade()->getId()));

        foreach($students as $stdy){
            $total = 0;
            $counter = 0;

            $html .=  '<tr class="rowNotas" style="height: 25px;">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';

            $row = $studentRow;


//Recorrer Todos los Periodos
            for($i = 1; $i < 4; $i++){
                $currentPeriod = $periods[$i];
                if(isset($currentPeriod)){
//$logger->err("Getting student with: " .  $stdy->getStudent()->getId() . " AND " . $currentPeriod->getId());
                    $currentSTDY = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->findOneBy(array('student' => $stdy->getStudent()->getId(), 'period' => $currentPeriod->getId()));
 if(isset($currentSTDY)){
                    if($currentPeriod->isEditable()){
                        $this->calculateStudentYearQualification($currentPeriod->getId(), $currentSTDY->getId(), $currentSTDY);
                    }
                    foreach( $courses as $course )
                    {
                        if($course->getPeriod()->getId() == $currentPeriod->getId()){
//$logger->err("Getting student with: " .  $currentSTDY->getId() . " AND " . $currentPeriod->getId());
                            $notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")->findOneBy(array('courseClass' => $course->getId(), 'studentYear' => $currentSTDY->getId()));

                            $typeC = $course->getCourse()->getType();
                            if( $typeC==1){
                                if(isset($notaFinal)){//Si existe
                                    //// nuevo
                                    if($notaFinal->getQualification() != '0'){
                                        $total += $notaFinal->getQualification();
                                        $counter += 1;
                                    }
                                    //// nuevo
                                    if($notaFinal->getQualification() < $notaMin->getNotaMin()){
                                        $row = str_replace("Nota_Period_" . $i, "* " .  $notaFinal->getQualification(), $row);
                                    } else {
                                        $row = str_replace("Nota_Period_" . $i, $notaFinal->getQualification(), $row);
                                    }
                                } else {
                                    $row = str_replace("Nota_Period_" . $i, "-", $row);
                                }

                            }
                            else{
                                if(isset($notaFinal)){//Si existe
                                    $valorNota =  $notaFinal->getQualification();
                                    if($valorNota == 99)
                                        $valorNota = "Exc";
                                    if($valorNota == 74)
                                        $valorNota = "V.Good";
                                    if($valorNota == 50)
                                        $valorNota = "Good";
                                    if($valorNota == 25)
                                        $valorNota = "N.I.";
                                    if($valorNota == 4)
                                        $valorNota = "Exc";
                                    if($valorNota == 3)
                                        $valorNota = "V.Good";
                                    if($valorNota == 2)
                                        $valorNota = "Good";
                                    if($valorNota == 1)
                                        $valorNota = "N.I.";
                                    $row = str_replace("Nota_Period_" . $i, $valorNota, $row);
                                }
                                else {
                                    $row = str_replace("Nota_Period_" . $i, "-", $row);
                                }
                            }
                        }

                    }//Fin del foreach de courses
}else {$row = str_replace("Nota_Period_" . $i, "-", $row);}
                }  else {//El periodo no existe
                    $row = str_replace("Nota_Period_" . $i, "-*-", $row);
                }
            }





            /***** Obtener Notas del Estudiante Inicio *****/
            if($counter > 0){
                $row = str_replace("Nota_Promedio", number_format($total/$counter, 2, '.', ''), $row);
            } else {
                $row = str_replace("Nota_Promedio", "-", $row);
            }
            $html .=  $row . "</tr>";
        }

        $html .= "</table>";

        return $html;
    }

    public function loadGroupObservationsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
//                $periodId = 3;
                $groupId = $request->get('groupId');
                $convocatoria = $request->get('conv');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];

                $referenceId = $request->get('referenceId');

                $translator = $this->get("translator");

                if( isset($referenceId) && isset($groupId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //$period = $em->getRepository("TecnotekExpedienteBundle:Period")->find($periodId);

                    $carne = "";
                    $teacherGroup = "";
                    $studentName = "";

                    $group = $em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId);
                    $teacher = $group->getTeacher();
                    $imgHeader = "encabezadoDefault.png";
                    $teacherGroup = $teacher->getFirstname() . " " . $teacher->getLastname();
                    $director = "Indefinido";
                    $institution = $group->getInstitution();
                    if(isset($institution)){
                        //Find Properties
                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "TICKETS_IMAGE" ));

                        if(isset($property)){
                            $imgHeader = $property->getValue();
                        }

                        $property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "DIRECTOR" ));

                        if(isset($property)){
                            $director = $property->getValue();
                        }

                        /*$property = $em->getRepository("TecnotekExpedienteBundle:InstitutionProperty")->findOneBy(
                            array('institution' => $institution->getId(), 'code' => "TICKETS_TEXT" ));

                        if(isset($property)){
                            $text = $property->getValue();
                        }*/
                    }

                    if($referenceId == 0){
                        $html = "";
                    } else{
                        $studentYear = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($referenceId);
                        $student = $studentYear->getStudent();
                        $carne = $student->getCarne();
                        $studentName = "" . $student;
                        $html = $this->getStudentByPeriodHTMLObservations($periodId, $gradeId, $groupId, $referenceId, $studentYear, $director, $institution, $convocatoria);
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html, 'carne' => $carne, 'teacherGroup' => $teacherGroup, "studentName" => $studentName, "imgHeader" => $imgHeader)));
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
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function getStudentByPeriodHTMLObservations($periodId, $gradeId, $groupId, $studentId, $studentYear, $director, $institution, $convocatoria){



        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();


        $em->persist($studentYear);
        $em->flush();


        $dia=date("l");

        if ($dia=="Monday") $dia="Lunes";
        if ($dia=="Tuesday") $dia="Martes";
        if ($dia=="Wednesday") $dia="Miércoles";
        if ($dia=="Thursday") $dia="Jueves";
        if ($dia=="Friday") $dia="Viernes";
        if ($dia=="Saturday") $dia="Sabado";
        if ($dia=="Sunday") $dia="Domingo";

        $mes=date("F");

        if ($mes=="January") $mes="Enero";
        if ($mes=="February") $mes="Febrero";
        if ($mes=="March") $mes="Marzo";
        if ($mes=="April") $mes="Abril";
        if ($mes=="May") $mes="Mayo";
        if ($mes=="June") $mes="Junio";
        if ($mes=="July") $mes="Julio";
        if ($mes=="August") $mes="Agosto";
        if ($mes=="September") $mes="Setiembre";
        if ($mes=="October") $mes="Octubre";
        if ($mes=="November") $mes="Noviembre";
        if ($mes=="December") $mes="Diciembre";

        $ano=date("Y");
        $dia2=date("d");


        $html = '<div style="margin-top: 25px; margin-bottom: 25px;">Desamparados, '. $dia.' '. $dia2 .' de '. $mes .' del '. $ano . '</br></br></div>';


        $sql = "SELECT obs FROM TecnotekExpedienteBundle:Observation obs"
            . " WHERE obs.studentYear = " .  $studentYear->getId();
        //. " AND obs.courseClass = ".$periodId;
        $query = $em->createQuery($sql);
        $observations = $query->getResult();
        $counter2=0;


        $separator[0] = '/1./';
        $separator[1] = '/2./';

        $html .= '<table class="new">';
        $html .= '<tr><td colspan="3" style="text-align: center"><FONT FACE="Geneva, Arial" color=black SIZE=3>Especificación por materia</FONT></td></tr>';
        foreach($observations as $observation){
            $profesor =  $observation->getTeacher();
            $courseClass =  $observation->getCourseClass();
            $comments =  $observation->getDetail();

            $courseName = preg_replace($separator,"",$courseClass->getCourse());
            $html .= '<tr>';

            $html .= '<td height="18" VALIGN="top" style="width: 160px; margin-top: 15px; margin-bottom: 25px;" ><FONT FACE="Geneva, Arial" color=black SIZE=3>'. $profesor  . '</FONT></td>';
            $html .= '<td height="18" VALIGN="top" style="width: 165px; margin-top: 15px; margin-bottom: 25px;" ><FONT FACE="Geneva, Arial" color=black SIZE=3>'. $courseName  . '</FONT></td>';
            $html .= '<td  height="18" VALIGN="top" style="width: 475px; margin-top: 15px; margin-bottom: 25px;"><FONT FACE="Geneva, Arial" color=black SIZE=3>'. $comments  . '</FONT></br>&nbsp;</td>';



            $html .= '</tr>';
        }

        $html .= '</table>';

        $html .= '<div>Nota: Comunicarse con Andrea Díaz la auxiliar administrativa para asuntos de conducta y ausentismo.</div>';

        $html .= '</br></br>';
        $html .= '<div class="clear"></div>';
        $html .= '<div class="left" style="width: 450px; text-align: center;"><div style="line-height: 25px;">______________________________</div><div>Firma del padre de familia o encargado</div></div>';
        //$html .= '<div class="left" style="width: 250px; text-align: center; margin-left: 300px;"><div style="line-height: 25px;">______________________________</div><div>' . $director . '</div><div>Director</div></div>';
        $html .= '<div class="clear"></div>';



        $html .= '';
        return $html;
    }

    public function observationsOfPeriodAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/periodGroupObservations.html.twig', array('menuIndex' => 4,
            'periods' => $periods
        ));
    }

    public function quintosMepAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        $years = array();

        foreach($periods as $period){
            if (!array_key_exists($period->getYear(), $years)) {
                $years[$period->getYear()] = $period->getYear();
            }
        }
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/mepQualifications.html.twig',
            array(
                'menuIndex' => 4,
                'years' => $years
        ));
    }

    public function getTenGradeStudentCourseAverage($previousYearCourseClasses, $studentId) {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT q FROM TecnotekExpedienteBundle:StudentYearCourseQualification q"
            . " JOIN q.studentYear stdy"
            . " JOIN stdy.student std"
            . " WHERE q.courseClass IN (" . $previousYearCourseClasses . ")"
            . " AND std.id = " . $studentId;
        $query = $em->createQuery($dql);
        $previousYearQualifications = $query->getResult();
        $total = 0;
        $counter = 0;
        foreach ($previousYearQualifications as $q) {
            $counter++;
            $total += $q->getQualification();
        }
        return ($total / $counter);
    }

    public function getMepHTMLReport($group, $courseId){
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        $myCourse = $em->getRepository("TecnotekExpedienteBundle:Course")->find($courseId);
        $dql = "SELECT cc "
            . " FROM TecnotekExpedienteBundle:CourseClass cc "
            . " JOIN cc.period p  "
            . " WHERE cc.course = " . $courseId . " AND cc.grade = " . $group->getGrade()->getId()
            . " ORDER BY p.orderInYear";
        $query = $em->createQuery($dql);
        $courses = $query->getResult();
        $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
            . " WHERE stdy.student = std AND stdy.group = " . $group->getId()
            . " ORDER BY std.lastname, std.firstname";
        $query = $em->createQuery($dql);
        $students = $query->getResult();
        // Get Previous Year Periods
        $dql = "SELECT p FROM TecnotekExpedienteBundle:Period p"
            . " WHERE p.year = " . ($group->getPeriod()->getYear()-1)
            . " ORDER BY p.orderInYear";
        $query = $em->createQuery($dql);
        $previousYearPeriods = $query->getResult();
        $previousYearPeriodsStr = "";
        foreach ($previousYearPeriods as $p) {
            $previousYearPeriodsStr .= "," . $p->getId();
        }
        $previousYearPeriodsStr = substr($previousYearPeriodsStr, 1);
        // Get Course Classes for 10th Grade
        $dql = "SELECT cc FROM TecnotekExpedienteBundle:CourseClass cc"
            . " WHERE cc.course = " . $courseId . " AND cc.grade = 10 AND cc.period IN (" . $previousYearPeriodsStr . ")"
            . " ";
        $query = $em->createQuery($dql);
        $previousYearCC = $query->getResult();
        $previousYearCCStr = "";
        foreach ($previousYearCC as $cc) {
            $logger->err("CC: " . $cc->getId());
            $previousYearCCStr .= "," . $cc->getId();
        }
        $previousYearCCStr = substr($previousYearCCStr, 1);
        //Agregar los 3 periodos y el promedio
        $headersRow =  '<thead>';
        $headersRow .=  '    <tr style="height: 145px;">';
        $headersRow .=  '        <th style="width: 75px; text-align: center;">Carne</th>';
        $headersRow .=  '        <th style="width: 250px; text-align: center;">Estudiante</th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Anual Décimo</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Trimestre I Undécimo</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Trimestre II Undécimo</div></th>';
        $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Promedio</div></th>';
        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';
        $studentRow = '';
        $studentRow .= '<td>Nota_Decimo</td>';
        $studentRow .= '<td>Nota_Period_1</td>';
        $studentRow .= '<td>Nota_Period_2</td>';
        $studentRow .= '<td>Nota_Promedio</td>';
        $html =  '<b>Grupo: '.$group->getGrade().'-'. $group->getName() . ", Materia: " . $myCourse->getName() . "</b><br/>";
        $html .= '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;
        $studentRowIndex = 0;
        $periods = array();
        $periods[1] = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('year' => $group->getPeriod()->getYear(), 'orderInYear' => 1));
        $periods[2] = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array('year' => $group->getPeriod()->getYear(), 'orderInYear' => 2));
        foreach ($students as $stdy) {
            $html .=  '<tr class="rowNotas" style="height: 25px;">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';
            $row = $studentRow;
            $previousYearAverage = $this->getTenGradeStudentCourseAverage($previousYearCCStr, $stdy->getStudent()->getId());
            $row = str_replace("Nota_Decimo", number_format($previousYearAverage, 2, '.', ''), $row);
            $total = $previousYearAverage;
            $counter = 1;
            // Recorrer Todos los Periodos
            for ($i = 1; $i < 3; $i++) {
                $currentPeriod = $periods[$i];
                if (isset($currentPeriod)) {
                    $currentSTDY = $em->getRepository("TecnotekExpedienteBundle:StudentYear")
                        ->findOneBy(array('student' => $stdy->getStudent()->getId(), 'period' => $currentPeriod->getId()));
                    if (isset($currentSTDY)) {
                        if ($currentPeriod->isEditable()) {
                            $this->calculateStudentYearQualification($currentPeriod->getId(), $currentSTDY->getId(), $currentSTDY);
                        }
                        foreach ($courses as $course) {
                            if ($course->getPeriod()->getId() == $currentPeriod->getId()) {
                                $notaFinal = $em->getRepository("TecnotekExpedienteBundle:StudentYearCourseQualification")
                                    ->findOneBy(array('courseClass' => $course->getId(), 'studentYear' => $currentSTDY->getId()));
                                $typeC = $course->getCourse()->getType();
                                if ($typeC==1) {
                                    if (isset($notaFinal)) {//Si existe
                                        if ($notaFinal->getQualification() != '0') {
                                            $total += $notaFinal->getQualification();
                                            $counter += 1;
                                        }
                                        $row = str_replace("Nota_Period_" . $i, $notaFinal->getQualification(), $row);
                                    } else {
                                        $row = str_replace("Nota_Period_" . $i, "-", $row);
                                    }
                                } else {
                                    if (isset($notaFinal)) {//Si existe
                                        $valorNota =  $notaFinal->getQualification();
                                        if($valorNota == 99)
                                            $valorNota = "Exc";
                                        if($valorNota == 74)
                                            $valorNota = "V.Good";
                                        if($valorNota == 50)
                                            $valorNota = "Good";
                                        if($valorNota == 25)
                                            $valorNota = "N.I.";
                                        if($valorNota == 4)
                                            $valorNota = "Exc";
                                        if($valorNota == 3)
                                            $valorNota = "V.Good";
                                        if($valorNota == 2)
                                            $valorNota = "Good";
                                        if($valorNota == 1)
                                            $valorNota = "N.I.";
                                        $row = str_replace("Nota_Period_" . $i, $valorNota, $row);
                                    } else {
                                        $row = str_replace("Nota_Period_" . $i, "-", $row);
                                    }
                                }
                            }
                        }//Fin del foreach de courses
                    }else {$row = str_replace("Nota_Period_" . $i, "-", $row);}
                }  else {//El periodo no existe
                    $row = str_replace("Nota_Period_" . $i, "-*-", $row);
                }
            }
            $row = str_replace("Nota_Promedio", number_format($total/$counter, 2, '.', ''), $row);
            $html .=  $row . "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    public function transportationTicketFromSiteListAction() {
        $text = $this->get('request')->query->get('text');
        $em = $this->getDoctrine()->getEntityManager();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/transportationTicketFromSite.html.twig',
            array(
                'menuIndex' => 3,
                'text' => $text,
                'states' => $em->getRepository("TecnotekExpedienteBundle:State")->findAll(),
        ));
    }

    public function searchTransportationTicketFromSiteListAction($rowsPerPage = 30) {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
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
            $state = $request->get('state');
            if ($state != 0) {
                $where .= $where == ""? "":" AND ";
                $where .= " s.id = " . $state;
            }
            $canton = $request->get('canton');
            if ($canton != 0) {
                $where .= $where == ""? "":" AND ";
                $where .= " c.id = " . $canton;
            }
            $district = $request->get('district');
            if ($district != 0) {
                $where .= $where == ""? "":" AND ";
                $where .= " d.id = " . $district;
            }
            $sql = "SELECT SUM($where) as filtered,"
                . " COUNT(*) as total FROM tek_site_transportation_tickets tickets"
                . " JOIN tek_students std ON std.id = tickets.student_id"
                . " JOIN tek_states s ON tickets.state_id = s.id"
                . " JOIN tek_cantones c ON tickets.canton_id = c.id"
                . " JOIN tek_districts d ON tickets.district_id = d.id"
                . ";";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $filtered = 0;
            $total = 0;
            $result = $stmt->fetchAll();
            foreach($result as $row) {
                $filtered = $row['filtered'];
                $total = $row['total'];
            }
            $sql = "SELECT tickets.id, DATE_FORMAT(tickets.date,'%d-%m-%Y %T') as 'date', std.id, "
                . " CONCAT(std.firstname, ' ', std.lastname) as 'fullName', std.groupyear, std.gender, std.carne, "
                . " CONCAT(s.name, ', ', c.name, ', ', d.name) as 'fullAddress', tickets.service"
                . " FROM tek_site_transportation_tickets tickets"
                . " JOIN tek_students std ON std.id = tickets.student_id"
                . " JOIN tek_states s ON tickets.state_id = s.id"
                . " JOIN tek_cantones c ON tickets.canton_id = c.id"
                . " JOIN tek_districts d ON tickets.district_id = d.id"
                . " WHERE $where"
                . " ORDER BY $sortBy $order"
                . " LIMIT $rowsPerPage OFFSET $offset";
            $stmt2 = $em->getConnection()->prepare($sql);
            $stmt2->execute();
            $tickets = $stmt2->fetchAll();
            return new Response(json_encode(array('error' => false,
                'filtered' => $filtered,
                'total' => $total,
                'tickets' => $tickets)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Report::searchTransportationTicketFromSiteListAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }
}