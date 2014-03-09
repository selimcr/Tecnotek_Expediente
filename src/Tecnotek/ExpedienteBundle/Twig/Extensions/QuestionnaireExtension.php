<?php
namespace Tecnotek\ExpedienteBundle\Twig\Extensions;

/**
 *
 */
class QuestionnaireExtension extends \Twig_Extension
{
    private $em;
    private $conn;
    private $translator;

    public function __construct(\Doctrine\ORM\EntityManager $em, $translator) {
        $this->em = $em;
        $this->conn = $em->getConnection();
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return array(
            'include_raw' => new \Twig_Function_Method($this, 'twig_include_raw'),
            'print_package_box_raw' => new \Twig_Function_Method($this, 'print_package_box_raw'),
        );
    }

    public function twig_include_raw($file) {
        return file_get_contents($file);
    }

    /**
     * This methods prints a html box with the package information
     *
     * @param $package Package Entity to be printed
     *
     * @return string raw html of the generated box
     */
    public function print_package_box_raw($package, $width = 'six') {
        $html = '<div class="' . $width . ' columns">';
        $html .= '<div class="side-ad clearfix">';
        $html .= '<h3 class="package">' . $package->getShortTitle() . '</h3>';
            $html .= '<h4 class="pricing">' . $this->formatDolars($package->getAmount()) . ' <br /><span>' .
                $package->getPaymentMode() . '</span><br /></h4>';
            $html .= '<ul class="checklist">';
                $html .= '<li>&nbsp;&nbsp;&nbsp;Unlimited Email Alerts</li>';
                $html .= '<li>&nbsp;&nbsp;&nbsp;Unlimited Text Alerts</li>';
            $html .= '</ul>';
        if( $width == "six"){
            $html .= '<div class="button darkgrey">';
            $html .= '<input class="btn-package" type="submit" rel="' . $package->getId() . '" value="' .
                $this->translator->trans('pentair.members.buy.plan.upgrade') .  '"/> ';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getFilters()
    {
        return array(
            'printQuestionnaireQuestion' => new \Twig_Filter_Method($this, 'printQuestionnaireQuestion'),
        );
    }

    public function printQuestionnaireQuestion( \Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $question,
        $answers)
    {
        $value1 = "";
        $value2 = "";
        if(array_key_exists($question->getId(), $answers)){
            $value1 = $answers[$question->getId()]->getMainText();
            $value2 = $answers[$question->getId()]->getSecondText();
        }
        $html = "";
        $html .= "<div class='questionnaireQuestion'>";
        switch($question->getType()){
            case 1: //Simple Input
                $html .= "<label>" . $question->getMainText() . "</label>";
                $html .= '<input class="along-input"' . $this->getFieldId($question) .
                    ' type="text" value="' . $value1 . '">';
                $html .= '<div class="clear"></div>';
                break;
            case 2://YesNoSelectionSimple
                $html .= 'Tipo desconocido: ' . $question->getType();
                break;
            case 3: //YesNoSelectionWithExplain
                $html .= "<label>" . $question->getMainText() . "</label>";
                $html .= '<select ' . $this->getFieldId($question) .
                    ' class="short"><option value="0"' .
                        (($value1 == "0")? " selected":"") .
                        '>No</option><option value="1"' .
                        (($value1 == "1")? " selected":"") .
                        '>Si</option></select>';
                $html .= '<span>' . $question->getSecondText() . '</span>';
                $html .= '<textarea rows="5" id="qaux-' . $question->getId() . '" name="qaux-' .
                    $question->getId() . '">' . $value2 . '</textarea>';
                $html .= "<div class='clear'></div>";
                break;
            case 4://Group Of Questions
                $html .= "<label>" . $question->getMainText() . '</label><div class="clear"></div>';
                $texts = explode('-',$question->getSecondText());
                $html .= '<div>' .
                    '<div class="left" style="padding-left: 10px; width: 373px;"> ' . $texts[0] . '</div>' .
                    '<div class="left"> ' . $texts[1] . '</div>' .
                    '<div class="clear"></div>' .
                    '</div>';
                $html .= '<div class="questions-group">';
                foreach ( $question->getChildren() as $q) {
                    $value1 = "";
                    $value2 = "";
                    if(array_key_exists($q->getId(), $answers)){
                        $value1 = $answers[$q->getId()]->getMainText();
                        $value2 = $answers[$q->getId()]->getSecondText();
                    }
                    $html .= "<label>" . $q->getMainText() . "</label>";
                    $html .= '<input ' . $this->getFieldId($q) .
                        ' type="text" value="' . $value1 . '">';
                    $html .= '<div class="clear"></div>';
                }
                $html .= '</div>';
                break;
            case 5://Date Input
                $html .= "<label>" . $question->getMainText() . "</label>";
                $html .= '<input class="date-input along-input" ' . $this->getFieldId($question) . ' type="text" value="'
                    . $value1 . '">';
                break;
            case 6: // TextArea Input
                $html .= "<label>" . $question->getMainText() . "</label>";
                $html .= '<textarea  class="along-input" rows="5" ' . $this->getFieldId($question) . '>' . $value1 .
                    '</textarea>';
                break;
            case 7: // 3 Columns Question
                $html .= '<label style="width: 100%">' . $question->getMainText() . '</label><div
                class="clear"></div>';
                $texts = explode('-',$question->getSecondText());
                $html .= '<div>' .
                    '<div class="left" style="padding-left: 10px; width: 325px;"> ' . $texts[0] . '</div>' .
                    '<div class="left" style="padding-left: 10px; width: 300px;"> ' . $texts[1] . '</div>' .
                    '<div class="left" style="padding-left: 10px;"> ' . $texts[2] . '</div>' .
                    '<div class="clear"></div>' .
                    '</div>';
                $html .= '<div class="questions-group">';
                foreach ( $question->getChildren() as $q) {
                    $value1 = "";
                    if(array_key_exists($q->getId(), $answers)){
                        $value1 = $answers[$q->getId()]->getMainText();
                        $value2 = explode('-*-', $value1);
                    }else {
                        $value2 = array("","","");
                    }
                    $html .= '<input type="hidden" ' . $this->getFieldId($q) . '>';
                    $html .= '<div>' .
                        '<div class="left" style="width: 325px;"> '
                        . '<input type="text" id="qaux1-' . $q->getId() . '" name="qaux1-' . $q->getId() .
                            '" value="' . $value2[0] . '">'
                        . '</div>' .
                        '<div class="left" style="width: 300px;"> '
                        . '<input type="text" id="qaux2-' . $q->getId() . '" name="qaux2-' . $q->getId()
                        . '" value="' . $value2[1] . '"></div>' .
                        '<div class="left"> '
                        . '<input style="width: 75px;" type="text" id="qaux3-' . $q->getId() . '" name="qaux3-' .
                                $q->getId() . '" value="' . $value2[2] . '">'
                        . '</div>' .
                        '<div class="clear"></div>' .
                        '</div>';
                }
                $html .= '</div>';
                break;
            default:
                $html .= 'Tipo desconocido: ' . $question->getType();
                break;
        }
        $html .= "</div>";
        return $html;
    }

    private function getFieldId(\Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $question){
        $id = ' id="q-' . $question->getId() . "-" . $question->getType() . '" ' .
            ' name="q-' . $question->getId() . "-" . $question->getType() . '" ';
        return $id;
    }

    public function getName()
    {
        return 'questionnaire_twig_extension';
    }
}