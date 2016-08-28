<?php
/**
 * Created by PhpStorm.
 * User: selim
 * Date: 8/27/2016
 * Time: 11:42 AM
 */

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tecnotek\ExpedienteBundle\Entity\PeriodMigration;

class PeriodController extends BaseController
{

    public function listPeriodMigrationsAction($rowsPerPage = 10) {
        return $this->renderPeriodMigrationList($rowsPerPage, "");
    }

    public function managePeriodMigrationsAction($id = 0) {
        $em = $this->getDoctrine()->getEntityManager();
        $trans = $this->get("translator");
        $entity =  new PeriodMigration();
        $sourcePeriod = null;
        $destinationPeriod = null;
        $errorMsg = "";
        if (isset($id) && $id !== 0) {
            $entity = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")->find($id);
            $sourcePeriod = $entity->getSourcePeriod();
            $destinationPeriod = $entity->getDestinationPeriod();
        } else {
            $id = 0;
            $sourcePeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(array("isActual" => 1));
            if (isset($sourcePeriod)) {
                $orderOfNextPeriod = $sourcePeriod->getOrderInYear() + 1;
                $yearOfNextPeriod = $sourcePeriod->getYear();
                if ($sourcePeriod->getOrderInYear() === 3) { //Last period of year
                    $orderOfNextPeriod = 1;
                    $yearOfNextPeriod = $yearOfNextPeriod + 1;
                }
                $destinationPeriod = $em->getRepository("TecnotekExpedienteBundle:Period")->findOneBy(
                    array("year" => $yearOfNextPeriod, "orderInYear" => $orderOfNextPeriod));
                if (!isset($destinationPeriod)) {
                    $errorMsg = $trans->trans('period.migration.error.no.next.period');
                }
            } else {
                $errorMsg = $trans->trans('period.migration.error.no.actual');
            }
        }
        if ($errorMsg === "") {
            $canExecute = $this->canUseThisMigration($entity);
            $logger = $this->get("logger");
            $logger->err("StepsStatus: " . $entity->getMigrationSteps() . " [" . json_decode($entity->getMigrationSteps(), true));
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/migration_manage.html.twig',
                array(
                    'entity' => $entity,
                    'migrationId' => $id,
                    'sourcePeriod' => $sourcePeriod,
                    'destinationPeriod' => $destinationPeriod,
                    'stepsStatus' => json_decode($entity->getMigrationSteps(), true),
                    'canExecute' => $canExecute,
                ));
        } else {
            return $this->renderPeriodMigrationList(10, $errorMsg);
        }
    }

    public function executePeriodMigrationStepAction() {
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        $logger = $this->get('logger');
        try {
            $request = $this->get('request')->request;
            $migrationId = $request->get('migrationId');
            $step = $request->get('step');
            $translator = $this->get("translator");
            // Validate Parameters
            if( isset($migrationId) && isset($step) && strlen(trim($migrationId)) > 0 && strlen(trim($step)) > 0) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $migration = new PeriodMigration();
                    if ($migrationId != 0) {//New Group
                        $migration = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")->find($migrationId);
                    } else {
                        $periodSourceId = $request->get('periodSourceId');
                        $periodDestinationId = $request->get('periodDestinationId');
                        $migration->setSourcePeriod($em->getRepository("TecnotekExpedienteBundle:Period")->find($periodSourceId));
                        $migration->setDestinationPeriod($em->getRepository("TecnotekExpedienteBundle:Period")->find($periodDestinationId));
                    }
                    if ($this->canUseThisMigration($migration)) {
                        $this->executeStepMigration($migration, $step);
                        $em->persist($migration);
                        $em->flush();
                        return new Response(json_encode(array(
                            'error' => false,
                            'migrationId' => $migration->getId())));
                    } else {
                        return new Response(json_encode(array(
                            'error' => true,
                            'message' => "Ya no es posible ejecutar pasos en esta migración")));
                    }

            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::executePeriodMigrationStepAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    private function executeStepMigration(PeriodMigration $migration, $step) {
        $step = $step * 1;
        $em = $this->getDoctrine()->getEntityManager();
        $stepsDetail = json_decode($migration->getMigrationSteps(), true);
        switch ($step) {
            case 1:
                $numberOfCreatedGroups = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                $migration->setStatus(2);
                $stepsDetail["G"] = $numberOfCreatedGroups;
                break;
            case 2:
                $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                break;
            case 3:
                $numberOfCourseClass = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                $migration->setStatus(3);
                $stepsDetail["CC"] = $numberOfCourseClass;
                break;
            case 4:
                $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                break;
            case 5:
                $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                break;
            case 6:
                $numberOfCourseEntries = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                $stepsDetail["CE"] = $numberOfCourseEntries;
                $migration->setStatus(4);
                break;
            case 7:
                $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")
                    ->executeStep($step, $migration->getSourcePeriod(), $migration->getDestinationPeriod(), $stepsDetail);
                $migration->setStatus(5);
                break;
            default: // Nothing to do
                return;
        }
        $stepsDetail["" . $step] = 1;
        $migration->setMigrationSteps(json_encode($stepsDetail, true));
    }

    private function renderPeriodMigrationList($rowsPerPage, $errorMsg) {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = "SELECT m FROM TecnotekExpedienteBundle:PeriodMigration m";
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');
        $rowsPerPage = (isset($param) && $param != "") ? $param : $rowsPerPage;

        $dql2 = "SELECT count(m) FROM TecnotekExpedienteBundle:PeriodMigration m";
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Period/migration_list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 5,
            'errorMessage' => $errorMsg
        ));
    }

    private function canUseThisMigration(PeriodMigration $migration) {
        $em = $this->getDoctrine()->getEntityManager();
        $lastMigration = $em->getRepository("TecnotekExpedienteBundle:PeriodMigration")->findLastMigrationInProgress();
        if (isset($lastMigration)) { // If exists one must be the same
            return ($lastMigration->getId() === $migration->getId());
        } else {
            return true;
        }
    }
}