<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\Domain
 */
class Domain
{
    use \OSS_Doctrine2_WithPreferences;

    /**
     * @var string $domain
     */
    private $domain;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var integer $quota
     */
    private $quota;

    /**
     * @var string $transport
     */
    private $transport;

    /**
     * @var boolean $backupmx
     */
    private $backupmx;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @var string $homedir
     */
    private $homedir;

    /**
     * @var string $maildir
     */
    private $maildir;

    /**
     * @var integer $uid
     */
    private $uid;

    /**
     * @var integer $gid
     */
    private $gid;

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
     * @var integer $max_quota
     */
    private $max_quota;

    /**
     * @var integer $max_aliases
     */
    private $max_aliases;

    /**
     * @var integer $max_mailboxes
     */
    private $max_mailboxes;

    /**
     * @var bigint $alias_count
     */
    private $alias_count;

    /**
     * @var bigint $mailbox_count
     */
    private $mailbox_count;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Mailboxes;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Aliases;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Logs;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Admins;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Mailboxes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Aliases = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Admins = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set domain
     *
     * @param string $domain
     * @return Domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return string 
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Domain
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get aliases
     *
     * @return integer 
     */
    public function getAliases()
    {
        return $this->Aliases;
    }

    /**
     * Get mailboxes
     *
     * @return integer 
     */
    public function getMailboxes()
    {
        return $this->Mailboxes;
    }

    /**
     * Set quota
     *
     * @param integer $quota
     * @return Domain
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;
    
        return $this;
    }

    /**
     * Get quota
     *
     * @return integer 
     */
    public function getQuota()
    {
        return $this->quota;
    }

    /**
     * Set transport
     *
     * @param string $transport
     * @return Domain
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
    
        return $this;
    }

    /**
     * Get transport
     *
     * @return string 
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set backupmx
     *
     * @param boolean $backupmx
     * @return Domain
     */
    public function setBackupmx($backupmx)
    {
        $this->backupmx = $backupmx;
    
        return $this;
    }

