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
 * @package    OSS_String
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * Class for string actions
 *
 * @category   OSS
 * @package    OSS_String
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_String
{

    /**
     * The Unicode version of ucfirst().
     *
     * @param string $string the input string
     * @param string $encoding default null the character encoding, if omitted the the PHP internal encoding is used
     * @return string
     */
    public static function mb_ucfirst( $string, $encoding = null )
    {
        if( function_exists( 'mb_strtoupper' ) && !empty( $string ) )
        {
            if ($encoding === null)
                $encoding = mb_internal_encoding();

            return mb_strtoupper( mb_substr( $string, 0, 1, $encoding ) ) . mb_substr( $string, 1, mb_strlen( $string, $encoding ) );
        }
        else
        {
            return ucfirst( $string );
        }
    }


    /**
     * The Unicode version of ucwords().
     *
     * @param string $string the input string
     * @param string $encoding default null the character encoding, if omitted the the PHP internal encoding is used
     * @return string
     */
    public static function mb_ucwords( $string, $encoding = null )
    {
        if( $encoding === null )
            $encoding = mb_internal_encoding();

        return mb_convert_case( $string, MB_CASE_TITLE, $encoding );
    }


    /**
     * The Unicode version of str_replace().
     *
     * @param string $needle      The string portion to replace in the haystack
     * @param string $replacement The replacement for the string portion
     * @param string $haystack    The haystack
     * @return string
     */
    public static function mb_str_replace( $needle, $replacement, $haystack )
    {
        $needle_len      = mb_strlen( $needle );
        $replacement_len = mb_strlen( $replacement );
        $pos             = mb_strpos( $haystack, $needle );

        while( $pos !== false )
        {
            $haystack = mb_substr( $haystack, 0, $pos ) . $replacement . mb_substr( $haystack, $pos + $needle_len );
            $pos = mb_strpos( $haystack, $needle, $pos + $replacement_len );
        }

        return $haystack;
    }


    /**
     * Generates a random string.
     *
     * @param int     $length     The length of the random string we want to generate. Default: 16
     * @param bool    $lowerCase  If true then lowercase characters will be used. Default: true
     * @param bool    $upperCase  If true then uppercase characters will be used. Default: true
     * @param bool    $numbers    If true then numbers will be used. Default: true
     * @param string  $additional These characters also will be used. Default: ''
     * @param string  $exclude    These characters will be excluded. Default: '1iIl0O'
     * @return string 
     */
    public static function random( $length=16, $lowerCase = true, $upperCase = true, $numbers = true, $additional = '', $exclude = '1iIl0O' )
    {
        $str = '';

        if( $length == 0 )
            return '';

        if( $lowerCase == true )
            $str .= 'abcdefghijklmnopqrstuvwxyz';

        if( $upperCase == true )
            $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if( $numbers == true )
            $str .= '0123456789';

        $str .= $additional;

        if( $exclude != '' )
        {
            foreach( str_split( $exclude ) as $char )
            {
                $str = OSS_String::mb_str_replace( $char, '', $str );
            }
        }

        $repeat = ceil( ( 1 + ( $length / mb_strlen( $str ) ) ) );
        $retVal = substr( str_shuffle( str_repeat( $str, $repeat ) ), 1, $length );

        return $retVal;
    }


    /**
    * Returns with a random string using the characters found in $charSet only.
    *
    * @param string $charSet
    * @param int $length
    * @return string
    */
    public static function randomFromSet( $charSet, $length = 16 )
    {
        $repeat = ceil( ( 1 + ( $length / mb_strlen( $charSet ) ) ) );
        return substr( str_shuffle( str_repeat( $charSet, $repeat ) ), 1, $length );
    }


    /**
    * Creates a random password string of a given length. Not the fastest way of generating random passwords, but ensures that it contains
    * both lowercase and uppercase letters and digits, so complies with our password strength "policy".
    *
    * Some letters are excluded from the character set: 1, 0, O, I, l
    *
    * @param int $length The length of the password to be generated.
    * @return string The password string.
    */
    public static function randomPassword( $length = 8 )
    {
        $chars = "23456789abcdefghijkmnopqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

        while( true )
        {
            $password = substr( str_shuffle( $chars ), 0, $length );

            // "/[a-zA-Z0-9]/" is NOT the same!
            if( preg_match( "/[a-z]/", $password ) && preg_match( "/[A-Z]/", $password ) && preg_match( "/[0-9]/", $password ) )
                return $password;
        }
    }


    /**
    * Takes a string, converts it to lowercase and creates a valid file name from it by replacing any
    * character by an underscore which is not [0-9a-z] (so only the standard neolatin (english) alphabet is
    * supported), then replaces any consecutive underscores with only one underscore. Leading and trailing
    * underscores are also removed.
    *
    * @param string $string
    * @return string
    */
    public static function toValidFieldName( $string )
    {
        $string = mb_strtolower( trim( $string ) );
        $string = preg_replace( "/[^0-9a-z]+/u", '_', $string );
        $string = preg_replace ("/[_]+/u", '_', $string );

        if( $string[0] == '_' )
            $string = mb_substr( $string, 1 );

        if( mb_substr( $string, -1 ) == '_')
            $string = mb_substr( $string, 0, -1 );

        return 'cf_' . $string;
    }


    /**
    * Removes any diacritic, accent and combining characters from the string.
    *
    * These settings should be set for working results:
    * mb_language('uni');
    * mb_internal_encoding('UTF-8');
    * setlocale(LC_ALL, "en_IE.utf8"); //or any other locale, as long as it's utf8
    *
    * @param string $input the original input string
    * @param bool $keepSpaces By default is false and it will remove spaces from string. Then it is set true it will keep spaces in string.
    * @return string
    */
    public static function normalise( $input, $keepSpaces = false )
    {
        iconv_set_encoding( 'internal_encoding', 'utf-8' );
        iconv_set_encoding( 'input_encoding', 'utf-8' );
        iconv_set_encoding( 'output_encoding', 'utf-8' );

        /**
        * Special cases
        * AE
        * ae
        * U+00F0  ð   c3 b0   LATIN SMALL LETTER ETH
        * U+00D8  Ø   c3 98   LATIN CAPITAL LETTER O WITH STROKE
        * U+00F8  ø   c3 b8   LATIN SMALL LETTER O WITH STROKE
        * 00DF  ß  Latin Small Letter Sharp S (German)
        * 00DE  Þ  Latin Capital Letter Thorn (Icelandic)
        * 00FE  þ latin small letter thorn
        * Ł, ł, đ, Đ, €
        * @see http://www.utf8-chartable.de/
        */

        $from = array( "\xC3\x86", "\xC3\xA6", "\xC3\xB0", "\xC3\x98", "\xC3\xB8", "\xC3\x9F", "\xC3\x9E", "\xC3\xBE", "\xC5\x81", "\xC5\x82", "\xC4\x91", "\xC4\x90", "\xE2\x82\xAC" );
        $to = array(   'AE',       'ae',       'd',        'O',        'o',        'ss',       'Th',       'th',       'L',        'l',        "d",        "D",        "EUR" );

        $retVal = iconv( 'UTF-8', 'ASCII//TRANSLIT', str_replace( $from, $to, $input ) ); // TRANSLIT does the whole job
        if( !$keepSpaces )
            $retVal = preg_replace( "/[^a-z]/", '', mb_strtolower( $retVal ) );
        else
            $retVal = preg_replace( "/[^a-z\s]/", '', mb_strtolower( $retVal ) );

        return $retVal;
    }

    /**
     * Generates a random salt.
     *
     * @param int $len Length of return salt.
     * @return string
     */
    public static function salt( $len )
    {
        return self::random( $len, true, true, true, '!$%^&*()_+-=[]{};#:@~\\|<>?,./', '' );
    }

    /**
     * Generates a random MAC address.
     *
     * @param bool $upperCase default false
     * @return string
     */
    public static function randomMacAddress( $upperCase = false )
    {
        $retArr = array();

        for( $x = 1; $x <= 6; $x++ )
            $retArr[] = OSS_String::random( 2, false, false, false, '0123456789abcdef', '' );

        $retVal = implode( ':', $retArr );

        return ( $upperCase ? strtoupper( $retVal ) : $retVal );
    }


    /**
     * If the parameter is an array, then runs stripslashes() on every item of it, recursively.
     * Otherwise treats the input as a string, and runs stripslashes() on it. If the input is an
     * object, then the return value will be an array of the public object properties.
     * The return value is either a string or an array.
     *
     * @param mixed $input
     * @return array|string
     */
    public static function stripSlashes( $input )
    {
        if( is_string( $input ) )
            return stripslashes( $input );

        if( !is_array( $input ) && !is_object( $input ) )
        {
            $input = (string) $input;
            return stripslashes( $input );
        }

        if( is_object( $input ) )
            $input = (array) $input;    

        foreach( $input as $key => $item )
        {
            if ( is_scalar( $item ) )
                $input[ $key ] = stripslashes( $item );
            else
                $input[ $key ] = self::stripSlashes( $item );
        }

        return $input;
    }


    /**
     * If the parameter is an array, then runs html_entity_decode() on every item of it, recursively.
     * Otherwise treats the input as a string, and runs html_entity_decode() on it. If the input is an
     * object, then the return value will be an array of the public object properties.
     * The return value is either a string or an array.
     *
     * @param mixed $input
     * @return array|string
     */
    public static function htmlEntityDecode( $input )
    {
        if( is_string( $input ) )
            return html_entity_decode( $input );

        if( !is_array( $input ) && !is_object( $input ) )
        {
            $input = (string) $input;
            return html_entity_decode( $input );
        }

        if( is_object( $input ) )
            $input = (array) $input;

        foreach( $input as $key => $item )
        {
            if ( is_scalar( $vItem ) )
                $input[ $key ] = html_entity_decode( $item );
            else
                $input[ $key ] = self::htmlEntityDecode( $item );
        }

        return $input;
    }

}
