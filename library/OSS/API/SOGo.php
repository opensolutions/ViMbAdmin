<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
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
 * A SOGo API via direct database manipulation.
 *
 * @category   OSS
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_API_SOGo
{
    // use DBAL connections for database manipulation
    use OSS_Doctrine2_DBAL_Connection;

    /**
     * Constructor - creates a new DBAL connection.
     *
     * @param array $dbparams
     * @return void
     */
    public function __construct( $dbparams )
    {
        $this->getDBAL( $dbparams );
    }


    /**
     * Get all exiting users profiles form SOGo database as an array.
     *
     * NOTICE: c_defaults and c_settings in database is stored as json array
     *
     * @param void
     * @return array All users profiles existing in the database
     * @access public
     */
    public function getAllUsersProfiles()
    {
        $profiles =  $this->getDBAL()->fetchAll( 'SELECT * FROM sogo_user_profile' );

        if( !$profiles )
            return false;

        foreach( $profiles as $idx => $profile )
        {
            if( $profile['c_defaults'] )
                $profiles[ $idx ]['c_defaults'] = json_decode( $profile['c_defaults'], true );

            if( $profile['c_settings'] )
                $profiles[ $idx ]['c_settings'] = json_decode( $profile['c_settings'], true );
        }

        return $profiles;
    }


    /**
     * Get user's profile from the SOGo database as an array.
     *
     * NOTICE: c_defaults and c_settings in database is stored as json array
     *
     * @param strint $uid User id ( usrname )
     * @return array|bool
     * @access public
     */
    public function getUserProfile( $uid )
    {
        $profile = $this->getDBAL()->fetchAssoc( 'SELECT * FROM sogo_user_profile WHERE c_uid = ?',
            [ $uid ]
        );

        if( !$profile )
            return false;

        if( $profile['c_defaults'] )
            $profile['c_defaults'] = json_decode( $profile['c_defaults'], true );

        if( $profile['c_settings'] )
            $profile['c_settings'] = json_decode( $profile['c_settings'], true );

        return $profile;
    }

    /**
     * Adds user profile to SOGo's database
     *
     * NOTICE: c_defaults and c_settings in database is stored as JSON array
     *
     * @param strint     $uid       New users id ( username )
     * @param bool|array $defaults  Users profile defaults. If it false it will not be added.
     * @param bool|array $settings  Users profile settings. If it false it will not be added.
     * @return bool
     * @access public
     */
    public function addUserProfile( $uid, $defaults = false, $settings = false )
    {
        $params = [ 'c_uid' => $uid ];

        if( $defaults )
            $params['c_defaults'] = json_encode( $defaults );

        if( $settings )
            $params['c_settings'] = json_encode( $settings );

        return $this->getDBAL()->insert( 'sogo_user_profile', $params );
    }

    /**
     * Updates SOGo users profile
     *
     * To  reset defaults or settings pass empty array. If defaults and settings will be false function will return
     * false without even trying to process sql query.
     *
     * NOTICE: c_defaults and c_settings in database is stored as JSON array
     *
     * @param strint     $uid       Users id to edit ( username )
     * @param bool|array $defaults  Users profile defaults. If it false it will not be updated to null it will leave es it is.
     * @param bool|array $settings  Users profile settings. If it false it will not be updated to null it will leave es it is.
     * @return bool
     * @access public
     */
    public function updateUserProfile( $uid, $defaults = false, $settings = false  )
    {
        if( $defaults === false && $settings === false )
            return false;

        $params = [];
        if( $defaults )
            $params['c_defaults'] = json_encode( $defaults );

        if( $settings )
            $params['c_settings'] = json_encode( $settings );

        return $this->getDBAL()->update( 'sogo_user_profile', $params,
            [ 'c_uid' => $uid ]
        );
    }

    /**
     * Sets access privileges to resource for another SOGo user.
     *
     * First script fill find acl table name for resource. then it will remove privileges
     * for the user and then it will add new ones.
     *
     * Privileges array sample
     *      $privileges = ['ConfidentialDandTViewer','ObjectCreator','PublicViewer','ConfidentialViewer','ObjectEraser'];
     *
     * @param string      $uid        Users id (username) to share resource with.
     * @param string      $own_uid    Resource owner SOGo user id (username)
     * @param string      $name       Resource name for example personal.
     * @param strig|array $privileges Privileges to set.
     * @param bool        $calendar   If it set to true then it will look for calendar. False for contacts.
     * @return bool
     */
    public function setAccessPrivileges( $uid, $own_uid, $name, $privileges, $calendar = true )
    {
        $type = $calendar ? 'Calendar' : 'Contacts';
        $privileges = is_array( $privileges ) ? $privileges : [ $privileges ];

        $acl_loc = $this->getDBAL()->fetchColumn( 'SELECT c_acl_location FROM sogo_folder_info WHERE c_path2 = ? AND c_path3 = ? AND c_path4 = ?',
            [ $own_uid, $type, $name ]
        );

        if( !$acl_loc )
            return false;

        $acl_name = substr( $acl_loc, strrpos( $acl_loc, "/") + 1 );
        $params = [
            'c_uid'    => $uid,
            'c_object' => sprintf( "/%s/%s/%s", $own_uid, $type, $name )
        ];

        $this->getDBAL()->delete( $acl_name, [ 'c_uid' => $uid ] );
        foreach( $privileges as $privilege )
        {
            $params['c_role'] = $privilege;
            $this->getDBAL()->insert( $acl_name, $params );
        }
        return true;
        
    }

    /**
     * Unsets access privileges to resource for another SOGo user.
     *
     * First script fill find acl table name for resource. then it will remove privileges
     * for the user.
     *
     * @param string      $uid        Users id (username) to share resource with.
     * @param string      $own_uid    Resource owner SOGo user id (username)
     * @param string      $name       Resource name for example personal.
     * @param bool        $calendar   If it set to true then it will look for calendar. False for contacts.
     * @return bool
     */
    public function unsetAccessPrivileges( $uid, $own_uid, $name, $calendar = true )
    {
        $type = $calendar ? 'Calendar' : 'Contacts';

        $acl_loc = $this->getDBAL()->fetchColumn( 'SELECT c_acl_location FROM sogo_folder_info WHERE c_path2 = ? AND c_path3 = ? AND c_path4 = ?',
            [ $own_uid, $type, $name ]
        );

        if( !$acl_loc )
            return false;

        $acl_name = substr( $acl_loc, strrpos( $acl_loc, "/") + 1 );
        $params = [
            'c_uid'    => $uid,
            'c_object' => sprintf( "/%s/%s/%s", $own_uid, $type, $name )
        ];

        $this->getDBAL()->delete( $acl_name, [ 'c_uid' => $uid ] );
        return true;
  
    }


    /**
     * Subscribe resource
     *
     * @param string  $uid      Users id (username) which is suscribing
     * @param string  $own_uid  Resource owner SOGo user id (username)
     * @param string  $name     Resource name for subscribing
     * @param bool    $calendar If it set to true then it will look for calendar. False for contacts.
     * @param string  $color    Color for new calendar. Applies only for calendars.
     * @return bool
     */
    public function subscribeResource( $uid, $own_uid, $name, $calendar = true , $color = false )
    {
        $type = $calendar ? 'Calendar' : 'Contacts';

        $profile = $this->getUserProfile( $uid );
        if( !$profile )
            return false;

        //FIXME: Check if resource exists

        $settings = $profile['c_settings'];
        $resource = sprintf( "%s:%s/%s", $own_uid, $type, $name );
        if( !is_array( $settings ) )
            $settings = [];
        
        if( !isset( $settings[ $type ]['SubscribedFolders'] ) )
            $settings[ $type ]['SubscribedFolders'] = [];

        if( !in_array( $resource, $settings[ $type ]['SubscribedFolders'] ) )
        {
            $settings[ $type ]['SubscribedFolders'][] = $resource;

            if( $calendar && $color )
                $settings[ $type ]['FolderColors'][ $resource ] = $color;
        }
        return $this->updateUserProfile( $uid, null, $settings );
    }

    /**
     * Gets calendar or contacts resource list for user.
     *
     * Return array structure:
     *   array(1) {
     *     [0] =>
     *     array(3) {
     *       'db_table' =>
     *       string(23) "sogoopensolu00471298964"
     *       'resource_name' =>
     *       string(8) "personal"
     *       'display_name' =>
     *       string(17) "Personal Calendar"
     *     }
     *   }
     *
     * @param string $uid      Users id (username) to have a list of his resources.
     * @param bool   $calendar If it set to true then it will look for calendars. False for contacts.
     * @return array|bool
     */
    public function getResourceNames( $uid, $calendar = true )
    {
        $type = $calendar ? 'Calendar' : 'Contacts';

        $data = $this->getDBAL()->fetchAll( 'SELECT c_location as db_table, c_path4 as resource_name, c_foldername as display_name FROM sogo_folder_info WHERE c_path2 = ? AND c_path3 = ?',
            [ $uid, $type ]
        );

        if( !$data )
            return false;
        
        if( isset( $data['db_table'] ) )
            $data = [ $data ];

        foreach( $data as $idx => $row )
            $data[$idx]['db_table'] = substr( $row['db_table'], strrpos( $row['db_table'], "/") + 1 );
        
        return $data;
    }
}
