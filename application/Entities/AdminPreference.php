<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\AdminPreference
 */
class AdminPreference
{
    /**
     * @var string $attribute
     */
    private $attribute;

    /**
     * @var integer $ix
     */
    private $ix;

    /**
     * @var string $op
     */
    private $op;

    /**
     * @var string $value
     */
    private $value;

    /**
     * @var integer $expire
     */
    private $expire;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Entities\Admin
     */
    private $Preferences;


    /**
     * Set attribute
     *
     * @param string $attribute
     * @return AdminPreference
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return string 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set ix
     *
     * @param integer $ix
     * @return AdminPreference
     */
    public function setIx($ix)
    {
        $this->ix = $ix;
    
        return $this;
    }

    /**
     * Get ix
     *
     * @return integer 
     */
    public function getIx()
    {
        return $this->ix;
    }

    /**
     * Set op
     *
     * @param string $op
     * @return AdminPreference
     */
    public function setOp($op)
    {
        $this->op = $op;
    
        return $this;
    }

    /**
     * Get op
     *
     * @return string 
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return AdminPreference
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set expire
     *
     * @param integer $expire
     * @return AdminPreference
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    
        return $this;
    }

    /**
     * Get expire
     *
     * @return integer 
     */
    public function getExpire()
    {
        return $this->expire;
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
     * Set Preferences
     *
     * @param Entities\Admin $preferences
     * @return AdminPreference
     */
    public function setPreferences(\Entities\Admin $preferences = null)
    {
        $this->Preferences = $preferences;
    
        return $this;
    }

    /**
     * Get Preferences
     *
     * @return Entities\Admin 
     */
    public function getPreferences()
    {
        return $this->Preferences;
    }
    /**
     * @var Entities\Admin
     */
    private $Admin;


    /**
     * Set Admin
     *
     * @param Entities\Admin $admin
     * @return AdminPreference
     */
    public function setAdmin(\Entities\Admin $admin = null)
    {
        $this->Admin = $admin;
    
        return $this;
    }

    /**
     * Get Admin
     *
     * @return Entities\Admin 
     */
    public function getAdmin()
    {
        return $this->Admin;
    }
}
