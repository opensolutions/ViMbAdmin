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
 */

/**
* The form for password reset.
*/
class ViMbAdmin_Form_Admin_ChangePassword extends ViMbAdmin_Form
{

    public function __construct( $options = null )
    {
        parent::__construct( $options );

        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'admin/form/change-password.phtml' ) ) ) );

        $this->setMethod( 'post' )
            ->setAttrib( 'id', 'change_password_form' )
            ->setAttrib( 'name', 'change_password_form' );

        $currentPassword = $this
            ->createElement( 'password', 'current_password' )
            ->setLabel( 'Current Password' )
            ->setAttrib( 'title', 'Current Password' )
            ->setAttrib( 'size', 20)
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', true, array( 8, 255 ) )
            ->addFilter( 'StripSlashes' );

        $password = $this
            ->createElement( 'password', 'password' )
            ->setLabel( 'New Password' )
            ->setAttrib( 'title', 'New Password' )
            ->setAttrib( 'size', 20)
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', true, array( 8, 255 ) )
            ->addFilter( 'StripSlashes' );

        $confirmPassword = $this
            ->createElement( 'password', 'confirm_password' )
            ->setLabel( 'Confirm New Password' )
            ->setAttrib( 'title', 'Confirm New Password' )
            ->setAttrib( 'size', 20)
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'OSSIdenticalField', true, array( 'fieldName' => 'password', 'fieldTitle' => 'the new password' ) )
            ->addFilter( 'StripSlashes' );

        $confirmPassword->getValidator( 'NotEmpty' )
            ->setMessage( 'The confirmation password is required and must match the new password', Zend_Validate_NotEmpty::IS_EMPTY );

        $this
            ->addElement( $currentPassword )
            ->addElement( $password )
            ->addElement( $confirmPassword );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }

}
