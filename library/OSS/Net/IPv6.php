<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
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
 * @package    OSS_Net
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Utility methods for IPv6 addresses
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Net
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Net_IPv6
{
    //e.g. 2001:7f8:18:2::147
    const TYPE_SHORT      = "0";

    //e.g. 2001:07f8:0018:0002:0000:0000:0000:0147
    const TYPE_LONG_FULL  = "1";

    //e.g. 2001:7f8:18:2:0:0:0:147
    const TYPE_LONG       = "2";

    //e.g. 2001:07f8:0018:0002::0147
    const TYPE_SHORT_FULL = "3";

    /**
     * Changes format of IPv6 address.
     *
     * @param string $address IPv6 address to change format
     * @param int    $type    Type to convert. One of OSS_Net_Ipv6::TYPE_
     * @return string
     * @throws OSS_Net_Exception Bad IPv6 format
     */
    static function formatAddress( $address, $type = self::TYPE_SHORT )
    {

        // 20170215 - barryo - I didn't write this and it doesn't work properly :-(
        // shortcutting for TYPE_SHORT:
        if( $type == self::TYPE_SHORT ) {
            return( inet_ntop( inet_pton( $address ) ) );
        }
        
        $address = strtolower( $address );
        $parts = explode( ":", $address );
        if( count( $parts ) > 8 || count( $parts ) < 4 )
            throw new OSS_Net_Exception( "Bad IPv6 format" );

        $tmp = $parts;
        $diff = 0;

        foreach( $tmp as $ix => $part)
        {
            if( $part === "" )
            {
                $diff = 8 - count( $parts );
                for( $i = $ix; $i <= $ix + $diff; $i++ )
                    $parts[$i] = '0';
            }
            else
                $parts[ $ix + $diff ] = ltrim( $part, '0' ) != '' ? ltrim( $part, '0' ) : '0';
        }

        $rm = false;
        foreach( $parts as $ix => $part )
        {
            if( ( $part != "" && !ctype_xdigit( $part ) ) || count( $part ) > 4 )
                throw new OSS_Net_Exception( "Bad IPv6 format: {$address}" );

            $part = self::formatPart( $part, $type, $rm );
            $parts[$ix] = $part;

            if( $part === false )
                unset( $parts[$ix] );
            else if ( $part == '' )
                $rm = true;
            else
                $rm = false;
        }
        return implode( ":", $parts );
    }

    /**
     * Converts IPv6 address to ARPA name.
     *
     * E.g. IPv6 address like 2001:7f8:18:2::147 will be converted
     * to 7.4.1.0.0.0.0.0.0.0.0.0.0.0.0.0.2.0.0.0.8.1.0.0.8.f.7.0.1.0.0.2.ip6.arpa,
     *
     * @param string $ip IP address to convert
     * @return string
     */
    public static function ipv6ToARPA( $ip )
    {
        $ip = self::formatAddress( $ip, self::TYPE_LONG_FULL );
        $ip = strrev( str_replace( ':', '', $ip ) );
        return implode( '.', str_split( $ip ) ) . '.ip6.arpa';
    }

    /**
     * Converts ARPA name to IPv6 address.
     *
     * E.g. IPv6 address like 7.4.1.0.0.0.0.0.0.0.0.0.0.0.0.0.2.0.0.0.8.1.0.0.8.f.7.0.1.0.0.2.ip6.arpa
     *  will be converted to 2001:7f8:18:2::147,
     *
     * @param string $ip   IP address to convert
     * @param int    $type Type to convert. One of OSS_Net_Ipv6::TYPE_
     * @return string
     */
    public static function arpaNameToIpv6( $ip, $type = self::TYPE_SHORT )
    {
        $ip = substr( strrev( $ip ), 9 );
        $ip = explode( ".", $ip );
        $tmp = "" ;
        for( $i = 0; $i < count( $ip ) / 4; $i++ )
        {
            $tmp .= $ip[ 0 + $i * 4 ] . $ip[ 1 + $i * 4 ];
            $tmp .= $ip[ 2 + $i * 4 ] . $ip[ 3 + $i * 4 ];
            if( $i + 1 != count( $ip ) / 4 )
                $tmp .= ':';
        }

        return self::formatAddress( $tmp, $type );
    }

    /**
     * Format part of IPv6
     *
     * @param string $part   Part of IPv6 address
     * @param int    $type   Type to convert. One of OSS_Net_Ipv6::TYPE_
     * @param bool   $remove If remove returns false.
     * @return string|bool
     * @throws OSS_Net_Exception Unknown type given
     */
    private static function formatPart( $part, $type, $remove )
    {
        switch( $type ){
            case self::TYPE_SHORT:
                if( $part == '0' )
                {
                    if( !$remove )
                        $ret = '';
                    else
                        $ret = false;
                }
                else
                    $ret = $part;
                break;

            case self::TYPE_LONG_FULL:
                $ret = sprintf( "%04s", $part );
                break;

            case self::TYPE_LONG:
                $ret = $part;
                break;

            case self::TYPE_SHORT_FULL:
                if( $part == '0' )
                {
                    if( !$remove )
                        $ret = '';
                    else
                        $ret = false;
                }
                else
                    $ret = sprintf( "%04s", $part );
                break;

            default:
                throw new OSS_Net_Exception( "Unknown type given." );
        };

        return $ret;
    }

    /**
     * Converts IPv6 address to numerical expresnion.
     *
     * This function is usefull then need to sort IPv6 addresses.
     * Function will takes IPv6 address converts it to long full type then removes ':'
     * and it becomes heximal number. Then function converts it to decimal.
     *
     * e.g. 2a01:8f80:5::9  => 55835678645609170133392336604536766473
     *      2a01:8f80:5::10 => 55835678645609170133392336604536766480
     *
     * @param string $ip IPv6 address to convert to numerical expresnion
     * @return string
     */
    public static function ip2numeric( $ip )
    {
        $ip = self::formatAddress( $ip, self::TYPE_LONG_FULL );
        $hex = str_replace( ":", "", $ip );

        $len = strlen($hex);
        $dec = "";

        for( $i = 1; $i <= $len; $i++ )
            $dec = bcadd( $dec, bcmul( strval( hexdec( $hex[$i - 1] ) ), bcpow( '16', strval( $len - $i ) ) ) );

        return $dec;
    }
}
