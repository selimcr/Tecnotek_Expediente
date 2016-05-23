<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Tecnotek\ExpedienteBundle\Util\ExcelColumnDefinition;

class ExcelFileGeneratorController extends Controller {

    private function createExcelObjectFromFile($creator, $fileName) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($fileName);
        $phpExcelObject->getProperties()->setCreator($creator);
        $phpExcelObject->setActiveSheetIndex(0);
        return $phpExcelObject;
    }

    public function loadGroupExcelAction(Request $request) {
        $logger = $this->get('logger');
        $translator = $this->get("translator");
        $tempFileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
        $fileBag = $request->files;
        $validationFile = $request->get('periodId') . "::" . $request->get('groupId') . "::" . $request->get('courseId');
        $file = $fileBag->get("file");
        //var_dump($file);
        $file->move($tempFileDir, $file->getClientOriginalName());
        $filePath = $tempFileDir . "/" . $file->getClientOriginalName();
        // Open file
        $logger->err($filePath);
        $phpExcelObject = \PHPExcel_IOFactory::load($filePath);
        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $decodedValue = base64_decode($activeSheet->getCell("A1")->getValue());
        if ($validationFile == $decodedValue) {
            $responseInfo = $this->loadFileData($activeSheet);
            return new Response(json_encode(
                array('error' => false,
                    'message' =>$translator->trans("excel.file.load.success") . "\n" . $responseInfo,
                    'name' => $file->getClientOriginalName())));
        } else {
            return new Response(json_encode(
                array('error' => true, 'message' =>$translator->trans("excel.file.invalid.file"), 'name' => $file->getClientOriginalName())));
        }
    }

    public function loadFileData($activeSheet) {
        $logger = $this->get('logger');
        $row = 8;
        $initialColumn = 4;
        $columns = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        ,'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
        ,'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
        $stdId = $activeSheet->getCell("A$row")->getValue();
        while ($this->isValidStudentId($stdId)) {
            $logger->err("Student: " . $activeSheet->getCell("C$row")->getValue());
            $column = $initialColumn;
            $nextColumn = $this->getNextColumn($activeSheet, $columns, $column);
            while ($nextColumn != 0) {
                $column = $nextColumn;
                $qualificationId = $activeSheet->getCell("$columns[$column]4")->getValue();
                $qualificationValue = $activeSheet->getCell("$columns[$column]$row")->getValue();
                //$logger->err("Id[$qualificationId]: " . $qualificationValue);
                $this->saveQualification($stdId, $qualificationId, $qualificationValue);
                $column++;
                $nextColumn = $this->getNextColumn($activeSheet, $columns, $column);
            }
            // Move to the next row and find the stdId
            $row++;
            $stdId = $activeSheet->getCell("A$row")->getValue();
        }
    }

    public function saveQualification($stdId, $qualificationId, $qualificationValue) {
        if (!isset($qualificationValue) || trim($qualificationValue) == "" || !is_numeric($qualificationValue)) {
            $qualificationValue = -1;
        }
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getEntityManager();
        $studentQ = $em->getRepository("TecnotekExpedienteBundle:StudentQualification")
            ->findOneBy(array('subCourseEntry' => $qualificationId, 'studentYear' => $stdId));
        if ( isset($studentQ) ) {
            if ($studentQ->getQualification() == $qualificationValue) {
                return;
            }
            $studentQ->setQualification($qualificationValue);
        } else {
            $studentQ = new StudentQualification();
            $studentQ->setSubCourseEntry($em->getRepository("TecnotekExpedienteBundle:SubCourseEntry")
                ->find( $qualificationId ));
            $studentQ->setStudentYear($em->getRepository("TecnotekExpedienteBundle:StudentYear")
                ->find( $stdId ));
            $studentQ->setQualification($qualificationValue);
        }
        $logger->err("Updating/Creating StdYearId[$stdId][$qualificationId]: " . $qualificationValue);
        $em->persist($studentQ);
        $em->flush();
    }

    public function getNextColumn($activeSheet, $columns, $column) {
        $allowedEmptyColumns = 3;
        while ($allowedEmptyColumns > 0) {
            $qualificationId = $activeSheet->getCell("$columns[$column]4")->getValue();
            if ($this->isValidStudentId($qualificationId)) {
                return $column;
            }
            $column++;
            $allowedEmptyColumns--;
        }
        return 0;
    }

    public function isValidStudentId($value) {
        return (isset($value) && is_numeric($value));
    }

    public function generateGroupExcelAction() {
        $logger = $this->get('logger');
        $fileName = "Calificaciones.xlsx";
        $phpExcelObject = $this->createExcelObjectFromFile("Tecnotek", $fileName);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $excelRow = 7;
        $fileName = "";
        try {
            $request = $this->get('request');
            $periodId = $request->get('periodId');
            $groupId = $request->get('groupId');
            $courseId = $request->get('courseId');
            $fileValidationString = "$periodId::$groupId::$courseId";
            $fileValidationString = base64_encode($fileValidationString);
            $keywords = preg_split("/[\s-]+/", $groupId);
            $groupId = $keywords[0];
            $gradeId = $keywords[1];
            $translator = $this->get("translator");
            $headerInfo = "";
            if (isset($courseId) && isset($groupId) && isset($periodId)) {
                $em = $this->getDoctrine()->getEntityManager();
                $course = $em->getRepository("TecnotekExpedienteBundle:Course")->findOneBy(array('id' => $courseId));
                $group = $em->getRepository("TecnotekExpedienteBundle:Group")->findOneBy(array('id' => $groupId));
                $headerInfo .= "Periodo: " . $group->getPeriod()->getName() . " " . $group->getPeriod()->getYear() . "\n";
                $headerInfo .= "Grupo: " . $group->getGrade()->getName() . " :: " . $group->getName() . "\n";
                $headerInfo .= "Materia: " . $course->getName() . "\n";
                /*$headerInfo .= "Profesor(a): " . $group->getTeacher()->getFirstname() . " "
                    . $group->getTeacher()->getLastname() . "";*/
                $fileName = $group->getPeriod()->getName() . "_" . $group->getPeriod()->getYear() .
                    "_" . $group->getGrade()->getName() . "_" . $group->getName() . $course->getName() . ".xlsx";
                $fileName = preg_replace('/[[:^print:]]/', '', $fileName);
                $dql = "SELECT ce FROM TecnotekExpedienteBundle:CourseEntry ce "
                    . " JOIN ce.courseClass cc"
                    . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                    . " AND cc.course = " . $courseId
                    . " ORDER BY ce.sortOrder";
                $query = $em->createQuery($dql);
                $entries = $query->getResult();
                /*$dql = "SELECT ce.id, ce.name FROM TecnotekExpedienteBundle:CourseEntry ce "
                    . " JOIN ce.courseClass cc"
                    . " WHERE ce.parent IS NULL AND cc.period = " . $periodId . " AND cc.grade = " . $gradeId
                    . " AND cc.course = " . $courseId
                    . " ORDER BY ce.sortOrder";
                $query = $em->createQuery($dql);*/
                $dql = "SELECT stdy FROM TecnotekExpedienteBundle:Student std, TecnotekExpedienteBundle:StudentYear stdy "
                    . " WHERE stdy.student = std AND stdy.group = " . $groupId . " AND stdy.period = " . $periodId
                    . " ORDER BY std.lastname, std.firstname";
                $query = $em->createQuery($dql);
                $students = $query->getResult();
                $colsCounter = 1;
                $specialCounter = 1;
                $columns = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
                    ,'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
                    ,'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
                $excelColum = 4;
                $titlesRow = 6;
                $initialHeaderRow = 5;
                $studentsTitleRow = 7;
                $entriesRow = 4;
                $columnDefinitions = array();
                $trimFormula = "=0+";
                foreach( $entries as $entry ) {
                    $temp = $entry;
                    $childrens = $temp->getChildrens();
                    $size = sizeof($childrens);
                    if($size == 0) { //No child
                        //Find SubEntries
                        $dql = "SELECT ce FROM TecnotekExpedienteBundle:SubCourseEntry ce "
                            . " WHERE ce.parent = " . $temp->getId()  . " AND ce.group = " . $groupId
                            . " ORDER BY ce.sortOrder, ce.name";
                        $query = $em->createQuery($dql);
                        $subentries = $query->getResult();
                        $size = sizeof($subentries);
                        if($size > 1) {
                            $initialBlockColumn = $excelColum;
                            foreach( $subentries as $subentry ) {
                                $colsCounter++;
                                $this->copyBlueStudentFormatCell($activeSheet, $columns[$excelColum] . $initialHeaderRow);
                                $specialCounter++;
                                $this->copyVerticalBlueStudentFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                                $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $subentry->getName());
                                $activeSheet->setCellValue($columns[$excelColum] . $entriesRow, $subentry->getId());
                                $cDefinition = new ExcelColumnDefinition($subentry->getId(), $excelColum, ExcelColumnDefinition::TYPE_SINGLE_ENTRY,
                                    array(
                                        'entryId' => $entry->getId()
                                    ));
                                array_push($columnDefinitions, $cDefinition);
                                $excelColum++;
                            }
                            //$specialCounter++;
                            $this->copyBlueCarneFormatCell($activeSheet, $columns[$excelColum] . $initialHeaderRow);
                            $this->copyVerticalBlueCarneFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                            $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, 'Promedio ' . $temp->getName());
                            $cDefinition = new ExcelColumnDefinition(0, $excelColum, ExcelColumnDefinition::TYPE_AVERAGE,
                                array(
                                    'firstColumn' => $initialBlockColumn,
                                    'lastColumn' => $excelColum-1
                                ));
                            array_push($columnDefinitions, $cDefinition);
                            $excelColum++;
                            //$specialCounter++;
                            $this->copyMoradoFormatCell($activeSheet, $columns[$excelColum] . $initialHeaderRow);
                            $activeSheet->setCellValue($columns[$excelColum] . "5", $temp->getCode());
                            $this->copyVerticalMoradoFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                            $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $temp->getPercentage() . '% ' . $temp->getName());
                            $trimFormula .= "IF(".$columns[$excelColum] . 'ROW="-",0,' .$columns[$excelColum] . 'ROW)+';
                            $cDefinition = new ExcelColumnDefinition(0, $excelColum, ExcelColumnDefinition::TYPE_PERCENTAGE,
                                array(
                                    'percentage' => $temp->getPercentage()
                                ));
                            array_push($columnDefinitions, $cDefinition);
                            $activeSheet->mergeCells($columns[$initialBlockColumn] . $studentsTitleRow . ':' . $columns[$excelColum] . $studentsTitleRow);
                            $this->copyBlueStudentFormatCell($activeSheet, $columns[$initialBlockColumn] . $studentsTitleRow);
                            $activeSheet->setCellValue($columns[$initialBlockColumn] . $studentsTitleRow, $temp->getName());
                            $this->setThinBorderToRange($activeSheet, $columns[$initialBlockColumn] . $studentsTitleRow . ":" . $columns[$excelColum] . $studentsTitleRow);
                            $excelColum++;
                        } else {
                            if ($size == 1) {
                                $initialBlockColumn = $excelColum;
                                foreach( $subentries as $subentry ) {
                                    $colsCounter++;
                                    $this->copyBlueStudentFormatCell($activeSheet, $columns[$excelColum] . $initialHeaderRow);
                                    $specialCounter++;
                                    $this->copyVerticalBlueStudentFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                                    $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $subentry->getName());
                                    $activeSheet->setCellValue($columns[$excelColum] . $entriesRow, $subentry->getId());
                                    $cDefinition = new ExcelColumnDefinition($subentry->getId(), $excelColum, ExcelColumnDefinition::TYPE_SINGLE_ENTRY,
                                        array(
                                            'entryId' => $entry->getId()
                                    ));
                                    array_push($columnDefinitions, $cDefinition);
                                    $excelColum++;
                                }
                                /* Copy Header of Purple Column, Percentage Column */
                                $this->copyMoradoFormatCell($activeSheet, $columns[$excelColum] . $initialHeaderRow);
                                $activeSheet->setCellValue($columns[$excelColum] . "5", $temp->getCode());
                                //$specialCounter++;
                                $this->copyVerticalMoradoFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                                $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $temp->getPercentage() . '% ' . $temp->getName());
                                $trimFormula .= "IF(".$columns[$excelColum] . 'ROW="-",0,' .$columns[$excelColum] . 'ROW)+';
                                $cDefinition = new ExcelColumnDefinition(0, $excelColum, ExcelColumnDefinition::TYPE_PERCENTAGE,
                                    array(
                                        'percentage' => $temp->getPercentage()
                                    ));
                                array_push($columnDefinitions, $cDefinition);
                                $activeSheet->mergeCells($columns[$initialBlockColumn] . $studentsTitleRow . ':' . $columns[$excelColum] . $studentsTitleRow);
                                $activeSheet->setCellValue($columns[$initialBlockColumn] . $studentsTitleRow, $temp->getName());
                                $this->copyBlueStudentFormatCell($activeSheet, $columns[$initialBlockColumn] . $studentsTitleRow);
                                $this->setThinBorderToRange($activeSheet, $columns[$initialBlockColumn] . $studentsTitleRow . ":" . $columns[$excelColum] . $studentsTitleRow);
                                $excelColum++;
                            }
                        }
                    }
                }
                $trimFormula .= "0";
                $excelRow = 8;
                foreach($students as $stdy) {
                    $dql = "SELECT qua FROM TecnotekExpedienteBundle:StudentQualification qua"
                        . " WHERE qua.studentYear = " . $stdy->getId();
                    $query = $em->createQuery($dql);
                    $qualifications = $query->getResult();
                    $qualificationsArray = array();
                    foreach ($qualifications as $qualification){
                        $qualificationsArray[$qualification->getSubCourseEntry()->getId()] = $qualification->getQualification();
                    }
                    $this->copyStudentRowFormat($activeSheet, $excelRow);
                    $phpExcelObject->setActiveSheetIndex(0)
                        ->setCellValue('A' . $excelRow, $stdy->getId())
                        ->setCellValue('B' . $excelRow, $stdy->getStudent()->getCarne())
                        ->setCellValue('C' . $excelRow, $stdy->getStudent())
                        ->setCellValue('D' . $excelRow, str_replace("ROW", $excelRow, $trimFormula));
                    // Copy cells with formulas and others
                    foreach ($columnDefinitions as $columnDefinition) {
                        //$columnDefinition =  new ExcelColumnDefinition();
                        $column = $columnDefinition->getColumn();
                        switch ($columnDefinition->getType()) {
                            case ExcelColumnDefinition::TYPE_SINGLE_ENTRY:
                                $this->copyBlueStudentNumberFormatCell($activeSheet, $columns[$columnDefinition->getColumn()] . $excelRow);
                                $value = "";
                                if (array_key_exists($columnDefinition->getEntryId(), $qualificationsArray)) {
                                    $value = $qualificationsArray[$columnDefinition->getEntryId()];
                                }
                                $activeSheet->setCellValue($columns[$columnDefinition->getColumn()] . $excelRow, $value);
                                break;
                            case ExcelColumnDefinition::TYPE_PERCENTAGE:
                                $this->copyMoradoNumberFormatCell($activeSheet, $columns[$columnDefinition->getColumn()] . $excelRow);
                                $config = $columnDefinition->getConfig();
                                $activeSheet->setCellValue($columns[$column] . $excelRow,
                                    '=IF(' . $columns[$column-1] . $excelRow . '="","-",(' . $columns[$column-1]
                                    . $excelRow . '*' . $config['percentage'] . ')/100)');
                                break;
                            case ExcelColumnDefinition::TYPE_AVERAGE:
                                $this->copyBlueCarneNumberFormatCell($activeSheet, $columns[$columnDefinition->getColumn()] . $excelRow);
                                $config = $columnDefinition->getConfig();
                                $activeSheet->setCellValue($columns[$column] . $excelRow, '=SUM(' .
                                    $columns[$config['firstColumn']] . $excelRow .
                                    ':' . $columns[$config['lastColumn']] . $excelRow . ')/' . ($config['lastColumn']-$config['firstColumn']+1));
                                break;
                            default:
                                $activeSheet->setCellValue($columns[$columnDefinition->getColumn()] . $excelRow, "-");
                                break;
                        }
                    }
                    $excelRow++;
                }
                $activeSheet->setCellValue("B4", $headerInfo);
                $activeSheet->setCellValue("A1", $fileValidationString);
            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Teacher::loadEntriesByCourseAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
        $phpExcelObject->getActiveSheet()->getProtection()->setSheet(true);
        $phpExcelObject->getActiveSheet()->getProtection()->setSort(true);
        $phpExcelObject->getActiveSheet()->getProtection()->setInsertRows(true);
        $phpExcelObject->getActiveSheet()->getProtection()->setFormatCells(true);
        $phpExcelObject->getActiveSheet()->getProtection()->setPassword('123');
        return $this->responseExcelFile($phpExcelObject, $fileName);
    }

    private function copyStudentRowFormat($activeSheet, $row) {
        $this->copyBlueCarneFormatCell($activeSheet, "B$row");
        $this->copyBlueStudentFormatCell($activeSheet, "C$row");
        $this->copyBlueTrimFormatCell($activeSheet, "D$row");
    }

    private function setThinBorderToRange($activeSheet, $range) {
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $activeSheet->getStyle($range)->applyFromArray($styleArray);
        unset($styleArray);
    }
    /* NUMBER FORMATS */
    private function copyBlueTrimNumberFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "J1", $destinationCell);
    }
    private function copyMoradoNumberFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "M1", $destinationCell);
    }
    private function copyBlueCarneNumberFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "L1", $destinationCell);
    }
    private function copyBlueStudentNumberFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "K1", $destinationCell);
    }
    /**/
    private function copyMoradoFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "G1", $destinationCell);
    }
    private function copyBlueCarneFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "B1", $destinationCell);
    }
    private function copyBlueStudentFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "C1", $destinationCell);
    }
    private function copyBlueTrimFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "D1", $destinationCell);
    }
    private function copyVerticalBlueStudentFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "E3", $destinationCell);
    }
    private function copyVerticalBlueCarneFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "F3", $destinationCell);
    }
    private function copyVerticalMoradoFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "G3", $destinationCell);
    }
    private function copyFormat($activeSheet, $sourceCell, $destinationCell) {
        $activeSheet->duplicateStyle($activeSheet->getStyle($sourceCell),$destinationCell);
    }

    private function responseExcelFile($phpExcelObject, $fileName) {
        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }
}