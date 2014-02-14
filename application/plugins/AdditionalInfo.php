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
 * The AdditionalInfo plugin
 *
 * @package ViMbAdmin
 * @subpackage Plugins
 */
class ViMbAdminPlugin_AdditionalInfo extends ViMbAdmin_Plugin implements OSS_Plugin_Observer
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
     * Prepares and adds additional information subform
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function mailbox_add_formPostProcess( $controller, $params )
    {
        $form    = $controller->getMailboxForm();
        $mailbox = $controller->getMailbox();
        $subform = new ViMbAdmin_Form_Mailbox_AdditionalInfo();

        if( $controller->isEdit() )
            $subform->createElements( $controller->getOptions()['vimbadmin_plugins']['AdditionalInfo']['elements'], $mailbox );
        else
            $subform->createElements( $controller->getOptions()['vimbadmin_plugins']['AdditionalInfo']['elements'] );
        
        $form->addSubForm( $subform, 'pluginsf_AdditionalInfo' );
        
    }
     
    /**
     * Sets additional information as mailbox preferences.
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function mailbox_add_addPreflush( $controller, $params )
    {
        $form    = $controller->getMailboxForm();
        $mailbox = $controller->getMailbox();
        $subform = $form->getSubForm( 'pluginsf_AdditionalInfo' );
        
        foreach( $subform->getValues() as $name => $value )
        {
            $name = substr( $name, 22 );
            $mailbox->setPreference( 'xpiInfo.' . $name, $value );
        }
        $controller->getD2Cache()->delete( 'ViMbAdmin_Plugin_AdditionalInfo_autocomplete_*' );
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
        $controller->getD2Cache()->delete( 'ViMbAdmin_Plugin_AdditionalInfo_autocomplete_*' );
    }
    
    /**
     * Prepares and adds additional information subform
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function alias_add_formPostProcess( $controller, $params )
    {
        $form    = $controller->getAliasForm();
        $alias   = $controller->getAlias();
        $subform = new ViMbAdmin_Form_Alias_AdditionalInfo(); 

        if( $controller->isEdit() )
            $subform->createElements( $controller->getOptions()['vimbadmin_plugins']['AdditionalInfo']['alias']['elements'], $alias );
        else
            $subform->createElements( $controller->getOptions()['vimbadmin_plugins']['AdditionalInfo']['alias']['elements'] );
        
        $form->addSubForm( $subform, 'pluginsf_AdditionalInfo' );
    }
    
    
    /**
     * Sets additional information as mailbox preferences.
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function alias_add_addPreflush( $controller, $params )
    {
        $form    = $controller->getAliasForm();
        $alias   = $controller->getAlias();
        $subform = $form->getSubForm( 'pluginsf_AdditionalInfo' );
        
        foreach( $subform->getValues() as $name => $value )
        {
            $name = substr( $name, 22 );
            $alias->setPreference( 'xpiInfo.' . $name, $value );
        }
        $controller->getD2Cache()->delete( 'ViMbAdmin_Plugin_AdditionalInfo_autocomplete_*' );
    }
    
    /**
     * Clears cache for addintional Info autocomplete values.
     *
     * @param object $controller an OSS_Controller_Action instance
     * @param array $params Additional parameters
     * @return void
     */
    public function alias_purge_postFlush( $controller, $params )
    {
        $controller->getD2Cache()->delete( 'ViMbAdmin_Plugin_AdditionalInfo_autocomplete_*' );
    }
}

