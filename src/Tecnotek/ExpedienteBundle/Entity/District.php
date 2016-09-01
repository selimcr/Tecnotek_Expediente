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
 * @ORM\Table(name="tek_districts")
 * @ORM\Entity()
 */
class District
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
     * @ManyToOne(targetEntity="Canton")
     * @JoinColumn(name="canton_id", referencedColumnName="id")
     */
    private $canton;

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
     * Set canton
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Canton $canton
     */
    public function setCanton(\Tecnotek\ExpedienteBundle\Entity\Canton $canton)
    {
        $this->canton = $canton;
    }

    /**
     * Get canton
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Canton
     */
    public function getCanton()
    {
        return $this->canton;
    }
}