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
 * @ORM\Table(name="tek_cantones")
 * @ORM\Entity()
 */
class Canton
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
    private $name;

    /**
     * @ManyToOne(targetEntity="State")
     * @JoinColumn(name="state_id", referencedColumnName="id")
     */
    private $state;

    /**
     * @var districts
     *
     * @ORM\OneToMany(targetEntity="District", mappedBy="canton")
     */
    private $districts;

    public function __construct()
    {

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
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set state
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\State $state
     */
    public function setState(\Tecnotek\ExpedienteBundle\Entity\State $state)
    {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }

    public function getDistricts() {
        return $this->districts;
    }
}