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
 * The AdditionalInfo plugin
 *
 * @package ViMbAdmin
 * @subpackage Plugins
 */
class ViMbAdminPlugin_DirectoryEntry extends ViMbAdmin_Plugin implements OSS_Plugin_Observer
{

    public function __construct( OSS_Controller_Action $controller )
    {
        parent::__construct( $controller, get_class() );
        
        // no setup tasks are required
        //
        // typically you might load an config file for example, but as this is a system
        // plugin, we can use the main application.ini for that.
    }
    
   /**
     * Prepares and adds directory entry subform
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function mailbox_add_formPostProcess( $controller, $params )
    {
        $form    = $controller->getMailboxForm();
        $mailbox = $controller->getMailbox();
        $subform = new ViMbAdmin_Form_Mailbox_DirectoryEntry();

        $disabled = isset( $controller->getOptions()['vimbadmin_plugins']['DirectoryEntry']['disabled_elements'] ) ?
                    $controller->getOptions()['vimbadmin_plugins']['DirectoryEntry']['disabled_elements'] :
                    [];
        if( isset( $controller->getOptions()['identity']['orgname'] ) )
            $subform->getElement( "plugin_directoryEntry_O" )->setValue( $controller->getOptions()['identity']['orgname'] );

        if( $controller->isEdit() )
            $subform->prepare( $disabled, $mailbox->getDirectoryEntry() );
        else
            $subform->prepare( $disabled );
        
        $form->addSubForm( $subform, 'pluginsf_DirectoryEntry' );
        
    }
     
    /**
     * Creates/updates directory entry.
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function mailbox_add_addPreflush( $controller, $params )
    {
        $form    = $controller->getMailboxForm();
        $mailbox = $controller->getMailbox();
        $subform = $form->getSubform( 'pluginsf_DirectoryEntry' );

        if( !$mailbox->getDirectoryEntry() )
        {
            $dentry = new \Entities\DirectoryEntry();
            $controller->getD2EM()->persist( $dentry );
            $dentry->setMailbox( $mailbox );
            $dentry->setVimbCreated( new \DateTime() );
        }
        else
            $dentry = $mailbox->getDirectoryEntry();

        $dentry->setMail( $mailbox->getUsername() );
        $subform->formToEntity( $dentry );
        $dentry->setVimbUpdate( new \DateTime() );
    }
    
    /**
     * Clears cache for additional Info autocomplete values.
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function mailbox_purge_postFlush( $controller, $params )
    {
        //$controller->getD2Cache()->delete( 'ViMbAdmin_Plugin_AdditionalInfo_autocomplete_*' );
    }
    
    
    /**
     * Deletes the drectory entry
     *
     * @param object $controller an OSS_Controller_Action instance
     * @return void
     * @access public
     */
    public function mailbox_purge_preFlush( $controller, $params )
    {
        $mailbox = $controller->getMailbox();
        
        if( $de = $mailbox->getDirectoryEntry() )
        {
            $controller->getD2EM()->remove( $de );
            $controller->getD2EM()->flush();
        }
    }            

    /**
     * Deletes the drectory entry when archiving
     *
     * @param object $controller an OSS_Controller_Action instance
     * @return void
     * @access public
     */
    public function archive_add_preSerialize( $controller, $params )
    {
        if( $de = $controller->getMailbox()->getDirectoryEntry() )
        {

            $controller->getD2EM()->remove( $de );
            $controller->getMailbox()->setDirectoryEntry( null );
            $controller->getD2EM()->flush();
        }
    }

                                                                                                                                                    
}

