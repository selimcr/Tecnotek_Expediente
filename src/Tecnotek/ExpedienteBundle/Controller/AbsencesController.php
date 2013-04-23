<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\AbsenceType;
use Tecnotek\ExpedienteBundle\Entity\AbsenceTypePoints;
use Tecnotek\ExpedienteBundle\Entity\Institution;
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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AbsencesController extends Controller
{
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

    function startsWith($haystack, $needle) {
        return (strpos($haystack, $needle) === 0);
    }

    /* Metodos para CRUD de Absence Types */
    public function absencesTypeListAction($rowsPerPage = 25)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT entity FROM TecnotekExpedienteBundle:AbsenceType entity";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(entity) FROM TecnotekExpedienteBundle:AbsenceType entity";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 5
        ));
    }

    public function absencesTypeCreateAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $institutions = $em->getRepository("TecnotekExpedienteBundle:Institution")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/new.html.twig', array('institutions' => $institutions, 'menuIndex' => 5));
    }

    public function absencesTypeSaveAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $name = $request->get('name');

        if(!isset($name) || trim($name) == ""){
            $error = "Este valor no debería estar vacío";
            $institutions = $em->getRepository("TecnotekExpedienteBundle:Institution")->findAll();
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/new.html.twig', array('institutions' => $institutions,
                'error' => $error, 'menuIndex' => 5));
        } else {
            $absenceType = new AbsenceType();
            $absenceType->setName($name);
            $em->persist($absenceType);
            $em->flush();

            $params = $this->getRequest()->request->keys();
            foreach ($params as $param) {
                if($this->startsWith($param, 'institution_')){
                    $pos = strrpos($param, "_") + 1;
                    $id = substr($param, $pos);
                    $absenceTypePoints = new AbsenceTypePoints();
                    $absenceTypePoints->setAbsenceType($absenceType);
                    $absenceTypePoints->setInstitution($em->getRepository("TecnotekExpedienteBundle:Institution")->find($id));
                    $absenceTypePoints->setPoints($request->get($param));
                    $em->persist($absenceTypePoints);
                }
            }
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_absenceType',
                array('id' => $absenceType->getId(), 'menuIndex' => 5)));
        }
    }

    public function absencesTypeShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find($id);

        $dql = "SELECT ins, " .
            " (SELECT p.points FROM TecnotekExpedienteBundle:AbsenceTypePoints p WHERE p.institution = ins AND p.absenceType = $id) as point" .
            " FROM TecnotekExpedienteBundle:Institution ins " .
            " "; //ORDER BY ins.name
        $query = $em->createQuery($dql);
        $institutions = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/show.html.twig', array('entity' => $entity,
            'institutions'   => $institutions, 'menuIndex' => 5));
    }

    public function absencesTypeEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find($id);

        $dql = "SELECT ins, " .
            " (SELECT p.points FROM TecnotekExpedienteBundle:AbsenceTypePoints p WHERE p.institution = ins AND p.absenceType = $id) as point" .
            " FROM TecnotekExpedienteBundle:Institution ins " ;
        $query = $em->createQuery($dql);
        $institutions = $query->getResult();

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/edit.html.twig', array('entity' => $entity,
            'institutions'   => $institutions, 'menuIndex' => 5));
    }

    public function absencesTypeUpdateAction(){
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $absenceType = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find($request->get('id'));

        if ( isset($absenceType) ) {
            $name = $request->get('name');
            if(!isset($name) || trim($name) == ""){
                $error = "Este valor no debería estar vacío";
                $dql = "SELECT ins, " .
                    " (SELECT p.points FROM TecnotekExpedienteBundle:AbsenceTypePoints p WHERE p.institution = ins AND p.absenceType = $absenceType) as point" .
                    " FROM TecnotekExpedienteBundle:Institution ins " ;
                $query = $em->createQuery($dql);
                $institutions = $query->getResult();

                return $this->render('TecnotekExpedienteBundle:SuperAdmin:AbsencesType/edit.html.twig', array('entity' => $absenceType,
                    'institutions'   => $institutions, 'menuIndex' => 5, 'error' => $error));
            } else {
                $absenceType->setName($name);
                $em->persist($absenceType);

                $params = $this->getRequest()->request->keys();
                foreach ($params as $param) {
                    if($this->startsWith($param, 'institution_')){
                        $pos = strrpos($param, "_") + 1;
                        $id = substr($param, $pos);

                        $absenceTypePoints = $em->getRepository("TecnotekExpedienteBundle:AbsenceTypePoints")->findOneBy(array('absenceType' => $absenceType->getId(), 'institution' => $id));

                        if ( !isset($absenceTypePoints) ) {
                            $absenceTypePoints = new AbsenceTypePoints();
                            $absenceTypePoints->setAbsenceType($absenceType);
                            $absenceTypePoints->setInstitution($em->getRepository("TecnotekExpedienteBundle:Institution")->find($id));
                        }

                        $absenceTypePoints->setPoints($request->get($param));
                        $em->persist($absenceTypePoints);
                    }
                }
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_absenceType_show_simple') . "/" . $absenceType->getId());
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_absenceType'));
        }
    }

    public function absencesTypeDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:AbsenceType")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_absenceType'));
    }


    /* Final de los metodos para CRUD de Absences Type */

}
