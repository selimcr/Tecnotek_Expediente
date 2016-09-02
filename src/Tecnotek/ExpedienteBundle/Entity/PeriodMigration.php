<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_period_migrations")
 * @ORM\Entity(repositoryClass="Tecnotek\ExpedienteBundle\Repository\PeriodMigrationRepository")
 */
class PeriodMigration
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
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 3)
     * @Assert\MaxLength(limit = 255)
     */
    private $migrationSteps;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="Period")
     * @ORM\JoinColumn(name="source_period_id", referencedColumnName="id")
     */
    private $sourcePeriod;

    /**
     * @ORM\ManyToOne(targetEntity="Period")
     * @ORM\JoinColumn(name="destination_period_id", referencedColumnName="id")
     */
    private $destinationPeriod;

    public function __construct() {
        $this->description = "";
        $this->status = 1;
        $this->migrationSteps = '{"1":0, "2":0, "3":0, "4":0, "5":0, "6":0, "7":0}';
    }

    public function __toString() {
        return "PeriodMigration [" . $this->id . "]";
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed $migrationSteps
     */
    public function setMigrationSteps($migrationSteps) {
        $this->migrationSteps = $migrationSteps;
    }

    /**
     * @return mixed
     */
    public function getMigrationSteps() {
        return $this->migrationSteps;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSourcePeriod() {
        return $this->sourcePeriod;
    }

    /**
     * @param mixed $sourcePeriod
     */
    public function setSourcePeriod($sourcePeriod) {
        $this->sourcePeriod = $sourcePeriod;
    }

    /**
     * @return mixed
     */
    public function getDestinationPeriod() {
        return $this->destinationPeriod;
    }

    /**
     * @param mixed $destinationPeriod
     */
    public function setDestinationPeriod($destinationPeriod) {
        $this->destinationPeriod = $destinationPeriod;
    }
}