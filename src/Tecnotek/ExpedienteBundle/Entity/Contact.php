<?php
namespace Tecnotek\ExpedienteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="tek_contacts")
 * @ORM\Entity()
 * @UniqueEntity("identification")
 */
class Contact
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
    private $firstname;

    /**
 * @ORM\Column(type="string", length=150)
 * @Assert\NotBlank()
 * @Assert\MinLength(limit = 3)
 * @Assert\MaxLength(limit = 150)
 */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit = 5)
     * @Assert\MaxLength(limit = 50)
     */
    private $identification;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $phone_c;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $m_status;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $phone_w;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $phone_h;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 60)
     */
    private $workplace;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 60)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 120)
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $payments_r;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 120)
     */
    private $restriction;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $religion;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $soc;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\MaxLength(limit = 5)
     */
    private $tipo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\MaxLength(limit = 15)
     */
    private $relacion;


    public function __construct()
    {
        $this->identification = "";
    }

    public function __toString()
    {
        return $this->firstname . " " . $this->lastname;
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
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set identificacion
     *
     * @param string $identification
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;
    }

    /**
     * Get identification
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set phone_c
     *
     * @param string $phone_c
     */
    public function setPhone_c($phone_c)
    {
        $this->phone_c = $phone_c;
    }

    /**
     * Get phone_c
     *
     * @return string
     */
    public function getPhone_c()
    {
        return $this->phone_c;
    }

    /**
     * Set m_status
     *
     * @param string $m_status
     */
    public function setM_status($m_status)
    {
        $this->m_status = $m_status;
    }

    /**
     * Get m_status
     *
     * @return string
     */
    public function getM_status()
    {
        return $this->m_status;
    }

    /**
     * Set phone_w
     *
     * @param string $phone_w
     */
    public function setPhone_w($phone_w)
    {
        $this->phone_w = $phone_w;
    }

    /**
     * Get phone_w
     *
     * @return string
     */
    public function getPhone_w()
    {
        return $this->phone_w;
    }

    /**
     * Set phone_h
     *
     * @param string $phone_h
     */
    public function setPhone_h($phone_h)
    {
        $this->phone_h = $phone_h;
    }

    /**
     * Get phone_h
     *
     * @return string
     */
    public function getPhone_h()
    {
        return $this->phone_h;
    }

    /**
     * Set fax
     *
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set workplace
     *
     * @param string $workplace
     */
    public function setWorkplace($workplace)
    {
        $this->workplace = $workplace;
    }

    /**
     * Get workplace
     *
     * @return string
     */
    public function getWorkplace()
    {
        return $this->workplace;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set adress
     *
     * @param string $adress
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;
    }

    /**
     * Get adress
     *
     * @return string
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set nationality
     *
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * Get nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set payments_r
     *
     * @param string $payments_r
     */
    public function setPayments_r($payments_r)
    {
        $this->payments_r = $payments_r;
    }

    /**
     * Get payments_r
     *
     * @return string
     */
    public function getPayments_r()
    {
        return $this->payments_r;
    }

    /**
     * Set restriction
     *
     * @param string $restriction
     */
    public function setRestriction($restriction)
    {
        $this->restriction = $restriction;
    }

    /**
     * Get restriction
     *
     * @return string
     */
    public function getRestriction()
    {
        return $this->restriction;
    }

    /**
     * Set religion
     *
     * @param string $religion
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;
    }

    /**
     * Get religion
     *
     * @return string
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * Set profession
     *
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * Get profession
     *
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set birthday
     *
     * @param string $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set soc
     *
     * @param string $soc
     */
    public function setSoc($soc)
    {
        $this->soc = $soc;
    }

    /**
     * Get soc
     *
     * @return string
     */
    public function getSoc()
    {
        return $this->soc;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set relacion
     *
     * @param integer $relacion
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

}