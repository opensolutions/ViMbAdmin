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
 * The form for adding and editing mailboxes.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Mailbox_AccessPermissions extends ViMbAdmin_Form_Plugin
{
    /**
     * Array of possible access permissions
     */
    private $accessPermissions = [];
    
    /**
     * Array of access elements
     */
    private $accessElements = [];
    
    public function init()
    {
        $this->setDecorators( [ [ 'ViewScript', [ 'viewScript' => 'mailbox/form/access-permissions.phtml' ] ] ] );
        $this->setAttrib( 'id', 'access' )
            ->setAttrib( 'title', 'Access' );

        $cbAccessPermissions = $this->createElement( 'checkbox', 'plugin_accessPermissions' )
            ->setLabel( _( 'Set specific access permissions for this mailbox' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );
        
        $this->addElement( $cbAccessPermissions );
    }
    
    public function setAccessPermissions( $perms, $mailbox = null )
    {
        $this->accessPermissions = $perms;
        
        foreach( $perms as $name => $label )
        {
            $ename = "plugin_accessPermission_{$name}";
            
            $e = $this->createElement( 'checkbox', $ename )
                ->setLabel( _( $label ) )
                ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
                ->addFilter( 'Digits' );
            
            $this->addElement( $e );
            $this->accessElements[] = $ename;
        }
        
        if( $mailbox !== null )
        {
            if( $mailbox->getAccessRestriction() != 'ALL' )
            {
                $this->getElement( 'plugin_accessPermissions' )->setChecked( true );
                
                foreach( explode( ',', $mailbox->getAccessRestriction() ) as $mbperm )
                    if( $this->getElement( "plugin_accessPermission_{$mbperm}" ) )
                        $this->getElement( "plugin_accessPermission_{$mbperm}" )->setChecked( true );
            }
        }
    }
    
    public function getAccessPermissions()
    {
        return $this->accessPermissions;
    }
    
    public function getSelectedAccessPermissions()
    {
        $perms = [];
        foreach( $this->getAccessPermissions() as $k => $v )
        {
            if( $this->getElement( "plugin_accessPermission_{$k}" ) && $this->getElement( "plugin_accessPermission_{$k}" )->isChecked() )
                $perms[] = $k;
        }
        
        return implode( ',', $perms );
    }
    
    public function getAccessElements()
    {
        return $this->accessElements;
    }
    
    public function isValid( $data )
    {
        $valid = parent::isValid( $data );
        
        if( $this->getElement( 'plugin_accessPermissions' )->isChecked() && $this->getSelectedAccessPermissions() == '' )
        {
            $this->getElement( 'plugin_accessPermissions' )->addError(
                _( "You must select which services the user can access if you are choosing to apply specific access permissions" )
            );
            $valid = false;
        }
        
        return $valid;
    }

    /*public function getName()
    {
        return "Access";
    }*/
}
