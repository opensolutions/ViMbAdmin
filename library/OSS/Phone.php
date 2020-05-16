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
 * @package    OSS_Log
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Log
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Phone
{

    /**
    * Returns true if the passed phone number is a mobile number.
    *
    * @param string $pNumber
    * @param string $pCountryCode default '353'
    * @return boolean
    */
    public static function isMobileNumber( $pNumber, $pCountryCode='353' )
    {
        $pNumber = preg_replace( "/\D/", '', $pNumber );

        if( ( strlen( $pNumber ) == 10 ) && ( substr( $pNumber, 0, 2 ) == '08' ) )
            return true;

        if( ( strlen( $pNumber ) == 12 ) && ( substr( $pNumber, 0, 4 ) == "{$pCountryCode}8" ) )
            return true;

        return false;
    }

    /**
     * Converts a phone number to its national format, stripping the intl. country code
     * from the beginning, and adding the leading local prefix if missing - for example
     * '353' and '0' for Ireland.
     *
     * @param string $pNumber
     * @param string|int $pCountryCode default '353'
     * @param string|int $pLocalPrefix default '0'
     * @return string
     */
    public static function localPhoneNumber( $pNumber, $pCountryCode = '353', $pLocalPrefix = '0' )
    {
        $pNumber = preg_replace( "/\D/", '', $pNumber );

        if( $pNumber == '' )
            return '';

        $pCountryCode = (string) $pCountryCode;
        $pLocalPrefix = (string) $pLocalPrefix;

        if( substr( $pNumber, 0, strlen( $pCountryCode ) ) == $pCountryCode )
            $pNumber = substr( $pNumber, strlen( $pCountryCode ) );

        if( ( $pLocalPrefix != '' ) && ( substr( $pNumber, 0, strlen( $pLocalPrefix ) ) != $pLocalPrefix ) )
            $pNumber = $pLocalPrefix . $pNumber;

        return $pNumber;
    }


    /**
     * Converts a phone number to its international format, adding the intl. country code
     * to the beginning, and removing the leading local prefix if present - for example
     * '353' and '0' for Ireland.
     *
     * @param string $pNumber
     * @param string|int $pCountryCode default '353'
     * @param string|int $pLocalPrefix default '0'
     * @return string
     */
    public static function intlPhoneNumber( $pNumber, $pCountryCode = '353', $pLocalPrefix = '0' )
    {
        $pNumber = preg_replace( "/\D/", '', $pNumber );

        if( $pNumber == '' )
            return '';

        $pCountryCode = (string) $pCountryCode;
        $pLocalPrefix = (string) $pLocalPrefix;

        if( substr( $pNumber, 0, strlen( $pLocalPrefix ) ) == $pLocalPrefix )
            $pNumber = substr( $pNumber, strlen( $pLocalPrefix ) );

        if( substr( $pNumber, 0, strlen( $pCountryCode ) ) != $pCountryCode )
            $pNumber = $pCountryCode . $pNumber;

        return $pNumber;
    }


    /**
     * Converts a phone number to its international format, adding the intl. country code
     * to the beginning, and removing the leading local prefix if present - for example
     * '353' and '0' for Ireland.
     *
     * @param string $pNumber
     * @param bool $pInternational default false
     * @param string|int $pCountryCode default '353'
     * @param string|int $pLocalPrefix default '0'
     * @return string
     */
    public static function formatPhoneNumber( $pNumber, $pInternational = false, $pCountryCode = '353', $pLocalPrefix = '0' )
    {
        if( $pInternational )
        {
            $pNumber = self::intlPhoneNumber( $pNumber, $pCountryCode, $pLocalPrefix );
            return preg_replace( "/({$pCountryCode})(\d{2})(\d{3})(\d{4})/", "+$1 $2 $3 $4", $pNumber );
        }
        else
        {
            $pNumber = self::localPhoneNumber( $pNumber, $pCountryCode, $pLocalPrefix );
            return preg_replace( "/(\d{3})(\d{3})(\d{4})/", "$1 $2 $3", $pNumber );
        }
    }
}
