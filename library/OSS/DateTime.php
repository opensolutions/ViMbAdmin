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
class OSS_DateTime
{

    /**
     * Returns with the difference between two dates in days.
     *
     * @param string $date1 default null if null then it will take the current date
     * @param string $date2 default null if null then it will take the current date
     * @return int
     */
    public static function dateDiffDays( $date1 = null, $date2= null )
    {
        if( !$date1 )
            $date1 = date("Y-m-d H:i:s");
        
        if( !$date2  )
            $date2 = date("Y-m-d H:i:s");

        $timeStamp1 = strtotime( date("Y-m-d H:i:s", strtotime( $date1 ) ) );
        $timeStamp2 = strtotime( date("Y-m-d H:i:s", strtotime( $date2 ) ) );

        if( $timeStamp1 > $timeStamp2 )
            list( $timeStamp1, $timeStamp2 ) = array( $timeStamp2, $timeStamp1 );

        return ( int ) round( ( $timeStamp2 - $timeStamp1 ) / ( 60 * 60 * 24 ) );
    }


    /**
     * Returns with the difference between two dates in months.
     *
     * @param string $date1 default null if null then it will take the current date
     * @param string $date2 default null if null then it will take the current date
     * @return int
     */
    public static function dateDiffMonths( $date1 = null, $date2 = null )
    {
        if( !$date1 )
            $date1 = date("Y-m");
        
        if( !$date2 )
            $date2 = date("Y-m");

        $timeStamp1 = strtotime( date( "Y-m", strtotime( $date1 ) ) );
        $timeStamp2 = strtotime( date( "Y-m", strtotime( $date2 ) ) );

        if( $timeStamp1 > $timeStamp2 ) list( $timeStamp1, $timeStamp2 ) = array( $timeStamp2, $timeStamp1 );

        return (int) round( ( $timeStamp2 - $timeStamp1 ) / ( 60 * 60 * 24 * 30.438 ) );
    }


    /**
     * Returns with the difference between two dates in years.
     *
     * @param string $date1 default null if null then it will take the current date
     * @param string $date2 default null if null then it will take the current date
     * @return int
     */
    public static function dateDiffYears( $date1 = null, $date2 = null)
    {
        if( !$date1 )
            $date1 = date("Y");

        if( !$date2 )
            $date2 = date("Y");

        $timeStamp1 = strtotime( date("Y", strtotime( $date1 ) ) );
        $timeStamp2 = strtotime( date("Y", strtotime( $date2 ) ) );

        if( $timeStamp1 > $timeStamp2 )
            list( $timeStamp1, $timeStamp2 ) = array( $timeStamp2, $timeStamp1 );

        return (int) round( ( $timeStamp2 - $timeStamp1 ) / ( 60 * 60 * 24 * 365.256 ) );
    }


    /**
     * Converts seconds to hours, minutes and seconds. Returns with an associative array having the keys 'hours', 'minutes' and 'seconds'.
     *
     * @param int $seconds
     * @return array
     */
    public static function secondsToHMS( $seconds )
    {
        $seconds = (int) $seconds;

        $hours = (int) ( $seconds / 3600 );

        $seconds = $seconds - ( $hours * 3600 );

        $minutes = (int) ($seconds / 60);

        $seconds = $seconds - ( $minutes * 60 );

        return array(
                    'hours' => $hours,
                    'minutes' => $minutes,
                    'seconds' => $seconds
                );
    }


    /**
     * Converts seconds to "A hours B minutes C seconds" string, skipping the unnecessary parts,
     * like it won't return with "0 hours 0 minutes 34 seconds" but with "34 seconds".
     *
     * @param int $seconds
     * @return string
     */
    public static function secondsToTimeString( $seconds )
    {
        $data = OSS_Utils::secondsToHMS( $seconds );

        $retVal = '';

        if( $data['hours'] > 0 )
            $retVal .= "{$data['hours']} hour" . ($data['hours'] != 1 ? 's' : '' );
        
        if( $data['minutes'] > 0 )
            $retVal .= " {$data['minutes']} minute" . ( $data['minutes'] != 1 ? 's' : '' );

        if( $data['seconds'] > 0 )
            $retVal .= " {$data['seconds']} second" . ( $data['seconds'] != 1 ? 's' : '' );

        return trim( $retVal );
    }


    /**
     * Takes a date string and returns with it as "yyyy-mm-dd". The Input can be in "yyyy-mm-dd" or "dd/mm/yyyy" format,
     * where the year can be two or four characters, and both the month and day can be one or two characters. It also takes
     * and formats the time part.
     *
     * @param string $date
     * @return string
     */
    public static function ISOdate( $date )
    {
        if( mb_strpos( $date, ' ' ) !== false )
        {
            $time = trim( mb_substr( $date, mb_strpos( $date, ' ' ) ) );
            $date = trim( mb_substr( $date, 0, mb_strpos( $date, ' ' ) ) );
        }
        else
        {
            $date = trim( mb_substr( $date, 0, 10 ) );
            $time = '';
        }

        if( mb_strpos( $date, '/' ) !== false )
        {
            if( preg_match( "/\d{4}\/\d{1,2}\/\d{1,2}/u", $date ) != 0 )
            {
                $date = preg_replace( "/(\d{4})\/(\d{1,2})\/(\d{1,2})/u", "$1-$2-$3", $date );
            }
            else
            {
                $date = preg_replace( "/(\d{1,2})\/(\d{1,2})\/(\d{2,4})/u", "$3-$2-$1", $date );
            }
        }

        if( $date != '' )
        {
            $dateParts = explode( '-', $date );

            if( mb_strlen( $dateParts[0] ) == 2 )
                $dateParts[0] = '20' . $dateParts[0];

            if( mb_strlen( $dateParts[1] ) == 1 )
                $dateParts[1] = '0' . $dateParts[1];
            
            if( mb_strlen( $dateParts[2] ) == 1 )
                $dateParts[2] = '0' . $dateParts[2];

            $date = implode('-', $vDateParts);
        }

        if( $time != '' ) 
        {
            $timeParts = explode(':', $time);

            if( mb_strlen( $timeParts[0] ) == 1 )
                $timeParts[0] = '0' . $timeParts[0];

            if( mb_strlen( $timeParts[1] ) == 1 )
                $timeParts[1] = '0' . $timeParts[1];
            
            if( mb_strlen( $timeParts[2] ) == 1 )
                $timeParts[2] = '0' . $timeParts[2];

            $time = implode(':', $timeParts);
        }

        return trim( "{$date} {$time}" );
    }

}
