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
class OSS_Validate_OSSNumericBetween extends Zend_Validate_Abstract
{

    const MSG_NUMERIC = 'msgNumeric';
    const MSG_MINIMUM = 'msgMinimum';
    const MSG_MAXIMUM = 'msgMaximum';

    /**
     * Minimum value
     * @var int
     */
    public $minimum = 0;
    
    /**
     * Minimum value
     * @var int
     */
    public $maximum = 100;

    /**
     * Error message variables
     * @var array
     */
    protected $_messageVariables = array(
        'min' => 'minimum',
        'max' => 'maximum'
    );

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::MSG_NUMERIC => "'%value%' is not integer",
        self::MSG_MINIMUM => "'%value%' must be at least '%min%'",
        self::MSG_MAXIMUM => "'%value%' must be no more than '%max%'"
    );

    /**
     * Constructor
     *
     * $table and $column are required parameters.
     *
     * @param string $entity The database entity to use
     * @param string $property The entity property to check for uniqueness
     * @throws OSS_Validate_Exception
     * @return void
     */
    public function __construct( $params )
    {
        if( is_array( $params ) )
        {
            if( isset( $params['min'] ) )
                $this->minimum = $params['min'];

            if( isset( $params['max'] ) )
                $this->maximum = $params['max'];
        }
    }

    /**
     * Returns true if and only if $value is integer and between $minimum and $maximum
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $this->_setValue( $value );

        if( !is_int( (int)$value ) )
        {
            $this->_error( self::MSG_NUMERIC );
            return false;
        }

        if( $value < $this->minimum )
        {
            $this->_error( self::MSG_MINIMUM );
            return false;
        }

        if( $value > $this->maximum )
        {
            $this->_error( self::MSG_MAXIMUM );
            return false;
        }

        return true;
    }
}
