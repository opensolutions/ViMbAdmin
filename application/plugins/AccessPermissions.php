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
 * The AccessPermissions plugin
 *
 * AccessPermissions were part of the main ViMbAdmin code but I have shunted them to a
 * plugin to demonstrate and prove the arcitecture. It's a slight cheat as AccessPermissions
 * rely on a specific column in the Mailbox database table which plugins should typically
 * avoid.
 *
 * See https://github.com/opensolutions/ViMbAdmin3/wiki/Plugin-Access-Permissions
 *
 * @package ViMbAdmin
 * @subpackage Plugins
 */
class ViMbAdminPlugin_AccessPermissions extends ViMbAdmin_Plugin implements OSS_Plugin_Observer
{

    public function __construct( OSS_Controller_Action $controller )
    {
        parent::__construct( $controller, get_class() );
        
        // no setup tasks are required
        //
        // typically you might load an config file for example, but as this is a system
        // plugin, we can use the main application.ini for that.
    }
    
    public function mailbox_add_formPostProcess( $controller, $params )
    {
        $form    = $controller->getMailboxForm();
        $mailbox = $controller->getMailbox();
        
        $subform = new ViMbAdmin_Form_Mailbox_AccessPermissions();

        if( $controller->isEdit() )
            $subform->setAccessPermissions( $controller->getOptions()['vimbadmin_plugins']['AccessPermissions']['type'], $mailbox );
        else
            $subform->setAccessPermissions( $controller->getOptions()['vimbadmin_plugins']['AccessPermissions']['type'] );
        
        $form->addSubForm( $subform, 'pluginsf_AccessPermissions' );
    }
    
    
    public function mailbox_add_addPostvalidate( $controller, $params )
    {
        $subform = $controller->getMailboxForm()->getSubform( 'pluginsf_AccessPermissions' );
        
        if( $subform->getElement( 'plugin_accessPermissions' )->isChecked() )
        {
            $controller->getMailbox()->setAccessRestriction( $subform->getSelectedAccessPermissions() );
        }
        else
            $controller->getMailbox()->setAccessRestriction( 'ALL' );
    }
    
    
    
  
}

