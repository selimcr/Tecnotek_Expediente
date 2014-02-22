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
 * @ORM\Table(name="tek_ticket_item")
 * @ORM\Entity()
 */
class TicketItem
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
    private $comments;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date_estimated;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date_in;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date_out;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $date_update;

    /**
     * @ManyToOne(targetEntity="Item")
     * @JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @ManyToOne(targetEntity="Student")
     * @JoinColumn(name="student_id", referencedColumnName="id", nullable=true)
     */
    private $student;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_update_id", referencedColumnName="id")
     */
    private $user_update;

    public function __construct()
    {

    }

    public function __toString()
    {
        return "Prestamo #" . $this->id . ": " . $this->item. " :: " . $this->student;
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
     * Set comments
     *
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set date
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
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
     * Set relative
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Relative $relative
     */
    public function setRelative(\Tecnotek\ExpedienteBundle\Entity\Relative $relative)
    {
        $this->relative = $relative;
    }

    /**
     * Get relative
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Relative
     */
    public function getRelative()
    {
        return $this->relative;
    }
}