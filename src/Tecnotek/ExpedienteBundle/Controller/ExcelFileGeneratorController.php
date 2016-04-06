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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

class ExcelFileGeneratorController extends Controller {

    private function createExcelObjectFromFile($creator, $fileName) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($fileName);
        $phpExcelObject->getProperties()->setCreator($creator);
        $phpExcelObject->setActiveSheetIndex(0);
        return $phpExcelObject;
    }
    public function generateGroupExcelAction() {
        $logger = $this->get('logger');
        $fileName = "Calificaciones.xlsx";
        $phpExcelObject = $this->createExcelObjectFromFile("Tecnotek", $fileName);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $excelRow = 7;
        try {
            /*$request = $this->get('request')->request;
            $periodId = $request->get('periodId');
            $groupId = $request->get('groupId');*/

            $request = $this->get('request')->request;
            $periodId = 4;
            $groupId = '117-6';

            $keywords = preg_split("/[\s-]+/", $groupId);
            $groupId = $keywords[0];
            $gradeId = $keywords[1];
            //$courseId = $request->get('courseId');
            $courseId = 3;
/*periodId:4
courseId:3
groupId:117-6*/

            $translator = $this->get("translator");

            if (isset($courseId) && isset($groupId) && isset($periodId)) {
                $em = $this->getDoctrine()->getEntityManager();
                $grade = $em->getRepository("TecnotekExpedienteBundle:Grade")->findOneBy(array('id' => $gradeId));

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
                //$html =  '<tr  style="height: 175px; line-height: 0px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px; height: 175px;"></td>';
                //$html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px; height: 175px;"></td>';
                //$html .= '<td class="azul headcoltrim" style="vertical-align: bottom; padding: 0.5625em 0.625em; height: 175px; line-height: 220px;"><div class="verticalText" style="color: #fff;">Promedio Trimestral</div></td>';

                $marginLeft = 48;
                $marginLeftCode = 62;
                //$htmlCodes =  '<tr  style="height: 30px;"><td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;"></td>';
                //$htmlCodes .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 8px;"></td>';
                //$htmlCodes .= '<td class="azul headcoltrim" style="color: #fff;">&nbsp;</td>';
                $jumpRight = 46;
                $width = 44;

                //$html3 =  '<tr style="height: 30px; line-height: 0px;" class="noPrint"><td class="celesteOscuro bold headcolcarne" style="width: 75px; font-size: 12px;">Carne</td>';
                //$html3 .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">Estudiante</td>';
                //$html3 .= '<td class="azul headcoltrim" style="color: #fff;">TRIM</td>';
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

                $columns = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
                    ,'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
                    ,'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
                $excelColum = 3;
                $titlesRow = 6;
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
                            foreach( $subentries as $subentry ) {

                                //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' .
                                // $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '"
                                // parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '"
                                // perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';

                                //$studentRow .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="2" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" max="' . $subentry->getMaxValue() . '" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                $colsCounter++;
                                //$htmlCodes .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                                $this->copyBlueStudentFormatCell($activeSheet, $columns[$excelColum] . 4);
                                //$activeSheet->setCellValue($columns[$excelColum] . 4, $subentry->getName());

                                $specialCounter++;
                                //$html .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"
                                // style="vertical-align: bottom; padding: 0.5625em 0.625em;">
                                //<div class="verticalText">' . $subentry->getName() . '</div></td>';
                                $this->copyVerticalBlueStudentFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                                $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $subentry->getName());
                                $excelColum++;
                                //$marginLeft += $jumpRight; $marginLeftCode += 25;
                            }

                            //$studentRow .= '<td class="itemHeaderCode itemPromedio" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            //$studentRow .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '" id="prom_' . $temp->getId() . '_stdId" perc="' . $temp->getPercentage() . '">-</td>';
                            //$htmlCodes .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                            $specialCounter++;
                            //$html .= '<td class="celesteOscuro' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;">
                            //div class="verticalText">Promedio ' . $temp->getName() . ' </div></td>';
                            $this->copyVerticalBlueCarneFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                            $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, 'Promedio ' . $temp->getName());
                            $excelColum++;
                            //$marginLeft += $jumpRight; $marginLeftCode += 25;

                            //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                            //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId' . ' entryBase entryB_' . $entry->getId() . '_' . '">-</td>';
                            //$htmlCodes .= '<td class="morado bold' . ' entryBase entryB_' . $entry->getId() . '_' . '">' . $temp->getCode() . '</td>';
                            $specialCounter++;
                            //$html .= '<td class="morado' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;">
                            //<div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                            $this->copyVerticalMoradoFormatCell($activeSheet, $columns[$excelColum] . $titlesRow);
                            $activeSheet->setCellValue($columns[$excelColum] . $titlesRow, $temp->getPercentage() . '% ' . $temp->getName());
                            $excelColum++;
                            $marginLeft += $jumpRight; $marginLeftCode += 25;

                            // $html3 .= '<div class="itemHeader2 itemNota" style="width: ' . (($width * (sizeof($subentries)+1)) + ((sizeof($subentries)) * 2) ) . 'px">' . $temp->getName() . '</div>';
                            //$html3 .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" colspan="' .
                            // (sizeof($subentries)+2) . '">' . $temp->getName() . '</td>';
                        } else {
                            if($size == 1){
                                foreach( $subentries as $subentry )
                                {
                                    //$studentRow .= '<td class=""><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></td>';
                                    //$studentRow .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"><div><input tabIndex=tabIndexCol'. $colsCounter . 'x type="text" class="textField itemNota item_' . $temp->getId() . '_stdId" val="val_stdId_' . $subentry->getId() .  '_" tipo="1"  max="' . $subentry->getMaxValue() . '" child="' . $size . '" parent="' . $temp->getId() . '" rel="total_' . $temp->getId() . '_stdId" perc="' . $subentry->getPercentage() . '" std="stdId"  entry="' . $subentry->getId() . '"  stdyId="stdyIdd"></input></div></td>';
                                    $colsCounter++;
                                    //$htmlCodes .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '"></td>';
                                    $specialCounter++;
                                    //$html .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $subentry->getName() . '</div></td>';
                                    $marginLeft += $jumpRight; $marginLeftCode += 25;
                                }

                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="itemHeaderCode itemPorcentage nota_stdId">-</td>';
                                //$studentRow .= '<td id="total_' . $temp->getId() . '_stdId" class="morado bold nota_stdId' . ' entryBase entryB_' . $entry->getId() . '_' . '">-</td>';
                                //$htmlCodes .= '<td class="morado bold' . ' entryBase entryB_' . $entry->getId() . '_' . '">' . $temp->getCode() . '</td>';
                                $specialCounter++;
                                //$html .= '<td class="morado' . ' entryBase entryB_' . $entry->getId() . '_' . '" style="vertical-align: bottom; padding: 0.5625em 0.625em;"><div class="verticalText">' . $temp->getPercentage() . '% ' . $temp->getName() . '</div></td>';
                                $marginLeft += $jumpRight; $marginLeftCode += 25;
                                //$html3 .= '<td class="celesteClaro' . ' entryBase entryB_' . $entry->getId() . '_' . '" colspan="' . (sizeof($subentries)+1) . '">' . $temp->getName() . '</td>';
                            }
                        }


                    } else {
                    }
                }

                //$htmlCodes .= "</tr>";
                //$html .= "</tr>";
                //$html3 .= "</tr>";
                //$html = '<table class="tableQualifications">' . $htmlCodes . $html . $html3;

                $studentRowIndex = 0;
                $excelRow = 8;
                foreach($students as $stdy){
                    //$html .=  '<tr style="height: 30px; line-height: 0px;">';
                    $studentRowIndex++;
                    //$html .=  '<td class="celesteOscuro headcolcarne" style="width: 75px; font-size: 10px;">' . $stdy->getStudent()->getCarne() . '</td>';
                    //$html .=  '<td class="celesteClaro bold headcolnombre" style="width: 250px; font-size: 12px;">' . $stdy->getStudent() . '</td>';

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
                    //$html .=  '<td id="total_trim_' . $stdy->getStudent()->getId() . '" class="azul headcoltrim" style="color: #fff;">-</td>' . $row . "</tr>";
                    //$baseStyle = $phpExcelObject->getActiveSheet()->g‌etStyle('A4:F4');

                    $this->copyStudentRowFormat($activeSheet, $excelRow);
                    $phpExcelObject->setActiveSheetIndex(0)
                        ->setCellValue('A' . $excelRow, $stdy->getStudent()->getCarne())
                        ->setCellValue('B' . $excelRow, $stdy->getStudent());

                    $excelRow++;
                }

                //$html .= "</table>";

                $logger->err("courseEntries: " . sizeof($courseEntries) . " ---> " . json_encode($courseEntries));
                /*return new Response(json_encode(array('error' => false, 'html' => $html,
                    "notaMin" => $grade->getNotaMin(),
                    "studentsCounter" => $studentsCount,
                    "codesCounter" => $specialCounter,
                    "entries" => $courseEntries)));*/

            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Teacher::loadEntriesByCourseAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
        $fileName = "Calificaciones_2.xlsx";
        return $this->responseExcelFile($phpExcelObject, $fileName);
    }

    private function copyStudentRowFormat($activeSheet, $row) {
        $this->copyBlueCarneFormatCell($activeSheet, "A$row");
        $this->copyBlueStudentFormatCell($activeSheet, "B$row");
        $this->copyBlueTrimFormatCell($activeSheet, "C$row");
    }

    private function copyBlueCarneFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "A1", $destinationCell);
    }
    private function copyBlueStudentFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "B1", $destinationCell);
    }
    private function copyBlueTrimFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "C1", $destinationCell);
    }
    private function copyVerticalBlueStudentFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "D3", $destinationCell);
    }
    private function copyVerticalBlueCarneFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "E3", $destinationCell);
    }
    private function copyVerticalMoradoFormatCell($activeSheet, $destinationCell) {
        $this->copyFormat($activeSheet, "F3", $destinationCell);
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