<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * All rights reserved.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 *
 * Contact: Barry O'Donovan - info (at) opensolutions (dot) ie
 *          http://www.opensolutions.ie/
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 *     http://www.opensolutions.ie/licenses/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@opensolutions.ie so we can send you a copy immediately.
 *
 * @category   OSS
 * @package    OSS_Validate
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Validate
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Validate_OSSIdenticalField extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const NO_FIELD_NAME = 'noFieldName';
    const FIELD_DOESNT_EXIST = 'fieldDoesntExist';

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'This field does not match %fieldTitle%.',
        self::NO_FIELD_NAME => 'No field name to match against provided',
        self::FIELD_DOESNT_EXIST => 'The given field name does not exist in the context'
    );

    /**
     * Error message variables
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
     * Constructor
     *
     * $fieldName is required and defines the element name to match the value
     * against in the current form / subform context.
     *
     * @param  array  $params Array of params containing fieldName and fieldTitle fields
     * @return void
     */
    public function __construct( $params )
    {
        $this->setFieldName( $params['fieldName'] );
        $this->setFieldTitle( 
            isset( $params['fieldName'] ) ? $params['fieldName'] : $params['fieldTitle'] 
        );
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
     * @return OSS_Validate_OSSIdenticalField
     */
    public function setFieldName( $fieldName )
    {
        $this->_fieldName = $fieldName;
        return $this;
    }


    /**
     * Returns the field title.
     *
     * @return int
     */
    public function getFieldTitle()
    {
        return $this->_fieldTitle;
    }


    /**
     * Sets the field title.
     *
     * @param  string:null $fieldTitle
     * @return OSS_Validate_OSSIdenticalField
     */
    public function setFieldTitle($fieldTitle = null)
    {
        $this->_fieldTitle = $fieldTitle ? $fieldTitle : $this->_fieldName;

        return $this;
    }


    /**
     * Checks to see if the given $value matches the value of the given $fieldName in $context.
     *
     * Returns true if and only if a field name has been set, the field name is available in the
     * context, and the value of that field name matches the provided value.
     *
     * @param  string $value
     * @param  null|array $context
     * @throws OSS_Validate_Exception
     * @return bool
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

        if ( !isset( $context[$field] ) )
        {
            $this->_error( self::FIELD_DOESNT_EXIST );
            return false;
        }

        if ( $value != $context[$field] )
        {
            $this->_error( self::NOT_MATCH );
            return false;
        }

        return true;
    }

}
