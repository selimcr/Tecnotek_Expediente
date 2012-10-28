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
        if( $route != 0){
            $where .= ($where=="")? " e.route = $route":" AND e.route = $route";
        }

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

    public function reportZonesAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository("TecnotekExpedienteBundle:Zone")->findAll();
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Reports/zone.html.twig', array('menuIndex' => 4,
            'entities' => $entities
        ));
    }
}
