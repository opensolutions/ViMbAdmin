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
class ViMbAdmin_Form_Mailbox_DirectoryEntry extends ViMbAdmin_Form_Plugin
{    
    public function init()
    {
        $this->setDecorators( [ [ 'ViewScript', [ 'viewScript' => 'mailbox/form/directory-entry.phtml' ] ] ] );
        $this->setAttrib( 'id', 'directory_entry' )
            ->setAttrib( 'title', 'Directory Entry' );

        $businesCategory = $this->createElement( 'text', 'plugin_directoryEntry_BusinessCategory' )
            ->setLabel( _( 'Business Category' ) )
            ->setAttrib( 'title', _( 'Busines Category' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $carLicense = $this->createElement( 'text', 'plugin_directoryEntry_CarLicense' )
            ->setLabel( _( 'Car License' ) )
            ->setAttrib( 'title', _( 'Car License' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $departmentNumber = $this->createElement( 'text', 'plugin_directoryEntry_DepartmentNumber' )
            ->setLabel( _( 'Department Number' ) )
            ->setAttrib( 'title', _( 'Department Number' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $displayName = $this->createElement( 'text', 'plugin_directoryEntry_DisplayName' )
            ->setLabel( _( 'Display Name' ) )
            ->setAttrib( 'title', _( 'Display Name' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $employeeNumber = $this->createElement( 'text', 'plugin_directoryEntry_EmployeeNumber' )
            ->setLabel( _( 'Employee Number' ) )
            ->setAttrib( 'title', _( 'Employee Number' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $employeeType = $this->createElement( 'text', 'plugin_directoryEntry_EmployeeType' )
            ->setLabel( _( 'Employee Type' ) )
            ->setAttrib( 'title', _( 'Employee Type' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $homePhone = $this->createElement( 'text', 'plugin_directoryEntry_HomePhone' )
            ->setLabel( _( 'Home Phone' ) )
            ->setAttrib( 'title', _( 'Home Phone' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $homePostalAddress = $this->createElement( 'textarea', 'plugin_directoryEntry_HomePostalAddress' )
            ->setLabel( _( 'Home Address' ) )
            ->setAttrib( 'title', _( 'Home Address' ) )
            ->setAttrib( 'rows', 3 )
            ->setRequired( false )
            ->addValidator( 'StringLength', true, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $initials = $this->createElement( 'text', 'plugin_directoryEntry_Initials' )
            ->setLabel( _( 'Initials' ) )
            ->setAttrib( 'title', _( 'Initials' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 10) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $jpegPhoto = $this->createElement( 'text', 'plugin_directoryEntry_JpegPhoto' )
            ->setLabel( _( '?JPEG Photo?' ) )
            ->setAttrib( 'title', _( '?JPEG Photo?' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $labeledUri = $this->createElement( 'textarea', 'plugin_directoryEntry_LabeledURI' )
            ->setLabel( _( 'Website' ) )
            ->setAttrib( 'title', _( 'Website' ) )
            ->setAttrib( 'rows', 2 )
            ->setRequired( false )
            ->addValidator( 'StringLength', true, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $manager = $this->createElement( 'text', 'plugin_directoryEntry_Manager' )
            ->setLabel( _( 'Manager' ) )
            ->setAttrib( 'title', _( 'Manager' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $mail = $this->createElement( 'text', 'plugin_directoryEntry_Mail' )
            ->setLabel( _( 'Mail' ) )
            ->setAttrib( 'title', _( 'Mail' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $mobile = $this->createElement( 'text', 'plugin_directoryEntry_Mobile' )
            ->setLabel( _( 'Mobile' ) )
            ->setAttrib( 'title', _( 'Mobile' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $o = $this->createElement( 'text', 'plugin_directoryEntry_O' )
            ->setLabel( _( 'Organization' ) )
            ->setAttrib( 'title', _( 'Organization' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $pager = $this->createElement( 'text', 'plugin_directoryEntry_Pager' )
            ->setLabel( _( 'Pager' ) )
            ->setAttrib( 'title', _( 'Pager' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $preferredLanguage = $this->createElement( 'text', 'plugin_directoryEntry_PreferredLanguage' )
            ->setLabel( _( '?Preferred Language?' ) )
            ->setAttrib( 'title', _( '?Preffered Language?' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $roomNumber = $this->createElement( 'text', 'plugin_directoryEntry_RoomNumber' )
            ->setLabel( _( 'Room Number' ) )
            ->setAttrib( 'title', _( 'Room Number' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $secretary = $this->createElement( 'text', 'plugin_directoryEntry_Secretary' )
            ->setLabel( _( 'Secretary' ) )
            ->setAttrib( 'title', _( 'Secretary' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $personalTitle = $this->createElement( 'text', 'plugin_directoryEntry_PersonalTitle' )
            ->setLabel( _( 'Personal Title' ) )
            ->setAttrib( 'title', _( 'Personal Title' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $sn = $this->createElement( 'text', 'plugin_directoryEntry_Sn' )
            ->setLabel( _( 'Last Name' ) )
            ->setAttrib( 'title', _( 'Last Name' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $ou = $this->createElement( 'text', 'plugin_directoryEntry_Ou' )
            ->setLabel( _( 'Department Name' ) )
            ->setAttrib( 'title', _( 'Department Name' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $title = $this->createElement( 'text', 'plugin_directoryEntry_Title' )
            ->setLabel( _( 'Work Title' ) )
            ->setAttrib( 'title', _( 'Work Title' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $facsimileTelephoneNumber = $this->createElement( 'text', 'plugin_directoryEntry_FacsimileTelephoneNumber' )
            ->setLabel( _( 'Fax Number' ) )
            ->setAttrib( 'title', _( 'Fax Number' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $givenName = $this->createElement( 'text', 'plugin_directoryEntry_GivenName' )
            ->setLabel( _( 'First Name' ) )
            ->setAttrib( 'title', _( 'First Name' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $telephoneNumber = $this->createElement( 'text', 'plugin_directoryEntry_TelephoneNumber' )
            ->setLabel( _( 'Telephone Number' ) )
            ->setAttrib( 'title', _( 'Telephone Number' ) )
            ->setRequired( false )
            ->addValidator( 'StringLength', false, array( 0, 255 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );

        $this->addElement( $personalTitle           )
            ->addElement( $givenName                )
            ->addElement( $sn                       )
            ->addElement( $displayName              )
            ->addElement( $initials                 )
            ->addElement( $businesCategory          )
            ->addElement( $employeeType             )
            ->addElement( $title                    )
            ->addElement( $departmentNumber         )
            ->addElement( $ou                       )
            ->addElement( $roomNumber               )
            ->addElement( $o                        )
            ->addElement( $carLicense               )
            ->addElement( $employeeNumber           )
            ->addElement( $mail                     )
            ->addElement( $homePhone                )
            ->addElement( $telephoneNumber          )
            ->addElement( $mobile                   )
            ->addElement( $pager                    )
            ->addElement( $facsimileTelephoneNumber )
            ->addElement( $homePostalAddress        )
            ->addElement( $jpegPhoto                )
            ->addElement( $labeledUri               )
            ->addElement( $manager                  )
            ->addElement( $secretary                )
            ->addElement( $preferredLanguage        );
    }


    /**
     * Prepares the directory entry subform.
     *
     * First it disables fields from disable array, and then it sets values from
     * $dentry if it was given.
     *
     * $disable array sample: [ 'Initials' => true, 'Secretary'=>true, 'HomePhone' => true ];
     * 
     * @param array                         $disable Array with field names to disable.
     * @param bool|\Entities\DirectoryEntry $dentry  Directory entry object to set the form
     * @return void
     */
    public function prepare( $disable, $dentry = false )
    {
        foreach( $disable as $name => $value )
        {
            if( $value )
            {
                if( !in_array( $name, [ 'DisplayName', 'Initials' ] ) )
                    $this->removeElement( "plugin_directoryEntry_" . $name );
                else
                    $this->getElement( "plugin_directoryEntry_" . $name )->setAttrib( 'class', 'hidden' );
            }
            
        }
        if( $dentry )
        {
            foreach( $this->getElements() as $name => $element )
            {
                $name = substr( $name, 22 );
                $getFn = "get" . $name;
                $element->setValue( $dentry->$getFn() );
            }
        }
    }

    /**
     * Updates directory entry object from form.
     *
     * @param bool|\Entities\DirectoryEntry $dentry  Directory entry object to set the form
     * @return void
     */
    public function formToEntity( $dentry )
    {
        foreach( $this->getElements() as $name => $element )
        {
            $name = substr( $name, 22 );
            $setFn = "set" . $name;
            $dentry->$setFn( $element->getValue() );
        }
    }
}
