<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 * @ORM\Table(name="tek_students")
 * @ORM\Entity()
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
    private $firstname;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 150)
     */
    private $lastname;

    /**
     * @ORM\ManyToMany(targetEntity="Club", mappedBy="clubs")
     */
    private $clubs;

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
     * @inheritDoc
     */
    public function getClubs()
    {
        return $this->clubs->toArray();
    }
}