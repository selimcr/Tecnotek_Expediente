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
 * @ORM\Table(name="tek_relatives")
 * @ORM\Entity()
 * @UniqueEntity("licensePlate")
 */
class Relative
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Min(limit = 1)
     */
    private $kinship;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 2)
     * @Assert\MaxLength(limit = 255)
     */
    private $description;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id")
     */
    private $student;

    /**
     * @ManyToOne(targetEntity="Contact")
     * @JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    private $FATHER = 1;
    private $MOTHER = 2;
    private $BROTHER = 3;
    private $SISTER = 4;
    private $OTHER = 99;

    public function getFatherType(){return $this->FATHER;}
    public function getMotherType(){return $this->MOTHER;}
    public function getBrotherType(){return $this->BROTHER;}
    public function getSisterType(){return $this->SISTER;}
    public function getOtherType(){return $this->OTHER;}

    public function __construct()
    {
        $this->description = "";
    }

    public function __toString()
    {
        return "Relative " . $this->id;
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
     * Set kinship
     *
     * @param integer $kinship
     */
    public function setKinship($kinship)
    {
        $this->kinship = $kinship;
    }

    /**
     * Get kinship
     *
     * @return integer 
     */
    public function getKinship()
    {
        return $this->kinship;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set contact
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Contact $contact
     */
    public function setContact(\Tecnotek\ExpedienteBundle\Entity\Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Get contact
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }
}