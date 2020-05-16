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
 * Requires pear package Net_DNS2.
 * 
 * @see http://pear.php.net/package/Net_DNS2/docs/1.3.0/
 */
require_once 'Net/DNS2.php';

/**
 * Utility methods for NET_DNS2
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Net
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Net_DNS2
{
    /**
     * Gets records form name server for given dns zone
     *
     * @param string $zone DNS zone to look for e.g. example.com
     * @param string $ns   Name server ip
     * @param string $type Record type
     * @return array
     */
    public static function getRecords( $zone, $ns, $type )
    {
        $r = new Net_DNS2_Resolver( [ 'nameservers' => [ gethostbyname( $ns ) ] ] );

        try
        {
            $records = $r->query( $zone, $type )->answer;
        }
        catch( Net_DNS2_Exception $e )
        {
            if( $e->getCode() == Net_DNS2_Lookups::RCODE_NXDOMAIN )
                return [];
            else if( $e->getCode() == Net_DNS2_Lookups::RCODE_NOTAUTH )
                return false;
            else
                return $e->getMessage() . " {$ns} quered by query( {$zone}, {$type} )";
        }

        return $records;
    }
}
