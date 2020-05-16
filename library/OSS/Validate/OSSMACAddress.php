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
class OSS_Validate_OSSMACAddress extends Zend_Validate_Abstract
{

    const INVALID_MAC = 'invalidMAC';

     /**
     * Error message templates
     * @var array
     */
    protected $_messages = array(
        self::INVALID_MAC => 'Invalid MAC address.'
    );


    /**
     * Cheks is given value is MAC address
     *
     * @param  string $value
     * @param  null $context
     * @return bool
     */
    public function isValid( $value, $context = null )
    {
        $vMAC = strtoupper( $value );

        // valid MAC address formats: 0123456789AB | 01-23-45-67-89-AB | 01:23:45:67:89:AB | 0123.4567.89AB
        $vMatch0 = preg_match( "/^[0-9A-F]{12}$/", $vMAC );
        $vMatch1 = preg_match( "/^([0-9A-F]{2}\-){5}([0-9A-F]{2})$/", $vMAC );
        $vMatch2 = preg_match( "/^([0-9A-F]{2}\:){5}([0-9A-F]{2})$/", $vMAC );
        $vMatch3 = preg_match( "/^([0-9A-F]{4}\.){2}([0-9A-F]{4})$/", $vMAC );

        if( $vMatch0 + $vMatch1 + $vMatch2 + $vMatch3 == 0 )
        {
            $this->_error( self::INVALID_MAC );
            return false;
        }

        return true;
    }

}
