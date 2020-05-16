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
 * @package    OSS_Auth
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * A class to hash and verify passwords using verious methods
 *
 * @category   OSS
 * @package    OSS_Auth
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Auth_Password
{
    const HASH_PLAINTEXT    = 'plaintext';
    const HASH_PLAIN        = 'plain';
    const HASH_BCRYPT       = 'bcrypt';
    const HASH_MD5          = 'md5';
    const HASH_MD5_SALTED   = 'md5-salted';
    const HASH_SHA1         = 'sha1';
    const HASH_SHA1_SALTED  = 'sha1-salted';
    const HASH_DOVECOT      = 'dovecot:';
    const HASH_CRYPT        = 'crypt:';
    const HASH_UNKNOWN      = '*unknown*';

    // Bad salts - in April 2016 it was pointed out that a typo in the code below meant that
    // md5.salted and sha1.salted didn't actually use the requested salt string but a fixed
    // salt of "md5.salted" and "sha1.salted" respectivily.
    // These constants are for backwards compatibility for anyone that used those:
    const HASH_MD5_BADSALT  = 'md5.salted';
    const HASH_SHA1_BADSALT = 'sha1.salted';

    /**
     * A generic password hashing method using a given configuration array
     *
     * The parameters expected in `$config` are:
     *
     * * `pwhash`      - a hashing method from the `HASH_` constants in this class
     * * `pwsalt`      - a hashing salt for HASH_SHA1_SALTED and HASH_MD5_SALTED
     * * `hash_cost`   - a *cost* parameter for certain hashing functions - e.g. bcrypt (defaults to 9)
     *
     * @param string $pw The plaintext password to hash
     * @param array $config The resources.auth.oss array from `application.ini`
     * @throws OSS_Exception
     * @return string The hashed password
     */
    public static function hash( $pw, $config )
    {
        $hash = self::HASH_UNKNOWN;

        if( is_array( $config ) )
        {
            if( !isset( $config['pwhash'] ) )
                throw new OSS_Exception( 'Cannot hash password without a hash method' );

            $hash = $config['pwhash'];
        }
        else
            $hash = $config;

        if( substr( $hash, 0, 8 ) == 'dovecot:' )
        {
            return ViMbAdmin_Dovecot::password( substr( $hash, 8 ), $pw, $config['username'] );
        }
        else if ( substr( $hash, 0, 6) == 'crypt:' )
        {
            $indicator = '';
            $salt_len = 2;
            switch( $hash )
            {
                case 'crypt:md5':
                    $salt = '$1$' . OSS_String::randomPassword( 12 ) . '$';
                    break;

                case 'crypt:blowfish':
                    $salt = '$2a$12$' . OSS_String::randomPassword( 22 ) . '$';
                    break;

                case 'crypt:sha256':
                    $salt = '$5$' . OSS_String::randomPassword( 16 ) . '$';
                    break;

                case 'crypt:sha512':
                    $salt = '$6$' . OSS_String::randomPassword( 12 ) . '$';
                    break;

                default:
                    throw new OSS_Exception( 'Unknown crypt password hashing method' );
            }
            return crypt( $pw, $salt );
        }
        else
        {
            switch( $hash )
            {
                case self::HASH_PLAINTEXT:
                case self::HASH_PLAIN:
                    return $pw;
                    break;

                case self::HASH_BCRYPT:
                    if( !isset( $config['hash_cost'] ) )
                        $config['hash_cost'] = 9;

                    $bcrypt = new OSS_Crypt_Bcrypt( $config['hash_cost'] );
                    return $bcrypt->hash( $pw );
                    break;

                case self::HASH_MD5:
                    return md5( $pw );
                    break;

                case self::HASH_MD5_SALTED:
                    return md5( $pw . $config['pwsalt'] );
                    break;

                case self::HASH_SHA1:
                    return sha1( $pw );
                    break;

                case self::HASH_SHA1_SALTED:
                    return sha1( $pw . $config['pwsalt'] );
                    break;



                case self::HASH_MD5_BADSALT:
                    return md5( $pw . $config['pwhash'] );
                    break;

                case self::HASH_SHA1_BADSALT:
                    return sha1( $pw . $config['pwhash'] );
                    break;

                // UPDATE PHPDOC ABOVE WHEN ADDING NEW METHODS!

                default:
                    throw new OSS_Exception( 'Unknown password hashing method' );
            }
        }
    }

    /**
     * A generic password verification function for various hashing methods using a given configuration array
     *
     * @see hash() for full documentation
     *
     * @param string $pwplain The plaintext password
     * @param string $pwhash The hashed password to use for verification
     * @param array $config The resources.auth.oss array from `application.ini`
     * @throws OSS_Exception
     * @return bool True if the passwords match
     */
    public static function verify( $pwplain, $pwhash, $config )
    {
        $hash = self::HASH_UNKNOWN;

        if( is_array( $config ) )
        {
            if( !isset( $config['pwhash'] ) )
                throw new OSS_Exception( 'Cannot verify password without a hash method' );

            $hash = $config['pwhash'];
        }
        else
            $hash = $config;

        switch( $config['pwhash'] )
        {
            case self::HASH_BCRYPT:
                if( !isset( $config['hash_cost'] ) )
                    $config['hash_cost'] = 9;

                $bcrypt = new OSS_Crypt_Bcrypt( $config['hash_cost'] );
                return $bcrypt->verify( $pwplain, $pwhash );
                break;
        }

        if( substr( $hash, 0, 6) == 'crypt:' )
            return crypt( $pwplain, $pwhash ) == $pwhash;

        if( substr( $hash, 0, 8 ) == 'dovecot:' )
            return ViMbAdmin_Dovecot::passwordVerify( substr( $hash, 8 ), $pwhash, $pwplain, $config['username'] );


        return $pwhash == self::hash( $pwplain, $config );
    }
}
