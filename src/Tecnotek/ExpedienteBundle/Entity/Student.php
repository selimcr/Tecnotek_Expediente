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
 * @ORM\Table(name="tek_students")
 * @ORM\Entity()
 * @UniqueEntity("carne")
 */
class Student
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 150)
     */
    private $carne;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 150)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 150)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\MaxLength(limit = 255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $fatherPhone;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $motherPhone;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $pickUp;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $leaveTime;

    /**
     * @ManyToOne(targetEntity="Route")
     * @JoinColumn(name="route_id", referencedColumnName="id")
     */
    private $route;

    /**
     * @ORM\ManyToMany(targetEntity="Club", mappedBy="clubs")
     */
    private $clubs;

    /**
     * @var Relative
     *
     * @ORM\OneToMany(targetEntity="Relative", mappedBy="student", cascade={"all"})
     */
    private $relatives;


    public function __construct()
    {
        $this->clubs = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstname . " " . $this->lastname;
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
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
 * Set lastname
 *
 * @param string $lastname
 */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set carne
     *
     * @param string $carne
     */
    public function setCarne($carne)
    {
        $this->carne = $carne;
    }

    /**
     * Get carne
     *
     * @return string
     */
    public function getCarne()
    {
        return $this->carne;
    }

    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set fatherPhone
     *
     * @param string $fatherPhone
     */
    public function setFatherPhone($fatherPhone)
    {
        $this->fatherPhone = $fatherPhone;
    }

    /**
     * Get fatherPhone
     *
     * @return string
     */
    public function getFatherPhone()
    {
        return $this->fatherPhone;
    }

    /**
     * Set motherPhone
     *
     * @param string $motherPhone
     */
    public function setMotherPhone($motherPhone)
    {
        $this->motherPhone = $motherPhone;
    }

    /**
     * Get motherPhone
     *
     * @return string
     */
    public function getMotherPhone()
    {
        return $this->motherPhone;
    }

    /**
     * Set pickUp
     *
     * @param string $pickUp
     */
    public function setPickUp($pickUp)
    {
        $this->pickUp = $pickUp;
    }

    /**
     * Get pickUp
     *
     * @return string
     */
    public function getPickUp()
    {
        return $this->pickUp;
    }

    /**
     * Set leaveTime
     *
     * @param string $leaveTime
     */
    public function setLeaveTime($leaveTime)
    {
        $this->leaveTime = $leaveTime;
    }

    /**
     * Get leaveTime
     *
     * @return string
     */
    public function getLeaveTime()
    {
        return $this->leaveTime;
    }

    /**
     * @inheritDoc
     */
    public function getClubs()
    {
        return $this->clubs->toArray();
    }

    public function getRelatives(){
        return $this->relatives;
    }

    /**
     * Set route
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Route $route
     */
    public function setRoute(\Tecnotek\ExpedienteBundle\Entity\Route $route)
    {
        $this->route = $route;
    }

    /**
     * Get route
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Route
     */
    public function getRoute()
    {
        return $this->route;
    }
}