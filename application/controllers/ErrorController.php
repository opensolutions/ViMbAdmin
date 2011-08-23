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
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/**
 * The error controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class ErrorController extends ViMbAdmin_Controller_Action
{

    /**
    * The default error handler action
    */
    public function errorAction()
    {
        $this->getLogger()->debug( "\n" );

        $this->getLogger()->debug('ErrorController::errorAction()');
        $this->getLogger()->warn('ERROR');

        $except = $this->getResponse()->getException();

        $this->getLogger()->debug( $except[0]->getMessage() . ' ' . _( 'on line' ) . ' ' . $except[0]->getLine() . ' ' . _( 'of file' ) . ' ' . $except[0]->getFile() );
        $this->getLogger()->debug( $except[0]->getTraceAsString() );
        $this->getLogger()->debug( "HTTP_HOST : {$_SERVER['HTTP_HOST']}" );
        $this->getLogger()->debug( "HTTP_USER_AGENT: {$_SERVER['HTTP_USER_AGENT']}" );
        $this->getLogger()->debug( "HTTP_COOKIE: {$_SERVER['HTTP_COOKIE']}" );
        $this->getLogger()->debug( "REMOTE_PORT: {$_SERVER['REMOTE_PORT']}" );
        $this->getLogger()->debug( "REQUEST_METHOD: {$_SERVER['REQUEST_METHOD']}" );
        $this->getLogger()->debug( "REQUEST_URI: {$_SERVER['REQUEST_URI']}" );
        $this->getLogger()->debug( "\n" );

        $this->getResponse()->setBody('OK: 0');

        if ( isset( $this->view ) )
        {
            $errors = $this->_getParam( 'error_handler' );

            $this->getResponse()->clearBody();
            //ob_clean();

            switch( $errors->type )
            {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                    // 404 error -- controller or action not found
                    $this->getResponse()
                         ->setRawHeader( 'HTTP/1.1 404 Not Found' );

                    $this->addMessage( _( 'The requested URL or page does not exist.' ), ViMbAdmin_Message::ERROR );
                    Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
                    $this->view->display( 'error/error-404.phtml' );
                    break;

                default:
                    // application error
                    if ( isset( $errors->exception ) )
                        $exception = $errors->exception;
                    elseif ( Zend_Registry::isRegistered( 'exception' ) )
                        $exception = Zend_Registry::get( 'exception' );

                    $this->getLogger()->crit( _( 'Uncaught Exception causing fatal error' ) . ': ' . $exception->getMessage() );
                    break;
            }
        }

        // conditionally display exceptions
        if( $this->getInvokeArg( 'displayExceptions' ) ) 
            $this->view->exception = $exception;
        else 
            $this->view->exception = false;
        
        $this->view->request   = $errors->request;
        
        return true;
    }

}
