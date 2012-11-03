<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 *
 * @ORM\Table(name="tek_absences")
 * @ORM\Entity()
 */
class Absence
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
    private $comments;

    /**
     * @ORM\Column(type="boolean", nullable = true)
     */
    private $justify;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id")
     */
    private $student;

    public function __construct()
    {
        $this->justify = false;
        $this->comments = "";
    }

    public function __toString()
    {
        return "Ausencia #" . $this->id . ": " . $this->student;
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
     * Set comments
     *
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set date
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return date 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set justify
     *
     * @param boolean $justify
     */
    public function setJustify($justify)
    {
        $this->justify = $justify;
    }

    /**
     * Get justify
     *
     * @return boolean
     */
    public function getJustify()
    {
        return $this->justify;
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