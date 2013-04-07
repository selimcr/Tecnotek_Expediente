<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
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
 * @ORM\OrderBy({"lastname" = "ASC"})
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dailyStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dailyDescription;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $gender;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $admission;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 25)
     */
    private $identification;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 5)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 5)
     */
    private $nacionality;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 5)
     */
    private $religion;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 120)
     */
    private $observation;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 75)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 5)
     */
    private $payment;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $code;

    /**
     * @ManyToOne(targetEntity="Route")
     * @JoinColumn(name="route_id", referencedColumnName="id", nullable=true)
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
        $this->dailyStatus = 0;
        $this->dailyDescription = "";
    }

    public function __toString()
    {

        return $this->lastname . " " . $this->firstname;
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
     * Set dailyStatus
     *
     * @param integer $dailyStatus
     */
    public function setDailyStatus($dailyStatus)
    {
        $this->dailyStatus = $dailyStatus;
    }

    /**
     * Get dailyStatus
     *
     * @return integer
     */
    public function getDailyStatus()
    {
        return $this->dailyStatus;
    }

    /**
     * Set dailyDescription
     *
     * @param string $dailyDescription
     */
    public function setDailyDescription($dailyDescription)
    {
        $this->dailyDescription = $dailyDescription;
    }

    /**
     * Get dailyDescription
     *
     * @return string
     */
    public function getDailyDescription()
    {
        return $this->dailyDescription;
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

    public function removeRoute()
    {
        $this->route = null;
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

    /**
     * Set age
     *
     * @param integer $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * Get age
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set birthday
     *
     * @param string $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set admission
     *
     * @param string $admission
     */
    public function setAdmission($admission)
    {
        $this->admission = $admission;
    }

    /**
     * Get admission
     *
     * @return string
     */
    public function getAdmission()
    {
        return $this->admission;
    }

    /**
     * Set identification
     *
     * @param string $identification
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;
    }

    /**
     * Get identification
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set nacionality
     *
     * @param string $nacionality
     */
    public function setNacionality($nacionality)
    {
        $this->nacionality = $nacionality;
    }

    /**
     * Get nacionality
     *
     * @return string
     */
    public function getNacionality()
    {
        return $this->nacionality;
    }

    /**
     * Set religion
     *
     * @param string $religion
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;
    }

    /**
     * Get religion
     *
     * @return string
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * Set observation
     *
     * @param string $observation
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;
    }

    /**
     * Get observation
     *
     * @return string
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set payment
     *
     * @param string $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get payment
     *
     * @return string
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}