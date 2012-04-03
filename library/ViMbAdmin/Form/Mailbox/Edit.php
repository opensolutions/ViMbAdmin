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
class ViMbAdmin_Form_Mailbox_Edit extends ViMbAdmin_Form
{

    public function __construct( $options = null, $domainList, $minPass = 8 )
    {
        parent::__construct( $options );

        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'mailbox/form/edit.phtml' ) ) ) );

        $this->setMethod( 'post' )
            ->setAttrib( 'id', 'mailbox_edit_form' )
            ->setAttrib( 'name', 'mailbox_edit_form' );

        $localPart = $this->createElement( 'text', 'local_part' )
            ->setAttrib( 'size', 40 )
            ->setLabel( _( 'Local Part' ) )
            ->setAttrib( 'title', _( 'Local Part' ) )
            ->setAttrib( 'class', 'required span2' )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', false, array( 1, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StringToLower' )
            ->addFilter( 'StripSlashes' );

        $domain = $this->createElement( 'select', 'domain' )
            ->setOptions( array( 'multiOptions' => array( '' => '- select -' ) + $domainList ) ) // array('' => _( '- select -' ) ) + $domainList
            ->setLabel( _( 'Domain' ) )
            ->setAttrib( 'title', _( 'Domain' ) )
            ->setRequired( true )
            ->setAttrib( 'class', 'required add-on-input' )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', false, array( 1, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' )
            ->addValidator( 'InArray', true, array( array_keys( $domainList ) ) );

        $domain->getValidator( 'InArray' )->setMessage( _( 'You must select a domain.' ), Zend_Validate_InArray::NOT_IN_ARRAY);

        $name = $this->createElement( 'text', 'name' )
            ->setAttrib( 'size', 40 )
            ->setLabel( _( 'Name' ) )
            ->setAttrib( 'title', _( 'Name' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' );

        $password = $this->createElement( 'text', 'password' )
            ->setLabel( _( 'Password' ) )
            ->setAttrib( 'title', _( 'Password' ) )
            ->setAttrib( 'size', 40 )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', true, array( $minPass, 32 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' );

        $active = $this->createElement( 'checkbox', 'active' )
            ->setLabel( _( 'Active' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );

        $quota = $this->createElement( 'text', 'quota' )
            ->setLabel( _( 'Quota' ) )
            ->setAttrib( 'title', _( 'Quota' ) )
            ->setAttrib( 'size', 5 )
            ->setRequired( false )
            ->addFilter( 'Digits' );

        $welcomeEmail = $this->createElement( 'checkbox', 'welcome_email' )
            ->setAttrib( 'onclick', "$('#cc_welcome_email_tr').toggle('fast');" )
            ->setLabel( _( 'Welcome email' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );

        $ccWelcomeEmail = $this->createElement( 'text', 'cc_welcome_email' )
            ->setAttrib( 'size', 40 )
            ->setLabel( _( 'CC welcome email' ) )
            ->setAttrib( 'title', _( 'CC welcome email' ) )
            //->setAttrib( 'autocomplete', 'off' )
            ->setRequired( false )
            ->addValidator( 'EmailAddress', true, array( 'mx' => true ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' );

        $access_restr = $this->createElement( 'checkbox', 'access_restr' )
            ->setLabel( _( 'Access Restriction' ) )
            ->addValidator( 'InArray', false, array( array( 0, 1 ) ) )
            ->addFilter( 'Digits' );

        $access_restriction = $this->createElement( 'select', 'access_restriction' )
            ->setOptions( array( 'multiOptions' => Mailbox::$MAILBOX_ACCESS_RESTR_TEXT ) ) // array('' => _( '- select -' ) ) + $domainList
            ->setRequired( true )
            ->setAttrib( 'class', 'required span2' )
            ->addValidator( 'NotEmpty', true )
            ->setValue( Mailbox::ACCESS_RESTR_IMAP )
            ->addValidator( 'InArray', true, array( array_keys( Mailbox::$MAILBOX_ACCESS_RESTR_TEXT ) ) );

        $domain->getValidator( 'InArray' )->setMessage( _( 'You must select a access restriction.' ), Zend_Validate_InArray::NOT_IN_ARRAY);

        $submit = $this->createElement( 'submit' , 'save' )
            ->setLabel( _( 'Save' ) );

        $this->addElement( $localPart )
            ->addElement( $domain )
            ->addElement( $name )
            ->addElement( $password )
            ->addElement( $active )
            ->addElement( $quota )
            ->addElement( $welcomeEmail )
            ->addElement( $ccWelcomeEmail )
            ->addElement( $access_restr )
            ->addElement( $access_restriction )
            ->addElement( $submit );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }

}
