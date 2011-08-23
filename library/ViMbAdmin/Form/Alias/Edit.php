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
 * The form for adding and editing aliases.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Alias_Edit extends ViMbAdmin_Form
{

    public function __construct( $options = null, $domainList )
    {
        parent::__construct( $options );

        $this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'alias/form/edit.phtml' ) ) ) );

        $this
            ->setMethod( 'post' )
            ->setAttrib( 'id', 'alias_edit_form' )
            ->setAttrib( 'name', 'alias_edit_form' );

        $localPart = $this
                        ->createElement( 'text', 'local_part' )
                        ->setAttrib( 'size', 40 )
                        ->setLabel( _( 'Local Part' ) )
                        ->setAttrib( 'title', _( 'Local Part' ) )
                        ->setRequired( false )
                        ->addValidator( 'StringLength', false, array( 0, 255 ) )
                        ->addFilter( 'StringTrim' )
                        ->addFilter( 'HtmlEntitiesDecode' )
                        ->addFilter( 'StringToLower' )
                        ->addFilter( 'StripSlashes' );

        $domain = $this
                        ->createElement( 'select', 'domain' )
                        ->setOptions( array( 'multiOptions' => array( '' => '- select -' ) + $domainList ) ) // array('' => _( '- select -' ) ) + $domainList
                        ->setLabel( _( 'Domain' ) )
                        ->setAttrib( 'title', _( 'Domain' ) )
                        ->setRequired( true )
                        ->setAttrib( 'class', 'required' )
                        ->addFilter( 'Digits' )
                        ->addValidator( 'InArray', true, array( array_keys( $domainList ) ) );

        $domain->getValidator( 'InArray' )->setMessage( _( 'You must select a domain.' ), Zend_Validate_InArray::NOT_IN_ARRAY);

        $active = $this
                        ->createElement( 'checkbox', 'active' )
                        ->setLabel( _( 'Active' ) )
                        ->addValidator('InArray', false, array( array( 0, 1 ) ) )
                        ->setValue( 1 )
                        ->addFilter( 'Digits' );

        $goto = $this
                    ->createElement( 'text', 'goto' )
                    ->setLabel( _( 'Goto' ) )
                    ->setRequired( false );

        $submit = $this
                        ->createElement( 'submit' , 'save' )
                        ->setLabel( _( 'Save' ) );

        $this
            ->addElement( $localPart )
            ->addElement( $domain )
            ->addElement( $active )
            ->addElement( $goto )
            ->addElement( $submit );

        $this->setElementDecorators( array( 'ViewHelper' ) );
    }

}
