<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 *
 * @ORM\Table(name="tek_clubs")
 * @ORM\Entity()
 * @UniqueEntity("name")
 */
class Club
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 60)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=120, nullable = true)
     * @Assert\MaxLength(limit = 120)
     */
    private $coordinator;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $day;

    /**
     * @ORM\Column(type="date", nullable = true)
     */
    private $timeI;

    /**
     * @ORM\Column(type="date", nullable = true)
     */
    private $timeO;

    /**
     * @ORM\ManyToMany(targetEntity="Student", inversedBy="students")
     *
     */
    private $students;

    public function __construct()
    {
        $this->students = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set coordinator
     *
     * @param string $coordinator
     */
    public function setCoordinator($coordinator)
    {
        $this->coordinator = $coordinator;
    }

    /**
     * Get coordinator
     *
     * @return string 
     */
    public function getCoordinator()
    {
        return $this->coordinator;
    }

    /**
     * Set day
     *
     * @param integer $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * Get day
     *
     * @return integer 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set timeI
     *
     * @param date $timeI
     */
    public function setTimeI($timeI)
    {
        $this->timeI = $timeI;
    }

    /**
     * Get timeI
     *
     * @return date 
     */
    public function getTimeI()
    {
        return $this->timeI;
    }

    /**
     * Set timeO
     *
     * @param date $timeO
     */
    public function setTimeO($timeO)
    {
        $this->timeO = $timeO;
    }

    /**
     * Get timeO
     *
     * @return date 
     */
    public function getTimeO()
    {
        return $this->timeO;
    }

    /**
     * @inheritDoc
     */
    public function getStudents()
    {
        return (isset($this->students))? $this->students:new ArrayCollection();
    }
}