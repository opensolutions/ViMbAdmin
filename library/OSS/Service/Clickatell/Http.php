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
class OSS_Service_Clickatell_Http extends OSS_Service_Clickatell_Abstract
{

    /**
     * API identifiers for different tasks
     */
    const API_METHOD_BALANCE       = 'getbalance';
    const API_METHOD_SENDSMS       = 'sendmsg';
    const API_METHOD_QUERY         = 'querymsg';

    const FEAT_TEXT = 1; // Text – set by default.

    /**
     * Features.
     *
     * for more than one feature request add the values up and pass the sum (FEAT_8BIT + FEAT_ALPHA + FEAT_CONCAT)
     */
    const FEAT_8BIT     = 2;     // 8-bit messaging – set by default.
    const FEAT_UDH      = 4;     // UDH (Binary) - set by default.
    const FEAT_UCS2     = 8;     // UCS2 / Unicode – set by default.
    const FEAT_ALPHA    = 16;    // Alpha source address (from parameter). Sender ID
    const FEAT_NUMER    = 32;    // Numeric source address (from parameter). Sender ID
    const FEAT_FLASH    = 512;   // Flash messaging.
    const FEAT_DELIVACK = 8192;  // Delivery acknowledgments.
    const FEAT_CONCAT   = 16384; // Concatenation – set by default


    /**
     * The API URL for Clickatell
     *
     * @var string
     */
    protected $_api_url = 'api.clickatell.com/http/';

    /**
     * Use sessions or authenticate for each request
     *
     * @var bool
     */
    protected $_use_session = false;

    /**
     * HTTP reponse object
     *
     * @var object
     */
    protected $_response;


    /**
     * A string containing the Sender ID, which is a short ( <= 11 characters) string
     *
     * @var string
     */
    protected $_sender_id;


    /**
     * Constructor
     * 
     * Accepted associated array parameters:
     *  api_url use_ession
     * Also see parent constructor for additional options.
     *   
     * @param $options array An associated array of options (see above for accepted keys).
     * @return void
     */
    public function __construct( $options = null )
    {
        if ( is_array( $options ) )
        {
            parent::__construct( $options );

            foreach( $options as $param => $value )
            {
                switch( $param )
                {
                    case 'api_url':
                        $this->setApiUrl( $value );
                        break;

                    case 'use_session':
                        $this->setUseSession( $value );
                        break;

                    case 'sender_id':
                        $this->_sender_id = $value;
                        break;
                }
            }
        }

        if ( !is_array( $options ) || !array_key_exists( 'api_url', $options ) ) $this->setApiUrl( $this->_api_url );
    }

    /**
     * A generic function to place API calls and catch common errors early (e.g. auth failure)
     * 
     * @param string $method The method to call (see OSS_Service_Clickatell_Http::API_METHOD_XXX)
     * @throws OSS_Service_Clickatell_Exception
     * @return void
     */
    protected function call( $method )
    {
        $this->getHttpClient()->setUri( $this->getApiUrl() . $method );
        $this->setAuthParams();
        $this->_response = $this->getHttpClient()->request();

        // ensure we have valid authentication parameters
        $auth = explode( ': ', $this->_response->getBody(), 2 );

        if ( $auth[0] == 'ERR' )
        {
            $error = explode( ',', $auth[1], 2 );

            if ( $error[0] == '001' )
            {
                throw new OSS_Service_Clickatell_Exception( 'Clickatell authentication failed' );
            }
            else
            {
                throw new OSS_Service_Clickatell_Exception( 'Clickatell Error: ' . $auth[1] );
            }
        }
    }


    /**
     * Get the Clickatell account balance.
     * 
     * This returns the number of credits remaining.
     * 
     * @throws OSS_Service_Clickatell_Exception
     * @return string
     */
    public function getBalance()
    {
        $this->call( OSS_Service_Clickatell_Http::API_METHOD_BALANCE );

        $credit = explode( ': ', $this->_response->getBody(), 2 );

        if ( $credit[0] != 'Credit' ) throw new OSS_Service_Clickatell_Exception( 'Unknown response for Clickatell credit check' );

        $this->log( 'Retrieved balance: ' . $credit[1] );    

        return $credit[1];
    }


