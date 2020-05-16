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
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * A DAViCal API via direct database manipulation.
 *
 * @see http://davical.org/
 *
 * @category   OSS
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_API_DAViCal
{
    //Use DBAL connections for database manipulation
    use OSS_Doctrine2_DBAL_Connection;
    
    /**
     * Privileges constants which refelects DAViCal database scheme
     */
    const PRIVILEGES_RW    = '000000001111111011100111';
    const PRIVILEGES_RO    = '000000000001001000100001';
    const PRIVILEGES_NONE  = '000000000000000000000000';
    const PRIVILEGES_BLOCK = '000000000001110000000000';
    
    /**
     * Names of Privieleges
     */
    public static $PRIVILEGES = [
        self::PRIVILEGES_RW   => "Read / Write",
        self::PRIVILEGES_RO   => "Read Only",
        self::PRIVILEGES_NONE => "No Actions"
    ];
    
    /**
     * Principal type IDs
     */
    const PRINCIPAL_TYPE_PERSON     = 1;
    const PRINCIPAL_TYPE_RESOURCE   = 2;
    const PRINCIPAL_TYPE_GROUP      = 3;
    
    /**
     * Collection types
     */
    const COLLECTION_TYPE_CALENDAR    = 'calendar';
    const COLLECTION_TYPE_ADDRESSBOOK = 'addressbook';
    
    /**
     * Password hashing methods
     */
    const PASSWORD_HASH_SSHA  = "hash";
    const PASSWORD_HASH_MD5   = "md5";
    const PASSWORD_HASH_PLAIN = "plain";
    
    
    /**
     * Constructor 
     * Initialize DBAL connection.
     */
    public function __construct( $dbparams )
    {
        $this->getDBAL( $dbparams );
    }
    
    /**
     * Get all users registered in the database as an array.
     *
     * Returns:
     *     array (size=n)
     *         0 =>
     *            'user_no' => int 1
     *            'active' => boolean true
     *            'email_ok' => string '2012-12-07 00:00:00+00' (length=22)
     *            'joined' => string '2012-12-07 11:49:55.231231+00' (length=29)
     *            'updated' => string '2012-12-07 13:27:31.698669+00' (length=29)
     *            'last_used' => string '2012-12-11 10:01:29.831451+00' (length=29)
     *            'username' => string 'usrname' (length=7)
     *            'password' => string 'hashed' (length=9)
     *            'fullname' => string 'Name susrname' (length=13)
     *            'email' => string 'example@example.ie' (length=18)
     *            'config_data' => null
     *            'date_format_type' => string 'E' (length=1)
     *            'locale' => string 'en' (length=2)
     *         1 =>
     *             ...
     *
     * @return array  All users registered in the database
     */
    public function getAllUsers()
    {
        return $this->getDBAL()->fetchAll( "SELECT * FROM principal" );
    }
    
    /**
     * Create user.
     *
     * params:
     *     array
     *            'active' => boolean
     *            'email_ok' => string date time   e.g.'2012-12-07 00:00:00+00'
     *            'joined' => string date time     e.g.'2012-12-07 00:00:00+00'
     *            'updated' => string date time    e.g.'2012-12-07 00:00:00+00'
     *            'last_used' => string date time  e.g.'2012-12-07 00:00:00+00'
     *            'username' => string             mandatory
     *            'password' => string 
     *            'fullname' => string 
     *            'email' => string 
     *            'config_data' => null
     *            'date_format_type' => string     e.g. 'E' 
     *            'locale' => string               e.g. 'en'
     *
     * Returns:
     *      array
     *            'user_no' => int 1
     *            'active' => boolean true
     *            'email_ok' => string '2012-12-07 00:00:00+00' (length=22)
     *            'joined' => string '2012-12-07 11:49:55.231231+00' (length=29)
     *            'updated' => string '2012-12-07 13:27:31.698669+00' (length=29)
     *            'last_used' => string '2012-12-11 10:01:29.831451+00' (length=29)
     *            'username' => string 'usrname' (length=7)
     *            'password' => string 'encrypted' (length=9)
     *            'fullname' => string 'Name susrname' (length=13)
     *            'email' => string 'example@example.ie' (length=18)
     *            'config_data' => null
     *            'date_format_type' => string 'E' (length=1)
     *            'locale' => string 'en' (length=2)
     *
     * @param array $params User parameters
     * @return array  of user details
     */
    public function createUser( $params )
    {   
        $this->getDBAL()->insert( 'usr', $params );
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM usr WHERE username = '{$params['username']}'" );
    }
    
    /**
     * Sets user password.
     * 
     * @param int $user_id User id ( user_no )
     * @param string $hashed_password Hashed pasword.
     * @return bool ture if success
     */
    public function setUserPassword( $user_id, $hashed_password )
    {   
        $values = [
            'password' => $hashed_password,
            'updated'  => 'now()'
        ];
        return $this->getDBAL()->update( 'usr', $values , [ 'user_no' => $user_id ] );
    }

    /**
     * Sets user active state.
     * 
     * @param int $user_id User id ( user_no )
     * @param bool $active Active state true for active, false for inactive.
     * @return bool ture if success
     */
    public function setUserActiveState( $user_id, $active )
    {   
        $values = [
            'active' => $active ? "TRUE" : "FALSE",
            'updated'  => 'now()'
        ];
        return $this->getDBAL()->update( 'usr', $values , [ 'user_no' => $user_id ] );
    }
    
    /**
     * Removes users
     *
     * @param int $user_id User id to remove (user_no)
     * @return bool true if removed.
     */
    public function removeUser( $user_id )
    {
        return $this->getDBAL()->delete( 'usr', [ 'user_no' => $user_id ] );
    }
    
    /**
     * Creates principal and dreturns principles data.
     *
     * Returns:
     *      array (size=5)
     *          'principal_id' => int 1
     *          'type_id' => int 1
     *          'user_no' => int 1
     *          'displayname' => string 'DAViCal Administrator' (length=21)
     *          'default_privileges' => string '000000000000000000000000' (length=24)
     *
     *
     * @param array $user Array of user details.
     * @param int $type Principal Type id, by default principal type is person.
     * @pram string $privileges Default prinicpals privileges by default is read/write.
     * @return array of prinicpal details
     */
    public function createPrincipal( $user, $type, $privileges = self::PRIVILEGES_NONE )
    {            
        $params = [ 
            'type_id'            => $type,
            'user_no'            => $user[ 'user_no' ],
            'displayname'        => $user[ 'fullname' ],
            'default_privileges' => $privileges
        ];
        
        $this->getDBAL()->insert( 'principal', $params );
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM principal WHERE user_no = '{$user['user_no']}'" );
    }
    
    /**
     * Creates user, principal and calendar. 
     * 
     * Takes same parameters as $this->createUser();
     * Returns an array wich contains user, principal and calendar collection data.
     *  array [
     *       'user'      => array users data,
     *       'principal' => array principals data,
     *     ]
     * 
     * @param array $params User parameters, sames as creatUser parameters.
     * @param int $type Principle type id
     * @return array Array wit data of user, principal and calendar collection.
     * 
     * @see createUser()
     * @see createPrincipal()
     */
    public function createPrincipalUser( $params, $type = self::PRINCIPAL_TYPE_PERSON )
    {
        $this->getDBAL()->beginTransaction();
        try
        {
            $user = $this->createUser( $params );
            $principal = $this->createPrincipal( $user, $type );
            
            $this->getDBAL()->commit();
        }
        catch( Exception $e )
        {
            $this->getDBAL()->rollback();
            throw $e;
        }
        
        return [ "user" => $user, "principal" => $principal ];
    }   
    
    /**
     * Creates colenction and returns it data.
     *
     * Returns:
     *  array (size=17)
     *      'user_no' => int 1024
     *      'parent_container' => string '/nbdavical/' (length=11)
     *      'dav_name' => string '/nbdavical/calendar/' (length=20)
     *      'dav_etag' => string '-1' (length=2)
     *      'dav_displayname' => string 'Davical User calendar' (length=21)
     *      'is_calendar' => boolean true
     *      'created' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *      'modified' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *      'public_events_only' => boolean false
     *      'publicly_readable' => boolean false
     *      'collection_id' => int 1034
     *      'default_privileges' => null
     *      'is_addressbook' => boolean false
     *      'resourcetypes' => string '<DAV::collection/><urn:ietf:params:xml:ns:carddav:calendar/>' (length=60)
     *      'schedule_transp' => string 'opaque' (length=6)
     *      'timezone' => null
     *      'description' => string '' (length=0)
     *
     * @param array $user Davical user data
     * @param string $type Collection type
     * @param string $name Calendars name
     * @param string|null $privileges Default privileges to access calendar
     * @param bool $public_events_only Flag to define if calendar contains only public event
     * @param bool $publicly_readable Flat to define publicly readable status
     * @param int|null $timezone Time zone id.
     * @param string $description Calendar description.
     * @return array
     */
    public function createCollection( $user, $type, $name = null, $privileges = null, $public_events_only = false, $publicly_readable = false, $timezone = null, $description = "" )
    {            
        if( $type == self::COLLECTION_TYPE_CALENDAR )
            $calendar = true;
        else if( $type == self::COLLECTION_TYPE_ADDRESSBOOK )
            $calendar = false;
        else throw new OSS_Exception( "Unknown collection type." );
            
        if( !$name ) 
            $name = $calendar ? $user[ "fullname" ] . " calendar" : $user[ "fullname" ] . " addressbook";
            
        $url_name = preg_replace( "/[^a-z0-9]/", '-', strtolower( $name ) );
        $dav_name = sprintf( "/%s/%s/", $user[ 'username' ], $url_name );
        if( $calendar )
            $last = $this->getDBAL()->fetchColumn( "SELECT dav_name FROM collection WHERE user_no = {$user[ 'user_no' ]} AND is_calendar = TRUE AND dav_name LIKE '{$dav_name}%' ORDER BY collection_id DESC LIMIT 1" );
        else       
            $last = $this->getDBAL()->fetchColumn( "SELECT dav_name FROM collection WHERE user_no = {$user[ 'user_no' ]} AND is_addressbook = TRUE AND dav_name LIKE '{$dav_name}%' ORDER BY collection_id DESC LIMIT 1" );
            
        if( $last )
        {
            $last = explode( "/", $last );
            $last = (int) OSS_Filter_Float::filter( $last[2] ) + 1;
            $dav_name = sprintf( "/%s/%s%s/", $user[ 'username' ], $url_name, $last );
        }
           
        $params = [ 
            'user_no'            => $user[ 'user_no' ],
            'parent_container'   => "/{$user[ 'username' ]}/",
            'dav_etag'           => -1,
            'dav_name'           => $dav_name,
            'dav_displayname'    => $name,
            'is_calendar'        => $calendar ? 1 : 0,
            'created'            => 'now()',
            'modified'           => 'now()',
            'public_events_only' => $public_events_only ? 1 : 0,
            'publicly_readable'  => $publicly_readable ? 1 : 0,
            'default_privileges' => $privileges,
            'is_addressbook'     => !$calendar ? 1 : 0,
            'resourcetypes'      => sprintf( "<DAV::collection/><urn:ietf:params:xml:ns:%s:%s/>", $calendar ? "caldav": "carddav", $calendar ? "calendar": "addressbook" ),
            'timezone'           => $timezone,
            'description'        => $description
        ];
        
        if( $this->getDBAL()->insert( 'collection', $params ) )
        {
            return $this->getDBAL()->fetchAssoc( "SELECT * FROM collection WHERE user_no = '{$user['user_no']}' AND dav_name = '{$params['dav_name']}'" );
        }
        else
            return false;
    }
    
    /**
     * Removes collection
     *
     * @param int $collection_id Collection id to remove
     * @return bool true if removed.
     */
    public function removeCollection( $collection_id )
    {
        return $this->getDBAL()->delete( 'collection', [ 'collection_id' => $collection_id ] );
    }
    
    /**
     * Creates calendar and returns calendar data.
     *
     * Returns same array structure as createCollection
     *
     * If principal has delegated principals, function iterates all of them and blocks access to new calendar.
     *
     * @param array $user Davical user data
     * @param string $name Calendars name
     * @param string|null $privileges Default privileges to access calendar
     * @param bool $public_events_only Flag to define if calendar contains only public event
     * @param bool $publicly_readable Flat to define publicly readable status
     * @param int|null $timezone Time zone id.
     * @param string $description Calendar description.
     *
     * @see createCollection()
     */
    public function createCalendar( $user, $name = null, $privileges = null, $public_events_only = false, $publicly_readable = false, $timezone = null, $description = "" )
    {
        $calendar = $this->createCollection( $user, self::COLLECTION_TYPE_CALENDAR, $name, $privileges, $public_events_only, $publicly_readable, $timezone, $description );

        $pids = $this->getGrantedToPrincipalsIds( $user['user_no'] );
        if( $pids && $calendar )
        {
            foreach( $pids as $pid )
            {
                $duser = $this->getPrincipalById( $pid['to_principal'] );
                if( $duser )
                    $this->grantPrivileges( $duser['user_no'], self::PRIVILEGES_BLOCK, null, $calendar['collection_id'] );
            }
        }

        return $calendar;
    }

    /**
     * Shares / delegates calendar
     *
     * Then sharing calendar then function iterate all users delegate principals if new calendar's owner
     * is already in list then it just update privileges. Otherwise it grants principal privileges to read only
     * then iterate to all calendars and block the access for them, and finally set correct privileges to given 
     * calendar.
     *
     * @param array $user Davical user data
     * @param string $description Calendar description.
     * @return bool
     */
    public function shareCalendar( $user_no, $collection_id, $privileges )
    {
        $calendar = $this->getCalendarById( $collection_id );
        $principal = $this->getPrincipalByUserId( $user_no );
        if( !$calendar || !$principal )
            return false;
        $dprincipal = $this->getPrincipalByUserId( $calendar['user_no'] );
        $pids = $this->getGrantedToPrincipalsIds( $calendar['user_no'] );
        
        $usr_del = false;
        if( $pids  )
        {
            foreach( $pids as $pid )
            {
                if( $pid['to_principal'] == $principal['principal_id'] )
                {
                    $usr_del = true;
                    break;
                }
            }
        }

        if( !$usr_del )
        {
            $ret = $this->grantPrivileges( $principal['user_no'], self::PRIVILEGES_RO, $dprincipal['user_no'] );
            if( !$ret )
                return false;
            $cals = $this->getCalendarsByUserId( $calendar['user_no'] );
            if( $cals )
            {
                foreach( $cals as $cal )
                {
                    $this->grantPrivileges( $principal['user_no'], self::PRIVILEGES_BLOCK, null, $cal['collection_id'] );
                }
            }

        }

        $this->removeGrantPrivileges( $principal['user_no'], null, $calendar['collection_id'] );
        return $this->grantPrivileges( $principal['user_no'], $privileges, null, $calendar['collection_id'] );
    }

    /**
     * Unshare calendar.
     *
     * Sets existent privileges to PRIVILEGES_BLOCK.
     *
     * @param array $user Davical user data
     * @param string $description Calendar description.
     * @return bool
     */
    public function unshareCalendar( $user_no, $collection_id )
    {
        $calendar = $this->getCalendarById( $collection_id );
        $principal = $this->getPrincipalByUserId( $user_no );
        if( !$calendar || !$principal )
            return false;
        $dprincipal = $this->getPrincipalByUserId( $calendar['user_no'] );
        $pids = $this->getGrantedToPrincipalsIds( $calendar['user_no'] );

        $this->removeGrantPrivileges( $principal['user_no'], null, $calendar['collection_id'] );
        return $this->grantPrivileges( $principal['user_no'], self::PRIVILEGES_BLOCK, null, $calendar['collection_id'] );
    }
    
    /**
     * Removes calendar
     *
     * Calls removeCollection() method
     *
     * @param int $collection_id Collection id to remove
     * @return bool true if removed.
     * @see removeCollection()
     */
    public function removeCalendar( $collection_id )
    {
        return $this->removeCollection( $collection_id );
    }
    
    /**
     * Creates user, principal and calendar. 
     * 
     * Takes same parameters as $this->createUser();
     * Returns an array wich contains user, principal and calendar collection data.
     *  array [
     *       'user'      => array users data,
     *       'principal' => array principals data,
     *       'calendar'  => array calendar collection data 
     *  ]      
     * 
     * @param array $params User parameters, sames as creatUser parameters.
     * @param string $cname Calendar name
     * @param int $type Principle type id
     * @return array Array wit data of user, principal and calendar collection.
     * 
     * @see createUser()
     * @see createPrincipal()
     * @see createCalendar()
     */
    public function createCalendarUser( $params, $cname = null, $type = self::PRINCIPAL_TYPE_PERSON )
    {
        $this->getDBAL()->beginTransaction();
        try
        {
            $user = $this->createUser( $params );
            $principal = $this->createPrincipal( $user, $type );
            $calendar = $this->createCalendar( $user, $cname );
            
            $this->getDBAL()->commit();
        }
        catch( Exception $e )
        {
            $this->getDBAL()->rollback();
            throw $e;
        }
        
        return [ "user" => $user, "principal" => $principal, "calendar" => $calendar ];
    }
    
    /**
     * Grants privileges to principal or collection
     * 
     * Grants privleges to principal if by_user_id is set or grant privileges to collection if by_collection_id is set. 
     * Users IDs should be passed, principal IDs will be loaded from database.
     *
     * NOTICE: $by_user_id or $by_collection_id are mandatory params, if both is null it will thorw an Exception.
     *
     * @param int $to_user_id Users for who privleges will be grant.
     * @param string Privileges to grant.
     * @param int $by_user_id User by who privileges will be grant.
     * @param int $by_collection_id Collection by which privileges will be grant.
     * @param bool $is_group Flag if to user is group
     * @return bool
     * @throws OSS_Execpion if $by_user_id and $by_collection_id is null
     */
    public function grantPrivileges( $to_user_id, $privileges, $by_user_id = null, $by_collection_id = null, $is_group = null )
    {
        if( !$by_user_id && !$by_collection_id )
            throw new OSS_Execption( "Missing mandatory arguments by user id or collection id" );

        $to_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$to_user_id}" );
        if( $by_user_id )
            $by_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$by_user_id}" );
        
        $params = [
            'by_principal'  => $by_user_id ? $by_principal['id'] : null,
            'to_principal'  => $to_principal['id'],
            'privileges'    => $privileges,
            'by_collection' => $by_collection_id,
            'is_group'      => $is_group
        ];
       
       return $this->getDBAL()->insert( 'grants', $params );
    }
    
    /**
     * Remove grant privileges
     * 
     * Grants privleges for prinicipal to collection or for principal to principal
     * Users IDs should be passed, principal IDs will be loaded from database.
     *
     * NOTICE: $by_user_id or $by_collection_id are mandatory params, if both is null it will thorw an Exception.
     *
     * @param int $to_user_id Users for who privleges will be removed.
     * @param int $by_user_id User by who privileges will be removed.
     * @param int $collection_id Collection by which privileges will be removed.
     * @return bool
     * @throws OSS_Execpion if $by_user_id and $by_collection_id is null
     */
    public function removeGrantPrivileges( $to_user_id, $by_user_id = null, $by_collection_id = null )
    {
        if( !$by_user_id && !$by_collection_id )
            throw new OSS_Execption( "Missing mandatory arguments by user id or collection id" );
            
        $to_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$to_user_id}" );
        $params = [ 'to_principal'  => $to_principal['id'] ];
        
        if( $by_collection_id )
            $params[ 'by_collection' ] = $by_collection_id;
        
        if( $by_user_id )
        {
            $by_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$by_user_id}" );
            $params[ 'by_principal'] = $by_user_id;
        }
        
        return $this->getDBAL()->delete( 'grants', $params );
    }
    
    /**
     * Update grant privileges to principal or collection
     * 
     * Updated grants privleges to principal if by_user_id is set or grant privileges to collection if by_collection_id is set. 
     * Users IDs should be passed, principal IDs will be loaded from database.
     *
     * NOTICE: $by_user_id or $by_collection_id are mandatory params, if both is null it will thorw an Exception.
     *
     * @param int $to_user_id Users for who privleges will be updated.
     * @param string Privileges to set.
     * @param int $by_user_id User by who privileges will be updated.
     * @param int $by_collection_id Collection by which privileges will be updated.
     * @return bool
     * @throws OSS_Execpion if $by_user_id and $by_collection_id is null
     */
    public function updateGrantPrivileges( $to_user_id, $privileges, $by_user_id = null, $by_collection_id = null )
    {
        if( !$by_user_id && !$by_collection_id )
            throw new OSS_Execption( "Missing mandatory arguments by user id or collection id" );
        
        $to_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$to_user_id}" );
        $params = [
            
            'to_principal'  => $to_principal['id'],
            'by_collection' => $by_collection_id
        ];
        if( $by_user_id )
        {
            $by_principal = $this->getDBAL()->fetchAssoc( "SELECT principal_id as id FROM principal WHERE user_no = {$by_user_id}" );
            $params[ 'by_principal'] = $by_user_id;
        }
        
        return $this->getDBAL()->update( 'grants', [ 'privileges' => $privileges ], $params );
    }       
    
    /**
     * Hash password for davical user.
     * 
     * It hashes three davical supported types hash:
     *  * First and most unsecured hash is plain it just add two start infont of password.
     *  * Second is md5 result is *<salt>* (where <salt> is a random series of characters not including '*') 
     *       then the rest of the string is a hash of (password + salt), i.e. a salted hash. 
     *  * Third  is SSHA result is is "*<salt>*<LDAP compatible SSHA password>" and the <LDAP compatible SSHA password> 
     *       is "{SSHA}<SHA-1 salted hash>". Read the code in /usr/share/awl/inc/AWLUtilities.php if you want to 
     *       understand that format more deeply! 
     *
     * @param string $password  Users password to login.
     * @param string $method Hash method
     * @return string return hashed string.
     */
    public static function hashPassword( $password, $method )
    {
        $salt = OSS_String::salt( 9 );
        if( $method == self::PASSWORD_HASH_PLAIN )
            return "**" . $password;
        else if( $method == self::PASSWORD_HASH_MD5 )
            return sprintf( "*%s*%s", $salt, md5( $password . $salt ) );    
        else if( $method == self::PASSWORD_HASH_SSHA )
            return sprintf( "*%s*{SSHA}%s", $salt, base64_encode( sha1( $password . $salt, true ) . $salt ) );
        else throw new OSS_Exception( 'Hash password method is unknown' );
    }
    
    /**
     * Gets user by Id ( user_no )
     *
     * Returns:
     *     array (size=13)
     *            'user_no' => int 1
     *            'active' => boolean true
     *            'email_ok' => string '2012-12-07 00:00:00+00' (length=22)
     *            'joined' => string '2012-12-07 11:49:55.231231+00' (length=29)
     *            'updated' => string '2012-12-07 13:27:31.698669+00' (length=29)
     *            'last_used' => string '2012-12-11 10:01:29.831451+00' (length=29)
     *            'username' => string 'usrname' (length=7)
     *            'password' => string 'encrypted' (length=9)
     *            'fullname' => string 'Name susrname' (length=13)
     *            'email' => string 'example@example.ie' (length=18)
     *            'config_data' => null
     *            'date_format_type' => string 'E' (length=1)
     *            'locale' => string 'en' (length=2)
     *
     * @param int $user_id User id (user_no)
     * @return array
     */
    public function getUserById( $user_id )
    {
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM usr WHERE user_no = {$user_id}" );
    }
    
    /**
     * Gets user by username
     *
     * Returns:
     *     array (size=13)
     *            'user_no' => int 1
     *            'active' => boolean true
     *            'email_ok' => string '2012-12-07 00:00:00+00' (length=22)
     *            'joined' => string '2012-12-07 11:49:55.231231+00' (length=29)
     *            'updated' => string '2012-12-07 13:27:31.698669+00' (length=29)
     *            'last_used' => string '2012-12-11 10:01:29.831451+00' (length=29)
     *            'username' => string 'usrname' (length=7)
     *            'password' => string 'encrypted' (length=9)
     *            'fullname' => string 'Name susrname' (length=13)
     *            'email' => string 'example@example.ie' (length=18)
     *            'config_data' => null
     *            'date_format_type' => string 'E' (length=1)
     *            'locale' => string 'en' (length=2)
     *
     * @param string $username Username
     * @return array
     */
    public function getUserByUsername( $username )
    {
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM usr WHERE username = '{$username}'" );
    }
    
    /**
     * Gets principal data by principal id
     *
     * Returns:
     *      array (size=5)
     *          'principal_id' => int 1
     *          'type_id' => int 1
     *          'user_no' => int 1
     *          'displayname' => string 'DAViCal Administrator' (length=21)
     *          'default_privileges' => string '000000000000000000000000' (length=24)
     *
     * @param int $principal_id Principal id
     * @return array
     */
    public function getPrincipalById( $principal_id )
    {
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM principal WHERE principal_id = {$principal_id}" );
    }

    /**
     * Gets principal data by user_no
     *
     * Returns:
     *      array (size=5)
     *          'principal_id' => int 1
     *          'type_id' => int 1
     *          'user_no' => int 1
     *          'displayname' => string 'DAViCal Administrator' (length=21)
     *          'default_privileges' => string '000000000000000000000000' (length=24)
     *
     * @param int $user_no User id
     * @return array
     */
    public function getPrincipalByUserId( $user_no )
    {
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM principal WHERE user_no = {$user_no}" );
    }
    
    /**
     * Gets calendar data by collection id
     *
     * Returns:
     *  array (size=17)
     *      'user_no' => int 1024
     *      'parent_container' => string '/nbdavical/' (length=11)
     *      'dav_name' => string '/nbdavical/calendar/' (length=20)
     *      'dav_etag' => string '-1' (length=2)
     *      'dav_displayname' => string 'Davical User calendar' (length=21)
     *      'is_calendar' => boolean true
     *      'created' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *      'modified' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *      'public_events_only' => boolean false
     *      'publicly_readable' => boolean false
     *      'collection_id' => int 1034
     *      'default_privileges' => null
     *      'is_addressbook' => boolean false
     *      'resourcetypes' => string '<DAV::collection/><urn:ietf:params:xml:ns:carddav:calendar/>' (length=60)
     *      'schedule_transp' => string 'opaque' (length=6)
     *      'timezone' => null
     *      'description' => string '' (length=0)
     *
     * @param int $collection_id Calendar collection id
     * @return array
     */
    public function getCalendarById( $collection_id )
    {
        return $this->getDBAL()->fetchAssoc( "SELECT * FROM collection WHERE collection_id = {$collection_id}" );
    }
    
    /**
     * Gets calendars data by user id ( user_no )
     *
     * Returns:
     *  array( size=n )
     *      0=> array (size=17)
     *          'user_no' => int 1024
     *          'parent_container' => string '/nbdavical/' (length=11)
     *          'dav_name' => string '/nbdavical/calendar/' (length=20)
     *          'dav_etag' => string '-1' (length=2)
     *          'dav_displayname' => string 'Davical User calendar' (length=21)
     *          'is_calendar' => boolean true
     *          'created' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *          'modified' => string '2012-12-13 11:39:07.767205+00' (length=29)
     *          'public_events_only' => boolean false
     *          'publicly_readable' => boolean false
     *          'collection_id' => int 1034
     *          'default_privileges' => null
     *          'is_addressbook' => boolean false
     *          'resourcetypes' => string '<DAV::collection/><urn:ietf:params:xml:ns:carddav:calendar/>' (length=60)
     *          'schedule_transp' => string 'opaque' (length=6)
     *          'timezone' => null
     *          'description' => string '' (length=0)
     *      1 => array ......   
     *
     * @param int $user_id Users id ( user_no )
     * @return array
     */
    public function getCalendarsByUserId( $user_id )
    {
        return $this->getDBAL()->fetchAll( "SELECT * FROM collection WHERE user_no = {$user_id} AND is_calendar = TRUE" );
    }
    
    /**
     * Gets users who has access to shared calendar by calendar id.
     *
     * Users with PRIVILEGES_BLOCK are ignored.
     *
     * Return:
     *  array ( size = n )
     *      0 => array (size=22)
     *          'by_principal' => int 1094
     *          'by_collection' => int 1095
     *          'to_principal' => int 1089
     *          'privileges' => string '000000001111111011100111' (length=24)
     *          'is_group' => null
     *          'principal_id' => int 1089
     *          'type_id' => int 1
     *          'user_no' => int 1048
     *          'displayname' => string 'Nerijus Barauskas' (length=17)
     *          'default_privileges' => string '000000000000000000000000' (length=24)
     *          'active' => boolean true
     *          'email_ok' => null
     *          'joined' => string '2012-12-14 13:25:53.173706+00' (length=29)
     *          'updated' => string '2012-12-14 13:25:53.173706+00' (length=29)
     *          'last_used' => null
     *          'username' => string 'nerijus@opensolutions.ie' (length=24)
     *          'password' => string '**qwerty78' (length=10)
     *          'fullname' => string 'Nerijus Barauskas' (length=17)
     *          'email' => string 'nerijus@opensolutions.ie' (length=24)
     *          'config_data' => null
     *          'date_format_type' => string 'E' (length=1)
     *          'locale' => null
     *      1 => aray ....
     * 
     * @param int $collection_id Collection id
     * @return array
     */
    public function getUsersForSharedCalendar( $collection_id )
    {
        return $this->getDBAL()->fetchAll( "SELECT * FROM grants as gr JOIN principal as pr ON pr.principal_id = gr.to_principal JOIN usr as us ON pr.user_no = us.user_no WHERE gr.by_collection = {$collection_id} AND gr.privileges <> '" . self::PRIVILEGES_BLOCK . "'" );
    }
    
    /**
     * Gets shared calendars which can be accessed by user
     *
     * Calendars with PRIVILEGES_BLOCK are ignored.
     *
     * Return:
     *  array ( size = n )
     *      0 => array (size=25)
     *          'by_principal' => int 1100
     *          'by_collection' => int 1101
     *          'to_principal' => int 1084
     *          'privileges' => string '000000001111111011100111' (length=24)
     *          'is_group' => null
     *          'principal_id' => int 1084
     *          'type_id' => int 1
     *          'user_no' => int 1051
     *          'displayname' => string 'Davical Box' (length=11)
     *          'default_privileges' => null
     *          'parent_container' => string '/sdcc@sdcc.ie/' (length=14)
     *          'dav_name' => string '/sdcc@sdcc.ie/calendar/' (length=23)
     *          'dav_etag' => string '-1' (length=2)
     *          'dav_displayname' => string 'South Dublin County Council calendar' (length=36)
     *          'is_calendar' => boolean true
     *          'created' => string '2012-12-14 16:24:20.325642+00' (length=29)
     *          'modified' => string '2012-12-14 16:24:20.325642+00' (length=29)
     *          'public_events_only' => boolean false
     *          'publicly_readable' => boolean false
     *          'collection_id' => int 1101
     *          'is_addressbook' => boolean false
     *          'resourcetypes' => string '<DAV::collection/><urn:ietf:params:xml:ns:carddav:calendar/>'' (length=61)
     *          'schedule_transp' => string 'opaque' (length=6)
     *          'timezone' => null
     *          'description' => string '' (length=0)
     *      1 => array ...
     *
     * @param int $user_id User id ( user_no )
     * @return array
     */
    public function getSharedCalendarsForUser( $user_id )
    {
        return $this->getDBAL()->fetchAll( "SELECT * FROM grants as gr JOIN principal as pr ON pr.principal_id = gr.to_principal JOIN collection as cl ON gr.by_collection = cl.collection_id WHERE pr.user_no = {$user_id} AND gr.privileges <> '" . self::PRIVILEGES_BLOCK . "'" );
    }

    /**
     * Gets principals IDs which have privileges to given users principal 
     *
     *
     * Return:
     *  array ( size = n )
     *      0 => ['to_principal' => int 1084 ]
     *      1 => ['to_principal' => int 1084 ]
     *      .............
     *      N => array ...
     *
     * @param int $user_id User id ( user_no )
     * @return array
     */
    public function getGrantedToPrincipalsIds( $user_id )
    {
        return $this->getDBAL()->fetchAll( "SELECT gr.to_principal FROM grants as gr JOIN principal as pr ON pr.principal_id = gr.by_principal WHERE pr.user_no = {$user_id} AND gr.by_principal IS NOT NULL" );
    }          
}
