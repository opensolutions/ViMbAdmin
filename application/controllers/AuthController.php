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
 * The authentication controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class AuthController extends ViMbAdmin_Controller_Action
{

    /**
     * Jumps to login action.
     */
    public function indexAction()
    {
        $this->_forward( 'login' );
    }


    /**
     * Display the login box or process a login on submission.
     */
    public function loginAction()
    {
        if( $this->getAuth()->getIdentity() )
        {
            $this->addMessage( _( 'You are already logged in.' ), ViMbAdmin_Message::INFO );
            $this->_redirect( 'domain/list' );
        }

        // make sure we have some users
        if( AdminTable::getCount() == 0 )
            $this->_redirect( 'auth/setup' );

        $auth = Zend_Auth::getInstance();

        $loginForm = new ViMbAdmin_Form_Auth_Login;

        if( $this->getRequest()->isPost() && $loginForm->isValid( $_POST ) )
        {
            try
            {
                $authAdapter = new ViMbAdmin_Auth_DoctrineAdapter(
                    $loginForm->getValue( 'username' ),
                    AdminTable::hashPassword( $loginForm->getValue( 'password' ), $this->_options[ 'securitysalt' ] )
                );

                $result = $auth->authenticate( $authAdapter );

                switch( $result->getCode() )
                {
                    case Zend_Auth_Result::SUCCESS:
                        $identity = $auth->getIdentity();
                        $this->getLogger()->info( "Admin {$identity['username']} logged in" );
                        $this->_redirect( 'domain/list' );
                        break;

                    default:
                        $this->addMessages( $result->getMessages(), ViMbAdmin_Message::ERROR );
                        $this->getLogger()->debug(
                            "Bad login for {$loginForm->getValue( 'username' )}: "
                                . implode( ' -- ', $result->getMessages() )
                        );
                        break;
                }
            }
            catch( Zend_Auth_Adapter_Exception $e )
            {
                $this->getLogger()->err( "Exception in AuthController::loginAction: " . $e->getMessage() );
                $this->addMessage(
                    _( "System error during login - please see system logs or contact your system administrator." ),
                    ViMbAdmin_Message::ERROR
                );
            }
        }

        $this->view->loggedOut = $this->_getParam( 'out', false );
        $this->view->loginForm = $loginForm;
    }


    /**
     * Logs the user out, clears the identity and the session.
     */
    public function logoutAction()
    {
        if( $this->getAuth()->getIdentity() )
            $this->getLogger()->debug( _( 'Admin' ) . ' ' . $this->getAdmin()->username . ' ' . _( 'logged out' ) . '.' );

        $this->getAuth()->clearIdentity();
        $this->getSession()->unsetAll();
        Zend_Session::destroy( true, true );

        $this->_redirect( 'auth/login/out/1' );
    }


    /**
     * Asks for a username (email) and sends a password reset token to the address. If the parameter "rid"
     * exists (click on link in email) and the RID is valid then sets the admin's password to random and emails
     * it to him/her, so he/she can log in and re-set the password if desired.
     */
    public function passwordResetAction()
    {
        $rid = $this->_getParam( 'rid', false );

        if( !$rid )
        {
            $resetForm = new ViMbAdmin_Form_Auth_PasswordReset;

            if( $this->getRequest()->isPost() && $resetForm->isValid( $_POST ) )
            {
                $username = $resetForm->getValue( 'username' );

                $adminModel = Doctrine_Query::create()
                    ->from( 'Admin' )
                    ->where( 'username = ?', $username )
                    ->fetchOne();

                if( !$adminModel )
                {
                    $this->addMessage( _( 'User does not exist.' ), ViMbAdmin_Message::ERROR );
                }
                else
                {
                    $tokenModel = TokenTable::addToken( $adminModel, 'PASSWORD_RESET', null, null );

                    $mailer = new Zend_Mail( 'UTF-8' );
                    $mailer->setSubject( _( 'ViMbAdmin :: Password Reset' ) );
                    $mailer->addTo( $adminModel->username );
                    $mailer->setFrom(
                        $this->_options['server']['email']['address'],
                        $this->_options['server']['email']['name']
                    );

                    $this->view->tokenModel = $tokenModel;
                    $this->view->adminModel = $adminModel;

                    $mailer->setBodyText( $this->view->render( 'auth/email/password_reset.phtml' ) );

                    $mailer->send();

                    $this->addMessage( _( 'We have sent you an email with further details.' ), ViMbAdmin_Message::SUCCESS );
                    $this->_redirect( 'auth/login' );
                }
            }

            $this->view->resetForm = $resetForm;
        }
        elseif( strlen( $rid ) != 32 )
        {
            $this->addMessage( _( 'Invalid token.' ), ViMbAdmin_Message::ERROR );
        }
        else
        {
            $tokenModel = Doctrine::getTable( 'Token' )->findOneByRid( $rid );

            if( !$tokenModel )
            {
                $this->addMessage( _( 'Invalid token.' ), ViMbAdmin_Message::ERROR );
            }
            else
            {
                $password = TokenTable::createRandomString( 10 );

                $tokenModel->Admin->setPassword( $password, $this->_options['securitysalt'], true );

                $mailer = new Zend_Mail( 'UTF-8' );
                $mailer->setSubject( _( 'ViMbAdmin :: Password Reset' ) );
                $mailer->setFrom(
                    $this->_options['server']['email']['address'],
                    $this->_options['server']['email']['name']
                );

                $mailer->addTo( $tokenModel->Admin->username );

                $this->view->password = $password;

                $mailer->setBodyText( $this->view->render( 'auth/email/new_password.phtml' ) );

                $mailer->send();

                TokenTable::deleteTokens( $tokenModel->Admin, 'PASSWORD_RESET' );

                $this->addMessage( _( 'We have sent you an email with further details.' ), ViMbAdmin_Message::SUCCESS );
                $this->_redirect( 'auth/login' );
            }
        }
    }


    /**
     * Action FOR USERS to change the password of their mailbox.
     */
    public function changePasswordAction()
    {
        $form = new ViMbAdmin_Form_Mailbox_Password( $this->_options['defaults']['mailbox']['min_password_length'] );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $mailbox = Doctrine_Query::create()
                ->from( 'Mailbox' )
                ->where( 'username = ?', $form->getValue( 'username' ) )
                ->fetchOne();

            if( !$mailbox )
            {
                $this->addMessage( _( 'Invalid username or password.' ), ViMbAdmin_Message::ERROR );
            }
            else
            {
                $cPassword = $mailbox['password'];

                if( $cPassword == $mailbox->hashPassword(
                            $this->_options['defaults']['mailbox']['password_scheme'],
                            $form->getValue( 'current_password' ),
                            $this->_options['defaults']['mailbox']['password_hash']
                        )
                    )
                {
                    $mailbox->hashPassword(
                        $this->_options['defaults']['mailbox']['password_scheme'],
                        $form->getValue( 'new_password' ),
                        $this->_options['defaults']['mailbox']['password_hash']
                    );
                    $mailbox->save();
                    $this->addMessage( _( 'You have successfully changed your password.' ), ViMbAdmin_Message::SUCCESS );
                    $this->_redirect( 'auth/login' );
                }
                else
                    $this->addMessage( _( 'Invalid username or password.' ), ViMbAdmin_Message::ERROR );
            }
        }

        $this->view->form = $form;
    }


    public function setupAction()
    {
        $form = new ViMbAdmin_Form_Admin_Edit;
        $form->removeElement( 'active' );
        $form->removeElement( 'super' );
        $form->removeElement( 'welcome_email' );

        if( $this->getAuth()->getIdentity() )
        {
            $this->addMessage( _( 'You are already logged in.' ), ViMbAdmin_Message::INFO );
            $this->_redirect( 'domain/list' );
        }

        if( $this->_options['securitysalt'] == '' )
        {
            $charSet = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomSalt = substr( str_shuffle( "{$charSet}{$charSet}" ), 0, 31 ); // please note this is not UTF-8 compatible

            $this->view->saltSet = false;
            $this->view->randomSalt = $randomSalt;
            $form->getElement( 'salt' )->setValue( $randomSalt );
        }
        elseif( !AdminTable::isEmpty() )
        {
            $this->addMessage( _( "Admins already exist in the system." ), ViMbAdmin_Message::INFO );
            $this->_redirect( 'auth/login' );
        }
        else
        {
            $this->view->saltSet = true;

            if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
            {
                if( $form->getElement( 'salt' )->getValue() != $this->_options['securitysalt'] )
                {
                    $this->addMessage( _( "Incorrect security salt provided. Please copy and paste it from the <code>application.ini</code> file." ), ViMbAdmin_Message::INFO );
                }
                else
                {
                    $admin = new Admin();
                    $admin['username'] = $form->getValue( 'username' );
                    $admin->setPassword( $form->getValue( 'password' ), $this->_options['securitysalt'], false );
                    $admin->super  = true;
                    $admin->active = true;
                    $admin->save();
                    
                    // we need to populate the Doctine migration table
                    $migration = new MigrationVersion();
                    $migration['version'] = $this->_options['migration_version'];
                    $migration->save();

                    try
                    {
                        $mailer = new Zend_Mail( 'UTF-8' );
                        $mailer->setSubject( _( 'ViMbAdmin :: Your New Administrator Account' ) );
                        $mailer->addTo( $admin['username'] );
                        $mailer->setFrom(
                            $this->_options['server']['email']['address'],
                            $this->_options['server']['email']['name']
                        );

                        $this->view->username = $admin['username'];
                        $this->view->password = $form->getValue( 'password' );

                        $mailer->setBodyText( $this->view->render( 'admin/email/new_admin.phtml' ) );

                        $mailer->send();
                    }
                    catch( Exception $e )
                    {}

                    $this->addMessage( _( 'Your administrator account has been added. Please log in below.' ), ViMbAdmin_Message::SUCCESS );
                }

                if( !( isset( $this->_options['skipInstallPingback'] ) && $this->_options['skipInstallPingback'] ) )
                {
                    // Try and track new installs to see if it is worthwhile continueing development
                    include_once( APPLICATION_PATH . '/../public/PiwikTracker.php' );

                    if( class_exists( 'PiwikTracker' ) )
                    {
                        if( $_SERVER['HTTPS'] == 'on' )
                            PiwikTracker::$URL = 'https://stats.opensolutions.ie/';
                        else
                            PiwikTracker::$URL = 'http://stats.opensolutions.ie/';

                        $piwikTracker = new PiwikTracker( $idSite = 5 );
                        $piwikTracker->doTrackPageView( 'Nes Install Completed' );
                        $piwikTracker->doTrackGoal( $idGoal = 1, $revenue = 0 );
                    }
                }

                $this->_helper->viewRenderer->setNoRender( true );
                $this->_redirect( 'auth/login' );
            }
        }
        $this->view->form = $form;
    }

}
