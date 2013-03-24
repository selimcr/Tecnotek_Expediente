<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_subrubro_concepto")
 * @ORM\Entity()
 */
class SubRubroConcepto
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="RubroConcepto")
     * @JoinColumn(name="rubro_concepto_id", referencedColumnName="id")
     */
    private $rubroConcepto;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    private $valor;

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->id;
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
     * Set rubroConcepto
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\RubroConcepto $rubroConcepto
     */
    public function setRubroConcepto(\Tecnotek\ExpedienteBundle\Entity\RubroConcepto $rubroConcepto)
    {
        $this->rubroConcepto = $rubroConcepto;
    }

    /**
     * Get rubroConcepto
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\RubroConcepto
     */
    public function getRubroConcepto()
    {
        return $this->rubroConcepto;
    }

    /**
     * Set valor
     *
     * @param float $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * Get valor
     *
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }
}