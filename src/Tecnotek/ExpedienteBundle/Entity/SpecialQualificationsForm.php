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
 * @ORM\Table(name="tek_special_qualifications_forms")
 * @ORM\Entity()
 */
class SpecialQualificationsForm
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
    private $name;

    /**
     * @ORM\Column(type="integer", name="sort_order")
     * @Assert\NotBlank()
     */
    private $sortOrder;

    /**
     * @ManyToOne(targetEntity="Grade")
     * @JoinColumn(name="grade_id", referencedColumnName="id")
     */
    private $grade;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Min(limit = 1)
     */
    private $entryType;

    /**
     * @ORM\Column(name="shows_on_period_one", type="boolean")
     */
    private $showsOnPeriodOne;

    /**
     * @ORM\Column(name="shows_on_period_two", type="boolean")
     */
    private $showsOnPeriodTwo;

    /**
     * @ORM\Column(name="shows_on_period_three", type="boolean")
     */
    private $showsOnPeriodThree;


    /**
     * @var questions
     *
     * @ORM\OneToMany(targetEntity="SpecialQualification", mappedBy="form", cascade={"all"})
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    private $questions;


    public function __construct()
    {
        $this->showsOnPeriodOne = false;
        $this->showsOnPeriodTwo = false;
        $this->showsOnPeriodThree = false;

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
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set grade
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Grade $grade
     */
    public function setGrade(\Tecnotek\ExpedienteBundle\Entity\Grade $grade)
    {
        $this->grade = $grade;
    }

    /**
     * Get grade
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Grade
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set entryType
     *
     * @param integer $entryType
     */
    public function setEntryType($entryType)
    {
        $this->entryType = $entryType;
    }

    /**
     * Get entryType
     *
     * @return integer
     */
    public function getEntryType()
    {
        return $this->entryType;
    }

    public function setShowsOnPeriodOne($showsOnPeriodOne)
    {
        $this->showsOnPeriodOne = $showsOnPeriodOne;
    }

    public function getShowsOnPeriodOne()
    {
        return $this->showsOnPeriodOne;
    }

    public function setShowsOnPeriodThree($showsOnPeriodThree)
    {
        $this->showsOnPeriodThree = $showsOnPeriodThree;
    }

    public function getShowsOnPeriodThree()
    {
        return $this->showsOnPeriodThree;
    }

    public function setShowsOnPeriodTwo($showsOnPeriodTwo)
    {
        $this->showsOnPeriodTwo = $showsOnPeriodTwo;
    }

    public function getShowsOnPeriodTwo()
    {
        return $this->showsOnPeriodTwo;
    }

    public function getQuestions(){
        return $this->questions;
    }

}