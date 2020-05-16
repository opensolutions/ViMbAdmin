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
 * @package    OSS_Doctrine
 * @subpackage Record
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Doctrine
 * @subpackage Record
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Doctrine_Record_WithPreferences extends OSS_Doctrine_Record
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
     * @var bool
     */
    private $_cache = true;

    /**
     * The namespace for the cache.
     * @var Zend_Session_Namespace
     */
    private $_namespace = null;

    /**
     * Constructor
     *
     * Calls the parent class constructor and sets the class name and preference class name.
     *
     * @paramDoctrine_Table $table
     * @param bool $isNewEntry
     * @return void
     */
    public function __construct( $table = null, $isNewEntry = false )
    {
        parent::__construct( $table, $isNewEntry );

        $this->_className           = get_class($this);
        $this->_preferenceClassName = $this->_className . '_Preference';
    }


    /**
     * Set (or update) a preference
     *
     * @param string $attribute The preference name
     * @param string $value The value to assign to the preference
     * @param string $op default '=' The operand (e.g. = (default), <, <=, :=, =, += etc)
     * @param int $expires default 0 The expiry as a UNIX timestamp. Default 0 which means never.
     * @param int $index default 0 If an indexed preference, set a specific index number. Default 0.
     * @return OSS_Doctrine_Record_WithPreferences
     */
    public function setPreference( $attribute, $value, $operator = '=', $expires = 0, $index = 0 )
    {
        if( $pref = $this->loadPreference( $attribute, $index ) )
        {
            $pref['value']   = $value;
            $pref['op']      = $operator;
            $pref['expire']  = $expires;
            $pref['ix']      = $index;
            $pref->save();

            return $this;
        }

        $pref                      = new $this->_preferenceClassName();
        $pref[ $this->_className ] = $this;
        $pref['attribute']         = $attribute;
        $pref['op']                = $operator;
        $pref['value']             = $value;
        $pref['expire']            = $expires;
        $pref['ix']                = $index;
        $pref->save();

        return $this;
    }

    /**
     * Stores an object in the users preference table using serialize()
     *
     * @see setPreference()
     *
     * @param string $attribute The preference name
     * @param mixed $object The object to store
     * @param string $op default '=' The operand (e.g. = (default), <, <=, :=, =, += etc)
     * @param int $expires default 0 The expiry as a UNIX timestamp. Default 0 which means never.
     * @param int $index default 0 If an indexed preference, set a specific index number. Default 0.
     * @return OSS_Doctrine_Record_WithPreferences 
     */
    public function storeObject( $attribute, $object, $operator = '=', $expires = 0, $index = 0 )
    {
        return $this->setPreference( $attribute, serialize( $object ), $operator, $expires, $index );
    }

    /**
     * Loads an object in the users preference table using unserialize()
     *
     * @see getPreference()
     *
     * @param string $attribute The preference name
     * @param int $index If an indexed preference, set a specific index number. Default 0.
     * @param bool $includeExpired If true, include preferences even if they have expired. Default: false
     * @return bool|string
     */
    public function loadObject( $attribute, $index = 0, $includeExpired = false )
    {
        $object = $this->getPreference( $attribute, $index, $includeExpired );

        if( $object === false )
            return false;

        return unserialize( $object );
    }


    /**
     * Get the named preference
     *
     * WARNING: Evaluate the return of this function using !== or === as a preference such as '0'
     * will evaluate as false otherwise.
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param bool $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return bool|string
     */
    public function getPreference( $attribute, $index = 0, $includeExpired = false )
    {
        $query = Doctrine_Query::create()
            ->select( 'p.value' )
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.attribute = ?', $attribute )
            ->andWhere( 'p.ix = ?', $index );

        if ( !$includeExpired )
            $query->andWhere( '( p.expire = 0 OR p.expire >= ? )', mktime() );

        $pref = $query->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );

        if( $pref === array() )
            return false;

        return $pref;
    }


    /**
     * Load the ORM object of the named preference
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param bool $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return bool|Doctrine_Record
     */
    public function loadPreference( $attribute, $index = 0, $includeExpired = false )
    {
        $query = Doctrine_Query::create()
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.attribute = ?', $attribute )
            ->andWhere( 'p.ix = ?', $index );

        if( !$includeExpired )
            $query->andWhere( '( p.expire = 0 OR p.expire >= ? )', mktime() );

        return $query->fetchOne( null, Doctrine_Core::HYDRATE_RECORD );
    }


    /**
     * Delete the named preference
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default null If an indexed preference then delete a specific index, if null then delete all
     * @return OSS_Doctrine_Record_WithPreferences 
     */
    public function deletePreference( $attribute, $index = null )
    {
        $vQuery = Doctrine_Query::create()
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.attribute = ?', $attribute );

        if ($index !== null)
            $vQuery->andWhere( 'p.ix = ?', $index );

        $vQuery
            ->delete()
            ->execute();

        return $this;
    }


    /**
     * Does the named preference exist or not?
     *
     * WARNING: Evaluate the return of this function using !== or === as a preference such as '0'
     * will evaluate as false otherwise.
     *
     * @see getPreference()
     *
     * @param string $attribute The named attribute / preference to check for
     * @param int $index default 0 If an indexed preference, get a specific index (default: 0)
     * @param bool $includeExpired default false If true, include preferences even if they have expired. Default: false
     * @return bool|string
     */
    public function hasPreference( $attribute, $index = 0, $includeExpired = false )
    {
        return $this->getPreference( $attribute, $index, $includeExpired );
    }


    /**
     * Get a preference if it exists or set it and return if not
     *
     * A useful function to replace clauses such as:
     *
     * $pref = $user->getPreference( 'qwerty' );
     * if( $pref === false )
     * {
     *     $pref = 'default';
     *     $user->setPreference( 'qwerty', 'default' );
     * }
     *
     * with:
     *
     * $pref = $user->getOrSetGetPreference( 'qwerty', 'default' );
     *
     * @see getPreference()
     *
     * @param string $attribute The preference to get or get/set
     * @param mixed $default The default value to set the preference to and return if not aleady set
     * @param string $operator default '=' The operand for the preference. Defaults to '='
     * @param int $expires default 0 the expiry date as a UNIX timestamp, 0 means forever
     * @param int $index default 0 the index
     * @return mixed
     */
    public function getOrSetGetPreference( $attribute, $default, $operator = '=', $expires = 0, $index = 0 )
    {
        // is the preference already set?
        $pref = $this->getPreference( $attribute, $index );

        if( $pref !== false )
            return $pref;

        return $this->setPreference( $attribute, $default, $operator, $expires, $index );
    }

    /**
     * Clean expired preferences
     *
     * Cleans preferences with an expiry date less than $asOf but not set to 0 (never expires).
     *
     * @param int $asOf default null The UNIX timestamp for the expriy, null means now
     * @param string $attribute default null Limit it to the specified attributes, null means all attributes
     * @return OSS_Doctrine_Record_WithPreferences An instance of this object for fluid interfaces
     */
    public function cleanExpiredPreferences( $asOf = null, $attribute = null )
    {
        if( $asOf === null )
            $asOf = mktime();

        $query = Doctrine_Query::create()
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.expire > 0' )
            ->andWhere( 'p.expire < ?', $asOf )
            ->delete();

        if( $attribute != null )
            $query->andWhere( 'p.attribute = ?', $attribute );

        $query->execute();

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
     * @return OSS_Doctrine_Record_WithPreferences
     */
    public function addIndexedPreference( $attribute, $value, $operator = '=', $expires = 0 )
    {
        $conn = Doctrine_Manager::connection();

        $conn->beginTransaction();

        // what's the current highest index?
        $index = Doctrine_Query::create()
            ->from( $this->_preferenceClassName . ' p' )
            ->select( 'p.ix' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.attribute = ?', $attribute )
            ->orderBy( 'p.ix DESC' )
            ->limit( 1 )
            ->fetchArray();

        $index = isset( $index[0]['ix'] ) ? ( (int) $index[0]['ix'] ) + 1 : 0;

        if( is_array( $value ) )
        {
            foreach( $value as $v )
            {
                $pref                    = new $this->_preferenceClassName();
                $pref[$this->_className] = $this;
                $pref['attribute']       = $attribute;
                $pref['op']              = $operator;
                $pref['value']           = $v;
                $pref['expire']          = $expires;
                $pref['ix']              = $index;

                $pref->save();
                $index++;
            }
        }
        else
        {
            $pref                    = new $this->_preferenceClassName();
            $pref[$this->_className] = $this;
            $pref['attribute']       = $attribute;
            $pref['op']              = $operator;
            $pref['value']           = $value;
            $pref['expire']          = $expires;
            $pref['ix']              = $index;

            $pref->save();
        }

        $conn->commit();

        return $this;
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
     * @param bool $withIndex default false Include index values. Default false.
     * @return bool|array
     */
    public function getIndexedPreference( $attribute, $withIndex = false )
    {
        $query = Doctrine_Query::create()
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'p.attribute = ?', $attribute )
            ->orderBy( 'p.ix ASC' );

        if( $withIndex )
        {
            $pref = $query
                ->select( 'p.ix AS index, p.value AS value' )
                ->execute( null, Doctrine_Core::HYDRATE_SCALAR );
        }
        else
        {
            $pref = $query
                ->select( 'p.value' )
                ->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );

            if( !is_array( $pref ) )
                $pref = array( $pref );
        }

        if( $pref === array() )
            return false;

        return $pref;
    }

    /**
     * Should we use a session cache?
     *
     * Unless you have a good reason to specify a namespace, leave it as null.
     *
     * NB: Only preferences with op = '=' are cached. All others are not.
     *
     * @param bool $b Set to true to enable cache, false to disable
     * @param string $namespace The name of the Zend Namespace to us. If null, uses default.
     * @return void;
     */
    public function setPreferenceCache( $b = true, $namespace = null )
    {
        $this->_cache = $b;

        if( $namespace === null )
        {
            $this->setPreferenceNamespace( new Zend_Session_Namespace( 'Pref_' . $this->_preferenceClassName ) );
        }
    }

    /**
     * Do we use a session cache?
     *
     * Returns true if cache enabled, false if disabled
     *
     * @return bool 
     */
    public function getPreferenceCache()
    {
        return $this->_cache;
    }

    /**
     * Get the Zend_Session_Namespace for caching
     *
     * @return Zend_Session_Namespace
     */
    public function getPreferenceNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set the Zend_Session_Namespace to use for caching
     *
     * @param Zend_Session_Namespace $_namespace The Zend_Session_Namespace to use for caching
     * @return void
     */
    public function setPreferenceNamespace( $_namespace )
    {
        $this->_namespace = $_namespace;
    }


    /**
     * Get an array value from an associative array for a given array of indexes
     *
     * @see _setAssocElementViaArray()
     *
     * @param array $haystack The associative array in which to get the value
     * @param array $needle   An ordered array of keys in the associative array
     * @throws OSS_Doctrine_Exception
     * @return mixed The value requested
     */
    public function _getAssocElementViaArray( &$haystack, $needle )
    {
        if( !is_array( $haystack ) or !is_array( $needle ) )
            throw new OSS_Doctrine_Exception( 'Both $haystack and $needle must be arrays' );

        if( count( $needle ) == 0 )
            return $haystack;

        $e = array_pop( $needle );
        $h = $haystack;

        foreach( $needle as $n )
        {
            if( !isset( $h[$n] ) )
                throw new OSS_Doctrine_Exception( 'Invalid array key specified.' );

            $h = $h[$n];
        }

        return $h[$e];
    }

    /**
     * Set an array value in an associative array for a given array of indexes
     *
     * @param array $haystack The associative array in which to set the value
     * @param array $needle   An ordered array of keys in the associative array
     * @param mixed $value    The value to set
     * @throws OSS_Doctrine_Exception
     * @return void
     */
    public function _setAssocElementViaArray( &$haystack, $needle, $value )
    {
        if( !is_array( $haystack ) or !is_array( $needle ) )
            throw new OSS_Doctrine_Exception( 'Both $haystack and $needle must be arrays' );

        $e = array_pop( $needle );
        $h = &$haystack;

        foreach( $needle as $n )
            $h = &$h[$n];

        $h[$e] = $value;
    }


    /**
    * Returns with all the preferences of $pAttribute attribute as a Doctrine_Collection object.
    *
    * @param string $pAttribute
    * @return object
    */
    public function getPreferences($pAttribute)
    {
        return Doctrine_Query::create()
            ->select( '*' )
            ->from( $this->_preferenceClassName . ' p' )
            ->where( 'p.attribute = ?', $pAttribute )
            ->orderBy( 'p.ix asc, p.id asc' )
            ->execute();
    }

    /**
     * Get a preferences count
     *
     * A useful function to replace clauses such as:
     *
     * $vResults = Doctrine_Query::create()
     *                   ->select( '*' )
     *                   ->from( 'User_Preference up' )
     *                   ->where( 'up.User_id = ?', $this->_identity['user']->id )
     *                   ->andWhere( 'up.attribute = ?', 'tokens.mobile_confirm' )
     *                   ->andWhere( 'up.created_at >= ?', date( "Y-m-d H:i:s", time() - 3600 ) ) // last 1 hour
     *                   ->fetchArray();
     *
     * $cnt = sizeof( $vResults );
     *
     * with:
     *
     * $cnt = $user->getCounttPreferences( 'tokens.mobile_confirm', date( "Y-m-d H:i:s", time() - 3600 ) );
     *
     * @param string $attribute The preferences to count
     * @param date $dateFrom The date from witch created
     * @param date $dateFrom The date to witch created
     * @return int
     */
    public static function getCountPreferences( $attribute, $dateFrom = null, $dateTo = null)
    {
        $q = Doctrine_Query::create()
            ->select( 'COUNT(up.id)' )
            ->from( 'User_Preference up' )
            ->where( 'p.' . $this->_className . '_id = ?', $this['id'] )
            ->andWhere( 'up.attribute = ?', 'tokens.mobile_confirm' );

        if( $dateFrom != null && $dateFrom instanceof date )
            $q->andWhere( 'up.created_at >= ?', $dateFrom );

        if( $dateFrom != null && $dateFrom instanceof date )
            $q->andWhere( 'up.created_at <= ?', $dateTo );

        $res = $q->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );

        if( !$res )
            $res = 0;

        return $res;
    }

}
