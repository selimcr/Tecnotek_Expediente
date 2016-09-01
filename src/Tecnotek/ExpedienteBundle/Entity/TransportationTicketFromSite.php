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
 * @ORM\Table(name="tek_site_transportation_tickets")
 * @ORM\Entity(repositoryClass="Tecnotek\ExpedienteBundle\Repository\TransportationTicketFromSiteRepository")
 */
class TransportationTicketFromSite
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $observations;

    /**
     * @ORM\Column(type="integer")
     */
    private $service;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $student;

    /**
     * @ManyToOne(targetEntity="State")
     * @JoinColumn(name="state_id", referencedColumnName="id")
     */
    private $state;

    /**
     * @ManyToOne(targetEntity="Canton")
     * @JoinColumn(name="canton_id", referencedColumnName="id")
     */
    private $canton;

    /**
     * @ManyToOne(targetEntity="District")
     * @JoinColumn(name="district_id", referencedColumnName="id")
     */
    private $district;

    public function __construct()
    {

    }

    public function __toString()
    {
        return "Ticket #" . $this->id . ": " . $this->student;
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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @param mixed $observations
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param mixed $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getCanton()
    {
        return $this->canton;
    }

    /**
     * @param mixed $canton
     */
    public function setCanton($canton)
    {
        $this->canton = $canton;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }
}