<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="tek_valores_concepto")
 * @ORM\Entity()
 */
class ValoresConcepto
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Concepto")
     * @JoinColumn(name="concepto_id", referencedColumnName="id")
     */
    private $concepto;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $relacion;

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
     * Set concepto
     *
     * @param \Tecnotek\ExpedienteBundle\Entity\Concepto $concepto
     */
    public function setConcepto(\Tecnotek\ExpedienteBundle\Entity\Concepto $concepto)
    {
        $this->concepto = $concepto;
    }

    /**
     * Get concepto
     *
     * @return \Tecnotek\ExpedienteBundle\Entity\Concepto
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set nombre
     *
     * @param integer $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Get nombre
     *
     * @return integer
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set relacion
     *
     * @param integer $code
     */
    public function setRelacion($relacion)
    {
        $this->relacion = $relacion;
    }

    /**
     * Get relacion
     *
     * @return integer
     */
    public function getRelacion()
    {
        return $this->relacion;
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