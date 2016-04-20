#!/usr/bin/env php
<?php

/**
 * CLI script
 */

require_once( dirname( __FILE__ ) . '/../vendor/autoload.php' );
require_once( dirname( __FILE__ ) . '/utils.inc' );
//define( 'APPLICATION_ENV', scriptutils_get_application_env() );

define( 'SCRIPT_NAME', 'vimbadtool - ViMbAdmin CLI Management Tool' );
define( 'SCRIPT_COPY', '(c) Copyright 2010 - ' . date( 'Y' ) . ' Open Source Solutions Limited' );

error_reporting( E_ALL | E_STRICT );
//error_reporting( ( E_ALL | E_STRICT ) ^ E_NOTICE );

ini_set( 'display_errors', true );

defined( 'APPLICATION_PATH' ) || define( 'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application' ) );

if( getenv( 'APPLICATION_TESTING' ) )
    define( 'APPLICATION_TESTING', getenv( 'APPLICATION_TESTING' ) );
else
    define( 'APPLICATION_TESTING', 0 );

// Ensure library/ is on include_path
set_include_path( implode( PATH_SEPARATOR,
        array(
            realpath( APPLICATION_PATH . '/../library' ),
            get_include_path()
        )
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini' );

try
{
    $application->bootstrap();
    $bootstrap = $application->getBootstrap();
    $bootstrap->bootstrap( 'frontController' );
}
catch( Exception $e )
{
    die( print_r( $e, true ) );
}

try
{
    $opts = new Zend_Console_Getopt(
        array(
            'help|h'        => 'Displays usage information.',
            'action|a=s'    => 'Action to perform in format of module.controller.action',
            'verbose|v'     => 'Verbose messages will be dumped to the default output.',
            'debug|d'       => 'Enables debug mode.',
            'copyright|c'   => 'Display copyright information.'
        )
    );

    $opts->parse();
}
catch( Zend_Console_Getopt_Exception $e )
{
    exit( $e->getMessage() ."\n\n". $e->getUsageMessage() );
}

if( isset( $opts->h ) )
{
    echo SCRIPT_NAME . "\n" . SCRIPT_COPY . "\n\n";
    echo $opts->getUsageMessage();

    exit;
}

if( isset( $opts->c ) )
{
    echo SCRIPT_NAME . "\n" . SCRIPT_COPY . "\n\n";
    echo "Information in this file is strictly confidential and the property of\n"
         . "Open Source Solutions Limited and may not be extracted or distributed,\n"
         . "in whole or in part, for any purpose whatsoever, without the express\n"
         . "written consent from Open Source Solutions Limited.\n\n";

    exit;
}

if( isset( $opts->a ) )
{
    try
    {
        $reqRoute = array_reverse( explode( '.', $opts->a ) );

        @list( $action, $controller, $module ) = $reqRoute;

        if ( ($action != '') && ($controller == '') )
        {
            $controller = $action;
            $action = 'index';
        }

        if ( $opts->d )
        {
            echo "Module:     $module\n";
            echo "Controller: $controller\n";
            echo "Action:     $action\n\n";
        }

        $front = $bootstrap->frontController;

        $front->throwExceptions( true );

        $front->setRequest(  new Zend_Controller_Request_Simple( $action, $controller, $module ) );
        $front->setRouter(   new OSS_Controller_Router_Cli() );
        $front->setResponse( new Zend_Controller_Response_Cli() );

        $front->setParam( 'noViewRenderer', true )
              ->setParam( 'disableOutputBuffering', true );
              
        if( $opts->v )
            $front->getRequest()->setParam( 'verbose', true );

        if( $opts->d )
        {
            $front->getRequest()->setParam( 'verbose', true );
            $front->getRequest()->setParam( 'debug', true );
        }
        
        $front->addModuleDirectory( APPLICATION_PATH . '/modules');

        $application->run();
    }
    catch( Exception $e )
    {
        echo "ERROR: " . $e->getMessage() . "\n\n";

        if( $opts->v )
        {
            echo $e->getTraceAsString();
        }
    }
}
else
{
    echo "\n\nERROR: no action specified. Please use --help for instructions.\n\n";
}
