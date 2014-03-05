<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_questionnaire_answers")
 * @ORM\Entity()
 */
class QuestionnaireAnswer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 255)
     */
    private $mainText;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 255)
     */
    private $secondText;

    /**
     * @ManyToOne(targetEntity="QuestionnaireQuestion")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $question;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id")
     */
    private $student;

    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->mainText;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mainText
     *
     * @param string $mainText
     */
    public function setMainText($mainText)
    {
        $this->mainText = $mainText;
    }

    /**
     * Get mainText
     *
     * @return string 
     */
    public function getMainText()
    {
        return $this->mainText;
    }

    /**
     * Set secondText
     *
     * @param string $secondText
     */
    public function setSecondText($secondText)
    {
        $this->secondText = $secondText;
    }

    /**
     * Get secondText
     *
     * @return string
     */
    public function getSecondText()
    {
        return $this->secondText;
    }

    /**
     * Set question
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $question
     */
    public function setQuestion(\Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set student
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Student $student
     */
    public function setStudent(\Tecnotek\ExpedienteBundle\Entity\Student $student)
    {
        $this->student = $student;
    }

    /**
     * Get student
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }
}