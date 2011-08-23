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
         // if an ajax request, remove the view help
        if( substr( $this->getRequest()->getParam( 'action' ), 0, 4 ) == 'ajax' )
            $this->_helper->viewRenderer->setNoRender( true );

        if( $this->getRequest()->getParam( 'action' ) != 'password' )
            $this->authorise( true ); // must be a super admin

        if( $this->_targetAdmin )
            $this->view->targetAdmin = $this->_targetAdmin;
    }


    /**
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->_forward( 'list' );
    }


    /**
     * Lists the admins.
     */
    public function listAction()
    {
        $this->view->admins = Doctrine_Query::create()
            ->from( 'Admin' )
            ->fetchArray();
    }


    /**
     * Toggles the active flag. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if ( ( !$this->_targetAdmin ) || ( $this->getAdmin()->id == $this->_targetAdmin->id ) )
            return print 'ko';

        $this->_targetAdmin['active'] = !$this->_targetAdmin['active'];
        $this->_targetAdmin->save();

        LogTable::log( 'ADMIN_TOGGLE_ACTIVE',
            "Set {$this->_targetAdmin['username']} set " . ( $this->_targetAdmin['active'] ? '' : 'de' ) . "active",
            $this->getAdmin(), null
        );

        print 'ok';
    }


    /**
     * Toggles the super flag. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleSuperAction()
    {
        if( ( !$this->_targetAdmin ) || ( $this->getAdmin()->id == $this->_targetAdmin->id ) )
            return print 'ko';

        $this->_targetAdmin['super'] = !$this->_targetAdmin['super'];
        $this->_targetAdmin->save();

        LogTable::log( 'ADMIN_TOGGLE_SUPER',
            "Set {$this->_targetAdmin['username']} with super = " . ( $this->_targetAdmin['super'] ? '1' : '0' ),
            $this->getAdmin(), null
        );

        print 'ok';
    }


    /**
     * Purges an admin with all of the related entries all across the tables. Prints 'ok'
     * on success or 'ko' otherwise to stdout.
     */
    public function ajaxPurgeAction()
    {
        if( !$this->_targetAdmin )
            return print 'ko';

        if( $this->getAdmin()->id == $this->_targetAdmin->id )
        {
            // cannot purge yourself!
            return print 'ko:' . _( 'You cannot purge yourself.' );
        }

        Doctrine_Query::create()
            ->delete()
            ->from( 'Log' )
            ->where( 'username = ?', $this->_targetAdmin['username'] )
            ->execute();

        Doctrine_Query::create()
            ->delete()
            ->from( 'DomainAdmin' )
            ->where( 'username = ?', $this->_targetAdmin['username'] )
            ->execute();

        LogTable::log( 'ADMIN_PURGE',
            "Purged {$this->_targetAdmin['username']}",
            $this->getAdmin(), null
        );

        $this->_targetAdmin->delete();

        print 'ok';
    }


    /**
     * Lists the domains of which the admin administers.
     */
    public function domainsAction()
    {
        $this->view->domainAdmins = Doctrine_Query::create()
            ->from( 'DomainAdmin' )
            ->where( 'username = ?', $this->_targetAdmin['username'] )
            ->execute();
    }


    /**
    * Adds a new domain to the admin.
    */
    public function addDomainAction()
    {
        if( !$this->_targetAdmin )
        {
            $this->addMessage( _( 'Invalid or missing admin id.' ), ViMbAdmin_Message::ERROR );
            return false;
        }

        $adminDomains = DomainAdminTable::getAllowedDomains( $this->_targetAdmin );
        $allDomains = DomainTable::getDomains( $this->_admin );
        $remainingDomains = array_diff( $allDomains, $adminDomains );

        $form = new ViMbAdmin_Form_Admin_AddDomain( null, $remainingDomains, $this->_targetAdmin );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $domainId = $form->getValue( 'domain' );
            $domain = Doctrine::getTable( 'Domain' )->find( $domainId );

            if( !$domain )
                $this->addMessage( _( 'Invalid or missing domain id.' ), ViMbAdmin_Message::ERROR );
            elseif( array_key_exists( $domainId, $adminDomains ) )
                $this->addMessage( _( 'This domain is already assigned to the admin.' ), ViMbAdmin_Message::ERROR );
            else
            {
                $domain->addAdmin( $this->_targetAdmin );

                unset( $remainingDomains[ $domainId ] );
                $form = new ViMbAdmin_Form_Admin_AddDomain( null, $remainingDomains, $this->_targetAdmin );

                $this->addMessage( _( 'You have successfully assigned a domain to the admin.' ), ViMbAdmin_Message::SUCCESS );
            }
        }

        if( sizeof( $remainingDomains ) == 0 )
            $this->addMessage( _( 'There is no domain to assign to this admin.' ), ViMbAdmin_Message::INFO );

        $this->view->form = $form;
    }


    /**
     * Removes an admin from a domain, so he/she won't be able to administer it any more.
     */
    public function ajaxRemoveDomainAction()
    {
        if( !$this->_targetAdmin )
            return print 'ko';

        if( !( $domain = $this->loadDomain( $this->_getParam( 'domain' ) ) ) )
            return print 'ko';

        Doctrine_Query::create()
            ->delete()
            ->from( 'DomainAdmin' )
            ->where( 'domain = ?', $domain['domain'] )
            ->andWhere( 'username = ?', $this->_targetAdmin['username'] )
            ->execute();

        LogTable::log( 'ADMIN_DOMAIN_REMOVE',
            "Removed {$this->_targetAdmin['username']} as an admin of {$domain['domain']}",
            $this->getAdmin(), $domain
        );

        print 'ok';
    }


    /**
     * Set the password for an admin, and optionally send an email to him/her with the new password.
     */
    public function passwordAction()
    {
        if( !$this->_targetAdmin )
        {
            $this->_helper->viewRenderer->setNoRender( true );
            $this->addMessage( _( 'Invalid or non-existent admin.' ), ViMbAdmin_Message::ERROR );
            print $this->view->render( 'close_colorbox_reload_parent.phtml');
        }

        if( ( !$this->authorise( true, null, false ) ) && // if not superadmin, and
            ( $this->_targetAdmin->id != $this->getAdmin()->id ) // admin id is not self id
        )
        {
            $this->getLogger()->alert(
                    _( 'Admin' ) . ' ' .
                    $this->_admin->username . ' ' .
                    _( 'tried to set the password for ' ) . ' ' .
                    $this->_targetAdmin->username . ' , ' .
                    _( 'but has no sufficient privileges.' ),
                    ViMbAdmin_Message::ALERT
                );

            $this->_helper->viewRenderer->setNoRender( true );
            $this->addMessage( _( 'You have insufficient privileges for this task.' ), ViMbAdmin_Message::ERROR );
            print $this->view->render( 'close_colorbox_reload_parent.phtml');
        }

        $form = new ViMbAdmin_Form_Admin_Password;

        if( $this->_targetAdmin->id == $this->getAdmin()->id )
            $form->removeElement( 'email' );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $this->_targetAdmin->setPassword( $form->getValue( 'password' ), $this->_options['securitysalt'], true );

            if( $form->getValue( 'email' ) )
            {
                $mailer = new Zend_Mail;
                $mailer->setSubject( _( 'ViMbAdmin :: New Password' ) );
                $mailer->setFrom( $this->_options['server']['email']['address'], $this->_options['server']['email']['name'] );
                $mailer->addTo( $this->_targetAdmin->username );

                $this->view->newPassword = $form->getValue( 'password' );
                $mailer->setBodyText( $this->view->render( 'admin/email/change_password.phtml' ) );

                try
                {
                    $mailer->send();
                }
                catch( Zend_Mail_Exception $vException )
                {
                    $this->getLogger()->debug( $vException->getTraceAsString() );
                    $this->addMessage( _( 'Sending the change password email failed.' ), ViMbAdmin_Message::INFO );
                    return false;
                }
            }

            LogTable::log( 'ADMIN_PW_CHANGE',
                "Changed password of {$this->_targetAdmin['username']}",
                $this->getAdmin(), null
            );

            $this->_helper->viewRenderer->setNoRender( true );

            if( $this->_targetAdmin->id != $this->getAdmin()->id )
                $this->addMessage( _( "You have successfully changed the user's password." ), ViMbAdmin_Message::SUCCESS );
            else
                $this->addMessage( _( "You have successfully changed your password." ), ViMbAdmin_Message::SUCCESS );

            return print $this->view->render( 'close_colorbox_reload_parent.phtml');
        }

        $this->view->form = $form;
    }


    /**
     * Adds a new admin or superadmin to the system. Optionally it can send a welcome email.
     */
    public function addAction()
    {
        $form = new ViMbAdmin_Form_Admin_Edit;
        $form->removeElement( 'salt' );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $adminModel = new Admin();
            $adminModel->fromArray( $form->getValues() );
            $adminModel->setPassword( $form->getValue( 'password' ), $this->_options['securitysalt'], false );
            $adminModel->save();

            LogTable::log( 'ADMIN_ADD',
                "Added new " . ( $adminModel['super'] ? 'super ' : '' ) . "admin {$adminModel['username']}",
                $this->getAdmin()
            );

            if( $form->getValue( 'welcome_email' ) )
            {
                try
                {
                    $mailer = new Zend_Mail();
                    $mailer->setSubject( _( 'ViMbAdmin :: Your New Administrator Account' ) );
                    $mailer->addTo( $adminModel->username );
                    $mailer->setFrom(
                        $this->_options['server']['email']['address'],
                        $this->_options['server']['email']['name']
                    );

                    $this->view->username = $adminModel->username;
                    $this->view->password = $form->getValue( 'password' );

                    $mailer->setBodyText( $this->view->render( 'admin/email/new_admin.phtml' ) );

                    $mailer->send();
                }
                catch( Exception $e )
                {
                    $this->getLogger()->debug( $vException->getTraceAsString() );
                    $this->addMessage( _( 'Could not send welcome email' ), ViMbAdmin_Message::ALERT );
                }
            }

            $this->addMessage( _( 'You have successfully added a new administrator to the system.' ), ViMbAdmin_Message::SUCCESS );
            $this->_helper->viewRenderer->setNoRender( true );
            return print $this->view->render( 'close_colorbox_reload_parent.phtml');
        }

        $this->view->form = $form;
    }

}
