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
 * The form for logging in.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Auth_Login extends ViMbAdmin_Form
{

    public function __construct( $options = null )
    {
        parent::__construct( $options );

        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'auth/form/login.phtml' ) ) ) );

        $this
            ->setMethod( 'post' )
            ->setAttrib( 'id', 'login_form' )
            ->setAttrib( 'name', 'login_form' );

        $username = $this
                        ->createElement( 'text', 'username' )
                        ->setAttrib( 'size', 30 )
                        ->setLabel( _( 'Username' ) )
                        ->setAttrib( 'title', _( 'Username' ) )
                        ->setAttrib( 'class', 'required' )
                        //->setAttrib( 'autocomplete', 'off' )
                        ->setRequired( true )
                        ->addValidator( 'NotEmpty', true )
                        ->addValidator( 'EmailAddress', true, array( 'mx' => true ) )
                        ->addFilter( 'StringTrim' )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StripSlashes' );

        $username->getValidator( 'NotEmpty' )->setMessage( _( 'You must enter your email address' ), Zend_Validate_NotEmpty::IS_EMPTY );

        $password = $this
                        ->createElement( 'password', 'password' )
                        ->setLabel( _( 'Password' ) )
                        ->setAttrib( 'title', _( 'Password' ) )
                        ->setAttrib( 'size', 30 )
                        ->setRequired( true )
                        ->addValidator( 'NotEmpty', true )
                        ->addValidator( 'StringLength', true, array( 8, 32 ) )
                        ->addFilter( 'StringTrim' )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StripSlashes' );

        $submit = $this
                        ->createElement( 'submit' , 'login' )
                        ->setLabel( _( 'Log In' ) );

        $this
            ->addElement( $username )
            ->addElement( $password )
            ->addElement( $submit );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }

}
