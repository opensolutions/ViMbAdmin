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
 * The alias controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class AliasController extends ViMbAdmin_Controller_Action
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
        $this->authorise();

        // if an ajax request, remove the view help
        if( substr( $this->getRequest()->getParam( 'action' ), 0, 4 ) == 'ajax' )
            $this->_helper->viewRenderer->setNoRender( true );
    }


    /**
     * The index action. Just jumps to list action.
     */
    public function indexAction()
    {
        $this->_forward( 'list' );
    }


    /**
     * Lists the aliases available to the admin and/or domain. Superadmin can see all.
     */
    public function listAction()
    {
        $includeMailboxAliases = $this->_getParam( 'ima', 0 );
        $this->view->includeMailboxAliases = $includeMailboxAliases;
        $this->view->domainModel = $this->_domain;
        $this->view->domain = 0;

        $q = Doctrine_Query::create()
                ->select( '*' )
                ->from( 'Alias a' );

        if( !$includeMailboxAliases )
            $q->where( 'a.address != a.goto' );

        if( $this->_domain )
        {
            // already authorised in preDispatch()
            $q->andWhere( 'a.domain = ?', $this->_domain['domain'] );
            $this->view->domain = $this->_domain;
        }
        else if( !$this->getAdmin()->isSuper() )
        {
            // if we're not a super admin and we don't have a specific domain, only load allowed
            $q->leftJoin( 'a.Domain d' )
              ->leftJoin( 'd.DomainAdmin da' )
              ->andWhere( 'da.username = ?', $this->getAdmin()->username );
        }

        $this->view->aliases = $q->fetchArray();
        $q->free();
    }


    /**
     * Toggles the active property of an alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->_alias )
            return print 'ko';

        $this->_alias['active'] = !$this->_alias['active'];
        $this->_alias->save();

        LogTable::log( 'ALIAS_TOGGLE_ACTIVE',
            "Set {$this->_alias['address']} " . ( $this->_alias['active'] ? '' : 'de' ) . "active",
            $this->getAdmin(), $this->_alias['domain']
        );

        print 'ok';
    }


    /**
     * Deletes an alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxDeleteAction()
    {
        if( !$this->_alias )
            return print 'ko';

        $this->_alias->delete();

        LogTable::log( 'ALIAS_DELETE',
            "Deleted {$this->_alias['address']}",
            $this->getAdmin(), $this->_alias['domain']
        );

        print 'ok';
    }


    /**
     * Edit an alias.
     */
    public function editAction()
    {
        if( !$this->_alias ) // no alias id passed so adding
        {
            $this->_alias = new Alias();

            if( $this->_domain )
                $this->view->domainModel = $this->_domain;
        }
        else
        {
            // if editing, then use that domain
            $this->view->domainModel = $this->_alias['Domain'];
        }

        $this->view->aliasModel = $this->_alias;

        $domainList = DomainTable::getDomains( $this->getAdmin() );
        $this->view->domainList = $domainList;

        $editForm = new ViMbAdmin_Form_Alias_Edit( null, $domainList );

        if( $this->getRequest()->isPost() )
        {
            if( $this->_alias['id'] ) // editing
            {
                $editForm->removeElement( 'local_part' );
                $editForm->removeElement( 'domain' );
            }

            if( $editForm->isValid( $_POST ) )
            {
                $postValues = $editForm->getValues();

                if( isset( $postValues['domain'] ) )
                {
                    $this->_domain = $this->loadDomain( $postValues['domain'] );
                }
 
                if( !$this->_domain || !$this->authorise( false, $this->_domain, false ) )
                {
                     $this->addMessage( _( "Invalid, unauthorised or non-existent domain." ), ViMbAdmin_Message::ERROR );
                     $this->_redirect( $this->getRequest()->getPathInfo() );
                 }
 
                if( !$this->_alias['id'] ) // adding
                {
                    $alias = Doctrine::getTable( 'Alias' )->findOneByAddress( "{$postValues['local_part']}@{$this->_domain['domain']}" );

                    if( $alias )
                    {
                        if( $this->_options['mailboxAliases'] )
                        {
                            if( $alias->address == $alias->goto )
                            {
                                $this->addMessage(
                                            _( 'A mailbox alias exists for' ) . " {$postValues['local_part']}@{$this->_domain['domain']}",
                                            ViMbAdmin_Message::ERROR
                                        );
                            }
                            else
                            {
                                $this->addMessage(
                                            _( 'Alias already exists for' ) . " {$postValues['local_part']}@{$this->_domain['domain']}",
                                            ViMbAdmin_Message::ERROR
                                        );
                            }
                        }
                        else
                        {
                            $this->addMessage(
                                        _( 'Alias already exists for' ) . " {$postValues['local_part']}@{$this->_domain['domain']}",
                                        ViMbAdmin_Message::ERROR
                                    );
                        }

                        $this->_redirect( $this->getRequest()->getPathInfo() );
                    }
                }

                if( !$postValues['goto'] )
                {
                    $editForm->getElement( 'goto' )->addError( _( 'You must have at least one goto address.' ) );
                }
                else
                {
                    // is the alias valid (allowing for wildcard domains (i.e. with no local part)
                    if( !$this->_alias['id'] && $postValues['local_part'] != '' && !Zend_Validate::is( "{$postValues['local_part']}@{$this->_domain['domain']}", 'EmailAddress', array( 1, null ) ) )
                        $editForm->getElement( 'local_part' )->addError( _( 'Invalid email address.' ) );
                        
                    foreach( $postValues['goto'] as $key => $oneGoto )
                    {
                        $oneGoto = trim( $oneGoto );

                        if( $oneGoto == '')
                            unset( $postValues['goto'][ $key ] );
                        else
                        {
                            if( !Zend_Validate::is( $oneGoto, 'EmailAddress', array( 1, null ) ) )
                                $editForm->getElement( 'goto' )->addError( _( 'Invalid email address(es).' ) );
                        }
                    }

                    if( !$postValues['goto'] )
                        $editForm->getElement( 'goto' )->addError( _( 'You must have at least one goto address.' ) );

                    if( !$editForm->getElement( 'goto' )->hasErrors() 
                        && ( $editForm->getElement( 'local_part' ) === null || !$editForm->getElement( 'local_part' )->hasErrors() ) )
                    {
                        $this->_alias->fromArray( $postValues );

                        if( !$this->_alias['id'] ) // NOT editing
                        {
                            // do we have available mailboxes?
                            if(     !$this->getAdmin()->isSuper() &&
                                    $this->_domain['aliases'] != 0 &&
                                    ( $this->_domain->countAliases() >= $this->_domain['aliases'] )
                                )
                            {
                                $this->_helper->viewRenderer->setNoRender( true );
                                $this->addMessage( _( 'You have used all of your allocated aliases.' ), ViMbAdmin_Message::ERROR );
                                return print $this->view->render( 'close_colorbox_reload_parent.phtml');
                            }

                            $this->_alias['domain']  = $this->_domain['domain'];
                            $this->_alias['address'] = "{$postValues['local_part']}@{$this->_domain['domain']}";

                            LogTable::log( 'ALIAS_ADD',
                                "Added {$this->_alias['address']} -> {$this->_alias['goto']}",
                                $this->getAdmin(), $this->_alias['domain']
                            );
                        }
                        else
                        {
                            LogTable::log( 'ALIAS_EDIT',
                                "Edited {$this->_alias['address']} -> {$this->_alias['goto']}",
                                $this->getAdmin(), $this->_alias['domain']
                            );
                        }

                        $this->_alias['goto'] = implode( ',', array_unique( $postValues['goto'] ) );

                        $this->_alias->save();

                        $this->_helper->viewRenderer->setNoRender( true );
                        $this->addMessage( _( 'You have successfully added/edited the alias.' ), ViMbAdmin_Message::SUCCESS );
                        return print $this->view->render( 'close_colorbox_reload_parent.phtml' );
                    }
                    
                }
            }
        }
        else
        {
            if( $this->_domain )
                $editForm->getElement( 'domain' )->setValue( $this->_domain->id );

            if( $this->_mailbox )
                $this->view->defaultGoto = "{$this->_mailbox->local_part}@{$this->_mailbox->Domain->domain}";

            if( $this->_alias['id'] ) // editing
            {
                $editForm->setDefaults( $this->_alias->toArray() );

                $editForm
                    ->getElement( 'local_part' )
                    ->setValue( str_replace("@{$this->_alias['domain']}", '', $this->_alias['address'] ) )
                    ->setAttrib( 'disabled', 'disabled' );

                $editForm
                    ->getElement( 'domain' )
                    ->setAttrib( 'disabled', 'disabled' );
            }
        }

        if( $this->_domain )
            $editForm->getElement( 'domain' )->setValue( $this->_domain['id'] );

        $this->view->editForm = $editForm;
    }


    /**
     * The Ajax function providing JSON data for the jQuery UI Autocomplete on adding/editing aliases.
     */
    public function ajaxAutocompleteAction()
    {
        $term = ( isset( $_GET['term'] ) ? $_GET['term'] : '' );

        if( mb_strlen( $term ) < $this->_options['alias_autocomplete_min_length'] )
            return print '';

        if( !$this->authorise( false, null, false ) )
            return print '';

        $query = Doctrine_Query::create()
                    ->select( 'a.address' )
                    ->from( 'Alias a' )
                    ->where( 'a.address like ?', "{$term}%" );

        if( !$this->getAdmin()->isSuper() )
        {
            $query->leftJoin( 'a.Domain d' )
                 ->leftJoin( 'd.DomainAdmin da' )
                 ->andWhere( 'da.username = ?', $this->getAdmin()->username )
                 ->andWhere( 'd.domain = da.domain' );
        }

        $temp = $query->fetchArray();

        $addresses = array();

        foreach( $temp as $oneAddress )
            $addresses[] = $oneAddress['address'];

        print json_encode( $addresses );
    }

}
