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

class OSS_Validate_OSSIPv6Cidr extends Zend_Validate_Abstract
{

    const INVALID_CIDR = 'invalidCidr';
    const INVALID_IP   = 'invalidIp';
    const INVALID_NET  = 'invalidNet';
    
    /**
     * Possible error messages
     *
     * @var array
     */
    protected $_messages = array(
        self::INVALID_CIDR => 'Invalid IPv4 CIDR format',
        self::INVALID_IP  => 'Invalid IPv4 address',
        self::INVALID_NET => 'Invalid IPv4 subnet size'
    );

    /**
     * It will return true if given value is valid A record (IPv4 address)
     *
     * @param strig $value Date string
     * @param null|mixed $context
     * @return bool
     */
    public function isValid( $value )
    {
        if( !( $pos = ( strpos( $value, '/' ) ) ) )
            return false;

        $ip  = substr( $value, 0, $pos );
        $net = substr( $value, $pos + 1 );

        if( !strlen( $net ) || !preg_match( '/^[0-9]+$/', $net ) )
            return false;
        
        if( $net > 128 )
            return false;

        return Zend_Validate::is( $ip, 'OSS_Validate_OSSIPv6' );
    }

}
