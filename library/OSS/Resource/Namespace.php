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
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Resource_Namespace extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Logger instance
     * 
     * @var null|Zend_Session_Namespace
     */
    protected $_session;


    /**
     * Initialisation function
     * 
     * @return Zend_Session_Namespace
     */
    public function init()
    {
        // Return session so bootstrap will store it in the registry
        return $this->getSession();
    }


    /**
     * Get session namespace
     * 
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if( null === $this->_session ) 
        {
            $this->getBootstrap()->bootstrap( 'Session' );

            // Get session configuration options from the application.ini file
            $options = $this->getOptions();

            $ApplicationNamespace = new Zend_Session_Namespace( 'Application' );

            // Secutiry tip from http://framework.zend.com/manual/en/zend.session.global_session_management.html
            if( !isset( $ApplicationNamespace->initialised ) )
            {
                // FIXME Zend_Session::regenerateId();
                $ApplicationNamespace->initialized = true;
            }

            // ensure IP consistancy
            if ( (isset($options['checkip'])) && ($options['checkip']) && (isset($_SERVER['REMOTE_ADDR'])) )
            {
                if( !isset( $ApplicationNamespace->clientIP ) )
                {
                    $ApplicationNamespace->clientIP = $_SERVER['REMOTE_ADDR'];
                }
                else if( $ApplicationNamespace->clientIP != $_SERVER['REMOTE_ADDR'] )
                {
                    // security violation - client IP has changed indicating a possible hijacked session
                    Zend_Session::destroy( true, true );
                    die(
                        "Your IP address has changed indication a possible session hijack attempt. Your session has been destroyed for your own security."
                    );
                }
            }

            $this->_session = $ApplicationNamespace;

        }

        return $this->_session;
    }    


} 
