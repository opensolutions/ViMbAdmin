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
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @see Zend_Rest_Client
 */
require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Rest_Client_Result
 */
require_once 'Zend/Rest/Client/Result.php';

/**
 * @see Zend_Oauth_Consumer
 */
require_once 'Zend/Oauth/Consumer.php';

/**
 * @category   OSS
 * @package    OSS_Service
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Service_Freshbooks extends Zend_Rest_Client
{

    /**
     * Cookie jar
     *
     * @var Zend_Http_CookieJar
     */
    protected $_cookieJar;

    /**
     * Date format for 'since' strings
     *
     * @var string
     */
    protected $_dateFormat = 'D, d M Y H:i:s T';

    /**
     * Subomain
     *
     * @var string
     */
    protected $_subdomain;

    /**
     * Current method type (for method proxying)
     *
     * @var string
     */
    protected $_methodType;

    /**
     * Zend_Oauth Consumer
     *
     * @var Zend_Oauth_Consumer
     */
    protected $_oauthConsumer = null;

    /**
     * Types of API methods
     *
     * @var array
     */
    protected $_methodTypes = array(
        'system.current',
        'client.create',
        'client.update',
        'client.get',
        'client.delete',
        'client.list',
        'invoice.create',
        'invoice.update',
        'invoice.get',
        'invoice.delete',
        'invoice.list',
        'invoice.sendByEmail',
        //'invoice.sendBySnailMail',
        'invoice.lines.add',
        'invoice.lines.delete',
        'invoice.lines.update',
        'recurring.create',
        'recurring.update',
        'recurring.get',
        'recurring.delete',
        'recurring.list',
        'recurring.lines.add',
        'recurring.lines.delete',
        'recurring.lines.update'
    );

    /**
     * Options passed to constructor
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Local HTTP Client cloned from statically set client
     *
     * @var Zend_Http_Client
     */
    protected $_localHttpClient = null;

    /**
     * Constructor
     *
     * @param  array $options Options array
     * @param null|Zend_Oauth_Consumer Optional consumer
     * @return void
     */
    public function __construct( $options, Zend_Oauth_Consumer $consumer = null )
    {
        if( $options instanceof Zend_Config )
            $options = $options->toArray();

        if( !is_array( $options ) )
            $options = array();

        $options['signatureMethod'] = 'PLAINTEXT';

        $this->_options = $options;
        if( isset( $options[ 'subdomain' ] ) )
        {
            
            $this->setSubdomain( $options[ 'subdomain' ] );
            $options['consumerKey'] = $options[ 'subdomain' ];
        }
        $this->setUri( 'https://'. $this->getSubdomain() . '.freshbooks.com/api/2.1/xml-in' );

        $options[ 'siteUrl' ] = 'https://'. $this->getSubdomain() . '.freshbooks.com/oauth';

        if( isset( $options[ 'accessToken' ] )
            && $options[ 'accessToken' ] instanceof Zend_Oauth_Token_Access )
        {
            $this->setLocalHttpClient( $options['accessToken']->getHttpClient( $options ) );
        } 
        else
        {
            $this->setLocalHttpClient( clone self::getHttpClient() );
            if( $consumer === null )
                $this->_oauthConsumer = new Zend_Oauth_Consumer( $options );
            else
                $this->_oauthConsumer = $consumer;
        }
    }

    /**
     * Set local HTTP client as distinct from the static HTTP client
     * as inherited from Zend_Rest_Client.
     *
     * @param Zend_Http_Client $client
     * @return OSS_Service_Freshbooks
     */
    public function setLocalHttpClient( Zend_Http_Client $client )
    {
        $this->_localHttpClient = $client;
        $this->_localHttpClient->setHeaders( 'Accept-Charset', 'ISO-8859-1,utf-8' );
        return $this;
    }

    /**
     * Get the local HTTP client as distinct from the static HTTP client
     * inherited from Zend_Rest_Client
     *
     * @return Zend_Http_Client
     */
    public function getLocalHttpClient()
    {
        return $this->_localHttpClient;
    }

    /**
     * Checks for an authorised state
     *
     * @return bool
     */
    public function isAuthorised()
    {
        if( $this->getLocalHttpClient() instanceof Zend_Oauth_Client ) 
            return true;
        
        return false;
    }

    /**
     * Retrieve username
     *
     * @return string
     */
    public function getSubdomain()
    {
        return $this->_subdomain;
    }

    /**
     * Set username
     *
     * @param  string $value
     * @return OSS_Service_Freshbooks
     */
    public function setSubdomain( $value )
    {
        $this->_subdomain = $value;
        return $this;
    }

    /**
     * Proxy service methods
     *
     * @param  string $type
     * @throws Zend_Service_Exception If method not in method types list
     * @return OSS_Service_Freshbooks
     */
    public function __get( $type )
    {
        if( !in_array( $type, $this->_methodTypes ) )
        {
            include_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception(
                'Invalid method type "' . $type . '"'
            );
        }
        $this->_methodType = $type;
        return $this;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $params
     * @throws Zend_Service_Exception if unable to find method
     * @return mixed
     */
    public function __call( $method, $params )
    {
        if( method_exists( $this->_oauthConsumer, $method ) )
        {
            $return = call_user_func_array( array( $this->_oauthConsumer, $method ), $params );
            if( $return instanceof Zend_Oauth_Token_Access )
            {
                $this->setLocalHttpClient( $return->getHttpClient( $this->_options ) );
            }
            return $return;
        }
        if( empty( $this->_methodType ) )
        {
            include_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception(
                'Invalid method "' . $method . '"'
            );
        }
        $test = $this->_methodType . ucfirst($method);
        if( !method_exists( $this, $test ) )
        {
            include_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception(
                'Invalid method "' . $test . '"'
            );
        }

        return call_user_func_array( array( $this, $test ), $params );
    }

    /**
     * Initialize HTTP authentication
     *
     * @throws Zend_Service_Exception if not authorised
     * @return void
     */
    protected function _init()
    {
        if( !$this->isAuthorised() && $this->getUsername() !== null )
        {
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception(
                'Freshbooks session is unauthorised. You need to initialize '
                . 'OSS_Service_Freshbooks with an OAuth Access Token or use '
                . 'its OAuth functionality to obtain an Access Token before '
                . 'attempting any API actions that require authorisation'
            );
        }
        $client = $this->_localHttpClient;
        $client->resetParameters();
        if( null == $this->_cookieJar )
        {
            $client->setCookieJar();
            $this->_cookieJar = $client->getCookieJar();
        }
        else
        {
            $client->setCookieJar( $this->_cookieJar );
        }
    }

    /**
     * Set date header
     *
     * @param  int|string $value
     * @return void
     */
    protected function _setDate( $value ) 
    {
        if( is_int( $value ) )
            $date = date( $this->_dateFormat, $value );
        else
            $date = date( $this->_dateFormat, strtotime( $value ) );

        $this->_localHttpClient->setHeaders( 'If-Modified-Since', $date );
    }

    /**
     * Public system current
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function systemCurrent()
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="system.current">' .
                    '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }


    /**
     * Public client create
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function clientCreate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="client.create">' .
                    '<client>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeClientXml( $params );

        $xml .= '</client></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

        /**
     * Public client create
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function clientUpdate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="client.update">' .
                    '<client>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeClientXml( $params );

        $xml .= '</client></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public client get
     *
     * @param int $client_id Id of the client
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function clientGet( $client_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="client.get">' .
                    '<client_id>' . $client_id . '</client_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public client delete
     *
     * @param int $client_id Id of the client
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function clientDelete( $client_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="client.delete">' .
                    '<client_id>' . $client_id . '</client_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public client list
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function clientList( $params = null )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="client.list">';
        if( $params && is_array( $params ) )
        {
            //Filter by email address (Optional) 
            if( isset( $params['email'] ) )
                $xml .= '<email>' . $params['email'] . '</email>';
            
            //Filter by username (Optional)
            if( isset( $params['username'] ) )
                $xml .= '<username>' . $params['username'] . '</username>';
            
            //Return only clients modified since this date (Optional)
            if( isset( $params['updated_from'] ) )
                $xml .= '<updated_from>' . $params['updated_from']->format('Y-m-d H:i:s') . '</updated_from>';

            //Return only clients modified before this date (Optional)
            if( isset( $params['updated_to'] ) )
                $xml .= '<updated_to>' . $params['updated_to']->format('Y-m-d H:i:s') . '</updated_to>';

            //The page number to show (Optional) 
            if( isset( $params['page'] ) )
                $xml .= '<page>' . $params['page'] . '</page>';

            //Number of results per page, default 25 (Optional)
            if( isset( $params['per_page'] ) )
                $xml .= '<per_page>' . $params['per_page'] . '</per_page>';

            //One of 'active', 'archived', 'deleted' (Optional)
            if( isset( $params['folder'] ) )
                $xml .= '<folder>' . $params['folder'] . '</folder>';

            //Return only clients with this text in their 'notes' (Optional)
            if( isset( $params['notes'] ) )
                $xml .= '<notes>' . $params['notes'] . '</notes>';
        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice create
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceCreate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="invoice.create">' .
                    '<invoice>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeInvoiceXml( $params ); 

        $xml .= '</invoice></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice update
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceUpdate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="invoice.update">' .
                    '<invoice>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeInvoiceXml( $params );

        $xml .= '</invoice></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice get
     *
     * @param int $invoice_id Id of the invoice
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceGet( $invoice_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="invoice.get">' .
                    '<invoice_id>' . $invoice_id . '</invoice_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice delete
     *
     * @param int $invoice_id Id of the invoice
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceDelete( $invoice_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="invoice.delete">' .
                    '<invoice_id>' . $invoice_id . '</invoice_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }


    /**
     * Public invoice list
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceList( $params = null)
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="invoice.list">';
        if( $params && is_array( $params ) )
        {
            //Filter by client (Optional) 
            if( isset( $params['client_id'] ) )
                $xml .= '<client_id>' . $params['client_id'] . '</client_id>';
            
            //Filter by recurring id (Optional)
            if( isset( $params['requirring_id'] ) )
                $xml .= '<requirring_id>' . $params['requirring_id'] . '</requirring_id>';

            //Filter by status (Optional)
            if( isset( $params['status'] ) )
                $xml .= '<status>' . $params['status'] . '</status>';

            //Returns invoices with a number like this arg (Optional)
            if( isset( $params['number'] ) )
                $xml .= '<number>' . $params['number'] . '</number>';

            //Return invoices dated after this arg (Optional)
            if( isset( $params['date_from'] ) )
                $xml .= '<date_from>' . $params['date_from']->format('Y-m-d H:i:s') . '</date_from>';

            //Return invoices dated before this arg (Optional)
            if( isset( $params['date_to'] ) )
                $xml .= '<date_to>' . $params['date_to']->format('Y-m-d H:i:s') . '</date_to>';

            //Return invoices modified after this arg (Optional)
            if( isset( $params['updated_from'] ) )
                $xml .= '<updated_from>' . $params['updated_from']->format('Y-m-d H:i:s') . '</updated_from>';

            //Return invoices modified before this arg (Optional)
            if( isset( $params['updated_to'] ) )
                $xml .= '<updated_to>' . $params['updated_to']->format('Y-m-d H:i:s') . '</updated_to>';

            //Page number to return, default is 1 (Optional)
            if( isset( $params['page'] ) )
                $xml .= '<page>' . $params['page'] . '</page>';

            //Number of results per page, default is 25 (Optional)
            if( isset( $params['per_page'] ) )
                $xml .= '<per_page>' . $params['per_page'] . '</per_page>';

            //One of 'active', 'archived', 'deleted' (Optional)
            if( isset( $params['folder'] ) )
                $xml .= '<folder>' . $params['folder'] . '</folder>';
        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice send by email
     *
     * @param int $invoice_id Id of the invoice
     * @param string $subject Email subject
     * @param string $message Email body to add invoice 
     * link write '::invoice link::'.
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceSendByEmail( $invoice_id, $subject = null, $message = null )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="invoice.sendByEmail">' .
                    '<invoice_id>' . $invoice_id . '</invoice_id>';
        if( $subject )
            $xml .= '<subject>' . $subject . '</subject>';

        if( $message )
            $xml .= '<message>' . $message . '</message>';

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice lines add
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceLinesAdd( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="invoice.lines.add">';
        if( $params && is_array( $params ) )
        {
            //Invoice to update
            if( isset( $params['invoice_id'] ) )
                $xml .= '<invoice_id>' . $params['invoice_id'] . '</invoice_id>';

            if( isset( $params['lines'] ) )
                $xml .= $this->_makeLinesXml( $params['lines'] );

        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice line delete
     *
     * @param int $invoice_id Id of the invoice
     * @param int $line_id Id of the line to delete
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceLinesDelete( $invoice_id, $line_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="invoice.lines.delete">' .
                    '<invoice_id>' . $invoice_id . '</invoice_id>' .
                    '<line_id>' . $line_id . '</line_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public invoice lines update
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function invoiceLinesUpdate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="invoice.lines.update">';
        if( $params && is_array( $params ) )
        {
            //Invoice to update
            if( isset( $params['invoice_id'] ) )
                $xml .= '<invoice_id>' . $params['invoice_id'] . '</invoice_id>';

            if( isset( $params['lines'] ) )
                $xml .= $this->_makeLinesXml( $params['lines'] );
        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring create
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringCreate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="recurring.create">' .
                    '<recurring>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeRecurringXml( $params );

        $xml .= '</recurring></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring update
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringUpdate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="recurring.update">' .
                    '<recurring>';
        if( $params && is_array( $params ) )
            $xml .= $this->_makeRecurringXml( $params );

        $xml .= '</recurring></request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring get
     *
     * @param int $recurring_id Id of the recurring
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringGet( $recurring_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="recurring.get">' .
                    '<recurring_id>' . $recurring_id . '</recurring_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring delete
     *
     * @param int $recurring_id Id of the recurring
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringDelete( $recurring_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="recurring.delete">' .
                    '<recurring_id>' . $recurring_id . '</recurring_id>' . 
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring list
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringList( $params = null)
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="recurring.list">';
        if( $params && is_array( $params ) )
        {
            //Filter by client (Optional) 
            if( isset( $params['client_id'] ) )
                $xml .= '<client_id>' . $params['client_id'] . '</client_id>';

            //Return auto-bills dated after this arg (Optional)
            if( isset( $params['date_from'] ) )
                $xml .= '<date_from>' . $params['date_from']->format('Y-m-d H:i:s') . '</date_from>';

            //Return auto-bills dated before this arg (Optional)
            if( isset( $params['date_to'] ) )
                $xml .= '<date_to>' . $params['date_to']->format('Y-m-d H:i:s') . '</date_to>';

            //Return auto-bills modified after this arg (Optional)
            if( isset( $params['updated_from'] ) )
                $xml .= '<updated_from>' . $params['updated_from']->format('Y-m-d H:i:s') . '</updated_from>';

            //Return auto-bills modified before this arg (Optional)
            if( isset( $params['updated_to'] ) )
                $xml .= '<updated_to>' . $params['updated_to']->format('Y-m-d H:i:s') . '</updated_to>';

            //Filter auto-bill profiles (Optional)
            if( isset( $params['autobill'] ) )
                $xml .= '<autobill>' . $params['autobill'] . '</autobill>';

            //Page number to return, default is 1 (Optional)
            if( isset( $params['page'] ) )
                $xml .= '<page>' . $params['page'] . '</page>';

            //Number of results per page, default is 25 (Optional)
            if( isset( $params['per_page'] ) )
                $xml .= '<per_page>' . $params['per_page'] . '</per_page>';

            //One of 'active', 'archived', 'deleted' (Optional)
            if( isset( $params['folder'] ) )
                $xml .= '<folder>' . $params['folder'] . '</folder>';
        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring lines add
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringLinesAdd( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="recurring.lines.add">';
        if( $params && is_array( $params ) )
        {
            //Invoice to update
            if( isset( $params['recurring_id'] ) )
                $xml .= '<recurring_id>' . $params['recurring_id'] . '</recurring_id>';

            if( isset( $params['lines'] ) )
                $xml .= $this->_makeLinesXml( $params['lines'] );

        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring line delete
     *
     * @param int $recurring_id Id of the recurring
     * @param int $line_id Id of the line to delete
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringLinesDelete( $recurring_id, $line_id )
    {
        $this->_init();

        $xml = '<!--?xml version="1.0" encoding="utf-8"?-->'.
                '<request method="recurring.lines.delete">' .
                    '<recurring_id>' . $recurring_id . '</recurring_id>' .
                    '<line_id>' . $line_id . '</line_id>' .
                '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Public recurring lines update
     *
     * @params araray $params Params of request
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function recurringLinesUpdate( $params )
    {
        $this->_init();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .  
                '<request method="recurring.lines.update">';
        if( $params && is_array( $params ) )
        {
            //Invoice to update
            if( isset( $params['recurring_id'] ) )
                $xml .= '<recurring_id>' . $params['recurring_id'] . '</recurring_id>';

            if( isset( $params['lines'] ) )
                $xml .= $this->_makeLinesXml( $params['lines'] );
        }

        $xml .= '</request>';
        
        $response = $this->_post( "", $xml );

        return new Zend_Rest_Client_Result( $response->getBody() );
    }

    /**
     * Call a remote REST web service URI and return the Zend_Http_Response object
     *
     * @param  string $path The path to append to the URI
     * @throws Zend_Rest_Client_Exception 
     * @return void
     */
    protected function _prepare( $path = "" )
    {
        // Get the URI object and configure it
        if( !$this->_uri instanceof Zend_Uri_Http )
        {
            require_once 'Zend/Rest/Client/Exception.php';
            throw new Zend_Rest_Client_Exception(
                'URI object must be set before performing call'
            );
        }

        if( $path != "" )
        {
            $uri = $this->_uri->getUri();

            if( $path[0] != '/' && $uri[ strlen( $uri ) - 1 ] != '/' )
                $path = '/' . $path;

            $this->_uri->setPath( $path );
        }

        /**
         * Get the HTTP client and configure it for the endpoint URI.
         * Do this each time because the Zend_Http_Client instance is shared
         * among all Zend_Service_Abstract subclasses.
         */
        $this->_localHttpClient->resetParameters()->setUri( ( string ) $this->_uri );
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function _get( $path = "", array $query = null )
    {
        $this->_prepare( $path );
        $this->_localHttpClient->setParameterGet( $query );
        return $this->_localHttpClient->request( Zend_Http_Client::GET );
    }

    /**
     * Performs an HTTP POST request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function _post( $path = "", $data = null )
    {
        $this->_prepare( $path );
        return $this->_performPost( Zend_Http_Client::POST, $data );
    }

    /**
     * Perform a POST or PUT
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * @return Zend_Http_Response
     */
    protected function _performPost( $method, $data = null )
    {
        $client = $this->_localHttpClient;
        
        if( is_string( $data ) )
            $client->setRawData( $data );
        elseif( is_array( $data ) || is_object( $data ) )
            $client->setParameterPost( (array)  $data );

        return $client->request( $method );
    }


    /**
     * Makes addres string xml from address array
     *
     * @param array $address The address data.
     * @param string $type The type of address( primary(p | secondary (s) )
     * @return string
     */
    protected function _makeAddressXml( $address, $type )
    {
        $xml = "";
        $type = substr( $type, 0, 1 );
        if( isset( $address['street1'] ) )
            $xml .= "<{$type}_street1>" . $address['street1'] . "</{$type}_street1>";

        if( isset( $address['street2'] ) )
            $xml .= "<{$type}_street2>" . $address['street2'] . "</{$type}_street2>";

        if( isset( $address['city'] ) )
            $xml .= "<{$type}_city>" . $address['city'] . "</{$type}_city>";

        if( isset( $address['state'] ) )
            $xml .= "<{$type}_state>" . $address['state'] . "</{$type}_state>";

        if( isset( $address['country'] ) )
            $xml .= "<{$type}_country>" . $address['country'] . "</{$type}_country>";

        if( isset( $address['code'] ) )
            $xml .= "<{$type}_code>" . $address['code'] . "</{$type}_code>";

        return $xml;
    }


    /**
     * Makes contacts string xml from contacts array
     *
     * @param array $contacts The contacts data.
     * @return string
     */
    protected function _makeContactsXml( $contacts )
    {
        $xml .= '<contacts>';
        foreach( $contacts as $key => $value )
        {
            $xml .= '<contact>';
            
            if( isset( $value['contact_id'] ) )
                $xml .= '<contact_id>' . $value['contact_id'] . '</contact_id>';

            if( isset( $value['username'] ) )
                $xml .= '<username>' . $value['username'] . '</username>';
            
            if( isset( $value['first_name'] ) )
                $xml .= '<first_name>' . $value['first_name'] . '</first_name>';
            
            if( isset( $value['last_name'] ) )
                $xml .= '<last_name>' . $value['last_name'] . '</last_name>';
            
            //Email address is the only required field
            if( isset( $value['email'] ) )
                $xml .= '<email>' . $value['email'] . '</email>';
            
            if( isset( $value['phone1'] ) )
                $xml .= '<phone1>' . $value['phone1'] . '</phone1>';
            
            if( isset( $value['phone2'] ) )
                $xml .= '<phone2>' . $value['phone2'] . '</phone2>';
            
            $xml .= '</contact>';
        }
        $xml .= '/<contacts>'; 

        return $xml;    
    }

    /**
     * Makes lines string xml from lines array
     *
     * @param array $lines The lines data.
     * @return string
     */
    protected function _makeLinesXml( $lines)
    {
        $xml = '<lines>';
        foreach( $lines as $key => $value )
        {
            $xml .= '<line>';

            //(Optional)
            if( isset( $value['line_id'] ) )
                        $xml .= '<line_id>' . $value['line_id'] . '</line_id>';

            //(Optional)
            if( isset( $value['amount'] ) )
                $xml .= '<amount>' . $value['amount'] . '</amount>';
            
            //(Optional)
            if( isset( $value['name'] ) )
                $xml .= '<name>' . $value['name'] . '</name>';
            
            //(Optional)
            if( isset( $value['description'] ) )
                $xml .= '<description>' . $value['description'] . '</description>';
            
            //Default is 0
            if( isset( $value['unit_cost'] ) )
                $xml .= '<unit_cost>' . $value['unit_cost'] . '</unit_cost>';
            
            //Default is 0
            if( isset( $value['quantity'] ) )
                $xml .= '<quantity>' . $value['quantity'] . '</quantity>';
            
            //(Optional)
            if( isset( $value['tax1_name'] ) )
                $xml .= '<tax1_name>' . $value['tax1_name'] . '</tax1_name>';
            
            //(Optional)
            if( isset( $value['tax1_percent'] ) )
                $xml .= '<tax1_percent>' . $value['tax1_percent'] . '</tax1_percent>';

            //(Optional)
            if( isset( $value['tax2_name'] ) )
                $xml .= '<tax2_name>' . $value['tax2_name'] . '</tax2_name>';
            
            //(Optional)
            if( isset( $value['tax2_percent'] ) )
                $xml .= '<tax2_percent>' . $value['tax2_percent'] . '</tax2_percent>';

            //One of 'Item' or 'Time'. If omitted, the line's type defaults to 'Item'
            if( isset( $value['type'] ) )
                $xml .= '<type>' . $value['type'] . '</type>';
            
            $xml .= '</line>';
        }
        $xml .= '</lines>';

        return $xml; 
    }

    /**
     * Makes client string xml from params
     *
     * @param array $params The client params.
     * @return string
     */
    protected function _makeClientXml( $params )
    {
        $xml = "";

        if( isset( $params['client_id'] ) )
                $xml .= '<client_id>' . $params['client_id'] . '</client_id>';

        if( isset( $params['first_name'] ) )
            $xml .= '<first_name>' . $params['first_name'] . '</first_name>';

        if( isset( $params['last_name'] ) )
            $xml .= '<last_name>' . $params['last_name'] . '</last_name>';

        if( isset( $params['organization'] ) )
            $xml .= '<organization>' . $params['organization'] . '</organization>';

        if( isset( $params['email'] ) )
            $xml .= '<email>' . $params['email'] . '</email>';

        //Defaults to first name + last name (Optional)
        if( isset( $params['username'] ) )
            $xml .= '<username>' . $params['username'] . '</username>';

        //Defaults to random password (Optional)
        if( isset( $params['password'] ) )
            $xml .= '<password>' . $params['password'] . '</password>';

        //(Optional) 
        if( isset( $params['contacts'] ) )
            $xml .= $this->_makeContactsXml( $params['contacts'] );               
        //(Optional)
        if( isset( $params['work_phone'] ) )
            $xml .= '<work_phone>' . $params['work_phone'] . '</work_phone>';

        //(Optional)
        if( isset( $params['home_phone'] ) )
            $xml .= '<home_phone>' . $params['home_phone'] . '</home_phone>';

        //(Optional) 
        if( isset( $params['mobile'] ) )
            $xml .= '<mobile>' . $params['mobile'] . '</mobile>';

        //(Optional)
        if( isset( $params['fax'] ) )
            $xml .= '<fax>' . $params['fax'] . '</fax>';

        //See language.list for codes. (Optional)
        if( isset( $params['language'] ) )
            $xml .= '<language>' . $params['language'] . '</language>';

        //(Optional)
        if( isset( $params['currency_code'] ) )
            $xml .= '<currency_code>' . $params['currency_code'] . '</currency_code>';

        //(Optional)
        if( isset( $params['notes'] ) )
            $xml .= '<notes>' . $params['notes'] . '</notes>';

        //Primary address (All optional)
        if( isset( $params['primary_address'] ) )
            $xml .= $this->_makeAddressXml( $params['primary_address'], 'priamry' );

        //secondary address (All optional)
        if( isset( $params['secondary_address'] ) )
            $xml .= $this->_makeAddressXml( $params['secondary_address'], 'secondary' );

        //e.g. 'VAT Number' (Optional)
        if( isset( $params['vat_name'] ) )
            $xml .= '<vat_name>' . $params['vat_name'] . '</vat_name>';

        //If set, shown with vat_name under client address (Optional)
        if( isset( $params['vat_number'] ) )
            $xml .= '<vat_number>' . $params['vat_number'] . '</vat_number>';

        return $xml;
    }


    /**
     * Makes invoice string xml from params
     *
     * @param array $params The invoice params.
     * @return string
     */
    protected function _makeInvoiceXml( $params )
    {
        $xml = "";

        //Invoice to update
        if( isset( $params['invoice_id'] ) )
            $xml .= '<invoice_id>' . $params['invoice_id'] . '</invoice_id>';

        //Client being invoiced
        if( isset( $params['client_id'] ) )
            $xml .= '<client_id>' . $params['client_id'] . '</client_id>';

        //(Optional) 
        if( isset( $params['contacts'] ) )
            $xml .= $this->_makeContactsXml( $params['contacts'] );

        //Number, as it appears on the invoice (Optional)
        if( isset( $params['number'] ) )
            $xml .= '<number>' . $params['number'] . '</number>';

        //One of sent, viewed or draft [default]
        if( isset( $params['status'] ) )
            $xml .= '<status>' . $params['status'] . '</status>';

        //If not supplied, defaults to today's date (Optional)
        if( isset( $params['date'] ) )
            $xml .= '<date>' . $params['date']->format( 'Y-m-d' ) . '</date>';
        
        //Purchase order number (Optional)
        if( isset( $params['po_number'] ) )
            $xml .= '<po_number>' . $params['po_number'] . '</po_number>';

        //Percent discount (Optional)
        if( isset( $params['discount'] ) )
            $xml .= '<discount>' . $params['discount'] . '</discount>';

        //Notes (Optional) 
        if( isset( $params['notes'] ) )
            $xml .= '<notes>' . $params['notes'] . '</notes>';

        //Currency Code, defaults to your base currency (Optional)
        if( isset( $params['currency_code'] ) )
            $xml .= '<currency_code>' . $params['currency_code'] . '</currency_code>';

        //Language code, defaults to the client's language; see language.list for codes (Optional)
        if( isset( $params['language'] ) )
            $xml .= '<language>' . $params['language'] . '</language>';

        //Terms (Optional)
        if( isset( $params['terms'] ) )
            $xml .= '<terms>' . $params['terms'] . '</terms>';

        //Return URI (Optional)
        if( isset( $params['return_uri'] ) )
            $xml .= '<return_uri>' . $params['return_uri'] . '</return_uri>';

        //(Optional)
        if( isset( $params['first_name'] ) )
            $xml .= '<first_name>' . $params['first_name'] . '</first_name>';

        //(Optional)
        if( isset( $params['last_name'] ) )
            $xml .= '<last_name>' . $params['last_name'] . '</last_name>';

        //(Optional)
        if( isset( $params['organization'] ) )
            $xml .= '<organization>' . $params['organization'] . '</organization>';

        //Primary address (All optional)
        if( isset( $params['primary_address'] ) )
            $xml .= $this->_makeAddressXml( $params['primary_address'], 'primary' );

        //e.g. 'VAT Number' (Optional)
        if( isset( $params['vat_name'] ) )
            $xml .= '<vat_name>' . $params['vat_name'] . '</vat_name>';

        //If set, shown with vat_name under client address (Optional)
        if( isset( $params['vat_number'] ) )
            $xml .= '<vat_number>' . $params['vat_number'] . '</vat_number>';

        if( isset( $params['lines'] ) )
            $xml .= $this->_makeLinesXml( $params['lines'] );

        return $xml;
    }


    /**
     * Makes recurring string xml from params
     *
     * @param array $params The recurring params.
     * @return string
     */
    protected function _makeRecurringXml( $params )
    {
        $xml = "";

        //Recurring to update
        if( isset( $params['recurring_id'] ) )
            $xml .= '<recurring_id>' . $params['recurring_id'] . '</recurring_id>';

        //Number of invoices to generate; 0 infinite (default 0)
        if( isset( $params['occurrences'] ) )
            $xml .= '<occurrences>' . $params['occurrences'] . '</occurrences>';

        //One of 'weekly', '2 weeks', '4 weeks', 'monthly', '2 months', '3 months', '6 months', 'yearly', '2 years'
        if( isset( $params['frequency'] ) )
            $xml .= '<frequency>' . $params['frequency'] . '</frequency>';

        //Send email notification(Default 1)
        if( isset( $params['send_email'] ) )
            $xml .= '<send_email>' . $params['send_email'] . '</send_email>';

        //Send copy by snail mail (Default 0)
        if( isset( $params['send_snail_mail'] ) )
            $xml .= '<send_snail_mail>' . $params['send_snail_mail'] . '</send_snail_mail>';

        $xml .= $this->_makeInvoiceXml( $params );

        //(Optional)
        if( isset( $params['autobill'] ) )
        {
            $xml .= '<autobill>';
            $autobill = $params['autobill'];
            
            //Case insensitive gateway name from gateway.list (Must be auto-bill capable)
            if( isset( $autobill['gateway_name'] ) )
                $xml .= '<gateway_name>' . $autobill['gateway_name'] . '</gateway_name>';

            if( isset( $autobill['card'] ) )
            {
                $xml .= '<card>';
                $card = $autobill['card'];

                //Can include spaces, hyphens and other punctuation marks
                if( isset( $card['number'] ) )
                    $xml .= '<number>' . $card['number'] . '</number>';

                if( isset( $card['name'] ) )
                    $xml .= '<name>' . $card['name'] . '</name>';

                $xml .= '<expiration>';
                if( isset( $card['expiration']['month'] ) )
                    $xml .= '<month>' . $card['expiration']['month'] . '</month>';

                if( isset( $card['expiration']['year'] ) )
                    $xml .= '<year>' . $card['expiration']['year'] . '</year>';

                $xml .= '</expiration>';
                $xml .= '</card>';
            }
            $xml .= '</autobill>';
        }

        return $xml;
    }
}
