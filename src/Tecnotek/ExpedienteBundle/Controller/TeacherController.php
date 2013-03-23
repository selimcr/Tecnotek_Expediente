<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\CourseEntry;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
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

                    //Get Groups
                    $sql = "SELECT CONCAT(g.id,'-',grade.id) as 'id', CONCAT(grade.name, ' :: ', g.name) as 'name'" .
                        " FROM tek_groups g, tek_assigned_teachers tat, tek_grades grade" .
                        " WHERE g.period_id = " . $periodId . " AND tat.group_id = g.id AND grade.id = g.grade_id AND tat.user_id = "  . $user->getId() .
                        " GROUP BY g.id" .
                        " ORDER BY g.name";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $groups = $stmt->fetchAll();

                    //$groups = $em->getRepository("TecnotekExpedienteBundle:Group")->findBy(array('period' => $periodId));

                    return new Response(json_encode(array('error' => false, 'groups' => $groups)));
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
                    $html = '<div class="itemPromedioPeriodo itemHeader" style="margin-left: 4px; color: #fff;">Promedio Trimestral</div>';
                    /*
                        <div class="itemHeader itemNota" style="margin-left: 125px;">Tarea 2</div>
                        <div class="itemHeader itemPromedio" style="margin-left:150px;">Promedio Tareas </div>
                        <div class="itemHeader itemPorcentage" style="margin-left: 175px;">10 % Tarea</div>

                    <div class="itemHeaderCode itemNota" style="margin-left: 0px;"></div>
                    */
                    $marginLeft = 48;
                    $marginLeftCode = 62;
                    $htmlCodes = '<div class="itemPromedioPeriodo itemHeaderCode" style="color: #fff;">SCIE</div>';
                    $jumpRight = 46;
                    $width = 44;
                    $html3 = '<div class="itemHeader2 itemPromedioPeriodo" style="width: 40px; color: #fff;">TRIM</div>';
                    $studentRow = "";
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
                                    //$studentRow .= '<input type="text" class="textField itemNota" tipo="2" rel="total_' . $subentry->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId" entry="' . $subentry->getId() . '"  stdyId="stdyIdd">';
                                    $studentRow .= '<input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd">';
                                    $colsCounter++;
                                    $htmlCodes .= '<div class="itemHeaderCode itemNota codeNota"></div>';
                                    $specialCounter++;
                                    $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $subentry->getName() . '</div>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                $studentRow .= '<div class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</div>';
                                $htmlCodes .= '<div class="itemHeaderCode itemPromedio codePromedio"></div>';
                                $specialCounter++;
                                $html .= '<div class="itemHeader itemPromedio" style="margin-left:' . $marginLeft . 'px;">Promedio ' . $temp->getName() . ' </div>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                                $htmlCodes .= '<div class="itemHeaderCode itemPorcentage codePorcentage">' . $temp->getCode() . '</div>';
                                $specialCounter++;
                                $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;

                                // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                                $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries) + 2)) + ((sizeof($subentries) + 1) * 2)) . 'px">' . $temp->getName() . '</div>';
                            } else {
                                if($size == 1){
                                    foreach( $subentries as $subentry )
                                    {
                                        $studentRow .= '<input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd">';
                                        $colsCounter++;
                                        $htmlCodes .= '<div class="itemHeaderCode itemNota codeNota"></div>';
                                        $specialCounter++;
                                        $html .= '<div class="itemHeader itemNota" style="margin-left: ' . $marginLeft . 'px;">' . $subentry->getName() . '</div>';
                                        $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    }

                                    $studentRow .= '<div id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</div>';
                                    $htmlCodes .= '<div class="itemHeaderCode itemPorcentage codePorcentage">' . $temp->getCode() . '</div>';
                                    $specialCounter++;
                                    $html .= '<div class="itemHeader itemPorcentage" style="margin-left: ' . $marginLeft . 'px;">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                    $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * 2) + ((sizeof($subentries)) * 2)) . 'px">' . $temp->getName() . '</div>';
                                }
                            }


                        } else {
                            /*if($size == 1){//one child
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
                            }*/
                        }
                    }

                    $html = $htmlCodes . '<div class="clear"></div>' .
                        '<div style="position: relative; height: 152px; margin-left: -59px;">' . $html . '</div>' . '<div class="clear"></div>' .
                        $html3;


                    //$students = $em->getRepository("TecnotekExpedienteBundle:Student")->findAll();
                    $studentRowIndex = 0;
                    foreach($students as $stdy){
                        $studentRowIndex++;
                        $studentsHeader .= '<div class="itemCarne">' . $stdy->getStudent()->getCarne() . '</div><div class="itemEstudiante">' . $stdy->getStudent()->getLastname() . ", " . $stdy->getStudent()->getFirstname() . '</div><div class="clear"></div>';
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
                        $html .=  '<div class="clear"></div><div id="total_trim_' . $stdy->getStudent()->getId() . '" class="itemHeaderCode itemPromedioPeriodo"style="color: #fff;">-</div>' . $row;
                    }

                    return new Response(json_encode(array('error' => false, 'html' => $html, 'studentsHeader' => $studentsHeader, "studentsCounter" => $studentsCount, "codesCounter" => $specialCounter)));
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
}
