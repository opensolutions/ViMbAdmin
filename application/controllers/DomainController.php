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
 * The domain management controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class DomainController extends ViMbAdmin_Controller_PluginAction
{

    /**
     * Local store for the form
     * @var ViMbAdmin_Form_Domain_AddEdit
     */
    private $domainForm = null;

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
        $this->authorise(); // ensure we are logged in
    }


    /**
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->forward( 'list' );
    }


    /**
     * List all domains if super admin, else relevant domains
     */
    public function listAction()
    {
        if( isset( $this->getSessionNamespace()->domain ) )
            unset( $this->getSessionNamespace()->domain );
        
        if( !isset( $this->_options['defaults']['server_side']['pagination']['domain']['enable'] ) || !$this->_options['defaults']['server_side']['pagination']['domain']['enable'] )
            $this->view->domains = $this->getD2EM()->getRepository( '\\Entities\\Domain' )->loadForDomainList( $this->getAdmin() );
        else
            $this->view->domains = [];

        if( isset( $this->_options['defaults']['list_size']['disabled'] ) && !$this->_options['defaults']['list_size']['disabled'] )
        {
            if( isset( $this->_options['defaults']['list_size']['multiplier'] ) && isset( OSS_Filter_FileSize::$SIZE_MULTIPLIERS[ $this->_options['defaults']['list_size']['multiplier'] ] ) )
                $size_multiplier = $this->_options['defaults']['list_size']['multiplier'];
            else
                $size_multiplier = OSS_Filter_FileSize::SIZE_KILOBYTES;

            $this->view->size_multiplier = $size_multiplier;
            $this->view->multiplier      = OSS_Filter_FileSize::$SIZE_MULTIPLIERS[ $size_multiplier ];
        }
    }

    public function listSearchAction()
    {
        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
        if( !isset( $this->_options['defaults']['server_side']['pagination']['domain']['enable'] ) || !$this->_options['defaults']['server_side']['pagination']['domain']['enable'] )
            echo "ko";
        else
        {
            $strl_len = isset( $this->_options['defaults']['server_side']['pagination']['domain']['min_search_str'] ) ? $this->_options['defaults']['server_side']['pagination']['domain']['min_search_str'] : 3;
            $search = $this->_getParam( "search", false );
            $this->view->ima = $ima = $this->_getParam( 'ima', 0 );
            if( $search && strlen( $search ) >= $strl_len )
            {
                $domains = $this->getD2EM()->getRepository( "\\Entities\\Domain" )->filterForDomainList( $search, $this->getAdmin() );
                $max_cnt = isset( $this->_options['defaults']['server_side']['pagination']['domain']['max_result_cnt'] ) ? $this->_options['defaults']['server_side']['pagination']['domain']['max_result_cnt'] : false;
                if( $domains && ( !$max_cnt || $max_cnt >= count( $domains ) ) )
                    echo json_encode( $domains );
                else
                    echo "ko";
            }
            else
                echo "ko";    
        }
    }


    /**
     * Toggles the active property for the current domain. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->_domain )
            print 'ko';

        $this->getDomain()->setActive( !$this->getDomain()->getActive() );
        $this->getDomain()->setModified( new DateTime() );
        
        $this->log(
            $this->getDomain()->getActive() ? \Entities\Log::ACTION_DOMAIN_ACTIVATE : \Entities\Log::ACTION_DOMAIN_DEACTIVATE,
            "{$this->getAdmin()->getFormattedName()} " . ( $this->getDomain()->getActive() ? 'activated' : 'deactivated' ) . " domain {$this->getDomain()->getDomain()}"
        );
        
        $this->getD2EM()->flush();

        print 'ok';
    }


    /**
     * Instantiate / get the domain add-edit form
     * @return ViMbAdmin_Form_Domain_AddEdit
     */
    public function getDomainForm()
    {
        if( $this->domainForm == null )
        {
            $form = new ViMbAdmin_Form_Domain_AddEdit();
            if( isset( $this->_options['defaults']['quota']['multiplier'] ) )
                $form->setFilterFileSizeMultiplier( $this->_options['defaults']['quota']['multiplier'] );
            
            $this->view->form = $this->domainForm = $form;
            
            // call plugins
            $this->notify( 'domain', 'add', 'formPostProcess', $this );
        }
        return $this->domainForm;
    }

    /**
     * Add / edit a domain.
     */
    public function addAction()
    {
        if( !$this->getDomain() )
        {
            $this->view->isEdit = $isEdit = false;
            $this->_domain = new \Entities\Domain();
            $this->getDomain()->setAliasCount( 0 );
            $this->getDomain()->setMailboxCount( 0 );
            $this->getDomain()->setCreated( new DateTime() );
            $this->getD2EM()->persist( $this->_domain );

            // set defaults
            $form = $this->getDomainForm();
            $form->getElement( 'max_mailboxes' )->setValue( $this->_options['defaults']['domain']['mailboxes'] );
            $form->getElement( 'max_aliases'   )->setValue( $this->_options['defaults']['domain']['aliases'] );
            $form->getElement( 'transport'     )->setValue( $this->_options['defaults']['domain']['transport'] );
            $form->getElement( 'quota'         )->setValue( $this->_options['defaults']['domain']['quota'] );
            $form->getElement( 'max_quota'     )->setValue( $this->_options['defaults']['domain']['maxquota'] );
        }
        else
        {
            $this->view->isEdit = $isEdit = true;
            $form = $this->getDomainForm();
            $form->assignEntityToForm( $this->getDomain(), $this, $isEdit );
            $form->getElement( 'domain' )
                ->setAttrib( 'readonly', 'readonly' )
                ->setRequired( false )
                ->removeValidator( 'OSSDoctrine2Uniqueness' );
        }

        $this->authorise( true ); // must be a super admin

        $this->view->quota_multiplier = $form->getFilterFileSizeMultiplier();

        $this->view->domain = $this->getDomain();

        $this->notify( 'domain', 'add', 'addPrepare', $this );
        
        if( $this->getRequest()->isPost() )
        {
            $this->notify( 'domain', 'add', 'addPrevalidate', $this );
            
            if( $form->isValid( $_POST ) )
            {
                $this->notify( 'domain', 'add', 'addPostvalidate', $this );
            
                $form->assignFormToEntity( $this->getDomain(), $this, $isEdit );

                if( $isEdit )
                    $this->getDomain()->setModified( new \DateTime() );

                $this->log(
                    $isEdit ? \Entities\Log::ACTION_DOMAIN_EDIT : \Entities\Log::ACTION_DOMAIN_ADD,
                    "{$this->getAdmin()->getFormattedName()} " . ( $isEdit ? ' edited' : ' added' ) . " domain {$this->getDomain()->getDomain()}"
                );
            
                $this->notify( 'domain', 'add', 'addPreflush', $this );
                $this->getD2EM()->flush();
                $this->notify( 'domain', 'add', 'addPostflush', $this );
            
                $this->notify( 'domain', 'add', 'addFinish', $this );
                $this->addMessage( _( "You have successfully added/edited the domain record." ), OSS_Message::SUCCESS );

                $this->redirect( 'domain/list' );
            } 
        }
    }

    public function editAction()
    {
        $this->forward( 'add' );
    }

    /**
     * Lists the domain admins.
     */
    public function adminsAction()
    {
        if( !$this->getDomain() )
        {
            $this->addMessage( 'Invalid or non-existent domain.', OSS_Message::ERROR );
            $this->redirect( 'domain/list' );
        }

        $this->authorise( true ); // must be a super admin
        $this->view->domain = $this->getDomain();
    }


    /**
     * Remove a domain admin. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function removeAdminAction()
    {
        if( !$this->getDomain() )
        {
            $this->addMessage( _( 'Invalid or missing admin id.' ), OSS_Message::ERROR );
            $this->redirect( 'domain/list' );
        }else if( !$this->getTargetAdmin() )
        {
            $this->addMessage( _( 'Invalid or missing domain id.' ), OSS_Message::ERROR );
            $this->redirect( 'domain/admins/did/' . $this->getDomain()->getId() );
        }

        $this->authorise( true ); // must be a super admin

        $this->getTargetAdmin()->removeDomain( $this->getDomain() );
        $this->log(
            \Entities\Log::ACTION_ADMIN_TO_DOMAIN_REMOVE,
            "{$this->getAdmin()->getFormattedName()} removed admin {$this->getTargetAdmin()->getFormattedName()} from domain {$this->getDomain()->getDomain()}"
        );

        $this->getD2EM()->flush();
        $this->addMessage( 'You have successfully removed the domain from admin '. $this->getTargetAdmin()->getUsername(), OSS_Message::SUCCESS );
        $this->redirect( 'domain/admins/did/' . $this->getDomain()->getId() );
    }

    /**
     * Add a domain admin. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function assignAdminAction()
    {
        if( !$this->getDomain() )
        {
            $this->addMessage( _( 'Invalid or missing domain id.' ), OSS_Message::ERROR );
            $this->redirect( 'doamin/list' );
        }

        $this->view->domain = $this->getDomain();
        $this->authorise( true ); // must be a super admin

        $remainingAdmins = $this->getD2em()->getRepository( "\\Entities\\Admin" )->getNotAssignedForDomain( $this->getDomain() );

        $this->view->form = $form = new ViMbAdmin_Form_Domain_AssignAdmin();
        $form->getElement( "admin" )->setMultiOptions( $remainingAdmins );   

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $this->_targetAdmin = $this->loadAdmin( $form->getValue( 'admin' ) );

            if( $this->getDomain()->getAdmins()->contains( $this->getTargetAdmin() ) )
                $this->addMessage( _( 'This admin is already assigned to the domain.' ), OSS_Message::ERROR );
            else
            {
                $this->getTargetAdmin()->addDomain( $this->getDomain() );
                $this->log(
                    \Entities\Log::ACTION_ADMIN_TO_DOMAIN_ADD,
                    "{$this->getAdmin()->getFormattedName()} added admin {$this->getTargetAdmin()->getFormattedName()} to domain {$this->getDomain()->getDomain()}"
                );
                $this->getD2EM()->flush();
                $this->addMessage(  'You have successfully assigned a admin to the domain.', OSS_Message::SUCCESS );
            }

            $this->redirect( 'domain/admins/did/' . $this->getDomain()->getId() );
        }
        if( sizeof( $remainingAdmins ) == 0 )
            $this->addMessage( 'There are no administrators to assign to this domain.', OSS_Message::INFO );
    }

    /**
     * Purges a mailbox, removes all the related entries from the other tables.
     */
    public function purgeAction()
    {
        $this->authorise( true );
        $this->notify( 'domain', 'purge', 'preRemove', $this );
        $this->getD2EM()->getRepository( '\\Entities\\Domain' )->purge( $this->getDomain() );
        $this->notify( 'domain', 'purge', 'purgeFinish', $this );
        $this->redirect( 'domain/list' );
    }
}
