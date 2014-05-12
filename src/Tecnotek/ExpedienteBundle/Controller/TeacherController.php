<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\CourseEntry;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
use Tecnotek\ExpedienteBundle\Entity\Observation;
use Tecnotek\ExpedienteBundle\Entity\StudentQualification;
use Tecnotek\ExpedienteBundle\Entity\SubCourseEntry;
use Tecnotek\ExpedienteBundle\Entity\Ticket;
use Tecnotek\ExpedienteBundle\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TeacherController extends Controller
{
    public function  qualificationsAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:Teacher:qualifications.html.twig', array('periods' => $periods,
            'menuIndex' => 1));
    }

    public function courseEntriesAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:Teacher:course_entries.html.twig', array('periods' => $periods,
            'menuIndex' => 2));
    }

    public function observationsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:Teacher:observations.html.twig', array('periods' => $periods,
            'menuIndex' => 3));
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
                    $user=$this->get('security.context')->getToken()->getUser();

                    $period = $em->getRepository("TecnotekExpedienteBundle:Period")->find( $periodId );
                    if ( isset($period) ) {
                        //Get Groups
                        $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                            " FROM tek_groups g, tek_assigned_teachers tat, tek_grades grade" .
                            " WHERE g.period_id = " . $periodId . " AND tat.group_id = g.id AND grade.id = g.grade_id AND tat.user_id = "  . $user->getId() .
                            " GROUP BY g.id" .
                            " ORDER BY g.name";
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->execute();
                        $groups = $stmt->fetchAll();

                        return new Response(json_encode(array('error' => false, 'groups' => $groups,
                            'isEditable' => $period->isEditable())));
                    } else {
                        return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Teacher::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadEntriesOfCourseAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $courseId = $request->get('courseId');
                $translator = $this->get("translator");

                if( isset($periodId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $user=$this->get('security.context')->getToken()->getUser();

                    $course = $em->getRepository("TecnotekExpedienteBundle:Course")->find( $courseId );
                    if ( isset($course) ) {
                        //Get Entries
                        /*$sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                            " FROM tek_groups g, tek_assigned_teachers tat, tek_grades grade" .
                            " WHERE g.period_id = " . $periodId . " AND tat.group_id = g.id AND grade.id = g.grade_id AND tat.user_id = "  . $user->getId() .
                            " GROUP BY g.id" .
                            " ORDER BY g.name";
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->execute();
                        $groups = $stmt->fetchAll();*/

                        $entries = new \Doctrine\Common\Collections\ArrayCollection();

                        return new Response(json_encode(array('error' => false, 'entries' => $entries)));
                    } else {
                        return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Teacher::loadGroupsOfPeriodAction [' . $info . "]");
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
                    $user=$this->get('security.context')->getToken()->getUser();

                    //Get Courses
                    $sql = "SELECT course.id, course.name " .
                        " FROM tek_assigned_teachers tat, tek_course_class tcc, tek_courses course " .
                        " WHERE tat.group_id = " . $groupId . " AND tat.course_class_id =  tcc.id AND tcc.course_id = course.id AND tat.user_id = " . $user->getId() .
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
                $logger->err('Teacher::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadEntriesByCourseAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                //$gradeId = $request->get('gradeId');
                $groupId = $request->get('groupId');

                $keywords = preg_split("/[\s-]+/", $groupId);
                $groupId = $keywords[0];
                $gradeId = $keywords[1];
                $logger->err("--------------> " . $groupId . " ------------ " . $keywords . " ---------- " . $gradeId);
                $courseId = $request->get('courseId');

                $translator = $this->get("translator");

                if( isset($gradeId) && isset($periodId) && isset($courseId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $dql = "SELECT e FROM TecnotekExpedienteBundle:CourseEntry e, TecnotekExpedienteBundle:CourseClass cc WHERE e.parent IS NULL AND e.courseClass = cc AND cc.period = $periodId AND cc.grade = $gradeId And cc.course = $courseId ORDER BY e.sortOrder";

                    /*e.parent IS NULL AND
                        e.courseClass = cc AND
                            cc.period = $periodId AND
                                cc.grade = $gradeId And
                                    cc.course = $courseId*/
                    $query = $em->createQuery($dql);
                    $entries = $query->getResult();

                    $html = "";
                    $entriesOptions = "";
                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
                    $courseClassId = 0;

                    $counterEntries = 0;

                    foreach( $entries as $entry ){
                        $temp = $entry;
                        $courseClassId = $temp->getCourseClass()->getId();
                        $childrens = $temp->getChildrens();
                        $size = sizeof($childrens);

                        $entriesOptions .= '<option value="' . $entry->getId() . '">' . $entry->getSortOrder() . ". " . $entry->getName() . " (" . $entry->getPercentage() . "%)" . '</option>';
                        $html .= '<div id="entryRow_' . $entry->getId() . '" class="row userRow tableRowOdd">';
                        $html .= '    <div id="entryNameField_' . $entry->getId() . '" name="entryNameField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getName() . '</div>';
                        $html .= '    <div id="entryCodeField_' . $entry->getId() . '" name="entryCodeField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getCode() . '</div>';
                        $html .= '    <div id="entryPercentageField_' . $entry->getId() . '" name="entryPercentageField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getPercentage() . '</div>';
                        $html .= '    <div id="entryMaxValueField_' . $entry->getId() . '" name="entryMaxValueField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getMaxValue() . '</div>';
                        $html .= '    <div id="entryOrderField_' . $entry->getId() . '" name="entryOrderField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getSortOrder() . '</div>';
                        $html .= '    <div id="entryParentField_' . $entry->getId() . '" name="entryParentField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getParent() . '</div>';

                        $html .= '    <div class="clear"></div>';
                        $html .= '</div>';
                        $counterEntries++;

                        foreach ( $childrens as $child){
                            $html .= '<div id="entryRow_' . $child->getId() . '" class="row userRow tableRowOdd">';
                            $html .= '    <div id="entryNameField_' . $child->getId() . '" name="entryNameField_' . $child->getId() . '" class="option_width" style="float: left; width: 150px;">' . $child->getName() . '</div>';
                            $html .= '    <div id="entryCodeField_' . $child->getId() . '" name="entryCodeField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getCode() . '</div>';
                            $html .= '    <div id="entryPercentageField_' . $child->getId() . '" name="entryPercentageField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getPercentage() . '</div>';
                            $html .= '    <div id="entryMaxValueField_' . $child->getId() . '" name="entryMaxValueField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getMaxValue() . '</div>';
                            $html .= '    <div id="entryOrderField_' . $child->getId() . '" name="entryOrderField_' . $child->getId() . '" class="option_width" style="float: left; width: 100px;">' . $child->getSortOrder() . '</div>';
                            $html .= '    <div id="entryParentField_' . $child->getId() . '" name="entryParentField_' . $child->getId() . '" class="option_width" style="float: left; width: 150px;">' . $child->getParent() . '</div>';
                            $html .= '    <div class="clear"></div>';
                            $html .= '</div>';
                            $counterEntries++;
                        }
                    }

                    $dql = "SELECT e FROM TecnotekExpedienteBundle:SubCourseEntry e" .
                        " JOIN e.parent p" .
                        " JOIN p.courseClass cc" .
                        " WHERE e.group = $groupId AND cc.course = $courseId ORDER BY e.sortOrder";
                    $query = $em->createQuery($dql);
                    $subentries = $query->getResult();
                    $html2 = "";
                    $counterSubEntries = 0;
                    foreach( $subentries as $entry ){
                        $html2 .= '<div id="subentryRow_' . $entry->getId() . '" class="row userRow tableRowOdd">';
                        $html2 .= '    <div id="subentryNameField_' . $entry->getId() . '" name="subentryNameField_' . $entry->getId() . '" class="option_width" style="float: left; width: 230px;">' . $entry->getName() . '</div>';
                        $html2 .= '    <div id="subentryCodeField_' . $entry->getId() . '" name="subentryCodeField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getCode() . '</div>';
                        $html2 .= '    <div id="subentryPercentageField_' . $entry->getId() . '" name="subentryPercentageField_' . $entry->getId() . '" class="option_width" style="float: left; width: 40px;">' . $entry->getPercentage() . '</div>';
                        $html2 .= '    <div id="subentryMaxValueField_' . $entry->getId() . '" name="subentryMaxValueField_' . $entry->getId() . '" class="option_width" style="float: left; width: 80px;">' . $entry->getMaxValue() . '</div>';
                        $html2 .= '    <div id="subentryOrderField_' . $entry->getId() . '" name="subentryOrderField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getSortOrder() . '</div>';
                        $html2 .= '    <div id="subentryParentField_' . $entry->getId() . '" name="subentryParentField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getParent() . '</div>';

                        $html2 .= '    <div class="right deleteButton imageButton deleteSubEntry" style="height: 16px;" title="Eliminar" rel="' . $entry->getId() . '"></div>';
                        $html2 .= '    <div class="right imageButton editButton editSubEntry" title="Editar" rel="' . $entry->getId() . '" entryParent="' . $entry->getParent()->getId() . '"></div>';
                        $html2 .= '    <div class="clear"></div>';
                        $html2 .= '</div>';
                        $counterSubEntries++;
                    }

                    return new Response(json_encode(array('error' => false, 'entries' => $entriesOptions, 'entriesHtml' => $html, 'subentriesHtml' => $html2,
                        'courseClassId' => $courseClassId, 'counter' => ($counterEntries > $counterSubEntries)? $counterEntries:$counterSubEntries)));
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

    public function createSubEntryAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $subentryId = $request->get('subentryId');
                $parentId = $request->get('parentId');
                $name = $request->get('name');
                $code = $request->get('code');
                $maxValue = $request->get('maxValue');
                $percentage = $request->get('percentage');
                $sortOrder = $request->get('sortOrder');
                $keywords = preg_split("/[\s-]+/", $request->get('groupId'));
                $groupId = $keywords[0];

                $translator = $this->get("translator");

                if( isset($parentId) && isset($name) && isset($code) && isset($maxValue) && isset($percentage)
                    && isset($sortOrder) && isset($groupId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    if($subentryId == "0"){
                        $courseEntry = new SubCourseEntry();
                    } else {
                        $courseEntry = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->find($subentryId);
                    }
                    $courseEntry->setName($name);
                    $courseEntry->setCode($code);
                    $courseEntry->setMaxValue($maxValue);
                    $courseEntry->setPercentage($percentage);
                    $courseEntry->setSortOrder($sortOrder);
                    $parent = $em->getRepository("TecnotekExpedienteBundle:CourseEntry")->find($parentId);
                    if(isset($parent)) $courseEntry->setParent($parent);
                    $courseEntry->setGroup($em->getRepository("TecnotekExpedienteBundle:Group")->find($groupId));
                    $em->persist($courseEntry);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Teacher::createEntryAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function removeSubEntryAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $subentryId = $request->get('subentryId');
                $translator = $this->get("translator");

                if( isset($subentryId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $entity = $em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")->find( $subentryId );
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
                $logger->err('SuperAdmin::removeSubEntryAction [' . $info . "]");
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
                $course = $em->getRepository("TecnotekExpedienteBundle:Course")->find( $courseId );

                $title = "Calificaciones del grupo: " . $group->getGrade() . "-" . $group . " en la materia: " . $course;

                $dql = "SELECT ce FROM TecnotekExpedienteBundle:CourseEntry ce "
                    . " JOIN ce.courseClass cc"
                    . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                    . " AND cc.course = " . $courseId
                    . " ORDER BY ce.sortOrder";
                $query = $em->createQuery($dql);
                $entries = $query->getResult();
                $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
                $html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro" style="min-width: 75px; font-size: 12px; height: 175px;">Carne</td>';
                $html .=  '<td class="celesteClaro bold" style="min-width: 250px; font-size: 12px; height: 175px;">Estudiante</td>';
                $html .= '<td class="azul" style="vertical-align: bottom; padding: 1.5625em 0.625em; height: 175px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';

                $marginLeft = 48;
                $marginLeftCode = 62;
                $htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro" style="width: 75px; font-size: 10px;"></td>';
                $htmlCodes .=  '<td class="celesteClaro bold" style="width: 250px; font-size: 8px;"></td>';
                $htmlCodes .= '<td class="azul" style="color: #fff;"></td>';
                $jumpRight = 46;
                $width = 44;

                $html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="min-width: 75px; font-size: 12px;">Carne</td>';
                $html3 .=  '<td class="celesteClaro bold headcolnombre" style="min-width: 250px; font-size: 12px;">Estudiante</td>';
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
                                $studentRow .= '<td class="celesteClaro noPrint"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                                $colsCounter++;
                                $htmlCodes .= '<td class="celesteClaro noPrint"></td>';
                                $specialCounter++;
                                $html .= '<td class="celesteClaro noPrint"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;
                            }

                            //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            $studentRow .= '<td class="celesteOscuro noPrint" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            $htmlCodes .= '<td class="celesteOscuro noPrint"></td>';
                            $specialCounter++;
                            $html .= '<td class="celesteOscuro noPrint"><div class="verticalText">Promedio ' . $temp->getName() . ' </div></td>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                            $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                            $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                            $specialCounter++;
                            $html .= '<td class="morado"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                            $html3 .= '<td class="celesteClaro noPrint" colspan="' . (sizeof($subentries)+2) . '">' . $temp->getName() . '</td>';
                        } else {
                            if($size == 1){
                                foreach( $subentries as $subentry )
                                {
                                    //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                    $studentRow .= '<td class="celesteClaro noPrint"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></div></td>';
                                    $colsCounter++;
                                    $htmlCodes .= '<td class="celesteClaro noPrint"></td>';
                                    $specialCounter++;
                                    $html .= '<td class="celesteClaro noPrint"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId">-</td>';
                                $htmlCodes .= '<td class="morado bold">' . $temp->getCode() . '</td>';
                                $specialCounter++;
                                $html .= '<td class="morado"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
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

                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Qualification/courseGroupQualification.html.twig', array('table' => $html,
                    'studentsCounter' => $studentsCount, "codesCounter" => $specialCounter, 'menuIndex' => 5, 'title' => $title));
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

                    $dql = "SELECT ce.id, ce.name FROM TecnotekExpedienteBundle:CourseEntry ce "
                        . " JOIN ce.courseClass cc"
                        . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                        . " AND cc.course = " . $courseId
                        . " ORDER BY ce.sortOrder";
                    $query = $em->createQuery($dql);
                    $courseEntries = $query->getResult();

                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseEntry();
                    $html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
                    $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';
                    $html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';

                    $marginLeft = 48;
                    $marginLeftCode = 62;
                    $htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
                    $htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
                    $htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;">&nbsp;</td>';
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
                        //$courseEntries->add(array('id'=>$entry->getId(), 'name'=>$entry->getName()));
                        //$courseEntries->add($entry->getName());
                        $temp = $entry;
                        $childrens = $temp->getChildrens();
                        $size = sizeof($childrens);
                        if($size == 0){//No child
                            //Find SubEntries
                            $dql = "SELECT ce FROM TecnotekExpedienteBundle:SubCourseEntry ce "
                                . " WHERE ce.parent = " . $temp->getId()  . " AND ce.group = " . $groupId
                                . " ORDER BY ce.sortOrder, ce.name";
                            $query = $em->createQuery($dql);
                            $subentries = $query->getResult();

                            $size = sizeof($subentries);

                            if($size > 1){
                                foreach( $subentries as $subentry )
                                {

                                    //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                    $studentRow .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                    $colsCounter++;
                                    $htmlCodes .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                                    $specialCounter++;
                                    $html .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                                $studentRow .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                                $htmlCodes .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                                $specialCounter++;
                                $html .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">Promedio ' . $temp->getName() . ' </div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId' . ' entryBase entryB_' . $entry->getId() . '_' . '">-</td>';
                                $htmlCodes .= '<td class="morado bold' . ' entryBase entryB_' . $entry->getId() . '_' . '">' . $temp->getCode() . '</td>';
                                $specialCounter++;
                                $html .= '<td class="morado' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                                $html3 .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" colspan="' . (sizeof($subentries)+2) . '">' . $temp->getName() . '</td>';
                            } else {
                                if($size == 1){
                                    foreach( $subentries as $subentry )
                                    {
                                        //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                        $studentRow .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                        $colsCounter++;
                                        $htmlCodes .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                                        $specialCounter++;
                                        $html .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                        $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    }

                                    //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                    $studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId' . ' entryBase entryB_' . $entry->getId() . '_' . '">-</td>';
                                    $htmlCodes .= '<td class="morado bold' . ' entryBase entryB_' . $entry->getId() . '_' . '">' . $temp->getCode() . '</td>';
                                    $specialCounter++;
                                    $html .= '<td class="morado' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    $html3 .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" colspan="' . (sizeof($subentries)+1) . '">' . $temp->getName() . '</td>';
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

                    $logger->err("courseEntries: " . sizeof($courseEntries) . " ---> " . json_encode($courseEntries));
                    return new Response(json_encode(array('error' => false, 'html' => $html, "studentsCounter" => $studentsCount, "codesCounter" => $specialCounter, "entries" => $courseEntries)));
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

    public function loadGroupObservationsAction(){
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

                    $dql = "SELECT cc FROM TecnotekExpedienteBundle:CourseClass cc "
                        . " WHERE cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                        . " AND cc.course = " . $courseId ;
                    $query = $em->createQuery($dql);
                    $courseClass = $query->getResult();
                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseClass();
                    $html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
                    $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';
                    $html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';
                    $html .= '<td  style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;"></div></td>';

                    $marginLeft = 48;
                    $marginLeftCode = 62;
                    $htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
                    $htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
                    $htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;"></td>';
                    $htmlCodes .= '<td  style="color: #fff;"></td>';
                    $jumpRight = 46;
                    $width = 44;

                    $html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="width: 75px; font-size: 12px;">Carne</td>';
                    $html3 .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">Estudiante</td>';
                    $html3 .= '<td class="azul headcoltrim" style="color: #fff;">TRIM</td>';
                    $html3 .= '<td  style="color: #fff;">Observaciones</td>';
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

                        foreach($courseClass as $ccs){
                            $row2 =  $ccs->getID();
                        }


                        $dql = "SELECT obs FROM TecnotekExpedienteBundle:Observation obs"
                            . " WHERE obs.studentYear = " . $stdy->getId()
                            . " AND obs.courseClass = ".$row2;
                            //. " AND obs.teacher = ".$user_id; //falta capturar el id del profesor
                        $query = $em->createQuery($dql);
                        $observations = $query->getResult();
                        foreach($observations as $observation){
                            $row =  $observation->getDetail();
                        }


                        $html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>';
                        $html .=  '<td id="obser_' . $stdy->getStudent()->getId() . '"  style="color: #000; width: 1600px"><input class="observation" courseClass="' . $row2 . '" style="width: 540px" size"255" maxlength="255"  std="stdId"  stdyId="' . $stdy->getId() . '" value ="' . $row . '"></input></td></tr>';
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

                    return new Response(json_encode(array('error' => false, 'elementId' => $request->get('elementId'), 'elementPerc' => $request->get('elementPerc'),
                        'elementMax' => $request->get('elementMax'), 'elementRel' => $request->get('elementRel'), 'elementChild' => $request->get('elementChild'),
                        'qualification' => $request->get('qualification'),
                        'elementParent' => $request->get('elementParent'), 'elementStdId' => $request->get('elementStdId'),'elementType' => $request->get('elementType'))));
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

    public function saveStudentObservationAction(){

        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $course_classId = $request->get('courseClass');
                $studentYearId = $request->get('studentYearId');
                $userId = $request->get('userId');
                $groupId = $request->get('groupId');
                $observation = $request->get('observation');
                $translator = $this->get("translator");
                $logger->err('--> ' . $studentYearId . " :: " . $observation);

                if( isset($course_classId) || isset($studentYearId) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $studentO = $em->getRepository("TecnotekExpedienteBundle:Observation")->findOneBy(array('courseClass' => $course_classId, 'studentYear' => $studentYearId));

                    if ( isset($studentO) ) {
                        $studentO->setDetail($observation);
                    } else {
                        $studentO = new Observation();
                        $studentO->setCourseClass($em->getRepository("TecnotekExpedienteBundle:CourseClass")->find( $course_classId ));
                        $studentO->setStudentYear($em->getRepository("TecnotekExpedienteBundle:StudentYear")->find( $studentYearId ));
                        $studentO->setTeacher($em->getRepository("TecnotekExpedienteBundle:User")->find( $userId ));
                        $studentO->setGroup($em->getRepository("TecnotekExpedienteBundle:Group")->find( $groupId ));
                        $studentO->setType(1);
                        $studentO->setDetail($observation);
                    }
                    $em->persist($studentO);
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

    public function specialQualificationsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $periods = $em->getRepository("TecnotekExpedienteBundle:Period")->findAll();
        return $this->render('TecnotekExpedienteBundle:Teacher:special_qualifications.html.twig',
            array('periods' => $periods, 'menuIndex' => 4));
    }

    public function loadTeacherGroupsOfPeriodAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $periodId = $request->get('periodId');
                $translator = $this->get("translator");

                if( isset($periodId) ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $user=$this->get('security.context')->getToken()->getUser();

                    $period = $em->getRepository("TecnotekExpedienteBundle:Period")->find( $periodId );
                    if ( isset($period) ) {
                        //Get Groups
                        $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                            " FROM tek_groups g, tek_grades grade" .
                            " WHERE g.period_id = " . $periodId . " AND g.user_id = " . $user->getId()
                            . " AND grade.id = g.grade_id"  .
                            " GROUP BY g.id" .
                            " ORDER BY g.name";
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->execute();
                        $groups = $stmt->fetchAll();

                        return new Response(json_encode(array('error' => false, 'groups' => $groups,
                                                              'isEditable' => $period->isEditable())));
                    } else {
                        return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Teacher::loadGroupsOfPeriodAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    public function loadGroupSpecialQualificationsAction(){
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

                if( isset($groupId) && isset($periodId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $dql = "SELECT cc FROM TecnotekExpedienteBundle:CourseClass cc "
                        . " WHERE cc.period = " . $periodId . " AND cc.grade = " . $gradeId;
                    $query = $em->createQuery($dql);
                    $courseClass = $query->getResult();
                    $temp = new \Tecnotek\ExpedienteBundle\Entity\CourseClass();
                    $html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
                    $html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';
                    $html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;"></div></td>';
                    $html .= '<td  style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;"></div></td>';

                    $marginLeft = 48;
                    $marginLeftCode = 62;
                    $htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
                    $htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
                    $htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;"></td>';
                    $htmlCodes .= '<td  style="color: #fff;"></td>';
                    $jumpRight = 46;
                    $width = 44;

                    $html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="width: 75px; font-size: 12px;">Carne</td>';
                    $html3 .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">Estudiante</td>';
                    $html3 .= '<td class="azul headcoltrim" style="color: #fff;"></td>';
                    $html3 .= '<td  style="color: #fff;"></td>';
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

                    $htmlCodes .= "</tr>";
                    $html .= "</tr>";
                    $html3 .= "</tr>";
                    $html = '<table class="tableQualifications">' . $html3;

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

                        foreach($courseClass as $ccs){
                            $row2 =  $ccs->getID();
                        }


                        $dql = "SELECT obs FROM TecnotekExpedienteBundle:Observation obs"
                            . " WHERE obs.studentYear = " . $stdy->getId()
                            . " AND obs.courseClass = ".$row2;
                        //. " AND obs.teacher = ".$user_id; //falta capturar el id del profesor
                        $query = $em->createQuery($dql);
                        $observations = $query->getResult();
                        foreach($observations as $observation){
                            $row =  $observation->getDetail();
                        }


                        $html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>';
                        $html .=  '<td id="obser_' . $stdy->getStudent()->getId() . '"  style="color: #000; width: 1600px">'
                            . '<input type="button" class="specialQualificationsButton" style="width: 540px"'
                            . ' stdyId="' . $stdy->getId() . '" value ="Ingresar Calificaciones"></td></tr>';
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

    public function loadStudentSpecialQualificationsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $stdyId = $request->get('stdyId');

                $translator = $this->get("translator");

                if( isset($stdyId)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $stdy = $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($stdyId);
                    $grade = $stdy->getGroup()->getGrade();

                    $dql = "SELECT obs FROM TecnotekExpedienteBundle:SpecialQualificationsForm obs"
                        . " WHERE obs.grade = " . $grade->getId();
                    $query = $em->createQuery($dql);
                    $forms = $query->getResult();

                    $html = "<table>";
                    $dql = "SELECT obs FROM TecnotekExpedienteBundle:SpecialQualificationResult obs"
                        . " WHERE obs.studentYear = " . $stdyId;
                    $query = $em->createQuery($dql);
                    $results = $query->getResult();
                    $sqr = null;
                    $responses = array();
                    foreach($results as $r){
                        $responses[$stdyId . '-' . $r->getSpecialQualification()->getId() . "-" . $r->getPeriodNumber()]
                            = $r->getMainText();
                    }

                    foreach($forms as $form){
                        $html .= "<tr><td>" . $this->printSpecialQualificationForm($form, $stdyId, $responses)
                            . "</td></tr>";
                    }

                    $html .= "</table>";

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
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    private function printSpecialQualificationForm(
        \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationsForm $form, $stdyId, $responses){
        $html = "";
        switch($form->getEntryType()){
            case 1:
                $html .= "<table class='special-q-form-1'>";
                $html .= '<tr class="header">';
                $html .= '<td>' . $form->getName() . '</td>';
                $html .= ($form->getShowsOnPeriodOne())? '<td>I</td>':'';
                $html .= ($form->getShowsOnPeriodTwo())? '<td>II</td>':'';
                $html .= ($form->getShowsOnPeriodThree())? '<td>III</td>':'';
                $html .= "</tr>";
                foreach($form->getQuestions() as $q){
                    $html .= '<tr class="sq-row">';
                    $html .= '<td>' . $q->getMainText() . '</td>';
                    $html .= ($form->getShowsOnPeriodOne())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 1) .
                        '</td>':'';
                    $html .= ($form->getShowsOnPeriodTwo())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 2) . '</td>':'';
                    $html .= ($form->getShowsOnPeriodThree())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 3) . '</td>':'';
                    $html .= "</tr>";
                }
                $html .= "</table>";
                break;
            case 2:
                $html .= "<table class='special-q-form-1'>";
                $html .= '<tr class="header">';
                $html .= '<td>' . $form->getName() . '</td>';
                $html .= ($form->getShowsOnPeriodOne())? '<td>I</td>':'';
                $html .= ($form->getShowsOnPeriodTwo())? '<td>II</td>':'';
                $html .= ($form->getShowsOnPeriodThree())? '<td>III</td>':'';
                $html .= "</tr>";
                foreach($form->getQuestions() as $q){
                    $html .= '<tr class="sq-row">';
                    $html .= '<td>' . $q->getMainText() . '</td>';
                    $html .= ($form->getShowsOnPeriodOne())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 1) .
                        '</td>':'';
                    $html .= ($form->getShowsOnPeriodTwo())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 2) . '</td>':'';
                    $html .= ($form->getShowsOnPeriodThree())? '<td>'
                        . $this-> printSpecialQualificationOptions($q->getType(), $stdyId, $q->getId(), $responses, 3) . '</td>':'';
                    $html .= "</tr>";
                }
                $html .= "</table>";
                break;
            default:
                break;
        }
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

    public function saveStudentSpecialQualificationsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $stdyId = $request->get('stdyId');
                $sq = $request->get('sq');
                $pn = $request->get('pn');
                $value = $request->get('value');

                $translator = $this->get("translator");

                if( isset($stdyId) && isset($sq) && isset($pn) && isset($value)) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $dql = "SELECT obs FROM TecnotekExpedienteBundle:SpecialQualificationResult obs"
                        . " WHERE obs.specialQualification = " . $sq . " AND obs.studentYear = " . $stdyId .
                        " AND obs.periodNumber = " . $pn;
                    $query = $em->createQuery($dql);
                    $results = $query->getResult();
                    $sqr = null;
                    foreach($results as $r){
                        $sqr = $r;
                    }
                    if($sqr === null){
                        $sqr = new \Tecnotek\ExpedienteBundle\Entity\SpecialQualificationResult();
                        $sqr->setSpecialQualification(
                            $em->getRepository("TecnotekExpedienteBundle:SpecialQualification")->find($sq));
                        $sqr->setStudentYear(
                            $em->getRepository("TecnotekExpedienteBundle:StudentYear")->find($stdyId));
                        $sqr->setPeriodNumber($pn);
                    }
                    $sqr->setMainText($value);

                    $em->persist($sqr);
                    $em->flush();

                    return new Response(json_encode(array('error' => false)));
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
