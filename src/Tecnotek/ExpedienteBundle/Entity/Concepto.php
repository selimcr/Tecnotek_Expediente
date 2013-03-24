<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_concepto")
 * @ORM\Entity()
 */
class Concepto
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Grade")
     * @JoinColumn(name="nivel_id", referencedColumnName="id")
     */
    private $grade;

    /**
     * @ManyToOne(targetEntity="Period")
     * @JoinColumn(name="period_id", referencedColumnName="id")
     */
    private $period;

    /**
     * @ManyToOne(targetEntity="AbsenceType")
     * @JoinColumn(name="type_id", referencedColumnName="id")
     */
    private $absenceType;

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
     * Set grade
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Grade $grade
     */
    public function setGrade(\Tecnotek\ExpedienteBundle\Entity\Grade $grade)
    {
        $this->grade = $grade;
    }

    /**
     * Get grade
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Grade
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set period
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Period $period
     */
    public function setPeriod(\Tecnotek\ExpedienteBundle\Entity\Period $period)
    {
        $this->period = $period;
    }

    /**
     * Get period
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Period
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set absenceType
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\AbsenceType $absenceType
     */
    public function setAbsenceType(\Tecnotek\ExpedienteBundle\Entity\AbsenceType $absenceType)
    {
        $this->absenceType = $absenceType;
    }

    /**
     * Get absenceType
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\AbsenceType
     */
    public function getAbsenceType()
    {
        return $this->absenceType;
    }
}