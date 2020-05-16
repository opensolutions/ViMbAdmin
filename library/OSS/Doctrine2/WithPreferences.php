<?php

/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * All rights reserved.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 *
 * Contact: Barry O'Donovan - info (at) opensolutions (dot) ie
 *          http://www.opensolutions.ie/
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 *     http://www.opensolutions.ie/licenses/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@opensolutions.ie so we can send you a copy immediately.
 *
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Functions to add preference functionality to users / customers / companies / etc
 *
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Doctrine2_WithPreferences
{

    /**
     * The name of the class. Set with get_class( $this )
     * @var string
     */
    protected $_className;

    /**
     * The name of the preference class for this class
     * @var string
     */
    protected $_preferenceClassName;


    /**
     * Should we cache the preferences in the session?
     * @var boolean
     */
    private $_cache = true;

    /**
     * The namespace for the cache.
     * @var Zend_Session_Namespace
     */
    private $_namespace = null;


    /**
     * Return the entity object of the named preference
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param boolean $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return WithPreference If the named preference is not defined, returns FALSE; otherwise it returns the Doctrine_Record
     */
    public function loadPreference( $attribute, $index = 0, $includeExpired = false )
    {
        foreach( $this->_getPreferences() as $pref )
        {
            if( $pref->getAttribute() == $attribute && $pref->getIx() == $index )
            {
                if( !$includeExpired )
                {
                    if( $pref->getExpire() == 0 || $pref->getExpire() > time() )
                        return $pref;
                    else
                        return false;
                }
                else
                {
                    return $pref;
                }
            }
        }

        return false;
    }

    /**
     * Does the named preference exist or not?
     *
     * WARNING: Evaluate the return of this function using !== or === as a preference such as '0'
     * will evaluate as false otherwise.
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param boolean $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return boolean|string If the named preference is not defined or has expired, returns FALSE; otherwise it returns the preference
     * @see getPreference()
     */
    public function hasPreference( $attribute, $index = 0, $includeExpired = false )
    {
        return $this->getPreference( $attribute, $index, $includeExpired );
    }

    /**
     * Get the named preference
     *
     * WARNING: Evaluate the return of this function using !== or === as a preference such as '0'
     * will evaluate as false otherwise.
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param boolean $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return boolean|string If the named preference is not defined or has expired, returns FALSE; otherwise it returns the preference
     */
    public function getPreference( $attribute, $index = 0, $includeExpired = false )
    {
        foreach( $this->_getPreferences() as $pref )
        {
            if( $pref->getAttribute() == $attribute && $pref->getIx() == $index )
            {
                if( !$includeExpired && $pref->getExpire() != 0 && $pref->getExpire() < time() )
                    return false;

                return $pref->getValue();
            }
        }

        return false;
    }

    /**
     * Set (or update) a preference
     *
     * @param string $attribute The preference name
     * @param string $value The value to assign to the preference
     * @param string $op default '=' The operand (e.g. = (default), <, <=, :=, =, += etc)
     * @param int $expires default 0 The expiry as a UNIX timestamp. Default 0 which means never.
     * @param int $index default 0 If an indexed preference, set a specific index number. Default 0.
     * @return OSS_Doctrine_Record_WithPreferences An instance of this object for fluid interfaces.
     */
    public function setPreference( $attribute, $value, $operator = '=', $expires = 0, $index = 0 )
    {
        $pref = $this->loadPreference( $attribute, $index );

        if( $pref )
        {
            $pref->setValue( $value );
            $pref->setOp( $operator );
            $pref->setExpire( $expires );
            $pref->setIx( $index );

            return $this;
        }

        $pref = $this->_createPreferenceEntity( $this );
        $pref->setAttribute( $attribute );
        $pref->setOp( $operator );
        $pref->setValue( $value );
        $pref->setExpire( $expires );
        $pref->setIx( $index );

        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];
        $em->persist( $pref );
        return $this;
    }



    /**
     * Add an indexed preference
     *
     * Let's say we need to add a list of email addresses as a preference where the following is
     * the list:
     *
     *     $emails = array( 'a@b.c', 'd@e.f', 'g@h.i' );
     *
     * then we could add these as an indexed preference as follows for a given User $u:
     *
     *     $u->addPreference( 'mailing_list.goalies.email', $emails );
     *
     * which would result in database entries as follows:
     *
     *     attribute                      index   op   value
     *     ------------------------------------------------------
     *     | mailing_list.goalies.email | 0     | =  | a@b.c    |
     *     | mailing_list.goalies.email | 1     | =  | d@e.f    |
     *     | mailing_list.goalies.email | 2     | =  | g@h.i    |
     *     ------------------------------------------------------
     *
     * we could then add a fourth address as follows:
     *
     *     $u->addPreference( 'mailing_list.goalies.email', 'j@k.l' );
     *
     * which would result in database entries as follows:
     *
     *     attribute                      index   op   value
     *     ------------------------------------------------------
     *     | mailing_list.goalies.email | 0     | =  | a@b.c    |
     *     | mailing_list.goalies.email | 1     | =  | d@e.f    |
     *     | mailing_list.goalies.email | 2     | =  | g@h.i    |
     *     | mailing_list.goalies.email | 3     | =  | j@k.l    |
     *     ------------------------------------------------------
     *
     *
     * ===== BEGIN NOT IMPLEMENTED =====
     *
     * If out list was to be of names and emails, then we could create an array as follows:
     *
     *     $emails = array(
     *         array( 'name' => 'John Smith', 'email' => 'a@b.c' ),
     *         array( 'name' => 'David Blue', 'email' => 'd@e.f' )
     *     );
     *
     * then we could add these as an indexed preference as follows for a given User $u:
     *
     *     $u->addPreference( 'mailing_list.goalies', $emails );
     *
     * which would result in database entries as follows:
     *
     *     attribute                      index   op   value
     *     --------------------------------------------------------
     *     | mailing_list.goalies!email | 0     | =  | a@b.c      |
     *     | mailing_list.goalies!name  | 0     | =  | John Smith |
     *     | mailing_list.goalies!email | 1     | =  | d@e.f      |
     *     | mailing_list.goalies!name  | 1     | =  | David Blue |
     *     --------------------------------------------------------
     *
     * We can further be specific on operator for each one as follows:
     *
     *     $emails = array(
     *         array( 'name' => array( value = 'John Smith', operator = ':=', expires = '123456789' ) )
     *     );
     *
     * Note that in the above form, value is required but if either or both of operator or expires is
     * not set, it will be taken from the function parameters.
     *
     * ===== END NOT IMPLEMENTED =====
     *
     * @param string $attribute The preference name
     * @param string $value The value to assign to the preference
     * @param string $operator default '=' The operand (e.g. = (default), <, <=, :=, =, += etc)
     * @param int $expires default 0 The expiry as a UNIX timestamp. Default 0 which means never.
     * @param int $max The maximum index allowed. Defaults to 0 meaning no limit.
     * @return OSS_Doctrine_Record_WithPreferences An instance of this object for fluid interfaces.
     * @throws OSS_Doctrine2_WithPreferences_IndexLimitException If $max is set and limit exceeded
     */
    public function addIndexedPreference( $attribute, $value, $operator = '=', $expires = 0, $max = 0 )
    {
        // what's the current highest index and how many is there?
        $highest = -1; $count = 0;

        foreach( $this->getPreferences() as $pref )
        {
            if( $pref->getAttribute() == $attribute && $pref->getOp() == $operator )
            {
                ++$count;
                if( $pref->getIx() > $highest )
                    $highest = $pref->getIx();
            }
        }

        if( $max != 0 && $count >= $max )
            throw new \OSS_Doctrine2_WithPreferences_IndexLimitException( 'Requested maximum number of indexed preferences reached' );

        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];
        if( is_array( $value ) )
        {
            foreach( $value as $v )
            {
                $pref = $this->_createPreferenceEntity( $this );
                $pref->setAttribute( $attribute );
                $pref->setOp( $operator );
                $pref->setValue( $v );
                $pref->setExpire( $expires );
                $pref->setIx( ++$highest );

                $em->persist( $pref );
            }
        }
        else
        {
            $pref = $this->_createPreferenceEntity( $this );
            $pref->setAttribute( $attribute );
            $pref->setOp( $operator );
            $pref->setValue( $value );
            $pref->setExpire( $expires );
            $pref->setIx( ++$highest );

            $em->persist( $pref );
        }

        return $this;
    }


    /**
     * Clean expired preferences
     *
     * Cleans preferences with an expiry date less than $asOf but not set to 0 (never expires).
     *
     * WARNING: You need to EntityManager#flush() if the return >0!
     *
     * @param int $asOf default null The UNIX timestamp for the expriy, null means now
     * @param string $attribute default null Limit it to the specified attributes, null means all attributes
     * @return int The number of preferences deleted
     */
    public function cleanExpiredPreferences( $asOf = null, $attribute = null )
    {
        $count = 0;

        if( $asOf === null )
            $asOf = time();

        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];
        foreach( $this->_getPreferences() as $pref )
        {
            if( $attribute !== null && $pref->getAttribute() != $attribute )
                continue;

            if( $pref->getExpire() != 0 && $pref->getExpire() < $asOf )
            {
                $count++;
                $this->getPreferences()->removeElement( $pref );
                $em->remove( $pref );
            }
        }

        return $count;
    }

    /**
     * Delete the named preference
     *
     * WARNING: You need to EntityManager#flush() if the return >0!
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default null If an indexed preference then delete a specific index, if null then delete all
     * @return int The number of preferences deleted
     */
    public function deletePreference( $attribute, $index = null )
    {
        $count = 0;

        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];
        foreach( $this->_getPreferences() as $pref )
        {
            if( $pref->getAttribute() == $attribute )
            {
                if( $index === null || $pref->getIx() == $index )
                {
                    $count++;
                    $this->getPreferences()->removeElement( $pref );
                    $em->remove( $pref );
                }
            }
        }

        return $count;
    }


    /**
     * Delete all preferences for a user
     *
     * @return int The number of preferences deleted
     */
    public function expungePreferences()
    {
        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];

        return $em->createQuery( "DELETE \\Entities\\UserPreference up WHERE up.User = ?1" )
            ->setParameter( 1, $this )
            ->execute();
    }


    /**
     * Get indexed preferences as an array
     *
     * The standard response is an array of scalar values such as:
     *
     *     array( 'a', 'b', 'c' );
     *
     * If $withIndex is set to true, then it will be an array of associated arrays with the
     * index included:
     *
     *     array(
     *         array( 'p_index' => '0', 'p_value' => 'a' ),
     *         array( 'p_index' => '1', 'p_value' => 'b' ),
     *         array( 'p_index' => '2', 'p_value' => 'c' )
     *     );
     *
     * @param string  $attribute The attribute to load
     * @param boolean $withIndex default false Include index values. Default false.
     * @param boolean $ignoreExpired If set to false, include expired preferences
     * @return boolean|array False if no such preference(s) exist, otherwise an array.
     */
    public function getIndexedPreference( $attribute, $withIndex = false, $ignoreExpired = true )
    {
        $values = array();

        foreach( $this->getPreferences() as $pref )
        {
            if( $pref->getAttribute() == $attribute )
            {
                if( !$ignoreExpired && $pref->getExpire() != 0 && $pref->getExpire() < time() )
                    continue;

                if( $withIndex )
                    $values[ $pref->getIx() ] = array( 'p_index' => $pref->getIx(), 'p_value' => $pref->getValue() );
                else
                    $values[ $pref->getIx() ] = $pref->getValue();
            }
        }

        if( $values === array() )
            return false;

        ksort( $values, SORT_NUMERIC );
        return $values;
    }


    /**
     * Get associative preferences as an array.
     *
     * For example, if we have preferences:
     *
     *     attribute email.address   idx=0 value=1email
     *     attribute email.confirmed idx=0 value=false
     *     attribute email.tokens.0  idx=0 value=fwfddwde
     *     attribute email.tokens.1  idx=0 value=fwewec4r
     *     attribute email.address   idx=1 value=2email
     *     attribute email.confirmed idx=1 value=true
     *
     * and if we search by `$attribute = 'email'` we will get:
     *
     *     [
     *         0 => [
     *             'address' => '1email',
     *             'confirmed' => false,
     *             'tokens' => [
     *                 0 => 'fwfddwde',
     *                 1 => 'fwewec4r'
     *             ]
     *         ],

     *         1 => [
     *             'address' => '2email',
     *             'confirmed' => true
     *         ]
     *     ]
     *
     *
     * @param string  $attribute The attribute to load
     * @param int     $index If an indexed preference, get a specific index, null means all indexes alowed (default: null)
     * @param boolean $ignoreExpired If set to false, include expired preferences
     * @return boolean|array False if no such preference(s) exist, otherwise an array.
     */
    public function getAssocPreference( $attribute, $index = null, $ignoreExpired = true )
    {
        $values = array();

        foreach( $this->_getPreferences() as $pref )
        {
            if( strpos( $pref->getAttribute(), $attribute ) === 0 )
            {
                if( $index == null || $pref->getIx() == $index )
                {
                    if( !$ignoreExpired && $pref->getExpire() != 0 && $pref->getExpire() < time() )
                        continue;

                    if( strpos( $pref->getAttribute(), "." ) !== false )
                        $key = substr( $pref->getAttribute(), strlen( $attribute )+1 );

                    if( $key )
                    {
                        $key = "{$pref->getIx()}.{$key}";
                        $values = $this->_processKey( $values, $key, $pref->getValue() );
                    }
                    else
                        $values[ $pref->getIx() ] = $pref->getValue();
                }
            }
        }

        if( $values === array() )
            return false;

        return $values;
    }

    /**
     * Delete the named preference
     *
     * WARNING: You need to EntityManager#flush() if the return >0!
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default null If an indexed preference then delete a specific index, if null then delete all
     * @return int The number of preferences deleted
     */
    public function deleteAssocPreference( $attribute, $index = null )
    {
        $cnt = 0;

        $em = \Zend_Registry::get( 'd2em' )[ 'default' ];
        foreach( $this->_getPreferences() as $pref )
        {
            if( strpos( $pref->getAttribute(), $attribute ) === 0 )
            {
                if( $index == null || $pref->getIx() == $index)
                {
                    $this->getPreferences()->removeElement( $pref );
                    $em->remove( $pref );
                    $cnt++;
                }
            }
        }

        return $cnt;
    }


    /**
     * Gets full class name. e.g. \Entities\User
     * It can be used when writing doctrine 2 queries.
     *
     * @return string
     */
    private function _getFullClassname()
    {
        $this->_className = get_called_class();
        if( strpos( $this->_className, "__CG__" ) !== false )
        {
            $this->_className = substr( $this->_className, strpos( $this->_className, "__CG__" ) + 6 );
        }
        return $this->_className;
    }

    /**
     * Gets shorten class name. e.g. If full class name is\Entities\User
     * then shorten will be User.
     *
     * It can be used when writing doctrine 2 queries.
     *
     * @return string
     */
    private function _getShortClassname()
    {
        return substr( $this->_className, strrpos( $this->_className, '\\' ) + 1 );
    }

    /**
     * Creates preference object.
     * New preference object depends current class. e.g. If we extending
     * \Entities\Customer functionality then our preference object will be
     * \Entities\CustomerPreference.
     *
     * @return object
     */
    private function _createPreferenceEntity( $owner = null )
    {
        $prefClass = $this->_getFullClassname() . 'Preference';
        $pref = new $prefClass();

        if( $owner != null )
        {
            $setEntity = 'set' . $this->_getShortClassname();
            $pref->$setEntity( $owner );
            $owner->addPreference( $pref );
        }

        return $pref;
    }

    /**
     * This is  similar to the `getPreference()` method but it creates
     * and executes DQL instead of simply returning `$this->getPreferences()`.
     *
     * NOTICE: Function required due to `$this->getPreferences()` iteration failure.
     * FIXME This should not be necessary
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function _getPreferences()
    {
        $query = sprintf(
            "SELECT p FROM %sPreference p WHERE p.%s = %d",
            $this->_getFullClassname(), $this->_getShortClassname(), $this->getId()
        );

        return \Zend_Registry::get( 'd2em' )['default']->createQuery( $query )->getResult();
    }

    /**
     * Assign the key's value to the property list. Handles the
     * nest separator for sub-properties.
     *
     * @param  array  $config
     * @param  string $key
     * @param  string $value
     * @throws Zend_Config_Exception
     * @return array
     */
    private function _processKey($config, $key, $value)
    {
        if( strpos( $key, "." ) !== false)
        {
            $pieces = explode( ".", $key, 2 );
            if( strlen( $pieces[0] ) && strlen( $pieces[1] ) )
            {
                if( !isset( $config[ $pieces[0] ] ) )
                {
                    if( $pieces[0] === '0' && !empty( $config ) )
                        $config = array($pieces[0] => $config);
                    else
                        $config[ $pieces[0] ] = array();
                }
                elseif( !is_array( $config[$pieces[0]] ) )
                {
                    //die("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[ $pieces[0] ] = $this->_processKey( $config[ $pieces[0] ], $pieces[1], $value );
            }
            else
            {
                //die("Invalid key '$key'");
            }
        }
        else
        {
            $config[$key] = $value;
        }
        return $config;
    }

}
