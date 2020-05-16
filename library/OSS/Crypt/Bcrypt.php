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
 * @package    OSS_Crypt
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Bcrypt (Blowfish) hashing tools for password.
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Crypt
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Crypt_Bcrypt
{
    
    /**
     * @var int The cost for the hashing algorithm
     *
     * Example values showing the cost of 10 and the average on an i7 are:
     *
     * 01: 0.001437    0.000144
     * 02: 0.001441    0.000144
     * 03: 0.002011    0.000201
     * 04: 0.017564    0.001756
     * 05: 0.029720    0.002972
     * 06: 0.055418    0.005542
     * 07: 0.109075    0.010908
     * 08: 0.207278    0.020728
     * 09: 0.407365    0.040737
     * 10: 0.839712    0.083971
     * 11: 1.674868    0.167487
     * 12: 3.336014    0.333601
     * 13: 6.699570    0.669957
     * 14: 15.655678   1.565568
     * 15: 26.771987   2.677199
     */
    private static $_cost = 9;

    
    public function __construct( $cost = 9 )
    {
        if( CRYPT_BLOWFISH != 1 )
            throw new OSS_Crypt_Exception( 'CRYPT_BLOWFISH unavailable. See http://php.net/crypt' );

        self::$_cost = $cost;
    }
    

    public static function hash( $plain )
    {
        $hash = crypt( $plain, self::generateSalt() );
    
        if( strlen( $hash ) > 13 )
            return $hash;
    
        return false;
    }
    
    
    public static function verify( $plain, $hash )
    {
        return $hash === crypt( $plain, $hash );
    }

    public static function generateSalt()
    {
        return sprintf( '$2a$%02d$%s', self::$_cost, OSS_String::random( 22, true, true, true, '', '' ) );
    }

}

