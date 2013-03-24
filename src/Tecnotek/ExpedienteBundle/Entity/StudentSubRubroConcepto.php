<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_student_subrubro_concepto")
 * @ORM\Entity()
 */
class StudentSubRubroConcepto
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="SubRubroConcepto")
     * @JoinColumn(name="subrubro_concepto_id", referencedColumnName="id")
     */
    private $subRubroConcepto;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id")
     */
    private $student;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    private $valor;

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->id;
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
     * Set subRubroConcepto
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\SubRubroConcepto $subRubroConcepto
     */
    public function setSubRubroConcepto(\Tecnotek\ExpedienteBundle\Entity\SubRubroConcepto $subRubroConcepto)
    {
        $this->subRubroConcepto = $subRubroConcepto;
    }

    /**
     * Get subRubroConcepto
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\SubRubroConcepto
     */
    public function getSubRubroConcepto()
    {
        return $this->subRubroConcepto;
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

    /**
     * Set valor
     *
     * @param float $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * Get valor
     *
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }
}