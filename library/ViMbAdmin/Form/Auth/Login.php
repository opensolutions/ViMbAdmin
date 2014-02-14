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
 */

/**
 * The form for logging in.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Auth_Login extends ViMbAdmin_Form
{

    public function init()
    {
        $this->setAttrib( 'id', 'login_form' )
            ->setAttrib( 'name', 'login_form' );

        $username = OSS_Form_Auth::createUsernameElement( OSS_Form_Auth::USERNAME_TYPE_EMAIL );
        $username->setAttrib( 'class', 'span3' );
        $this->addElement( $username );
        
        $this->addElement( OSS_Form_Auth::createPasswordElement() );
        $this->addElement( OSS_Form_Auth::createRememberMeElement() );
        
        $submit = $this->createElement( 'submit' , 'login' )
            ->setLabel( _( 'Log In' ) );
        $this->addElement( $submit );
        
        $this->_addActionsDisplayGroupElement( OSS_Form_Auth::createLostPasswordElement() );
    }

}
