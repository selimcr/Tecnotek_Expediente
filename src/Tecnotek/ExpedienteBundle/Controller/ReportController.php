<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
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

        $gender = $request->get('gender');
        $age = $request->get('age');

        $groups = null;
        $grades = null;
        $institutions = null;
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
            'age' => $age, 'gender' => $gender, 'tipo' => $tipo
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

                $referenceId = $request->get('referenceId');

                $translator = $this->get("translator");

                if( isset($referenceId) && isset($groupId) && isset($periodId)) {
                    if($referenceId == 0){
                        $html = $this->getGroupHTMLQualifications($periodId, $gradeId, $groupId);
                    } else {
                        $html = "Not available.";
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

    public function getGroupHTMLQualifications($periodId, $gradeId, $groupId){
        $em = $this->getDoctrine()->getEntityManager();

        $dql = "SELECT c "
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
            $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $course->getName() . '</div></th>';
            $studentRow .= '<td id="std_stdId_' . $course->getId() . '_stdId">-</td>';
        }

        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';
        $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $studentRowIndex = 0;
        foreach($students as $stdy){
            $html .=  '<tr class="rowNotas">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';

            $row = str_replace("stdId", $stdy->getStudent()->getId(), $studentRow);
            $row = str_replace("stdyIdd", $stdy->getId(), $row);

            /*$dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
. " WHERE qua.studentYear = " . $stdy->getId();
$query = $em->createQuery($dql);
$qualifications = $query->getResult();
foreach($qualifications as $qualification){
$row = str_replace("val_" . $stdy->getStudent()->getId() . "_" . $qualification->getSubCourseEntry()->getId() . "_", "" . $qualification->getQualification(), $row);
}
$html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>' . $row . "</tr>";*/
            $html .=  $row . "</tr>";
        }

        $html .= "</table>";

        return $html;
    }

    public function getStudentByPeriodHTMLQualifications($periodId, $gradeId, $groupId){
        $em = $this->getDoctrine()->getEntityManager();

        $dql = "SELECT c "
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
            $headersRow .=  '<th style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $course->getName() . '</div></th>';
            $studentRow .= '<td id="std_stdId_' . $course->getId() . '_stdId">-</td>';
        }

        $headersRow .=  '    </tr>';
        $headersRow .=  '</thead>';
        $html = '<table class="tableQualifications" cellSpacing="0" cellPadding="0">' . $headersRow;

        $studentRowIndex = 0;
        foreach($students as $stdy){
            $html .=  '<tr class="rowNotas">';
            $studentRowIndex++;
            $html .=  '<td>' . $stdy->getStudent()->getCarne() . '</td>';
            $html .=  '<td>' . $stdy->getStudent() . '</td>';

            $row = str_replace("stdId", $stdy->getStudent()->getId(), $studentRow);
            $row = str_replace("stdyIdd", $stdy->getId(), $row);

            /*$dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
. " WHERE qua.studentYear = " . $stdy->getId();
$query = $em->createQuery($dql);
$qualifications = $query->getResult();
foreach($qualifications as $qualification){
$row = str_replace("val_" . $stdy->getStudent()->getId() . "_" . $qualification->getSubCourseEntry()->getId() . "_", "" . $qualification->getQualification(), $row);
}
$html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>' . $row . "</tr>";*/
            $html .=  $row . "</tr>";
        }

        $html .= "</table>";

        return $html;
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

}
