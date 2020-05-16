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
 * @package    OSS_Service
 * @subpackage Clickatell
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Service
 * @subpackage Clickatell
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
abstract class OSS_Service_Clickatell_Abstract extends Zend_Service_Abstract
{

    /**
     * An array of Clickatell delivery states (e.g. @see OSS_Service_Clickatell_Http::getQueryMessage() )
     *
     * @var array
     */
    public static $MESSAGE_STATES = array(
        '001' => 'Message unknown',
        '002' => 'Message queued',
        '003' => 'Delivered to gateway',
        '004' => 'Received by recipient',
        '005' => 'Error with message',
        '006' => 'User cancelled message delivery',
        '007' => 'Error delivering message',
        '008' => 'OK',
        '009' => 'Routing error',
        '010' => 'Message expired',
        '011' => 'Message queued for later delivery',
        '012' => 'Out of credit'
    );


    /**
     * API access username
     * @var string
     */
    protected $_username;

    /**
     * API access password
     * @var string
     */
    protected $_password;

    /**
     * API access ID
     * @var string
     */
    protected $_api_id;

    /**
     * Use an SSL connection?
     * @var bool
     */
    protected $_ssl = false;

    /**
     * A logger object
     * @var Zend_Logger
     */
    protected $_logger = null;

    /**
     * The log level
     * @var integer
     */
    protected $_loglevel = 7;

    /**
     * Send IDs of the last batch of messages sent
     * Keys are the numbers and values are the IDs
     *
     * @var array 
     */
    protected $_send_ids;

    /**
     * An array of errors per phone number from the last send
     * Keys are the numbers and values are the error messages
     *
     * @var array 
     */
    protected $_send_errors;



    /**
     * Constructor
     * 
     * Accepted associated array parameters:
     *   username, password, ssl, api_id
     *   
     * @param $options array An associated array of options (see above for accepted keys).
     * @retrun void
     */
    public function __construct( $options = null )
    {
        if( is_array( $options ) )
        {
            foreach( $options as $param => $value )
            {
                switch( $param )
                {
                    case 'username':
                        $this->setUsername( $value );
                        break;

                    case 'password':
                        $this->setPassword( $value );
                        break;

                    case 'api_id':
                        $this->setApiId( $value );
                        break;

                    case 'ssl':
                        $this->setSSL( (bool)$value );
                        break;


                }
            }
        }
    }

    /**
     * Set SSL enabled
     *
     * @param $ssl Pass boolean true to set SSL enabled
     * @return void
     */
    public function setSSL( $ssl = true ) 
    {
        $this->_ssl = (bool) $ssl;
    }

    /**
     * Set the API ID
     *
     * @param $api_id the API ID to set
     * @return void
     */
    public function setApiId( $api_id ) 
    {
        $this->_api_id = $api_id;
    }

    /**
     * Set the API password
     *
     * @param $password the password to set
     * @return void
     */
    public function setPassword( $password ) 
    {
        $this->_password = $password;
    }

    /**
     * Set the username parameter
     *
     * @param $username The username to set
     * @retrn void
     */
    public function setUsername( $username ) 
    {
        $this->_username = $username;
    }

    /**
     * Is SSL enabled?
     * Returns a boolean indicating whether SSL is enabled or not
     *
     * @return bool
     */
    public function getSSL() 
    {
        return (bool)$this->_ssl;
    }

    /**
     * Get the API ID
     *
     * @return string
     */
    public function getApiId() 
    {
        return $this->_api_id;
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword() 
    {
        return $this->_password;
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername() 
    {
        return $this->_username;
    }



    /**
     * Set the logger object
     *
     * @param Zend_Logger $logger The Zend_Logger object
     * @return void
     */
    public function setLogger( &$logger )
    {
        $this->_logger = $logger;
    }

    /**
     * Get the logger object
     *
     * @return Zend_Logger
     */
    protected function getLogger()
    {
        return $this->_logger;
    }


    /**
     * Set the log level
     *
     * @param int $loglevel The log level
     * @return void
     */
    public function setLogLevel( $loglevel )
    {
        $this->_loglevel = $loglevel;
    }

    /**
     * Get the log level
     *
     * @return int
     */
    protected function getLogLevel()
    {
        return $this->_loglevel;
    }


    /** 
     * Add an entry to the log
     *
     * @param string $log Log message
     * @return void
     */
    protected function log( $log )
    {
        if( $this->getLogger() === null )
            return;

        $this->getLogger()->log( 'Clickatell: ' . $log, $this->getLogLevel() );
    }



    /**
     * Get the last batch of send IDs
     * 
     * Returns an associated array of number => ID where number is the destination SMS
     * number and ID is the send ID from the Clickatell API.
     * 
     * @return array
     */
    public function getSendIds()
    {
        return $this->_send_ids;
    }

    /**
     * Get the last batch of send errors
     * 
     * Returns an associated array of number => error_message where number is the destination SMS
     * number and error is that as returned from the Clickatell API.
     * 
     * @return array
     */
    public function getSendErrors()
    {
        return $this->_send_errors;
    }

    /**
     * Get the text description for a given state
     * 
     * @see OSS_Service_Clickatell_Abstract::$MESSAGE_STATES
     * @see OSS_Service_Clickatell_Http::queryMessage()
     *
     * @param string The state to query
     * @return void
     */
    public function getMessageStateDescription( $state )
    {
        return OSS_Service_Clickatell_Abstract::$MESSAGE_STATES[$state];
    }

}


