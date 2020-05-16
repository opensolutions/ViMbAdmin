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

class OSS_Validate_OSSDateValidate extends Zend_Validate_Abstract
{

    const INVALID_DATE = 'invalidDate';

    /**
     * Possible error messages
     *
     * @var array
     */
    protected $_messages = array(
        self::INVALID_DATE => 'Invalid date.'
    );

    /**
     * Ignore empty flag
     *
     * @var bool
     */
    protected $_ignore_empty = false;

    /**
     * Date format
     *
     * @var string
     */
    protected $_dateFormat = OSS_Date::DF_EUROPEAN;

    /**
     * Constructor
     *
     * Sets validator options
     *
     * @param  array $options
     * @return void
     */
    public function __construct( $options = array() )
    {
        if( !array_key_exists( 'ignore_empty', $options ) )
        {
            $options['ignore_empty'] = false;
        }

        $this->setIgnoreEmpty( $options['ignore_empty'] );

        if( isset( $options['dateformat'] ) )
            $this->_dateFormat = $options['dateformat'];
    }

    /**
     * Set ingore empty flag
     *
     * @param bool $b Flag value
     * @return void
     */
    public function setIgnoreEmpty( $b = true )
    {
        $this->_ignore_empty = $b;
    }

    /**
     * Get ingore empty flag
     *
     * @return bool
     */
    public function getIgnoreEmpty()
    {
        return (bool)$this->_ignore_empty;
    }

    /**
     * Set the format of the date to validate against
     *
     * @see OSS_Date::$DATE_FORMATS
     * @param $f int The format
     */
    public function setDateFormat( $f )
    {
        $this->_dateFormat = $f;
    }

    /**
     * It will if check if given date is European
     *
     * @param strig $value Date string
     * @param null|mixed $context
     * @return bool
     */
    public function isValid( $value, $context = null )
    {

        if( ( trim( $value ) == "" || trim( $value ) == "--" ) && $this->getIgnoreEmpty() )
            return true;

        $ts = OSS_Date::getTimestamp( $value, $this->_dateFormat );

        if( $ts === false )
        {
            $this->_error( self::INVALID_DATE );
            return false;
        }

        $dparts = OSS_Date::dateSplit( $value, $this->_dateFormat );

        $value = sprintf( "%02d/%02d/%d", $dparts[0], $dparts[1], $dparts[2] );

        if( $value != date( "d/m/Y", mktime( 0, 0, 0, $dparts[1], $dparts[0], $dparts[2] ) ) )
        {
            $this->_error( self::INVALID_DATE );
            return false;
        }

        return true;
    }

}
