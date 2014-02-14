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
 */

/**
 * The admin controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class AdminController extends ViMbAdmin_Controller_Action
{

    /**
     * Most actions in this object will require an admin object to edit / act on.
     *
     * This method will look for an 'id' parameter and, if set, will
     * try to load the admin model and authorise the user to edit / act on
     * it.
     *
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        if( $this->getRequest()->getActionName() != 'password' )
            $this->authorise( true ); // must be a super admin

        if( $this->getTargetAdmin() )
            $this->view->targetAdmin = $this->getTargetAdmin();
    }


    /**
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->forward( 'list' );
    }


    /**
     * Lists the admins.
     */
    public function listAction()
    {
        $this->view->admins = $this->getD2EM()->getRepository( "\\Entities\\Admin" )->findAll();
    }

     /**
     * Adds a new admin or superadmin to the system. Optionally it can send a welcome email.
     */
    public function addAction()
    {
        $this->view->form = $form = new ViMbAdmin_Form_Admin_AddEdit();
        $form->removeElement( 'salt' );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $this->_targetAdmin = new \Entities\Admin();

            $this->getD2EM()->persist( $this->getTargetAdmin() );
            $form->assignFormToEntity( $this->getTargetAdmin(), $this, false );
            $this->getTargetAdmin()->setCreated( new \DateTime() );

            $password = $this->getTargetAdmin()->getPassword();
            $this->getTargetAdmin()->setPassword(
                 OSS_Auth_Password::hash(
                    $password,
                    $this->_options['resources']['auth']['oss']
                )
            );
            
            $this->log(
                \Entities\Log::ACTION_ADMIN_ADD,
                "{$this->getAdmin()->getFormattedName()} added admin {$this->getTargetAdmin()->getFormattedName()}"
            );

            $this->getD2EM()->flush();

            if( $form->getValue( 'welcome_email' ) )
            {
                $mailer = $this->getMailer();
                $mailer->setSubject( 'ViMbAdmin :: Your New Administrator Account' );
                $mailer->addTo( $this->getTargetAdmin()->getUsername() );
                $mailer->setFrom(
                    $this->_options['server']['email']['address'],
                    $this->_options['server']['email']['name']
                );

                $this->view->username = $this->getTargetAdmin()->getUsername();
                $this->view->password = $form->getValue( 'password' );
                $mailer->setBodyText( $this->view->render( 'admin/email/new_admin.phtml' ) );
             
                try
                {
                    $mailer->send();
                }
                catch( Exception $e )
                {
                    $this->getLogger()->debug( $e->getTraceAsString() );
                    $this->addMessage( 'Could not send welcome email', OSS_Message::ALERT );
                }
            }

            $this->addMessage( _( 'You have successfully added a new administrator to the system.' ), OSS_Message::SUCCESS );
            $this->_redirect( 'admin/list' );
        }
    }


    /**
     * Toggles the active flag. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->getTargetAdmin() || $this->getAdmin()->getId() == $this->getTargetAdmin()->getId() )
            print 'ko';

        $this->getTargetAdmin()->setActive( !$this->getTargetAdmin()->getActive() );
        $this->getTargetAdmin()->setModified( new \DateTime() );

        $this->log(
            $this->getTargetAdmin()->getActive() ? \Entities\Log::ACTION_ADMIN_ACTIVATE : \Entities\Log::ACTION_ADMIN_DEACTIVATE,
            "{$this->getAdmin()->getFormattedName()} " . ( $this->getTargetAdmin()->getActive() ? 'activated' : 'deactivated' ) . " admin {$this->getTargetAdmin()->getFormattedName()}"
        );

        $this->getD2EM()->flush();
        print 'ok';
    }


    /**
     * Toggles the super flag. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleSuperAction()
    {
        if( !$this->getTargetAdmin() || $this->getAdmin()->getId() == $this->getTargetAdmin()->getId() )
            print 'ko';

        $this->getTargetAdmin()->setSuper( !$this->getTargetAdmin()->getSuper() );
        $this->getTargetAdmin()->setModified( new \DateTime() );

        $this->log(
            $this->getTargetAdmin()->getSuper() ? \Entities\Log::ACTION_ADMIN_SUPER : \Entities\Log::ACTION_ADMIN_NORMAL,
            "{$this->getAdmin()->getFormattedName()} set admin {$this->getTargetAdmin()->getFormattedName()} as " .( $this->getTargetAdmin()->getSuper() ? 'super' : 'normal' )
        );

        $this->getD2EM()->flush();
        print 'ok';
    }

    /**
     * Set the password for an admin, and optionally send an email to him/her with the new password.
     */
    public function passwordAction()
    {
        if( !$this->getTargetAdmin() )
        {
            $this->addMessage( 'Invalid or non-existent admin.', OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }
        $this->view->targetAdmin = $this->getTargetAdmin();

        $self = false;
        if( $this->getTargetAdmin()->getId() == $this->getAdmin()->getId() )
            $self = true;

        if( !$this->authorise( true, null, false ) && !$self )// if not superadmin, and admin id is not self id
        {
            $this->getLogger()->alert( sprintf( 'Admin %s tried to set the password for %s but has no sufficient privileges.',
                        $this->getAdmin()->getUsername(),
                        $this->getTargetAdmin()->getUsername() ),
                    OSS_Message::ALERT
                );

            $this->addMessage( _( 'You have insufficient privileges for this task.' ), OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }

        if( $self )
            $this->view->form = $form = new ViMbAdmin_Form_Admin_ChangePassword();
        else
            $this->view->form = $form = new ViMbAdmin_Form_Admin_Password();

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            
            if( $self )
            {
                if( !OSS_Auth_Password::verify( $form->getValue( 'current_password' ), $this->getTargetAdmin()->getPassword(), $this->getOptions()['resources']['auth']['oss'] ) )
                {
                    $form->getElement( 'current_password')->addError( 'Invalid password.' );
                    return;
                }
            }

            $this->getTargetAdmin()->setPassword( 
                OSS_Auth_Password::hash(
                    $form->getValue( 'password'),
                    $this->_options['resources']['auth']['oss']
                )
            );

            if( !$self )
            {
                $this->log(
                    \Entities\Log::ACTION_ADMIN_PW_CHANGE,
                    "{$this->getAdmin()->getFormattedName()} changed password for admin {$this->getTargetAdmin()->getFormattedName()}"
                );
            }

            $this->getD2EM()->flush();

            if( $form->getValue( 'email' ) )
            {
                $mailer = $this->getMailer();
                $mailer->setSubject( _( 'ViMbAdmin :: New Password' ) );
                $mailer->setFrom( $this->_options['server']['email']['address'], $this->_options['server']['email']['name'] );
                $mailer->addTo( $this->getTargetAdmin()->getUsername() );

                $this->view->newPassword = $form->getValue( 'password' );
                $mailer->setBodyText( $this->view->render( 'admin/email/change_password.phtml' ) );

                try
                {
                    $mailer->send();
                }
                catch( Zend_Mail_Exception $e )
                {
                    $this->getLogger()->debug( $e->getTraceAsString() );
                    $this->addMessage( _( 'Sending the change password email failed.' ), OSS_Message::INFO );
                }
            }

            if( !$self )
                $this->addMessage( "You have successfully changed the user's password.", OSS_Message::SUCCESS );
            else
                $this->addMessage( "You have successfully changed your password.", OSS_Message::SUCCESS );

            $this->redirect( 'admin/list' );
        }

    }


    /**
     * Purges an admin with all of the related entries all across the tables. Prints 'ok'
     * on success or 'ko' otherwise to stdout.
     */
    public function purgeAction()
    {
        if( !$this->getTargetAdmin() )
        {
            $this->addMessage( 'Invalid or non-existent admin.', OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }
        
        if( $this->getAdmin()->getId() == $this->getTargetAdmin()->getId() )
        {
           $this->addMessage( 'You cannot purge yourself.', OSS_Message::ERROR );
           $this->redirect( 'admin/list' );
        }
 
        foreach( $this->getTargetAdmin()->getPreferences() as $pref )
            $this->getD2EM()->remove( $pref );

        foreach( $this->getTargetAdmin()->getLogs() as $log )
            $this->getD2EM()->remove( $log );

        foreach( $this->getTargetAdmin()->getDomains() as $domain )
            $domain->removeAdmin( $this->getTargetAdmin() );

        $this->getD2EM()->remove( $this->getTargetAdmin() );
        
        $this->log(
            \Entities\Log::ACTION_ADMIN_PURGE,
            "{$this->getAdmin()->getFormattedName()} purged admin {$this->getTargetAdmin()->getFormattedName()}"
        );
        $this->getD2EM()->flush();

        $this->addMEssage( 'You have successfully purged the admin record.', OSS_Message::SUCCESS );
        $this->redirect( 'admin/list' );
    }


    /**
     * Lists the domains of which the admin administers.
     */
    public function domainsAction()
    {
        if( !$this->getTargetAdmin() )
        {
            $this->addMessage( 'Invalid or non-existent admin.', OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }

        $this->view->targetAdmin = $this->getTargetAdmin();
    }

    /**
    * Adds a new domain to the admin.
    */
    public function assignDomainAction()
    {
        if( !$this->getTargetAdmin() )
        {
            $this->addMessage( _( 'Invalid or missing admin id.' ), OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }
        $this->view->targetAdmin = $this->getTargetAdmin();
        
        $remainingDomains = $this->getD2EM()->getRepository( '\\Entities\\Domain' )->getNotAssignedForAdmin( $this->getTargetAdmin() );
        $this->view->form = $form = new ViMbAdmin_Form_Admin_AssignDomain();

        $form->getElement( "domain" )->setMultiOptions( $remainingDomains ); 

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $this->_domain = $this->loadDomain( $form->getValue( 'domain' ) );

            if( $this->getTargetAdmin()->getDomains()->contains( $this->getDomain() ) )
                $this->addMessage( _( 'This domain is already assigned to the admin.' ), OSS_Message::ERROR );
            else
            {
                $this->getTargetAdmin()->addDomain( $this->getDomain() );
                $this->log(
                    \Entities\Log::ACTION_ADMIN_TO_DOMAIN_ADD,
                    "{$this->getAdmin()->getFormattedName()} added admin {$this->getTargetAdmin()->getFormattedName()} to domain {$this->getDomain()->getDomain()}"
                );
                $this->getD2EM()->flush();
                $this->addMessage(  'You have successfully assigned a domain to the admin.', OSS_Message::SUCCESS );
            }

            $this->redirect( 'admin/domains/aid/' . $this->getTargetAdmin()->getId() );
        }

        if( sizeof( $remainingDomains ) == 0 )
            $this->addMessage( 'There are no domains to assign to this administrator.', OSS_Message::INFO );
    }


    /**
     * Removes an admin from a domain, so he/she won't be able to administer it any more.
     */
    public function removeDomainAction()
    {
        if( !$this->getTargetAdmin() )
        {
            $this->addMessage( _( 'Invalid or missing admin id.' ), OSS_Message::ERROR );
            $this->redirect( 'admin/list' );
        }

        if( !$this->getDomain() )
        {
            $this->addMessage( _( 'Invalid or missing domain id.' ), OSS_Message::ERROR );
            $this->redirect( 'admin/domains/aid/' . $this->getTargetAdmin()->getId() );
        }

        $this->getTargetAdmin()->removeDomain( $this->getDomain() );        
        $this->log(
            \Entities\Log::ACTION_ADMIN_TO_DOMAIN_REMOVE,
            "{$this->getAdmin()->getFormattedName()} removed admin {$this->getTargetAdmin()->getFormattedName()} from domain {$this->getDomain()->getDomain()}"
        );

        $this->getD2EM()->flush();
        $this->addMessage( 'You have successfully removed the admin from domain '. $this->getDomain()->getDomain(), OSS_Message::SUCCESS );
        $this->redirect( 'admin/domains/aid/' . $this->getTargetAdmin()->getId() );
    }
}