    /**
     * Get backupmx
     *
     * @return boolean 
     */
    public function getBackupmx()
    {
        return $this->backupmx;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Domain
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
     * Set homedir
     *
     * @param string $homedir
     * @return Domain
     */
    public function setHomedir($homedir)
    {
        $this->homedir = $homedir;
    
        return $this;
    }

    /**
     * Get homedir
     *
     * @return string 
     */
    public function getHomedir()
    {
        return $this->homedir;
    }

    /**
     * Set maildir
     *
     * @param string $maildir
     * @return Domain
     */
    public function setMaildir($maildir)
    {
        $this->maildir = $maildir;
    
        return $this;
    }

    /**
     * Get maildir
     *
     * @return string 
     */
    public function getMaildir()
    {
        return $this->maildir;
    }

    /**
     * Set uid
     *
     * @param integer $uid
     * @return Domain
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    
        return $this;
    }

    /**
     * Get uid
     *
     * @return integer 
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set gid
     *
     * @param integer $gid
     * @return Domain
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    
        return $this;
    }

    /**
     * Get gid
     *
     * @return integer 
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Domain
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
     * @return Domain
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
     * Add Mailboxes
     *
     * @param Entities\Mailbox $mailboxes
     * @return Domain
     */
    public function addMailbox(\Entities\Mailbox $mailboxes)
    {
        $this->Mailboxes[] = $mailboxes;
    
        return $this;
    }

    /**
     * Remove Mailboxes
     *
     * @param Entities\Mailbox $mailboxes
     */
    public function removeMailbox(\Entities\Mailbox $mailboxes)
    {
        $this->Mailboxes->removeElement($mailboxes);
    }

    /**
     * Add Aliases
     *
     * @param Entities\Alias $aliases
     * @return Domain
     */
    public function addAlias(\Entities\Alias $aliases)
    {
        $this->Aliases[] = $aliases;
    
        return $this;
    }

    /**
     * Remove Aliases
     *
     * @param Entities\Alias $aliases
     */
    public function removeAlias(\Entities\Alias $aliases)
    {
        $this->Aliases->removeElement($aliases);
    }

    /**
     * Add Logs
     *
     * @param Entities\Log $logs
     * @return Domain
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
     * Add Admins
     *
     * @param Entities\Admin $admins
     * @return Domain
     */
    public function addAdmin(\Entities\Admin $admins)
    {
        $this->Admins[] = $admins;
    
        return $this;
    }

    /**
     * Remove Admins
     *
     * @param Entities\Admin $admins
     */
    public function removeAdmin(\Entities\Admin $admins)
    {
        $this->Admins->removeElement($admins);
    }

    /**
     * Get Admins
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAdmins()
    {
        return $this->Admins;
    }
   


    /**
     * Set max_aliases
     *
     * @param integer $maxAliases
     * @return Domain
     */
    public function setMaxAliases($maxAliases)
    {
        $this->max_aliases = $maxAliases;
    
        return $this;
    }

    /**
     * Get max_aliases
     *
     * @return integer 
     */
    public function getMaxAliases()
    {
        return $this->max_aliases;
    }

    /**
     * Set max_mailboxes
     *
     * @param integer $maxMailboxes
     * @return Domain
     */
    public function setMaxMailboxes($maxMailboxes)
    {
        $this->max_mailboxes = $maxMailboxes;
    
        return $this;
    }

    /**
     * Get max_mailboxes
     *
     * @return integer 
     */
    public function getMaxMailboxes()
    {
        return $this->max_mailboxes;
    }



    /**
     * Set max_quota
     *
     * @param integer $maxQuota
     * @return Domain
     */
    public function setMaxQuota($maxQuota)
    {
        $this->max_quota = $maxQuota;
    
        return $this;
    }

    /**
     * Get max_quota
     *
     * @return integer 
     */
    public function getMaxQuota()
    {
        return $this->max_quota;
    }
    


    /**
     * Set alias_count
     *
     * @param bigint $aliasCount
     * @return Domain
     */
    public function setAliasCount($aliasCount)
    {
        $this->alias_count = $aliasCount;
        return $this;
    }

    /**
     * Get alias_count
     *
     * @return bigint
     */
    public function getAliasCount()
    {
        return $this->alias_count;
    }

    /**
     * Increase alias_count
     *
     * @return void
     */
    public function increaseAliasCount()
    {
        $this->alias_count += 1;
    }

    /**
     * Decrease alias_count
     *
     * @return void
     */
    public function decreaseAliasCount()
    {
        if( $this->alias_count > 0 )
            $this->alias_count -= 1;
    }



    /**
     * Set mailbox_count
     *
     * @param bigint $mailboxCount
     * @return Domain
     */
    public function setMailboxCount($mailboxCount)
    {
        $this->mailbox_count = $mailboxCount;
        return $this;
    }

    /**
     * Get mailbox_count
     *
     * @return bigint 
     */
    public function getMailboxCount()
    {
        return $this->mailbox_count;
    }

    /**
     * Increase mailbox_count
     *
     * @return void
     */
    public function increaseMailboxCount()
    {
        $this->mailbox_count += 1;
    }

    /**
     * Decrease mailbox_count
     *
     * @return void
     */
    public function decreaseMailboxCount()
    {
        if( $this->mailbox_count > 0 )
            $this->mailbox_count -= 1;
    }

    /**
     * Add Mailboxes
     *
     * @param \Entities\Mailbox $mailboxes
     * @return Domain
     */
    public function addMailboxe(\Entities\Mailbox $mailboxes)
    {
        $this->Mailboxes[] = $mailboxes;
    
        return $this;
    }

    /**
     * Remove Mailboxes
     *
     * @param \Entities\Mailbox $mailboxes
     */
    public function removeMailboxe(\Entities\Mailbox $mailboxes)
    {
        $this->Mailboxes->removeElement($mailboxes);
    }

    /**
     * Add Aliases
     *
     * @param \Entities\Alias $aliases
     * @return Domain
     */
    public function addAliase(\Entities\Alias $aliases)
    {
        $this->Aliases[] = $aliases;
    
        return $this;
    }

    /**
     * Remove Aliases
     *
     * @param \Entities\Alias $aliases
     */
    public function removeAliase(\Entities\Alias $aliases)
    {
        $this->Aliases->removeElement($aliases);
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Preferences;


    /**
     * Add Preferences
     *
     * @param \Entities\DomainPreference $preferences
     * @return Domain
     */
    public function addPreference(\Entities\DomainPreference $preferences)
    {
        $this->Preferences[] = $preferences;
    
        return $this;
    }

    /**
     * Remove Preferences
     *
     * @param \Entities\DomainPreference $preferences
     */
    public function removePreference(\Entities\DomainPreference $preferences)
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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Archives;


    /**
     * Add Archives
     *
     * @param \Entities\Archive $archives
     * @return Domain
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
