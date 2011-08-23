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

/*
 * Validator to match two given fields (e.g. password and confirm password).
 *
 * @package ViMbAdmin
 * @subpackage Validator
 */
class ViMbAdmin_Validate_IdenticalField extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const NO_FIELD_NAME = 'noFieldName';
    const FIELD_DOESNT_EXIST = 'fieldDoesntExist';

    /**
    * @var array
    */
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'This field does not match %fieldTitle%.',
        self::NO_FIELD_NAME => 'No field name to match against provided',
        self::FIELD_DOESNT_EXIST => 'The given field name does not exist in the context'
    );

    /**
    * @var array
    */
    protected $_messageVariables = array(
                                            'fieldName' => '_fieldName',
                                            'fieldTitle' => '_fieldTitle'
                                    );

    /**
    * Name of the field as it appear in the $context array.
    *
    * @var string
    */
    protected $_fieldName;

    /**
    * Title of the field to display in an error message.
    *
    * If evaluates to null then will be set to $this->_fieldName.
    *
    * @var string
    */
    protected $_fieldTitle;


    /**
    * Validator constructor.
    *
    * $fieldName is required and defines the element name to match the value
    * against in the current form / subform context.
    *
    * @param  string $fieldName The form element name to match the value against
    * @param  string $fieldTitle Added to the error message. E.g. 'the password' produces: This field does not match the password"
    * @return void
    */
    public function __construct( $pParams )
    {
        $this->setFieldName( $pParams['fieldName'] );
        $this->setFieldTitle( isset( $pParams['fieldTitle'] ) ? $pParams['fieldTitle'] : $pParams['fieldName'] );
    }


    /**
    * Returns the field name.
    *
    * @return string
    */
    public function getFieldName()
    {
        return $this->_fieldName;
    }


    /**
    * Sets the field name.
    *
    * @param  string $fieldName
    * @return Zend_Validate_IdenticalField Provides a fluent interface
    */
    public function setFieldName( $fieldName )
    {
        $this->_fieldName = $fieldName;
        return $this;
    }


    /**
    * Returns the field title.
    *
    * @return integer
    */
    public function getFieldTitle()
    {
        return $this->_fieldTitle;
    }


    /**
    * Sets the field title.
    *
    * @param  string:null $fieldTitle
    * @return Zend_Validate_IdenticalField Provides a fluent interface
    */
    public function setFieldTitle( $fieldTitle = null )
    {
        $this->_fieldTitle = ( $fieldTitle ? $fieldTitle : $this->_fieldName );

        return $this;
    }


    /**
    * Checks to see if the given $value matches the value of the given $fieldName in $context.
    *
    * Returns true if and only if a field name has been set, the field name is available in the
    * context, and the value of that field name matches the provided value.
    *
    * @param  string $value
    * @param  array $context
    * @return boolean
    * @throws ViMbAdmin_Validate_Exception
    */
    public function isValid( $value, $context = null )
    {
        $this->_setValue( $value );

        $field = $this->getFieldName();

        if ( empty( $field ) )
        {
            $this->_error( self::NO_FIELD_NAME );
            return false;
        }

        if ( !isset( $context[ $field ] ) )
        {
            $this->_error( self::FIELD_DOESNT_EXIST );
            return false;
        }

        if ( $value != $context[ $field ] )
        {
            $this->_error( self::NOT_MATCH );
            return false;
        }

        return true;
    }

}
