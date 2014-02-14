<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * RememberMe
 */
class RememberMe
{
    /**
     * @var string
     */
    private $userhash;

    /**
     * @var string
     */
    private $ckey;

    /**
     * @var string
     */
    private $original_ip;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Entities\Admin
     */
    private $User;


    /**
     * Set userhash
     *
     * @param string $userhash
     * @return RememberMe
     */
    public function setUserhash($userhash)
    {
        $this->userhash = $userhash;
    
        return $this;
    }

    /**
     * Get userhash
     *
     * @return string 
     */
    public function getUserhash()
    {
        return $this->userhash;
    }

    /**
     * Set ckey
     *
     * @param string $ckey
     * @return RememberMe
     */
    public function setCkey($ckey)
    {
        $this->ckey = $ckey;
    
        return $this;
    }

    /**
     * Get ckey
     *
     * @return string 
     */
    public function getCkey()
    {
        return $this->ckey;
    }

    /**
     * Set original_ip
     *
     * @param string $originalIp
     * @return RememberMe
     */
    public function setOriginalIp($originalIp)
    {
        $this->original_ip = $originalIp;
    
        return $this;
    }

    /**
     * Get original_ip
     *
     * @return string 
     */
    public function getOriginalIp()
    {
        return $this->original_ip;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return RememberMe
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    
        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return RememberMe
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set User
     *
     * @param \Entities\Admin $user
     * @return RememberMe
     */
    public function setUser(\Entities\Admin $user = null)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return \Entities\Admin 
     */
    public function getUser()
    {
        return $this->User;
    }
    /**
     * @var \DateTime
     */
    private $last_used;


    /**
     * Set last_used
     *
     * @param \DateTime $lastUsed
     * @return RememberMe
     */
    public function setLastUsed($lastUsed)
    {
        $this->last_used = $lastUsed;
    
        return $this;
    }

    /**
     * Get last_used
     *
     * @return \DateTime 
     */
    public function getLastUsed()
    {
        return $this->last_used;
    }

}
