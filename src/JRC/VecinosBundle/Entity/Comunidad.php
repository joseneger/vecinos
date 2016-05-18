<?php

namespace JRC\VecinosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comunidad
 *
 * @ORM\Table(name="comunidad")
 * @ORM\Entity(repositoryClass="JRC\VecinosBundle\Entity\ComunidadRepository")
 */
class Comunidad
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=150)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=255)
     */
    private $direccion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mancomunidad", type="boolean")
     */
    private $mancomunidad;


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
     * Set nombre
     *
     * @param string $nombre
     * @return Comunidad
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Comunidad
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set mancomunidad
     *
     * @param boolean $mancomunidad
     * @return Comunidad
     */
    public function setMancomunidad($mancomunidad)
    {
        $this->mancomunidad = $mancomunidad;

        return $this;
    }

    /**
     * Get mancomunidad
     *
     * @return boolean 
     */
    public function getMancomunidad()
    {
        return $this->mancomunidad;
    }
}
