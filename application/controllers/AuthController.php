<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2014 Open Source Solutions Limited
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
 * @copyright Copyright (c) 2011 - 2014 Open Source Solutions Limited
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
    use OSS_Controller_Trait_Auth;

    /**
     * A pre-login function allow and pre-login processing / checks.
     * Override if you need to add functionality.
     */
    protected function _preLogin()
    {
        if( $this->getD2EM()->getRepository( '\\Entities\\Admin' )->getCount() == 0 )
            $this->_redirect( 'auth/setup' );
    }

    /**
     * Get the login form
     *
     * @return ViMbAdmin_Form_Auth_Login The login form
     */
    protected function _getFormLogin()
    {
        return new ViMbAdmin_Form_Auth_Login();
    }

    /**
     * Get the lost password form
     *
     * @return ViMbAdmin_Form_Auth_LostPassword The login form
     */
    protected function _getFormLostPassword()
    {
        return new ViMbAdmin_Form_Auth_LostPassword();
    }

    /**
     * Get the reset password form
     *
     * @return ViMbAdmin_Form_Auth_ResetPassword The login form
     */
    protected function _getFormResetPassword()
    {
        return new ViMbAdmin_Form_Auth_ResetPassword();
    }


    /**
     * Action FOR USERS to change the password of their mailbox.
     */
    public function changePasswordAction()
    {
        $form = new ViMbAdmin_Form_Mailbox_Password();

        if( isset( $this->_options['defaults']['mailbox']['min_password_length'] ) )
            $form->setMinPasswordLength( $this->_options['defaults']['mailbox']['min_password_length'] );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $mailbox = $this->getD2EM()->getRepository( '\\Entities\\Mailbox' )->findOneBy( ['username' => $form->getValue( 'username' ) ] );

            if( !$mailbox )
            {
                $this->addMessage( _( 'Invalid username or password.' ), OSS_Message::ERROR );
            }
            else
            {

                if( OSS_Auth_Password::verify(
                            $form->getValue( 'current_password' ), $mailbox->getPassword(),
                            [ 
                                'pwhash' => $this->_options['defaults']['mailbox']['password_scheme'],
                                'pwsalt' => isset( $this->_options['defaults']['mailbox']['password_salt'] )
                                                ? $this->_options['defaults']['mailbox']['password_salt'] : null, 
                                'pwdovecot' => isset( $this->_options['defaults']['mailbox']['dovecot_pw_binary'] )
                                                ? $this->_options['defaults']['mailbox']['dovecot_pw_binary'] : null,
                                'username' => $form->getValue( 'username' )
                            ]
                        )
                    )
                {
                    $mailbox->setPassword(
                         OSS_Auth_Password::hash(
                            $form->getValue( 'new_password' ),
                            [ 
                                'pwhash' => $this->_options['defaults']['mailbox']['password_scheme'],
                                'pwsalt' => isset( $this->_options['defaults']['mailbox']['password_salt'] )
                                                ? $this->_options['defaults']['mailbox']['password_salt'] : null, 
                                'pwdovecot' => isset( $this->_options['defaults']['mailbox']['dovecot_pw_binary'] )
                                                ? $this->_options['defaults']['mailbox']['dovecot_pw_binary'] : null,
                                 'username' => $form->getValue( 'username' )

                            ]
                        )
                    );

                    $this->getD2EM()->flush();
                    $this->addMessage( _( 'You have successfully changed your password.' ), OSS_Message::SUCCESS );
                    $this->_redirect( 'auth/change-password' );
                }
                else
                    $this->addMessage( _( 'Invalid username or password.' ), OSS_Message::ERROR );
            }
        }

        $this->view->form = $form;
    }


    public function setupAction()
    {
        if( $this->getD2EM()->getRepository( '\\Entities\\Admin' )->getCount() != 0 )
        {
            $this->addMessage( _( "Admins already exist in the system." ), OSS_Message::INFO );
            $this->_redirect( 'auth/login' );
        }

        if( $this->getAuth()->getIdentity() )
        {
            $this->addMessage( _( 'You are already logged in.' ), OSS_Message::INFO );
            $this->_redirect( 'domain/list' );
        }

        $this->view->form = $form = new ViMbAdmin_Form_Admin_AddEdit();
        $form->removeElement( 'active' );
        $form->removeElement( 'super' );
        $form->removeElement( 'welcome_email' );

        if( !isset( $this->_options['securitysalt'] ) || strlen( $this->_options['securitysalt'] ) != 64 )
        {
            $this->view->saltSet = false;
            $randomSalt = $this->view->randomSalt = OSS_String::salt( 64 );
            $form->getElement( 'salt' )->setValue( $randomSalt );
            $this->view->rememberSalt = OSS_String::salt( 64 );
            $this->view->passwordSalt = OSS_String::salt( 64 );
        }
        else
        {
            $this->view->saltSet = true;

            if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
            {
                if( $form->getElement( 'salt' )->getValue() != $this->_options['securitysalt'] )
                {
                    $this->addMessage( _( "Incorrect security salt provided. Please copy and paste it from the <code>application.ini</code> file." ), OSS_Message::INFO );
                }
                else
                {
                    $admin = new \Entities\Admin();
                    $admin->setUsername( $form->getValue( 'username' ) );
                    $admin->setPassword(
                        OSS_Auth_Password::hash( $form->getValue( 'password' ), $this->_options['resources']['auth']['oss'] )
                    );
                    $admin->setSuper( true );
                    $admin->setActive( true );
                    $admin->setCreated( new \DateTime() );
                    $admin->setModified( new \DateTime() );
                    $this->getD2EM()->persist( $admin );

                    // we need to populate the Doctine migration table
                    $dbversion = new \Entities\DatabaseVersion();
                    $dbversion->setVersion( ViMbAdmin_Version::DBVERSION );
                    $dbversion->setName( ViMbAdmin_Version::DBVERSION_NAME );
                    $dbversion->setAppliedOn( new \DateTime() );
                    $this->getD2EM()->persist( $dbversion );

                    $this->getD2EM()->flush();

                    try
                    {
                        $mailer = $this->getMailer();
                        $mailer->setSubject( _( 'ViMbAdmin :: Your New Administrator Account' ) );
                        $mailer->addTo( $admin->getUsername() );
                        $mailer->setFrom(
                            $this->_options['server']['email']['address'],
                            $this->_options['server']['email']['name']
                        );

                        $this->view->username = $admin->getUsername();
                        $this->view->password = $form->getValue( 'password' );

                        $mailer->setBodyText( $this->view->render( 'admin/email/new_admin.phtml' ) );

                        $mailer->send();
                    }
                    catch( Zend_Mail_Exception $e )
                    {
                        $this->addMessage( _( 'Could not send welcome email to the new administrator. 
                            Please ensure you have configured a mail relay server in your <code>application.ini</code>.' ), OSS_Message::ALERT );
                    }

                    $this->addMessage( _( 'Your administrator account has been added. Please log in below.' ), OSS_Message::SUCCESS );
                }

                if( !( isset( $this->_options['skipInstallPingback'] ) && $this->_options['skipInstallPingback'] ) )
                {
                    try
                    {
                        // Try and track new installs to see if it is worthwhile continuing development
                        include_once( APPLICATION_PATH . '/../public/PiwikTracker.php' );

                        if( class_exists( 'PiwikTracker' ) )
                        {
                            if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' )
                                PiwikTracker::$URL = 'https://stats.opensolutions.ie/';
                            else
                                PiwikTracker::$URL = 'http://stats.opensolutions.ie/';

                            $piwikTracker = new PiwikTracker( $idSite = 5 );
                            $piwikTracker->doTrackPageView( 'New V3 Install Completed' );
                            $piwikTracker->doTrackGoal( $idGoal = 2, $revenue = 1 );
                        }
                    }
                    catch( Exception $e ){}
                }

                $this->_redirect( 'auth/login' );
            }
        }
    }
}


