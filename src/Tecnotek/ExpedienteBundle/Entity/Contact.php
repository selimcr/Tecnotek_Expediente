<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="tek_contacts")
 * @ORM\Entity()
 * @UniqueEntity("identification")
 */
class Contact
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
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 5)
     * @Assert\MaxLength(limit = 50)
     */
    private $identification;

    public function __construct()
    {
        $this->identification = "";
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
     * Set identificacion
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

}