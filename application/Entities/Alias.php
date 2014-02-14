<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\Alias
 */
class Alias
{
    use \OSS_Doctrine2_WithPreferences;

    /**
     * @var string $address
     */
    private $address;

    /**
     * @var string $goto
     */
    private $goto;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @var \DateTime $created
     */
    private $created;

    /**
     * @var \DateTime $modified
     */
    private $modified;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Entities\Domain
     */
    private $Domain;


    /**
     * Set address
     *
     * @param string $address
     * @return Alias
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set goto
     *
     * @param string $goto
     * @return Alias
     */
    public function setGoto($goto)
    {
        $this->goto = $goto;
    
        return $this;
    }

    /**
     * Get goto
     *
     * @return string 
     */
    public function getGoto()
    {
        return $this->goto;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Alias
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Alias
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Alias
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
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
     * Set Domain
     *
     * @param Entities\Domain $domain
     * @return Alias
     */
    public function setDomain(\Entities\Domain $domain = null)
    {
        $this->Domain = $domain;
    
        return $this;
    }

    /**
     * Get Domain
     *
     * @return Entities\Domain 
     */
    public function getDomain()
    {
        return $this->Domain;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Preferences;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Preferences = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add Preferences
     *
     * @param \Entities\AliasPreference $preferences
     * @return Alias
     */
    public function addPreference(\Entities\AliasPreference $preferences)
    {
        $this->Preferences[] = $preferences;
    
        return $this;
    }

    /**
     * Remove Preferences
     *
     * @param \Entities\AliasPreference $preferences
     */
    public function removePreference(\Entities\AliasPreference $preferences)
    {
        $this->Preferences->removeElement($preferences);
    }

    /**
     * Get Preferences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPreferences()
    {
        return $this->Preferences;
    }
}
