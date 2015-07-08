<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2012 Open Source Solutions Limited
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
 * The form for adding and editing admins.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Admin_AddEdit extends ViMbAdmin_Form
{

    public function init()
    {
        $this->setDecorators( [ [ 'ViewScript', [ 'viewScript' => 'admin/form/add-edit.phtml' ] ] ] );

        $this->setAttrib( 'id', 'admin_edit_form' )
            ->setAttrib( 'name', 'admin_edit_form' );

        $salt = $this->createElement( 'text', 'salt' )
            ->setLabel( _( 'Security Salt' ) )
            ->setAttrib( 'title', _( 'Security Salt' ) )
            ->setAttrib( 'size', 64 )
            ->setAttrib( 'class', 'span6 required' )
            ->setAttrib( 'autocomplete', 'off' )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addFilter( 'StringTrim' )
            ->addFilter( new OSS_Filter_HtmlEntitiesDecode );

        $username = OSS_Form_Auth::createUsernameElement( OSS_Form_Auth::USERNAME_TYPE_EMAIL );
        $username->addValidator( new OSS_Validate_OSSDoctrine2Uniqueness( array( 'entity' => '\\Entities\\Admin', 'property' => 'username' ) ), true );

        $password = OSS_Form_Auth::createPasswordElement();
        $password->setAttrib( 'autocomplete', 'off' );

        $active = $this->createElement( 'checkbox', 'active' )
            ->setLabel( _( 'Active' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' )
            ->setChecked( true );

        $super = $this->createElement( 'checkbox', 'super' )
            ->setLabel( _( 'Superadmin' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );

        $welcomeEmail = $this->createElement( 'checkbox', 'welcome_email' )
            ->setLabel( _( 'Welcome email' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' )
            ->setChecked( true );

        $cancel = $this->createElement( 'button' , 'cancel' )
            ->setLabel( _( 'Cancel' ) );

        $submit = $this->createElement( 'submit' , 'save' )
            ->setLabel( _( 'Save' ) );

        $this->addElement( $salt )
            ->addElement( $username )
            ->addElement( $password )
            ->addElement( $active )
            ->addElement( $super )
            ->addElement( $welcomeEmail )
            ->addElement( $cancel )
            ->addElement( $submit );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }

}
