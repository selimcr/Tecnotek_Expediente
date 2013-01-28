<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\CourseEntry;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
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
                            $html .= '    <div class="clear"></div>';
                            $html .= '</div>';
                        }
                    }

                    $dql = "SELECT e FROM TecnotekExpedienteBundle:SubCourseEntry e" .
                        " JOIN e.parent p" .
                        " JOIN p.courseClass cc" .
                        " WHERE e.group = $groupId AND cc.course = $courseId ORDER BY e.sortOrder";
                    $query = $em->createQuery($dql);
                    $subentries = $query->getResult();
                    $html2 = "";
                    foreach( $subentries as $entry ){
                        $html2 .= '<div id="subentryRow_' . $entry->getId() . '" class="row userRow tableRowOdd">';
                        $html2 .= '    <div id="subentryNameField_' . $entry->getId() . '" name="subentryNameField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getName() . '</div>';
                        $html2 .= '    <div id="subentryCodeField_' . $entry->getId() . '" name="subentryCodeField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getCode() . '</div>';
                        $html2 .= '    <div id="subentryPercentageField_' . $entry->getId() . '" name="subentryPercentageField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getPercentage() . '</div>';
                        $html2 .= '    <div id="subentryMaxValueField_' . $entry->getId() . '" name="subentryMaxValueField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getMaxValue() . '</div>';
                        $html2 .= '    <div id="subentryOrderField_' . $entry->getId() . '" name="subentryOrderField_' . $entry->getId() . '" class="option_width" style="float: left; width: 100px;">' . $entry->getSortOrder() . '</div>';
                        $html2 .= '    <div id="subentryParentField_' . $entry->getId() . '" name="subentryParentField_' . $entry->getId() . '" class="option_width" style="float: left; width: 150px;">' . $entry->getParent() . '</div>';

                        $html2 .= '    <div class="right imageButton editButton editEntry" title="Editar" rel="' . $entry->getId() . '" entryParent="1"></div>';
                        $html2 .= '    <div class="clear"></div>';
                        $html2 .= '</div>';
                    }

                    return new Response(json_encode(array('error' => false, 'entries' => $entriesOptions, 'entriesHtml' => $html, 'subentriesHtml' => $html2, 'courseClassId' => $courseClassId)));
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

                    $courseEntry = new SubCourseEntry();
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
}
