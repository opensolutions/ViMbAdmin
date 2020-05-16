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
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action - Trait for CLI controllers
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Cli
{
    

    /**
     * The trait's initialisation method.
     *
     * This function is called from the Action's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function OSS_Controller_Action_Trait_Cli_Init( $request, $response, $invokeArgs )
    {
        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
        if ( php_sapi_name() != 'cli' )
        {
            $this->getLogger()->warn( 'Non CLI access to a CLI controller from ' . $_SERVER['REMOTE_ADDR'] . ' to ' . $_SERVER['REQUEST_URI'] );
            die( 'Unauthorised access!' );
        }

        // Used in connection with a CLI Tool script. See for example:
        // https://github.com/inex/IXP-Manager/blob/master/bin/ixptool.php
        
        $this->_verbose = $this->getFrontController()->getParam( 'verbose', false );
        $this->_debug   = $this->getFrontController()->getParam( 'debug', false );
        
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Cli' );
    }
    

    /**
     * Verbose flag
     */
    private $_verbose = false;
    
    /**
     * Debug flag
     */
    private $_debug = false;
    

    /**
     * True if the user has requested verbose mode
     */
    public function isVerbose()
    {
        return $this->_verbose;
    }
    
    /**
     * If running in verbose mode, echoes the request msg
     *
     * @param string $msg The message
     * @param bool $implicitNewline Set to false to prevent a newline from being echoed
     */
    public function verbose( $msg = "", $implicitNewline = true )
    {
        if( $this->_verbose )
            echo "{$msg}" . ( $implicitNewline ? "\n" : "" );
    }
    
    
    /**
     * True if the user has requested debug mode
     */
    public function isDebug()
    {
        return $this->_debug;
    }
    
    /**
     * If running in debug mode, echoes the request msg
     *
     * @param string $msg The message
     * @param bool $implicitNewline Set to false to prevent a newline from being echoed
     */
    public function debug( $msg = "", $implicitNewline = true )
    {
        if( $this->_debug )
            echo "{$msg}" . ( $implicitNewline ? "\n" : "" );
    }
    
    
    /**
     * Utility function to verify that a file passed via --config exists and is readable.
     *
     * No attempt is made to parse / use the file as calling functions may use this for Smarty,
     * Zend, or other configuration file types.
     *
     * @throws Zend_Validate_Exception If no file is specified or if the file cannot be read
     * @return string The specified verified (as existing and reabable) config file
     */
    public function loadConfig()
    {
        $cfile = $this->getFrontController()->getParam( 'config', false );
        if( $cfile )
        {
            if( file_exists( $cfile ) && is_readable( $cfile ) )
                return $cfile;
            
            throw new Zend_Validate_Exception( 'Cannot open / read specificed configuration file' );
        }
        
        throw new Zend_Validate_Exception( 'No configuration file specificed - please use --config=/path/to/file' );
    }
    
    /**
     * Write text from a variable (e.g. configuration data) to a file in an atomic way.
     *
     * I.e. write to temporary file first (`$filename . '$$'`) and then move to the
     * intended file name.
     *
     *  @param string $filename The full path and filename of the target
     *  @param string $config The text / configuration data
     *  @return bool Success flag
     */
    public function writeConfig( $filename, $config )
    {
        if( @file_put_contents( $filename . '.$$', $config, LOCK_EX ) !== false )
            if( @rename( $filename . '.$$', $filename ) !== false )
                return true;

        return false;
    }
        
}

