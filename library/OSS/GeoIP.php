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
 * @package    OSS_GeoIP
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * Geo IP class.
 *
 * @category   OSS
 * @package    OSS_GeoIP
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_GeoIP
{

    /**
     * Returns the two letter country code of a given IP (or an error code)
     *
     * Error codes:
     * 	 10 - GeoIP is not installed
     *   11 - Country database is not available
     *   12 - Unknown / empty result. RFC1918?
     *
     * @param string $ip The IP to look up
     * @return string The two letter country code (or error code)
     */
    public static function getCountryCode( $ip )
    {
        if( !defined( 'GEOIP_COUNTRY_EDITION' ) )
            return '10';

        if( !geoip_db_avail( GEOIP_COUNTRY_EDITION ) )
            return '11';

        $code = @geoip_country_code_by_name( $ip );

        if( $code == '' )
            return '12';

        return $code;
    }


    /**
     * Returns the timezone for a IP (or the default on error)
     *
     * @param string $ip The IP to look up
     * @param string $default The default timezone to use on error
     * @return string The timezone (e.g. 'Europe/Dublin')
     */
    public static function getTimezone( $ip, $default )
    {
        if( !defined( 'GEOIP_COUNTRY_EDITION' ) )
            return $default;

        if( !geoip_db_avail( GEOIP_COUNTRY_EDITION ) )
            return $default;

        $tz = @geoip_time_zone_by_country_and_region(
                    @geoip_country_code_by_name( $ip ),
                    @geoip_region_by_name( $ip )
        );

        if( $tz === false )
            $tz = @geoip_time_zone_by_country_and_region( @geoip_country_code_by_name( $ip ) );
            
        if( $tz === false )
            return $default;
            
        return $tz;
    }
}