    /**
     * Send a one off SMS to a single or multiple users
     * 
     * If $number is an array, then the SMS will be sent to multple users.
     * 
     * The success IDs or send errors will be found referenced by the destination number
     * via getSendIds() and getSendErrors().
     *
     * You cannot just use any sender id you like, first you have to register and approve it by Clickatell.
     *
     * Return true if all messages sent successfully, false if there were errors (@see OSS_Service_Clickatell_Abstract::$_send_errors)
     *
     * @param string|array $number The full international version of the number to send the SMS to (or an array of multiple numnbers)
     * @param string $message The message to send
     * @param string $senderid NULL to use the default from application.ini, false to not to use any, a valid international format number between 1 and 16 characters long, or an 11 character alphanumeric string
     * @return bool 
     */
    public function sendSms( $number, $message, $senderid=null )
    {
        $this->_send_errors = array();
        $this->_send_ids    = array();

        if ( !is_array( $number ) ) $number = array( $number );

        foreach( array_chunk( $number, 100 ) as $numbers )
        {
            if ( ($senderid === null) && ($this->_sender_id != '') )
            {
                $this->getHttpClient()->setParameterGet( 'req_feat', OSS_Service_Clickatell_Http::FEAT_ALPHA );
                $this->getHttpClient()->setParameterGet( 'from', $this->_sender_id );
            }
            elseif ($senderid !== false)
            {
                $this->getHttpClient()->setParameterGet( 'req_feat', OSS_Service_Clickatell_Http::FEAT_ALPHA );
                $this->getHttpClient()->setParameterGet( 'from', trim(mb_substr($senderid, 0, 11)) );
            }

            $this->getHttpClient()->setParameterGet( 'to',   implode( ',', $number  ) );
            $this->getHttpClient()->setParameterGet( 'text', $message );

            $this->call( OSS_Service_Clickatell_Http::API_METHOD_SENDSMS );

            if ( count( $number ) == 1 )
            {
                $send = explode( ': ', $this->_response->getBody(), 2 );

                if( $send[0] != 'ID' )
                {
                    $this->_send_errors[$number[0]] = $this->_response->getBody();
                    //$this->log( 'Sent SMS to : ' . $number[0] . ' with send ID: ' . $send[1] );
                    $this->log( 'SMS sent to : ' . $number[0] . ' returned with error: ' . $this->_response->getBody() );
                    return false;
                }

                $this->log( 'Sent SMS to: ' . $number[0] . ' with send ID: ' . $send[1] );
                $this->_send_ids = array( $numbers[0] => $send[1] );
                return true;
            }

            foreach( explode( "\n", $this->_response->getBody() ) as $line )
            {
                $data = explode( ' ', $line );

                if( $data[0] == 'ID:' )
                    $this->_send_ids[$data[3]] = $data[1];
                else
                    $this->_send_errors[$data[ count($data) - 1]] = $line;
            }
        }

        $this->log( 'Sent ' . count( $this->_send_ids ) . '/' . count($number) . ' SMS messages' );

        return ( count($this->_send_errors) == 0 ? true : false);
    }



    /**
     * Query the status of a message previously sent.
     * 
     * Use OSS_Service_Clickatll_Abstract::$MESSAGE_STATES and OSS_Service_Clickatll_Abstract::getMessageStateDescription()
     * to interpret the state.
     * 
     * @throws OSS_Service_Clickatell_Exception
     * @param $msgid string The message ID to query
     * @returns string
     */
    public function queryMessage( $msgid )
    {
        $this->getHttpClient()->setParameterGet( 'apimsgid', $msgid );
        $this->call( OSS_Service_Clickatell_Http::API_METHOD_QUERY );

        $data = explode( ' ', $this->_response->getBody() );

        if( $data[0] == 'ID:' )
            return $data[3];

        throw new OSS_Service_Clickatell_Exception( 'Unknown response for Clickatell message query: ' 
            . $this->_response->getBody() );
    }


    /**
     * Set the API URL
     * @param $api_url the API ID to set
     * @return void
     */
    public function setApiUrl( $api_url ) 
    {
        $this->_api_url = $api_url;

        $this->setHttpClient( new Zend_Http_Client( $this->getApiUrl() ) );
    }


    /**
     * Sets the authentication params for the URI on the HTTP client object
     * 
     * Specifically, if we are using sessions, then a session ID parameter will
     * be set, otherwise we'll set a username, password and API ID.
     * 
     * @return void
     */
    public function setAuthParams()
    {
        if( $this->getUseSession() )
        {
            // FIXME Need to add session functionality
        }
        else
        {
            $this->getHttpClient()->setParameterGet( 'user',     $this->getUsername() );
            $this->getHttpClient()->setParameterGet( 'password', $this->getPassword() );
            $this->getHttpClient()->setParameterGet( 'api_id',   $this->getApiId()    );
        }
    }


    /**
     * Get the API URL
     *
     * @return string
     */
    public function getApiUrl() 
    {
        return ( $this->getSSL() ? 'https://' : 'http://' ) . $this->_api_url;
    }


    /**
     * Should we use sessions?
     *
     * @param $api_url the API ID to set
     * @return void
     */
    public function setUseSession( $use_session = true ) 
    {
        $this->_use_session = (bool)$use_session;
    }


    /**
     * Should we use sessions?
     * Returns true if we will use sessions
     *
     * @return bool
     */
    public function getUseSession() 
    {
        return $this->_use_session;
    }

}
