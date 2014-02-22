<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="tek_item")
 * @ORM\Entity()
 * @UniqueEntity("code")
 */
class Item
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=60)
     * @Assert\NotBlank()
     * @Assert\Min(limit = 1)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=160)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 1)
     * @Assert\MaxLength(limit = 60)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=200, nullable = true)
     * @Assert\MaxLength(limit = 200)
     */
    private $description;

     /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Min(limit = 1)
     * 1: Bodega, 2: Prestamo
     */
    private $status;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="CategoryItem")
     * @JoinColumn(name="category_item_id", referencedColumnName="id")
     */
    private $category;


    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->code . " :: " . $this->name;
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
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set code
     *
     * @param integer $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
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
     * Set user
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\User $user
     */
    public function setUser(\Tecnotek\ExpedienteBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set category
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\CategoryItem $category
     */
    public function setCategory(\Tecnotek\ExpedienteBundle\Entity\CategoryItem $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\CategoryItem
     */
    public function getCategory()
    {
        return $this->category;
    }


}