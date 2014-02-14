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
 * @copyright Copyright (c) 2011 - 2012 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/**
 * The form for password reset.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Auth_ResetPassword extends ViMbAdmin_Form
{

    public function init()
    {
        $this->setAttrib( 'id', 'auth_reset_password' )
            ->setAttrib( 'name', 'auth_reset_password' );
        
        $this->addElement( OSS_Form_Auth::createUsernameElement() );
        $this->addElement( OSS_Form_Auth::createPasswordResetTokenElement() );
        $this->addElement( OSS_Form_Auth::createPasswordElement() );
        $this->addElement( OSS_Form_Auth::createPasswordConfirmElement() );
        $this->addElement( OSS_Form::createSubmitElement( 'submit', _( 'Reset Password' ) ) );
        $this->_addActionsDisplayGroupElement( OSS_Form_Auth::createReturnToLoginElement() );
    }

}
