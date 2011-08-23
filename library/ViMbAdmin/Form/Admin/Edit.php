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
 * The form for adding and editing admins.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Admin_Edit extends ViMbAdmin_Form
{

    public function __construct( $options = null )
    {
        parent::__construct( $options );

        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'admin/form/edit.phtml' ) ) ) );

        $this
            ->setMethod( 'post' )
            ->setAttrib( 'id', 'admin_edit_form' )
            ->setAttrib( 'name', 'admin_edit_form' );

        $salt = $this
                        ->createElement( 'text', 'salt' )
                        ->setLabel( _( 'Security Salt' ) )
                        ->setAttrib( 'title', _( 'Security Salt' ) )
                        ->setAttrib( 'size', 64 )
                        ->setAttrib( 'class', 'required' )
                        ->setAttrib( 'autocomplete', 'off' )
                        ->setRequired( true )
                        ->addValidator( 'NotEmpty', true )
                        ->addFilter( 'StringTrim' )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StripSlashes' );

        $username = $this
                        ->createElement( 'text', 'username' )
                        ->setAttrib( 'size', 30 )
                        ->setLabel( _( 'Username' ) )
                        ->setAttrib( 'title', _( 'Username' ) )
                        ->setAttrib( 'class', 'required' )
                        ->setAttrib( 'autocomplete', 'off' )
                        ->setRequired( true )
                        ->addValidator( 'NotEmpty', true )
                        ->addValidator( 'EmailAddress', true, array( 'mx' => true ) )
                        ->addValidator( 'DoctrineUniqueness', true, array( 'table' => 'Admin', 'column' => 'username' ) )
                        ->addFilter( 'StringTrim' )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StripSlashes' );

        $username->getValidator( 'NotEmpty' )->setMessage( _( 'You must enter an email address.' ), Zend_Validate_NotEmpty::IS_EMPTY );

        $password = $this
                        ->createElement( 'password', 'password' )
                        ->setLabel( _( 'Password' ) )
                        ->setAttrib( 'title', _( 'Password' ) )
                        ->setAttrib( 'size', 40 )
                        ->setRequired( true )
                        ->addValidator( 'NotEmpty', true )
                        ->addValidator( 'StringLength', true, array( 8, 32 ) )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StripSlashes' );

        $active = $this
                        ->createElement( 'checkbox', 'active' )
                        ->setLabel( _( 'Active' ) )
                        ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
                        ->addFilter( 'Digits' );

        $super = $this
                        ->createElement( 'checkbox', 'super' )
                        ->setLabel( _( 'Superadmin' ) )
                        ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
                        ->addFilter( 'Digits' );

        $welcomeEmail = $this
                        ->createElement( 'checkbox', 'welcome_email' )
                        ->setLabel( _( 'Welcome email' ) )
                        ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
                        ->addFilter( 'Digits' );

        $cancel = $this
                        ->createElement( 'button' , 'cancel' )
                        ->setLabel( _( 'Cancel' ) );

        $submit = $this
                        ->createElement( 'submit' , 'save' )
                        ->setLabel( _( 'Save' ) );

        $this
            ->addElement( $salt )
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
