<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_questionnaire_questions")
 * @ORM\Entity()
 */
class QuestionnaireQuestion
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
    private $mainText;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 255)
     */
    private $secondText;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $sortOrder;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @ManyToOne(targetEntity="Questionnaire")
     * @JoinColumn(name="questionnaire_id", referencedColumnName="id")
     */
    private $questionnaire;

    /**
     * @ManyToOne(targetEntity="QuestionnaireQuestion")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var children
     *
     * @ORM\OneToMany(targetEntity="QuestionnaireQuestion", mappedBy="parent", cascade={"all"})
     */
    private $children;



    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->mainText;
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
     * Set mainText
     *
     * @param string $mainText
     */
    public function setMainText($mainText)
    {
        $this->mainText = $mainText;
    }

    /**
     * Get mainText
     *
     * @return string 
     */
    public function getMainText()
    {
        return $this->mainText;
    }

    /**
     * Set secondText
     *
     * @param string $secondText
     */
    public function setSecondText($secondText)
    {
        $this->secondText = $secondText;
    }

    /**
     * Get secondText
     *
     * @return string
     */
    public function getSecondText()
    {
        return $this->secondText;
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
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set questionnaire
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Questionnaire $questionnaire
     */
    public function setBus(\Tecnotek\ExpedienteBundle\Entity\Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
    }

    /**
     * Get questionnaire
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set parent
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $parent
     */
    public function setParent(\Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\QuestionnaireQuestion
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren(){
        return $this->children;
    }
}