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
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   OSS
 * @package    OSS_Validate
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Validate_OSSNotRegex extends Zend_Validate_Abstract
{
    const INVALID   = 'regexInvalid';
    const MATCH     = 'regexMatch';

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::MATCH => "'%value%' matches against pattern '%pattern%'"
    );

    /**
     * Error message variables
     * @var array
     */
    protected $_messageVariables = array(
        'pattern' => '_pattern'
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern;

    /**
     * Constructor
     *
     * Sets validator options
     *
     * @param  string $pattern
     * @return void
     */
    public function __construct( $pattern )
    {
        $this->setPattern( $pattern );
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @return OSS_Validate_OSSNotRegex
     */
    public function setPattern( $pattern )
    {
        $this->_pattern = (string) $pattern;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @throws Zend_Validate_Exception if there is a fatal error in pattern matching
     * @return bool
     */
    public function isValid( $value ) 
    {
        if( !is_string( $value ) && !is_int( $value ) && !is_float( $value ) )
        {
            $this->_error( self::INVALID );
            return false;
        }

        $this->_setValue( $value );

        $status = @preg_match( $this->_pattern, $value );

        if( $status === false )
        {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception( "Internal error matching pattern '$this->_pattern' against value '$value'" );
        }

        if( $status )
        {
            $this->_error( self::MATCH );
            return false;
        }

        return true;
    }

}
