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
 * The mailbox controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class MailboxController extends ViMbAdmin_Controller_Action
{

    /**
     * Most actions in this object will require a domain object to edit / act on.
     *
     * This method will look for an 'id' parameter and, if set, will
     * try to load the domain model and authorise the user to edit / act on
     * it.
     *
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        if( !$this->_mailbox && !$this->_domain )
            $this->authorise();

        // if an ajax request, remove the view help
        if( substr( $this->getRequest()->getParam( 'action' ), 0, 4 ) == 'ajax' )
            $this->_helper->viewRenderer->setNoRender( true );

        if( $this->_getParam( 'unset', false ) )
            unset( $this->_session->domain );
        else
        {
            if( isset( $this->_session->domain) && $this->_session->domain )
            $this->_domain = $this->_session->domain;
        }
    }


    /**
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->_forward( 'list' );
    }


    /**
     * Lists all mailboxes available to the admin (superadmin sees all) or to the specified domain.
     */
    public function listAction()
    {
        $query = Doctrine_Query::create()
                    ->from( 'Mailbox m' );

        if( !$this->_domain )
        {
            if( !$this->getAdmin()->isSuper() )
            {
                $query
                    ->leftJoin( 'm.Domain d' )
                    ->leftJoin( 'd.DomainAdmin da' )
                    ->andWhere( 'da.username = ?', $this->getAdmin()->username );
            }

            $this->view->domain = 0;
        }
        else
        {
            $this->view->domain = $this->_session->domain = $this->_domain;
            $query->andWhere( 'm.domain = ?', $this->_domain['domain'] );
        }

        $this->view->mailboxes = $query->execute();
    }


    /**
     * Toggles the active property of the current mailbox. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->_mailbox )
            return print 'ko';

        $this->_mailbox['active'] = !$this->_mailbox['active'];
        $this->_mailbox->save();

        LogTable::log( 'MAILBOX_TOGGLE_ACTIVE',
            "Set {$this->_mailbox['username']} set " . ( $this->_targetAdmin['active'] ? '' : 'de' ) . "active",
            $this->getAdmin(), $this->_mailbox['domain']
        );

        return print 'ok';
    }


    /**
     * Purges a mailbox from the system, with all the related entries in other tables.
     */
    public function purgeAction()
    {
        $this->view->modal = $modal = $this->_getParam( 'modal', false );

        if( !$this->_mailbox )
            return $this->_forward( 'list' );

        $this->view->mailboxModel = $this->_mailbox;

        $this->view->aliases = Doctrine_Query::create()
                                    ->from( 'Alias a' )
                                    ->where( 'a.goto = ?', $this->_mailbox['username'] )
                                    ->andWhere( 'a.address != a.goto' );

        if( !$this->getAdmin()->isSuper() )
        {
            $this->view->aliases
                            ->leftJoin( 'a.Domain d' )
                            ->leftJoin( 'd.DomainAdmin da' )
                            ->andWhere( 'da.username = ?', $this->getAdmin()->username );
        }

        $this->view->aliases = $this->view->aliases->execute();

        $this->view->inAliases = Doctrine_Query::create()
                                    ->from( 'Alias a' )
                                    ->where( 'a.address != a.goto' )
                                    ->andWhere( 'a.goto != ?', $this->_mailbox['username'] )
                                    ->andWhere( 'a.goto like ?', '%' . $this->_mailbox['username'] . '%' );

        if( !$this->getAdmin()->isSuper() )
        {
            $this->view->inAliases
                            ->leftJoin( 'a.Domain d' )
                            ->leftJoin( 'd.DomainAdmin da' )
                            ->andWhere( 'da.username = ?', $this->getAdmin()->username );
        }

        $this->view->inAliases = $this->view->inAliases->execute();

        if( isset( $_POST['purge'] ) && ( $_POST['purge'] == 'purge' ) )
        {
            // this won't delete the alias entry where address == goto
            foreach( $this->view->aliases as $oneAlias )
                $oneAlias->delete();

            // this delete fixes issue #3 : https://code.google.com/p/vimbadmin/issues/detail?id=3

            Doctrine_Query::create()
                ->delete()
                ->from( 'Alias' )
                ->where( 'address = ?', $this->_mailbox['username'] )
                ->execute();

            foreach( $this->view->inAliases as $key => $oneAlias )
            {
                $gotoArray = explode( ',', $oneAlias->goto );

                foreach( $gotoArray as $key => $item )
                {
                    $gotoArray[ $key ] = $item = trim( $item );

                    if( ( $item == $this->_mailbox['username'] ) || ( $item == '' ) )
                        unset( $gotoArray[ $key ] );
                }

                if( sizeof( $gotoArray ) == 0 )
                {
                    $oneAlias->delete();
                }
                else
                {
                    $oneAlias->goto = implode( ',', $gotoArray );
                    $oneAlias->save();
                }
            }

            $domain = $this->_mailbox['domain'];
            $this->_mailbox->delete();

            LogTable::log( 'MAILBOX_PURGE',
                "Purged mailbox and aliases for {$this->_mailbox['username']}",
                $this->getAdmin(), $domain
            );

            $this->addMessage( _( 'You have successfully purged the mailbox.' ), ViMbAdmin_Message::SUCCESS );

            if( !$modal )
            {
                $this->_redirect( 'mailbox/list' );
            }
            else
            {
                $this->_helper->viewRenderer->setNoRender( true );
                print "ok";
            }

        }
    }


    /**
     * Lists the aliases of a mailbox, except the default alias (where address == goto).
     */
    public function aliasesAction()
    {
        if( !$this->_mailbox )
            return $this->_forward( 'list' );

        $includeMailboxAliases = $this->_getParam( 'ima', 0 );
        $this->view->includeMailboxAliases = $includeMailboxAliases;
        $this->view->mailboxModel = $this->_mailbox;

        $this->view->aliases = Doctrine_Query::create()
                                    ->select( '*' )
                                    ->from( 'Alias a' );

        if( !$includeMailboxAliases )
            $this->view->aliases->where( 'a.address != a.goto' );

        $this->view->aliases
                        ->andWhere(
                            '( a.address = ? ) or ( a.goto like ? )',
                            array( $this->_mailbox['username'], "%{$this->_mailbox['username']}%" )
                        );

        if( !$this->getAdmin()->isSuper() )
        {
            $this->view->aliases
                            ->leftJoin( 'a.Domain d' )
                            ->leftJoin( 'd.DomainAdmin da' )
                            ->andWhere( 'da.username = ?', $this->getAdmin()->username );
        }

        $this->view->aliases = $this->view->aliases->fetchArray();
    }


    /**
     * Deletes a mailbox alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxDeleteAliasAction()
    {
        if( !$this->_mailbox || !$this->_alias )
            return print 'ko';

        if( $this->_mailbox['username'] == $this->_alias['goto'] )
        {
            $this->_alias->delete();

            LogTable::log( 'ALIAS_DELETE',
                "Deleted alias {$this->_alias['address']} -> {$this->_mailbox['username']}",
                $this->getAdmin(), $this->_mailbox['domain']
            );
        }
        else
        {
            $gotoArray = explode( ',', $this->_alias->goto );

            foreach( $gotoArray as $key => $item )
            {
                $gotoArray[ $key ] = $item = trim( $item );

                if( ( $item == $this->_mailbox['username'] ) || ( $item == '' ) )
                    unset( $gotoArray[ $key ] );
            }

            $this->_alias['goto'] = implode( ',', $gotoArray );
            $this->_alias->save();

            LogTable::log( 'ALIAS_DELETE',
                "Removed destination {$this->_mailbox['username']} from alias {$this->_alias['address']}",
                $this->getAdmin(), $this->_mailbox['domain']
            );
        }

        $this->addMessage( _( 'You have successfully removed' ) . " {$this->_mailbox['username']} " . _( 'from the alias.') , ViMbAdmin_Message::SUCCESS );

        return print 'ok';
    }


    /**
     * Edit a mailbox.
     */
    public function editAction()
    {
        $this->view->modal = $modal = $this->_getParam( 'modal', false );

        $this->view->operation = 'Edit';
        if( !$this->_mailbox )
        {
            $this->_mailbox = new Mailbox();
            $this->view->operation = 'Add';
        }

        $this->view->mailboxModel = $this->_mailbox;

        $domainList = DomainTable::getDomains( $this->getAdmin() );

        $editForm = new ViMbAdmin_Form_Mailbox_Edit( null, $domainList, $this->_options['defaults']['mailbox']['min_password_length'] );
        $editForm->setDefaults( $this->_mailbox->toArray() );

        if( $this->_mailbox['id'] )
        {
            $editForm->removeElement( 'password' );
            $editForm->getElement( 'local_part' )->setAttrib( 'disabled', 'disabled' )->setRequired( false );
            $editForm->getElement( 'domain' )->setAttrib( 'disabled', 'disabled' )->setRequired( false );
        }

        if( $this->getRequest()->isPost() && !$modal )
        {
            if( $editForm->isValid( $_POST ) )
            {
                do
                {
                    // do we have a domain
                    if( !$this->_domain )
                    {
                        $this->_domain = Doctrine::getTable( 'Domain' )->find( $editForm->getElement( 'domain' )->getValue() );

                        if( !$this->_domain || !$this->authorise( false, $this->_domain, false ) )
                        {
                            $this->addMessage( _( "Invalid, unauthorised or non-existent domain." ), ViMbAdmin_Message::ERROR );
                            $this->_redirect( 'domain/list' );
                        }
                    }

                    if( $this->_mailbox['id'] )
                    {
                        $this->_domain = $this->_mailbox->Domain;

                        $editForm->removeElement( 'local_part' );
                        $editForm->removeElement( 'domain' );
                        $editForm->removeElement( 'password' );

                        $this->_mailbox->fromArray( $editForm->getValues() );
                        $op = 'edit';
                    }
                    else
                    {
                        // do we have available mailboxes?
                        if( !$this->getAdmin()->isSuper() && $this->_domain['mailboxes'] != 0 && $this->_domain->countMailboxes() >= $this->_domain['mailboxes'] )
                        {
                            $this->_helper->viewRenderer->setNoRender( true );
                            $this->addMessage( _( 'You have used all of your allocated mailboxes.' ), ViMbAdmin_Message::ERROR );
                            break;
                        }

                        $this->_mailbox->fromArray( $editForm->getValues() );

                        $this->_mailbox['domain']    = $this->_domain['domain'];
                        $this->_mailbox['username']  = "{$this->_mailbox['local_part']}@{$this->_mailbox['domain']}";

                        $this->_mailbox['homedir']   = $this->_options['defaults']['mailbox']['homedir'];
                        $this->_mailbox['uid']       = $this->_options['defaults']['mailbox']['uid'];
                        $this->_mailbox['gid']       = $this->_options['defaults']['mailbox']['gid'];

                        $this->_mailbox->formatMaildir( $this->_options['defaults']['mailbox']['maildir'] );

                        $plainPassword = $this->_mailbox['password'];
                        $this->_mailbox->hashPassword(
                            $this->_options['defaults']['mailbox']['password_scheme'],
                            $this->_mailbox['password'],
                            $this->_options['defaults']['mailbox']['password_hash']
                        );

                        // is the mailbox address valid?
                        if( !Zend_Validate::is( "{$this->_mailbox['local_part']}@{$this->_mailbox['domain']}", 'EmailAddress', array( 1, null ) ) )
                        {
                            $editForm->getElement( 'local_part' )->addError( _( 'Invalid email address.' ) );
                            break;
                        }


                        // does a mailbox of the same name exist?
                        $dup = Doctrine_Query::create()
                            ->from( 'Mailbox m' )
                            ->where( 'm.local_part = ?', $this->_mailbox['local_part'] )
                            ->andWhere( 'm.domain = ?', $this->_mailbox['domain'] )
                            ->execute( null, Doctrine_Core::HYDRATE_ARRAY );

                        if( count( $dup ) )
                        {
                            $this->addMessage(
                                _( 'Mailbox already exists for' ) . " {$this->_mailbox['local_part']}@{$this->_mailbox['domain']}",
                                ViMbAdmin_Message::ERROR
                            );
                            break;
                        }

                        if( $this->_options['mailboxAliases'] == 1 )
                        {
                            $aliasModel = new Alias();
                            $aliasModel->address   = $this->_mailbox['username'];
                            $aliasModel->goto      = $this->_mailbox['username'];
                            $aliasModel->domain    = $this->_domain['domain'];
                            $aliasModel->active    = 1;
                            $aliasModel->save();
                        }

                        $op = 'add';
                    }

                    // check quota
                    if( $this->_domain['quota'] != 0 )
                    {
                        if( $this->_mailbox['quota'] <= 0 || $this->_mailbox['quota'] > $this->_domain['quota'] )
                        {
                            $this->_mailbox['quota'] = $this->_domain['quota'];
                            $this->addMessage(
                                _( "Mailbox quota set to " ) . $this->_domain['quota'],
                                ViMbAdmin_Message::ALERT
                            );
                        }
                    }

                    $this->_mailbox->save();

                    if( $editForm->getValue( 'welcome_email' ) )
                    {
                        if( !$this->_sendSettingsEmail(
                                ( $editForm->getValue( 'cc_welcome_email' ) ? $editForm->getValue( 'cc_welcome_email' ) : false ),
                                $plainPassword, true )
                        )
                            $this->addMessage( _( 'Could not sent welcome email' ), ViMbAdmin_Message::ALERT );
                    }

                    LogTable::log( 'MAILBOX_' . ( $op == 'add' ? 'ADD' : 'EDIT' ),
                        print_r( $this->_mailbox->toArray(), true ),
                        $this->getAdmin(), $this->_mailbox['domain']
                    );

                    $this->addMessage( _( "You have successfully added/edited the mailbox record." ), ViMbAdmin_Message::SUCCESS );

                    if( $this->_getParam( 'helper', true ) )
                    {
                        if( $this->_domain )
                            $this->_redirect( 'mailbox/list/did/' . $this->_domain['id'] );
                        else
                            $this->_redirect( 'mailbox/list' );
                    }
                    else
                    {
                        $this->_helper->viewRenderer->setNoRender( true );
                        print "ok";
                        return;
                    }

                }while( false ); // break-able clause
            }
            else
            {
                if( !$this->_getParam( 'helper', true ) )
                {
                    $this->view->modal = true;
                }
            }
        }

        if( $this->_domain )
            $editForm->getElement( 'domain' )->setValue( $this->_domain['id'] );

        $this->view->editForm = $editForm;
    }


    public function emailSettingsAction()
    {
        if( $this->_mailbox )
        {
            if( $this->_sendSettingsEmail() )
                $this->addMessage( _( 'Settings email successfully sent' ), ViMbAdmin_Message::SUCCESS );
            else
                $this->addMessage( _( 'Could not send settings email' ), ViMbAdmin_Message::ERROR );
        }

        $this->_redirect( 'mailbox/list' );
    }


    private function _sendSettingsEmail( $cc = false, $password = '', $isWelcome = false )
    {
        $mailer = new Zend_Mail();

        if( $isWelcome )
            $mailer->setSubject( _( "Welcome to your new mailbox on" ) . " {$this->_mailbox['domain']}" );
        else
            $mailer->setSubject( _( "Settings for your mailbox on" ) . " {$this->_mailbox['domain']}" );

        $mailer->setFrom(
            $this->_options['server']['email']['address'],
            $this->_options['server']['email']['name']
        );

        $mailer->addTo( $this->_mailbox['username'], $this->_mailbox['name'] );

        if( $cc )
            $mailer->addCc( $cc );

        $this->view->mailbox  = $this->_mailbox;
        $this->view->welcome  = $isWelcome;
        $this->view->password = $password;

        $settings = $this->_options['server'];

        foreach( $settings as $tech => $params )
            foreach( $params as $k => $v )
                $settings[$tech][$k] = Mailbox::substitute( $this->_mailbox['username'], $v );

        $this->view->settings = $settings;

        $mailer->setBodyText( $this->view->render( 'mailbox/email/settings.phtml' ) );

        try
        {
            $mailer->send();
            return true;
        }
        catch( Exception $e )
        {}

        return false;
    }

    /**
     * Action FOR ADMINS AND SUPERADMINS to change the password of a mailbox.
     */
    public function passwordAction()
    {
        $this->view->modal = $modal = $this->_getParam( 'modal', false );

        if( !$this->_mailbox )
        {
            $this->_helper->viewRenderer->setNoRender( true );
            $this->addMessage( _( 'No mailbox id passed.' ), ViMbAdmin_Message::ERROR );
            return print $this->view->render( 'close_colorbox_reload_parent.phtml' );
        }

        $this->view->mailbox = $this->_mailbox;

        $form = new ViMbAdmin_Form_Admin_Password( $this->_options['defaults']['mailbox']['min_password_length'] );

        if( $this->getRequest()->isPost() && !$modal )
        {
            if( $form->isValid( $_POST ) )
            {
                $plainPassword = $form->getValue( 'password' );
                $this->_mailbox->hashPassword(
                    $this->_options['defaults']['mailbox']['password_scheme'],
                    $plainPassword,
                    $this->_options['defaults']['mailbox']['password_hash']
                );

                $this->_mailbox->save();

                if( $form->getValue( 'email' ) )
                {
                    $mailer = new Zend_Mail;
                    $mailer->setSubject( _( 'New Password for ' . $this->_mailbox['username'] ) );
                    $mailer->setFrom( $this->_options['server']['email']['address'], $this->_options['server']['email']['name'] );
                    $mailer->addTo( $this->_mailbox['username'], $this->_mailbox['name'] );

                    $this->view->newPassword = $form->getValue( 'password' );
                    $mailer->setBodyText( $this->view->render( 'mailbox/email/change_password.phtml' ) );

                    try
                    {
                        $mailer->send();
                    }
                    catch( Zend_Mail_Exception $vException )
                    {
                        $this->getLogger()->debug( $vException->getTraceAsString() );
                        $this->addMessage( _( 'Could not send email.' ), ViMbAdmin_Message::ALERT );
                        if( $this->_getParam( 'helper', true ) )
                        {
                            $this->_redirect( 'mailbox/list' );
                        }
                        else
                        {
                            $this->_helper->viewRenderer->setNoRender( true );
                            print "ok";
                        }
                    }
                }

                LogTable::log( 'MAILBOX_PW_CHANGE',
                    "Changed password for {$this->_mailbox['username']}",
                    $this->getAdmin(), $this->_mailbox['domain']
                );


                $this->addMessage( _( "Password has been sucessfully changed." ), ViMbAdmin_Message::SUCCESS );
                if( $this->_getParam( 'helper', true ) )
                {
                    $this->_redirect( 'mailbox/list' );
                }
                else
                {
                    $this->_helper->viewRenderer->setNoRender( true );
                    print "ok";
                }
            }
            else
            {
                if( !$this->_getParam( 'helper', true ) )
                {
                    $this->view->modal = true;
                }
            }
        }

        $this->view->form = $form;
    }

}
