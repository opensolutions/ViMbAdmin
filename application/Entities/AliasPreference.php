<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * AliasPreference
 */
class AliasPreference
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * @var integer
     */
    private $ix;

    /**
     * @var string
     */
    private $op;

    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $expire;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Entities\Alias
     */
    private $Alias;


    /**
     * Set attribute
     *
     * @param string $attribute
     * @return AliasPreference
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
     * @return AliasPreference
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
     * @return AliasPreference
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
     * @return AliasPreference
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
     * @return AliasPreference
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
     * Set Alias
     *
     * @param \Entities\Alias $alias
     * @return AliasPreference
     */
    public function setAlias(\Entities\Alias $alias = null)
    {
        $this->Alias = $alias;
    
        return $this;
    }

    /**
     * Get Alias
     *
     * @return \Entities\Alias 
     */
    public function getAlias()
    {
        return $this->Alias;
    }
}
