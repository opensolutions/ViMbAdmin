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
 * The form for changing passwords.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Admin_Password extends ViMbAdmin_Form
{
    /**
     *  Minimum password length
     * @var int Minimum password length
     */
    private $minPasswordLength = 8;

    public function init( $options = null )
    {
        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'admin/form/password.phtml' ) ) ) );

        $this->setMethod( 'post' )
            ->setAttrib( 'id', 'change_password_form' )
            ->setAttrib( 'name', 'change_password_form' );

        $password = $this->createElement( 'text', 'password' )
            ->setAttrib( 'size', 40 )
            ->setLabel( _( 'Password' ) )
            ->setAttrib( 'title', _( 'Password' ) )
            ->setAttrib( 'class', 'required' )
            ->setAttrib( 'autocomplete', 'off' )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', false, array( $this->minPasswordLength, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' );

        $sendEmail = $this->createElement( 'checkbox', 'email' )
            ->setLabel( _( 'Send email' ) )
            ->addValidator('InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );

        $submit = $this->createElement( 'submit' , 'change' )
            ->setLabel( _( 'Submit' ) );

        $this->addElement( $password )
            ->addElement( $sendEmail )
            ->addElement( $submit );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }
    
    /**
     * Setter method for the minimum password length
     *
     * @param int $len The minimum password length
     * @return ViMbAdmin_Form_Mailbox_AddEdit
     */
    public function setMinPasswordLength( $len )
    {
        $this->minPasswordLength = $len;
        return $this;
    }

    /**
     * Getter method for the minimum password length
     *
     * @return int $len The minimum password length
     */
    public function getMinPasswordLength()
    {
        return $this->minPasswordLength;
    }

}
