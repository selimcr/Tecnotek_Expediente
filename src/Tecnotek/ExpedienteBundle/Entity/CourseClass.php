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
 * @ORM\Table(name="tek_course_class")
 * @ORM\Entity()
 */
class CourseClass
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $teacher;

    /**
     * @ManyToOne(targetEntity="Period")
     * @JoinColumn(name="period_id", referencedColumnName="id")
     */
    private $period;

    /**
     * @ManyToOne(targetEntity="Grade")
     * @JoinColumn(name="grade_id", referencedColumnName="id")
     */
    private $grade;

    /**
     * @ManyToOne(targetEntity="Course")
     * @JoinColumn(name="course_id", referencedColumnName="id")
     */
    private $course;

    /**
     * @var AssignedTeachers
     *
     * @ORM\OneToMany(targetEntity="AssignedTeacher", mappedBy="courseClass", cascade={"persist", "remove"})
     */
    private $assignedTeachers;

    public function __construct()
    {

    }

    public function __toString()
    {

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
     * Set period
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Period $period
     */
    public function setPeriod(\Tecnotek\ExpedienteBundle\Entity\Period $period)
    {
        $this->period = $period;
    }

    /**
     * Get period
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Period
     */
    public function getPeriod()
    {
        return $this->period;
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
     * Set teacher
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\User $teacher
     */
    public function setTeacher(\Tecnotek\ExpedienteBundle\Entity\User $teacher)
    {
        $this->teacher = $teacher;
    }

    /**
     * Get teacher
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\User
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * Set course
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Course $course
     */
    public function setCourse(\Tecnotek\ExpedienteBundle\Entity\Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get course
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Course
     */
    public function getCourse()
    {
        return $this->course;
    }
}