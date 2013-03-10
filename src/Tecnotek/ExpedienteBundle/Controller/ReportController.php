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
        $entities = $em->getRepository("TecnotekExpedienteBundle:Route")->findAll();

        $html = "";

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/absences_by_route.html.twig', array('menuIndex' => 4,
            'entities' => $entities
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
}
