<?php
/**
 * Created by PhpStorm.
 * User: selim
 * Date: 8/27/2016
 * Time: 11:42 AM
 */

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Tecnotek\ExpedienteBundle\Entity\TransportationTicketFromSite;

class ApiController extends Controller
{

    public function getAllStatesAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        $translator = $this->get("translator");
        $em = $this->getDoctrine()->getEntityManager();
        try {
            $states = $em->getRepository("TecnotekExpedienteBundle:State")->findAll();
            $statesArray = array();
            foreach ($states as $state) {
                array_push($statesArray, array(
                   "id" => $state->getId(),
                    "name"  => $state->getName()
                ));
            }
            return new Response(json_encode(array('code' => 200, 'states' => $statesArray)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::executePeriodMigrationStepAction [' . $info . "]");
            return new Response(json_encode(array('code' => 500, 'message' => $info)));
        }
    }

    public function getCantonesOfStateAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        try {
            $request = $this->getRequest();
            $stateId = $request->get("state");
            $state = $em->getRepository("TecnotekExpedienteBundle:State")->find($stateId);
            $cantonesArray = array();
            foreach ($state->getCantones() as $canton) {
                array_push($cantonesArray, array(
                    "id" => $canton->getId(),
                    "name"  => $canton->getName()
                ));
            }
            return new Response(json_encode(array('code' => 200, 'cantones' => $cantonesArray)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::executePeriodMigrationStepAction [' . $info . "]");
            return new Response(json_encode(array('code' => 500, 'message' => $info)));
        }
    }

    public function getDistrictsOfCantonAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        try {
            $request = $this->getRequest();
            $cantonId = $request->get("canton");
            $canton = $em->getRepository("TecnotekExpedienteBundle:Canton")->find($cantonId);
            $districtsArray = array();
            foreach ($canton->getDistricts() as $district) {
                array_push($districtsArray, array(
                    "id" => $district->getId(),
                    "name"  => $district->getName()
                ));
            }
            return new Response(json_encode(array('code' => 200, 'districts' => $districtsArray)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::getDistrictsOfCantonAction [' . $info . "]");
            return new Response(json_encode(array('code' => 500, 'message' => $info)));
        }
    }

    public function getStudentAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        $translator = $this->get("translator");
        $em = $this->getDoctrine()->getEntityManager();
        try {
            $request = $this->get('request')->request;
            $carne = $request->get('carne');
            if (isset($carne) && strlen(trim($carne)) > 0) {
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->findOneBy(array("carne" => $carne));
                if (isset($student)) {
                    return new Response(json_encode(array('code' => 200, 'full_name' => $student->__toString())));
                } else {
                    return new Response(json_encode(array('code' => 400, 'message' => 'El estudiante no fue encontrado')));
                }
            } else {
                return new Response(json_encode(array('code' => 400)));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::executePeriodMigrationStepAction [' . $info . "]");
            return new Response(json_encode(array('code' => 500, 'message' => $info)));
        }
    }

    public function saveTransportationTicketAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        $translator = $this->get("translator");
        $em = $this->getDoctrine()->getEntityManager();
        try {
            $request = $this->get('request')->request;
            $carne = $request->get('carne');
            $email = $request->get('email');
            $state = $request->get('state');
            $canton = $request->get('canton');
            $district = $request->get('district');
            $observations = $request->get('observations');
            $service = $request->get('service');
            $sendCopy = $request->get('send-copy');

            if (isset($carne) && strlen(trim($carne)) > 0
                && isset($email) && strlen(trim($email)) > 0) {
                $student = $em->getRepository("TecnotekExpedienteBundle:Student")->findOneBy(array("carne" => $carne));
                if (isset($student)) {
                    $ticket = new TransportationTicketFromSite();
                    $ticket->setStudent($student);
                    $ticket->setState($em->getRepository("TecnotekExpedienteBundle:State")->find($state));
                    $ticket->setCanton($em->getRepository("TecnotekExpedienteBundle:Canton")->find($canton));
                    $ticket->setDistrict($em->getRepository("TecnotekExpedienteBundle:District")->find($district));
                    $ticket->setDate(new \DateTime());
                    $ticket->setEmail($email);
                    $ticket->setObservations($observations);
                    $ticket->setService($service);
                    $em->persist($ticket);
                    $em->flush();
                    return new Response(json_encode(array('code' => 200, 'id' => $ticket->getId())));
                } else {
                    return new Response(json_encode(array('code' => 404, 'message' => 'No existe un estudiante con ese carné')));
                }
            } else {
                return new Response(json_encode(array('code' => 400)));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::executePeriodMigrationStepAction [' . $info . "]");
            return new Response(json_encode(array('code' => 500, 'message' => $info)));
        }
    }
}