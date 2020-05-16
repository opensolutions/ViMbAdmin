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
 * @package    OSS_Date
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Date
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Date
{
    const DF_EUROPEAN = 1;
    const DF_AMERICAN = 2;
    const DF_COMPUTER = 3;
    const DF_REVERSE  = 4;
    const DF_COMPACT  = 5;
    
    
    /**
     * Date formats
     *
     * @var array
     */
    public static $DATE_FORMATS = array(
        self::DF_EUROPEAN => 'DD/MM/YYYY',
        self::DF_AMERICAN => 'MM/DD/YYYY',
        self::DF_COMPUTER => 'YYYY-MM-DD',
        self::DF_REVERSE  => 'YYYY/MM/DD',
        self::DF_COMPACT  => 'YYYYMMDD'
    );
    
    
    /**
     * Date formats for data picker
     *
     * @var array 
     */
    public static $DATEPICKER_FORMATS = array(
        self::DF_EUROPEAN => 'dd/mm/yy',
        self::DF_AMERICAN => 'mm/dd/yy',
        self::DF_COMPUTER => 'yy-mm-dd',
        self::DF_REVERSE  => 'yy/mm/dd',
        self::DF_COMPACT  => 'yymmdd'
    );
    
    /**
     * Date formats for PHP
     *
     * @var array 
     */
    public static $PHP_FORMATS = array(
        self::DF_EUROPEAN => 'd/m/Y',
        self::DF_AMERICAN => 'm/d/Y',
        self::DF_COMPUTER => 'Y-m-d',
        self::DF_REVERSE  => 'Y/m/d',
        self::DF_COMPACT  => 'Ymd'
    );
    
    
    
    /**
     * Returns an associative array of date formats.
     *
     * I.e. returns self::$DATE_FORMATS
     *
     * @return array
     */
    public static function getDateFormats()
    {
        return self::$DATE_FORMATS;
    }
    
    /**
     * Returns array of date format codes.
     *
     * ie. returns the keys of self::$DATE_FORMATS
     *
     * @return array
     */
    public static function getDateFormatKeys()
    {
        return array_keys( self::$DATE_FORMATS );
    }
    
    /**
     * Returns the format for a given format code or fallback to default.
     *
     * @param int $code The format code
     * @param int $default The default value if $code does not exist
     * @return string|bool
     */
    public static function getFormat( $code, $default = self::DF_EUROPEAN )
    {
        if( isset( self::$DATE_FORMATS[$code] ) )
            return self::$DATE_FORMATS[$code];
        else
            return self::$DATE_FORMATS[$default];
    }
    
    /**
     * Returns the JQuery date picker format for a given format code or fallback to default.
     *
     * @param int $code The format code
     * @param int $default The default value if $code does not exist
     * @return string|bool
     */
    public static function getDatepickerFormat( $code, $default = self::DF_EUROPEAN )
    {
        if( isset( self::$DATEPICKER_FORMATS[$code] ) )
            return self::$DATEPICKER_FORMATS[$code];
        else
            return self::$DATEPICKER_FORMATS[$default];
    }
    
    /**
     * Returns the PHP date() format for a given format code or fallback to default.
     *
     * @param int $code The format code
     * @param int $default The default value if $code does not exist
     * @return string|bool
     */
    public static function getPhpFormat( $code, $default = self::DF_EUROPEAN )
    {
        if( isset( self::$PHP_FORMATS[$code] ) )
            return self::$PHP_FORMATS[$code];
        else
            return self::$PHP_FORMATS[$default];
    }
    
    /**
     * Parse a string in a given format to a UNIX timestamp
     *
     * @param string $string
     * @param int $format
     * @return int
     */
    public static function getTimestamp( $string, $format = self::DF_EUROPEAN )
    {
        // strtotime will parse all our (current) formats except European and compact
        if( $format == self::DF_EUROPEAN )
            $string = str_replace( '/', '.', $string );
        else if( $format == self::DF_COMPACT )
            $string = substr( $string, 0, 4 ) . '-' . substr( $string, 4, 2 ) . '-' . substr( $string, 6, 2 );
        
        return strtotime( $string );
    }
    
    /**
     * Parse a date in a given format to an array of (day, month, year)
     *
     * @param string $string
     * @param int $format
     * @return array
     */
    public static function dateSplit( $string, $format = self::DF_EUROPEAN )
    {
        $dparts = array();
    
        // case values correspond to OSS_Date DF_* constants
        switch( $format )
        {
            case self::DF_AMERICAN: // mm/dd/yyyy
                $t = explode( '/', $string );
                $dparts[0] = $t[1];
                $dparts[1] = $t[0];
                $dparts[2] = $t[2];
                break;
                	
            case self::DF_COMPUTER: // YYYY-MM-DD
                $t = explode( '-', $string );
                $dparts[0] = $t[2];
                $dparts[1] = $t[1];
                $dparts[2] = $t[0];
                break;
                	
            case self::DF_REVERSE: // yyyy/mm/dd
                $t = explode( '/', $string );
                $dparts[0] = $t[2];
                $dparts[1] = $t[1];
                $dparts[2] = $t[0];
                break;
                
            case self::DF_COMPACT: // yyyymmdd
                    $dparts[0] = substr( $string, 6, 2 );
                    $dparts[1] = substr( $string, 4, 2 );
                    $dparts[2] = substr( $string, 0, 4 );
                    break;
                
            case self::DF_EUROPEAN: // dd/mm/yyyy
            default:
                $t = explode( '/', $string );
                $dparts[0] = $t[0];
                $dparts[1] = $t[1];
                $dparts[2] = $t[2];
                break;
        }
    
        return $dparts;
    }
    
    
}
