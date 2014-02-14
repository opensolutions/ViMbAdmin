<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 Open Source Solutions Limited
 *
 * ViMbAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ViMbAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ViMbAdmin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Open Source Solutions Limited T/A Open Solutions
 *   147 Stepaside Park, Stepaside, Dublin 18, Ireland.
 *   Barry O'Donovan <barry _at_ opensolutions.ie>
 *
 * @copyright Copyright (c) 2011 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 */

// load version information
require_once( dirname( __FILE__ ) . '/../library/ViMbAdmin/Version.php' );

mb_internal_encoding('UTF-8');
mb_language('uni');
setlocale(LC_ALL, "en_IE.utf8");

define( 'APPLICATION_ENV', scriptutils_get_application_env() );
define('APPLICATION_PATH', realpath( dirname(__FILE__) . '/../application' ) );

set_include_path( implode( PATH_SEPARATOR, array(
    realpath( APPLICATION_PATH . '/../library' ),
    get_include_path(),
)));

require_once 'Zend/Application.php';   

/**
 * Parses public/.htaccess for application environment
 *
 * die()'s if not found
 */
function scriptutils_get_application_env()
{
    $htaccess_path = dirname( __FILE__ ) . "/../public/.htaccess";

    if( !is_readable( $htaccess_path ) )
    {
        die( "ERROR: public/.htaccess does not exist / is not readable - set this up first!\n" );
    }

    $htaccess = file_get_contents( $htaccess_path );

    $matches = array();
    preg_match_all( '/SetEnv\s+APPLICATION_ENV\s+(\w+)/i', $htaccess, $matches );

    if( isset( $matches[1][0] ) && strlen( $matches[1][0] ) )
        return trim( $matches[1][0] );
    
    die( "ERROR: Could not parse or find APPLICATION_ENV in $htaccess_path\n" );
}

function get_doctrine2_entity_manager( $application, $db = 'default' ){

    $config = $application->getOption( 'resources' );

    $plugin = new OSS_Resource_Doctrine2( $config['doctrine2'] );
    $application->getBootstrap()->registerPluginResource( $plugin );

    //registers entity manager to zend_registry
    Zend_Registry::set( 'd2em', [ $db => $plugin->getDoctrine2( $db ) ] );
    return $plugin->getDoctrine2( $db );
}

function get_doctrine2_entity_cache()
{
    if( Zend_Registry::isRegistered( 'd2cache' ) )
       return Zend_Registry::get( 'd2cache' );
    else
    {
        $d2cache = $application->getBootstrap()->getResource( 'doctrine2cache' );
        Zend_Registry::set( 'd2cache', $d2cache );
        return $d2cache;
    }
}

function get_zend_application()
{
    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
    );

    $application->getBootstrap()->bootstrap( 'OSSAutoLoader' );
    return $application;
}




    

