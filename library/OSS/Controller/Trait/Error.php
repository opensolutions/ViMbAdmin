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
 * @package    OSS_Controller
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: A generic trait to implement basic functionality in an ErrorController
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Trait
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Trait_Error
{

    /**
    * The default error handler action
    */
    public function errorAction()
    {
        $this->getLogger()->debug( "\n" );
        $this->getLogger()->debug( 'ErrorController::errorAction()' );

        $log = "\n\n************************************************************************\n"
        	. "****************************** EXCEPTIONS *******************************\n"
        	. "************************************************************************\n\n";

        $exceptions = $this->getResponse()->getException();

        if( is_array( $exceptions ) )
        {
            foreach( $exceptions as $e )
            {
                $log .= "--------------------------- EXCEPTION --------------------------\n\n"
                	. "Message: " . $e->getMessage()
                	. "\nLine: "  . $e->getLine()
                	. "\nFile: "  . $e->getFile();
    
            	$log .= "\n\nTrace:\n\n"
            		. $e->getTraceAsString() . "\n\n"
            		. print_r( OSS_Debug::compact_debug_backtrace(), true )
            		. "\n\n";
            }
        }
        
        $log .= "------------------------\n\n"
        	. "HTTP_HOST : {$_SERVER['HTTP_HOST']}\n"
        	. "HTTP_USER_AGENT: {$_SERVER['HTTP_USER_AGENT']}\n"
                . ( isset( $_SERVER['HTTP_COOKIE'] ) ? "HTTP_COOKIE: {$_SERVER['HTTP_COOKIE']}\n" : "" )
        	. "REMOTE_PORT: {$_SERVER['REMOTE_PORT']}\n"
        	. "REQUEST_METHOD: {$_SERVER['REQUEST_METHOD']}\n"
        	. "REQUEST_URI: {$_SERVER['REQUEST_URI']}\n\n";

        $this->getResponse()->setBody( 'OK: 0' );

        if( isset( $this->view ) )
        {
            if( $errors = $this->_getParam( 'error_handler', false ) )
            {
                $this->getResponse()->clearBody();

                switch( $errors->type )
                {
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                        // 404 error -- controller or action not found
                        $this->getResponse()
                             ->setRawHeader( 'HTTP/1.1 404 Not Found' );
 
                        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
                        $this->view->display( 'error/error-404.phtml' );
                        $this->getLogger()->debug( $log );
                        break;

                    default:
                	$this->getLogger()->err( $log );
                        $this->view->exceptions = $exceptions;
                        break;
                }
            }
        }

        return true;
    }

    public function error404Action()
    {
        $this->getResponse()->setRawHeader( 'HTTP/1.1 404 Not Found' );
    }
    
    public function insufficientPermissionsAction()
    {
    }

}
