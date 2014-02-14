<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\Admin
 */
class Admin
{
    use \OSS_Doctrine2_WithPreferences;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var boolean $super
     */
    private $super;

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
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Admin;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Logs;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Domains;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Admin = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Domains = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set username
     *
     * @param string $username
     * @return Admin
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Utility function to get the user's email (which is the username)
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getUsername();
    }
                                   
    /**
     * Utility function to get the user's "formatted name" as required by some OSS functions
     *
     * @return string
     */
    public function getFormattedName()
    {
        return $this->getUsername();
    }
                                   
                                   

    /**
     * Set password
     *
     * @param string $password
     * @return Admin
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set super
     *
     * @param boolean $super
     * @return Admin
     */
    public function setSuper($super)
    {
        $this->super = $super;
    
        return $this;
    }

    /**
     * Get super
     *
     * @return boolean
     */
    public function getSuper()
    {
        return $this->super;
    }

    /**
     * Alias fot getSuper
     *
     * @return boolean
     */
    public function isSuper()
    {
        return $this->getSuper();
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Admin
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
     * @return Admin
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
     * @return Admin
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
     * Add Admin
     *
     * @param Entities\AdminPreference $admin
     * @return Admin
     */
    public function addAdmin(\Entities\AdminPreference $admin)
    {
        $this->Admin[] = $admin;
    
        return $this;
    }

    /**
     * Remove Admin
     *
     * @param Entities\AdminPreference $admin
     */
    public function removeAdmin(\Entities\AdminPreference $admin)
    {
        $this->Admin->removeElement($admin);
    }

    /**
     * Get Admin
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAdmin()
    {
        return $this->Admin;
    }

    /**
     * Add Logs
     *
     * @param Entities\Log $logs
     * @return Admin
     */
    public function addLog(\Entities\Log $logs)
    {
        $this->Logs[] = $logs;
    
        return $this;
    }

    /**
     * Remove Logs
     *
     * @param Entities\Log $logs
     */
    public function removeLog(\Entities\Log $logs)
    {
        $this->Logs->removeElement($logs);
    }

    /**
     * Get Logs
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->Logs;
    }

    /**
     * Add Domains
     *
     * @param Entities\Domain $domains
     * @return Admin
     */
    public function addDomain(\Entities\Domain $domains)
    {
        $this->Domains[] = $domains;
    
        return $this;
    }

    /**
     * Remove Domains
     *
     * @param Entities\Domain $domains
     */
    public function removeDomain(\Entities\Domain $domains)
    {
        $this->Domains->removeElement($domains);
    }

    /**
     * Get Domains
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getDomains()
    {
        return $this->Domains;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Preferences;


    /**
     * Add Preferences
     *
     * @param Entities\AdminPreference $preferences
     * @return Admin
     */
    public function addPreference(\Entities\AdminPreference $preferences)
    {
        $this->Preferences[] = $preferences;
    
        return $this;
    }

    /**
     * Remove Preferences
     *
     * @param Entities\AdminPreference $preferences
     */
    public function removePreference(\Entities\AdminPreference $preferences)
    {
        $this->Preferences->removeElement($preferences);
    }

    /**
     * Get Preferences
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPreferences()
    {
        return $this->Preferences;
    }
    

    /**
     * Check to see if this user is linked to a given domain (does not check for super - see below)
     *
     * This function is slightly misnamed as it does not check if your are a super admin (`isSuper()`)
     * but rather whether you are linked to a domain or not. So a use case in practice would be:
     *
     *     if( $admin->isSuper() || $admin->canManageDomain( $domain ) ) ...;
     *
     *
     * @param \Entities\Domain $domain The domain object
     * @return boolean
     */
    public function canManageDomain( $domain )
    {
        foreach( $this->getDomains() as $d )
            if( $domain->getId() == $d->getId() )
                return true;
        
        return false;
    }
    

    /**
     * Add Preferences
     *
     * @param Entities\AdminPreference $preferences
     * @return Admin
     */
    public function addAdminPreference(\Entities\AdminPreference $preferences)
    {
        $this->Preferences[] = $preferences;
        return $this;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $RememberMes;


    /**
     * Add RememberMes
     *
     * @param \Entities\RememberMe $rememberMes
     * @return Admin
     */
    public function addRememberMe(\Entities\RememberMe $rememberMes)
    {
        $this->RememberMes[] = $rememberMes;
    
        return $this;
    }

    /**
     * Remove RememberMes
     *
     * @param \Entities\RememberMe $rememberMes
     */
    public function removeRememberMe(\Entities\RememberMe $rememberMes)
    {
        $this->RememberMes->removeElement($rememberMes);
    }

    /**
     * Get RememberMes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRememberMes()
    {
        return $this->RememberMes;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Archives;


    /**
     * Add Archives
     *
     * @param \Entities\Archive $archives
     * @return Admin
     */
    public function addArchive(\Entities\Archive $archives)
    {
        $this->Archives[] = $archives;
    
        return $this;
    }

    /**
     * Remove Archives
     *
     * @param \Entities\Archive $archives
     */
    public function removeArchive(\Entities\Archive $archives)
    {
        $this->Archives->removeElement($archives);
    }

    /**
     * Get Archives
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchives()
    {
        return $this->Archives;
    }
}
