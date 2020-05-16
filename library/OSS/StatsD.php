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
 * @package    OSS_StatsD
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @link       https://github.com/etsy/statsd/blob/master/examples/php-example.php
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_StatsD
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       https://github.com/etsy/statsd/blob/master/examples/php-example.php
 */
class OSS_StatsD
{
    /**
     * @var bool Determines whther we are gathering statistics or not
     */
    private $_enabled = true;

    /**
     * @var string The StatsD host
     */
    private $_host = null;

    /**
     * @var int The StatsD port
     */
    private $_port = null;


    /**
     * Construct the object with the host and port to use.
     *
     * @param string $host The StatsD host
     * @param int $port The StatsD port
     * @param bool $enabled Whether we should sent updates or not
     */
    public function __construct( $host, $port, $enabled = true )
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_enabled = $enabled;
    }

    /**
     * Log timing information
     *
     * @param string $stat The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     **/
    public function timing( $stat, $time, $sampleRate=1 )
    {
        $this->send(array($stat => "$time|ms"), $sampleRate);
    }

    /**
     * Increments one or more stats counters
     *
     * @param string|array $stats The metric(s) to increment.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     **/
    public function increment( $stats, $sampleRate=1 )
    {
        $this->updateStats( $stats, 1, $sampleRate );
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string|array $stats The metric(s) to decrement.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     **/
    public function decrement( $stats, $sampleRate=1 )
    {
        $this->updateStats( $stats, -1, $sampleRate );
    }

    /**
     * Updates one or more stats counters by arbitrary amounts.
     *
     * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     **/
    public function updateStats( $stats, $delta=1, $sampleRate=1 )
    {
        if ( !is_array( $stats ) )
            $stats = array($stats);

        $data = array();
        foreach( $stats as $stat )
            $data[$stat] = "$delta|c";

        $this->send( $data, $sampleRate );
    }

    /*
     * Squirt the metrics over UDP
     **/
    public function send( $data, $sampleRate = 1 )
    {
        if( !$this->isEnabled() )
            return;

        // sampling
        $sampledData = array();

        if( $sampleRate < 1 )
        {
            foreach( $data as $stat => $value )
                if( ( mt_rand() / mt_getrandmax()) <= $sampleRate )
                    $sampledData[$stat] = "$value|@$sampleRate";
        }
        else
            $sampledData = $data;

        if( empty( $sampledData ) )
            return;

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try
        {
            if( !( $fp = fsockopen( "udp://" . $this->getHost(), $this->getPort(), $errno, $errstr ) ) )
                return;

            foreach( $sampledData as $stat => $value )
                fwrite( $fp, "$stat:$value" );

            fclose($fp);

        }catch( Exception $e ) {}
    }

    /**
     * Indicates whether StatsD is enabled by configuration or not
     *
     * @return bool 
     */
    public function isEnabled()
    {
        return (bool)$this->_enabled;
    }

}

