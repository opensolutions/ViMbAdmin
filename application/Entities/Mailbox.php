<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\Mailbox
 */
class Mailbox
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
     * @var string $name
     */
    private $name;

    /**
     * @var string $alt_email
     */
    private $alt_email;

    /**
     * @var integer $quota
     */
    private $quota;

    /**
     * @var string $local_part
     */
    private $local_part;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @var string $access_restriction
     */
    private $access_restriction = 'ALL';

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
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $Preferences;

    /**
     * @var integer
     */
    private $homedir_size;

    /**
     * @var integer
     */
    private $maildir_size;

    /**
     * @var \DateTime
     */
    private $size_at;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Preferences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Mailbox
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
     * Set password
     *
     * @param string $password
     * @return Mailbox
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
     * Set name
     *
     * @param string $name
     * @return Mailbox
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alt_email
     *
     * @param string $altEmail
     * @return Mailbox
     */
    public function setAltEmail($altEmail)
    {
        $this->alt_email = $altEmail;

        return $this;
    }

    /**
     * Get alt_email
     *
     * @return string
     */
    public function getAltEmail()
    {
        return $this->alt_email;
    }

    /**
     * Set quota
     *
     * @param integer $quota
     * @return Mailbox
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
     * Set local_part
     *
     * @param string $localPart
     * @return Mailbox
     */
    public function setLocalPart($localPart)
    {
        $this->local_part = $localPart;

        return $this;
    }

    /**
     * Get local_part
     *
     * @return string
     */
    public function getLocalPart()
    {
        return $this->local_part;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Mailbox
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
     * Set access_restriction
     *
     * @param string $accessRestriction
     * @return Mailbox
     */
    public function setAccessRestriction($accessRestriction)
    {
        $this->access_restriction = $accessRestriction;

        return $this;
    }

    /**
     * Get access_restriction
     *
     * @return string
     */
    public function getAccessRestriction()
    {
        return $this->access_restriction;
    }

    /**
     * Set homedir
     *
     * @param string $homedir
     * @return Mailbox
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
     * @return Mailbox
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
     * Get maildir
     *
     * @return string
     */
    public function getCleanedMaildir()
    {
        return self::cleanMaildir( $this->getMaildir() );
    }

    /**
     * Set uid
     *
     * @param integer $uid
     * @return Mailbox
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
     * @return Mailbox
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
     * @return Mailbox
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
     * @return Mailbox
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
     * Add Preferences
     *
     * @param Entities\MailboxPreference $preferences
     * @return Mailbox
     */
    public function addPreference(\Entities\MailboxPreference $preferences)
    {
        $this->Preferences[] = $preferences;

        return $this;
    }

    /**
     * Remove Preferences
     *
     * @param Entities\MailboxPreference $preferences
     */
    public function removePreference(\Entities\MailboxPreference $preferences)
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
     * @var Entities\Domain
     */
    private $Domain;


    /**
     * Set Domain
     *
     * @param Entities\Domain $domain
     * @return Mailbox
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
     * Add Preferences
     *
     * @param Entities\MailboxPreference $preferences
     * @return Mailbox
     */
    public function addMailboxPreference(\Entities\MailboxPreference $preferences)
    {
        $this->Preferences[] = $preferences;
        return $this;
    }

    /**
     * Set the maildir
     *
     * Replaces the following characters in the $maildir parameter:
     *
     * %u - the local part of the username (email address)
     * %d - the domain part of the username (email address)
     * %m - the username (email address)
     *
     * FIXME refactor formatMaildir/Homedir()
     * FIXME allow for multiple storage formats including uniform hashing
     *
     * @param string $maildir The maildir format
     * @return string The newly created maildir (also set in the object)
     */
    public function formatMaildir( $maildir = '' )
    {
        $this->setMaildir( self::substitute( $this->getUsername(), $maildir ) );
        return $this->getMaildir();
    }

    /**
     * Set the homedir
     *
     * Replaces the following characters in the $homedir parameter:
     *
     * %u - the local part of the username (email address)
     * %d - the domain part of the username (email address)
     * %m - the username (email address)
     *
     * FIXME refactor formatMaildir/Homedir()
     * FIXME allow for multiple storage formats including uniform hashing
     *
     * @param string $homedir The homedir format
     * @return string The newly created homedir (also set in the object)
     */
    public function formatHomedir( $homedir = '' )
    {
        $this->setHomedir( self::substitute( $this->getUsername(), $homedir ) );
        return $this->getHomedir();
    }

    /**
     * Replaces the following characters in the $str parameter:
     *
     * %u - the local part of the username (email address)
     * %d - the domain part of the username (email address)
     * %m - the username (email address)
     *
     * @param string $email An email address used to extract the domain name
     * @param string $str The format string
     * @return string The newly created maildir (also set in the object)
     */
    public static function substitute( $email, $str )
    {
        list( $un, $dn ) = explode( '@', $email );

        $str = str_replace ( '%atmail', substr( $email, 0, 1 ) . '/' . substr( $email, 1, 1 ) . '/' . $email, $str );
        $str = str_replace ( '%u',      $un,    $str );
        $str = str_replace ( '%d',      $dn,    $str );
        $str = str_replace ( '%m',      $email, $str );

        return $str;
    }

    /**
     * Set homedir_size
     *
     * @param integer $homedirSize
     * @return Mailbox
     */
    public function setHomedirSize($homedirSize)
    {
        $this->homedir_size = $homedirSize;

        return $this;
    }

    /**
     * Get homedir_size
     *
     * @return integer
     */
    public function getHomedirSize()
    {
        return $this->homedir_size;
    }

    /**
     * Set maildir_size
     *
     * @param integer $maildirSize
     * @return Mailbox
     */
    public function setMaildirSize($maildirSize)
    {
        $this->maildir_size = $maildirSize;

        return $this;
    }

    /**
     * Get maildir_size
     *
     * @return integer
     */
    public function getMaildirSize()
    {
        return $this->maildir_size;
    }

    /**
     * Set size_at
     *
     * @param \DateTime $sizeAt
     * @return Mailbox
     */
    public function setSizeAt($sizeAt)
    {
        $this->size_at = $sizeAt;

        return $this;
    }

    /**
     * Get size_at
     *
     * @return \DateTime
     */
    public function getSizeAt()
    {
        return $this->size_at;
    }

    /**
     * Clean a maildir string into a standard filesystem path
     *
     * For example, turns: ''maildir:/srv/vmail/example.com/jbloggs/mail:LAYOUT=fs''
     * into: /srv/vmail/example.com/jbloggs/mail
     *
     * @param string $maildir The maildir string
     * @return string The path from $maildir
     */
    public static function cleanMaildir( $maildir )
    {
        // typical maildir that needs to be cleaned:
        //     maildir:/srv/vmail/example.com/jbloggs/mail:LAYOUT=fs
        if( substr( $maildir, 0, 8 ) == 'maildir:' )
        $maildir = substr( $maildir, 8 );

        if( substr( $maildir, strrpos( $maildir, ':' ) + 1, 6 ) == 'LAYOUT' )
        $maildir = substr( $maildir, 0, strrpos( $maildir, ':' ) );

        return $maildir;
    }
    /**
     * @var \Entities\DirectoryEntry
     */
    private $DirectoryEntry;


    /**
     * Set DirectoryEntry
     *
     * @param \Entities\DirectoryEntry $directoryEntry
     * @return Mailbox
     */
    public function setDirectoryEntry(\Entities\DirectoryEntry $directoryEntry = null)
    {
        $this->DirectoryEntry = $directoryEntry;

        return $this;
    }

    /**
     * Get DirectoryEntry
     *
     * @return \Entities\DirectoryEntry
     */
    public function getDirectoryEntry()
    {
        return $this->DirectoryEntry;
    }
    /**
     * @var boolean
     */
    private $delete_pending;


    /**
     * Set delete_pending
     *
     * @param boolean $deletePending
     *
     * @return Mailbox
     */
    public function setDeletePending($deletePending)
    {
        $this->delete_pending = $deletePending;

        return $this;
    }

    /**
     * Get delete_pending
     *
     * @return boolean
     */
    public function getDeletePending()
    {
        return $this->delete_pending;
    }
}
