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
 * The domain management controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class DomainController extends ViMbAdmin_Controller_Action
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
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->_forward( 'list' );
    }


    /**
     * List all domains if super admin, else relevant domains
     */
    public function listAction()
    {
        $query = Doctrine_Query::create()
                    ->select( 'd.*' )
                    ->from( 'Domain d' );

        if( !$this->getAdmin()->isSuper() )
        {
            $query->leftJoin( 'DomainAdmin da' )
                  ->where( 'da.username = ?', $this->getAdmin()->username )
                  ->andWhere( 'da.domain = d.domain' );
        }

        $this->view->domains = $query->execute();
        $query->free();
    }


    /**
     * Toggles the active property for the current domain. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->_domain )
            return print 'ko';

        $this->_domain['active'] = !$this->_domain['active'];
        $this->_domain->save();

        LogTable::log( 'DOMAIN_TOGGLE_ACTIVE',
            "Set " . ( $this->_domain['active'] ? '' : 'de' ) . "active",
            $this->getAdmin(), $this->_domain
        );

        print 'ok';
    }


    /**
     * Edit a domain.
     */
    public function editAction()
    {
        $editForm = new ViMbAdmin_Form_Domain_Edit();
        $this->view->modal = $modal = $this->_getParam( 'modal', false );

        $add = true;
        if( !$this->_domain )
        {
            $this->view->operation = 'Add';
            $this->_domain = new Domain();

            // set defaults
            $this->_domain['mailboxes'] = $this->_options['defaults']['domain']['mailboxes'];
            $this->_domain['aliases']   = $this->_options['defaults']['domain']['aliases'];
            $this->_domain['transport'] = $this->_options['defaults']['domain']['transport'];
            $this->_domain['quota']     = $this->_options['defaults']['domain']['quota'];
            $this->_domain['maxquota']  = $this->_options['defaults']['domain']['maxquota'];
        }
        else
        {
            $add = false;
            $this->view->operation = 'Edit';

            $editForm
                ->getElement( 'domain' )
                ->setAttrib( 'readonly', 'readonly' )
                ->setRequired( false )
                ->removeValidator( 'DoctrineUniqueness' );
        }

        $this->view->domainModel = $this->_domain;

        if( $this->getRequest()->isPost() && !$modal )
        {
            if( $editForm->isValid( $_POST ) )
            {
                $this->_domain->fromArray( $editForm->getValues() );
                $this->_domain->save();

                LogTable::log( ( $this->view->operation == 'Add' ? 'DOMAIN_ADD' : 'DOMAIN_EDIT' ),
                    print_r( $this->_domain->toArray(), true ),
                    $this->getAdmin(), $this->_domain
                );

                if( $this->_getParam( 'helper', true ) )
                {
                    $this->addMessage( _( "You have successfully added/edited the domain record." ), ViMbAdmin_Message::SUCCESS );
                    $this->_redirect( 'domain/list' );
                }
                else
                {
                    if( $add )
                        $this->addMessage( _( "You have successfully added/edited the domain record." ), ViMbAdmin_Message::SUCCESS );

                    $this->_helper->viewRenderer->setNoRender( true );
                    print 'ok';
                }
            } // if valid post
            else
            {
                if( !$this->_getParam( 'helper', true ) )
                {
                    $this->view->modal = true;
                }
            }

        }
        else
        {
            $editForm->setDefaults( $this->_domain->toArray() );
        }

        $this->view->editForm = $editForm;
    }


    /**
     * Lists the domain admins.
     */
    public function adminsAction()
    {
        if( !$this->_domain )
            return $this->_forward( 'list' );

        $this->authorise( true ); // must be a super admin

        $this->view->domainModel = $this->_domain;

        $alreadyAssigned = array();

        foreach( $this->_domain->DomainAdmin as $da )
            $alreadyAssigned[] = $da->Admin->id;

        $this->view->adminList = Doctrine_Query::create()
            ->from( 'Admin' )
            ->where( 'super = 0' )
            ->whereNotIn( 'id', $alreadyAssigned )
            ->orderBy( 'username asc' )
            ->fetchArray();
    }


    /**
     * Remove a domain admin. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxRemoveAdminAction()
    {
        if( !$this->_targetAdmin || !$this->_domain )
            return print 'ko';

        $this->authorise( true ); // must be a super admin

        Doctrine_Query::create()
            ->delete()
            ->from( 'DomainAdmin' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->andWhere( 'username = ?', $this->_targetAdmin['username'] )
            ->execute();

        LogTable::log( 'DOMAIN_REMOVE_ADMIN',
            "Removed {$this->_targetAdmin['username']}",
            $this->getAdmin(), $this->_domain
        );

        print 'ok';
    }


    /**
     * Add a domain admin. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxAddAdminAction()
    {
        if( !$this->_targetAdmin || !$this->_domain )
            return print 'ko'; // 'ok' we just don't do anything

        $this->authorise( true ); // must be a super admin

        $adminAlready = Doctrine_Query::create()
            ->from( 'DomainAdmin' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->andWhere( 'username = ?', $this->_targetAdmin['username'] )
            ->fetchArray();

        if( sizeof( $adminAlready ) == 0 ) // not already assigned
        {
            $domainAdmin = new DomainAdmin;
            $domainAdmin['domain'] = $this->_domain['domain'];
            $domainAdmin['username'] = $this->_targetAdmin['username'];
            $domainAdmin->save();
        }

        LogTable::log( 'DOMAIN_ADD_ADMIN',
            "Added {$this->_targetAdmin['username']}",
            $this->getAdmin(), $this->_domain
        );

        $this->addMessage( _( 'You have successfully added an admin to this domain.' ), ViMbAdmin_Message::SUCCESS );
        print 'ok';
    }


    /**
     * Purges a mailbox, removes all the related entries from the other tables.
     * Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxPurgeAction()
    {
        $this->authorise( true );

        Doctrine_Query::create()
            ->delete()
            ->from( 'Mailbox' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->execute();

        Doctrine_Query::create()
            ->delete()
            ->from( 'Log' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->execute();

        Doctrine_Query::create()
            ->delete()
            ->from( 'DomainAdmin' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->execute();

        Doctrine_Query::create()
            ->delete()
            ->from( 'Alias' )
            ->where( 'domain = ?', $this->_domain['domain'] )
            ->execute();

            $this->_domain->delete();

        LogTable::log( 'DOMAIN_PURGE',
            "Purged {$this->_domain['domain']}",
            $this->getAdmin(), null
        );

        print 'ok';
    }

}
