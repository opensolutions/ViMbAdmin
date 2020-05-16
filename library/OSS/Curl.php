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
 * @package    OSS_Countries
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Countries
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Curl
{

    const RFC_1738 = 'RFC 1738';
    const RFC_3986 = 'RFC 3986';

    const HTTP_POST   = 'POST';
    const HTTP_GET    = 'GET';
    const HTTP_DELETE = 'DELETE';


    /**
     * The cURL handler. We need a global variable to store the handle between cURL operations, so we can use persistent connections.
     *
     * @var resource
     */
    private $_curlHandler = null;

    /**
     * Curl method POST or GET
     *
     * @var string
     */
    private $_method = null;

    /**
     * URL for cURL
     *
     * @var string
     */
    private $_url = null;

    /**
     * cURl response
     *
     * @var array
     */
    private $_response = array();


    /**
     * Constructor
     *
     * @param int $connectTimeOut Connection time out, default is 3 seconds.
     * @param int $requestTimeOut Request time out, default is 5 seconds,
     * @param bool $returnTransfer
     * @param string $userName User name if page url requires authorization
     * @param string $password Password if page url requires authorization
     * @return void
     */
    public function __construct( $connectTimeOut = 3, $requestTimeOut = 5, $returnTransfer = true, $userName = '', $password = '' )
    {
        $this->init( $connectTimeOut, $requestTimeOut, $returnTransfer, $userName, $password );
    }


    /**
     * Initialize cURL object
     *
     * @param int $connectTimeOut Connection time out, default is 3 seconds.
     * @param int $requestTimeOut Request time out, default is 5 seconds,
     * @param bool $returnTransfer
     * @param string $userName User name if page url requires authorization
     * @param string $password Password if page url requires authorization
     * @return void
     */
    public function init( $connectTimeOut = 3, $requestTimeOut = 5, $returnTransfer = true, $userName = '', $password = '' )
    {
        $connectTimeOut = abs( (int) $connectTimeOut );
        $requestTimeOut = abs( (int) $requestTimeOut );
        $returnTransfer = (boolean) $returnTransfer;
        $userName = (string) $userName;
        $password = (string) $password;

        $this->_curlHandler = curl_init();

        curl_setopt( $this->_curlHandler, CURLOPT_CONNECTTIMEOUT, $connectTimeOut ); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt( $this->_curlHandler, CURLOPT_TIMEOUT, $requestTimeOut ); // The maximum number of seconds to allow cURL functions to execute.
        curl_setopt( $this->_curlHandler, CURLOPT_RETURNTRANSFER, $returnTransfer ); // if false then curl_exec() return value is simply true or false

        if( $userName != '' )
            $this->login( $userName, $password );
    }

    /**
     * Closing curl object
     *
     * @return void
     */
    public function close()
    {
        curl_close( $this->_curlHandler );
    }


    /**
     * Set login parameters to cURL. If url requires authorization
     *
     * @param string $userName User name if page url requires authorization
     * @param string $password Password if page url requires authorization
     * @return void
     */
    public function login( $userName, $password)
    {
        curl_setopt( $this->_curlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
        curl_setopt( $this->_curlHandler, CURLOPT_USERPWD, "{$userName}:{$password}" );
    }


    /**
     * Set additional options for curl
     *
     * @param string $option Option name
     * @param mixed $value Parameter value
     * @return void
     */
    public function setOption( $option, $value)
    {
        curl_setopt( $this->_curlHandler, $option, $value );
    }


    /**
    * Represents an associative array ('key' => 'value', 'key' => 'value', ...) in an RFC 1738 or 3986 format. It does what http_build_query() does, but
    * http_build_query() only supports the older RFC 1738 format. New versions of PHP will support RFC 3986 natively, probably as of version
    * 5.3.6 and 5.2.11. Check the online PHP documentation and your PHP version. By default this method is also using http_build_query() in
    * RFC 1738 mode.
    *
    * @param array $pArray
    * @param string $pRfc default self::RFC_1738 either RFC_1738 or RFC_3986, if any other value is presented then it is set to RFC_1738
    * @return string
    */
    public static function httpBuildQuery($pArray, $pMode=self::RFC_1738)
    {
        if (in_array($pMode, array( self::RFC_1738, self::RFC_3986)) == false) $pMode=self::RFC_1738;

        if ($pMode == self::RFC_1738) return http_build_query($pArray);

        if ( (is_array($pArray) == false) || (sizeof($pArray) == 0) ) return '';

        $vRetVal = '';

        // urlencode() is RFC 1738 (use that for form data!!), rawurlencode() is RFC 3986
        foreach($pArray as $vKey => $vValue) $vRetVal .= rawurlencode($vKey) . '=' . rawurlencode($vValue) . '&';

        return mb_substr($vRetVal, 0, -1); // no need for the last '&'
    }


    /**
     * Sets method
     *
     * @param string $method One of methods POST, GET, DELETE.
     * @return void
     */
    public function setMethod( $method )
    {
        $method = strtoupper( trim( $method ) );

        switch( $method )
        {
            case self::HTTP_GET:
                $this->_method = self::HTTP_GET;
                curl_setopt( $this->_curlHandler, CURLOPT_CUSTOMREQUEST, self::HTTP_GET );
                break;

            case self::HTTP_POST:
                $this->_method = self::HTTP_POST;
                curl_setopt( $this->_curlHandler, CURLOPT_CUSTOMREQUEST, self::HTTP_POST );
                break;

            case self::HTTP_DELETE:
                $this->_method = self::HTTP_DELETE;
                curl_setopt( $this->_curlHandler, CURLOPT_CUSTOMREQUEST, self::HTTP_DELETE );
                break;

            default:
                $this->_method = self::HTTP_POST;
                curl_setopt( $this->_curlHandler, CURLOPT_CUSTOMREQUEST, self::HTTP_POST );
                break;
        }
    }


    /**
     * Gets method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }


    /**
     * Sets URL
     *
     * @param string $url Requests URL
     * @return void
     */
    public function setUrl( $url )
    {
        $this->_url = trim( $url );
        curl_setopt( $this->_curlHandler, CURLOPT_URL, $url );

        if( strpos( strtolower( $url ), 'https://' ) !== false )
            curl_setopt( $this->_curlHandler, CURLOPT_SSL_VERIFYPEER, 0 );
    }


    /**
     * Gets URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }


    /**
     * Set data
     *
     * Please note that it overwrites (extends with data) the URL in GET requests.
     *
     *
     * @param array $data
     * @return void
     */
    public function setData( $data )
    {
        // convert $pData to string if it is array or object, that step takes care of urlencode()-ing, too
        if( $data === null )
            $data = ''; // IMPORTANT!

        if( is_object( $data ) )
            $pData = OSS_Array::objectToArray( $data );

        if( is_array( $data ) )
            $data = self::httpBuildQuery( $data );

        //OSS_Debug::prr( $pData ); die();

        if( $this->getMethod() == self::HTTP_POST )
        {
            // we need that even if there is no data to make cURL to create the "Content-Type: application/x-www-form-urlencoded" header for us
            if( is_string( $data ) )
                curl_setopt( $this->_curlHandler, CURLOPT_POSTFIELDS, $data );
        }
        elseif( $this->getMethod() == self::HTTP_GET )
        {
            if( is_string( $data ) )
                curl_setopt( $this->_curlHandler, CURLOPT_URL, $this->getUrl() . '?' . $data );
        }
    }


    /**
    * Sends a request to the the specified URL and returns with the response.
    *
    * If $pData is not empty in a GET request, then it will be appended to the URL. In this case arrays are passed through http_build_query() first
    * (so the values are urlencode()-ed), strings just simply appended to the URL. Append means the function will add the leading '?', too.
    *
    * @param string $pUrl
    * @param string|array|object|null $pData arrays values and object properties are automatically urlencode()-ed, otherwise you have to do it yourself if you pass a string
    * @param string $pMethod default self::HTTP_POST it can be HTTP_GET, HTTP_POST or HTTP_DELETE, any other value will be replaced by HTTP_POST; case insensitive
    * @param int $pResponseCode the HTTP response code is placed in this parameter
    * @return string|boolean a response string if $pReturnTransfer was true in init(), otherwise true on success or false on error
    */
    public function execute( $pUrl, $pData, $pMethod=self::HTTP_POST, &$pResponseCode )
    {
        $this->setUrl( $pUrl ); // we set this as default, setData() may overwrite it
        $this->setMethod( $pMethod );

        if( $pData )
            $this->setData( $pData );

        // debugging
        //$f = fopen('cabcall/var/tmp/abc.txt', 'wt');
        //curl_setopt($this->_curlHandler, CURLOPT_WRITEHEADER, $f);
        //curl_setopt($this->_curlHandler, CURLOPT_HEADER, 1);
        //curl_setopt($this->_curlHandler, CURLOPT_VERBOSE, 1);

        $vResult = curl_exec( $this->_curlHandler );
        $this->_response = curl_getinfo( $this->_curlHandler );

        //var_dump( $this->_response );
        //var_dump( $vResult );

        $pResponseCode = $this->_response['http_code'];

        return $vResult;
    }

    /**
     * Gets response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Gets response
     *
     * @return string
     */
    public function getErrorCode()
    {
        return curl_errno( $this->_curlHandler );
    }

    /**
     * Gets error message
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return curl_error( $this->_curlHandler );
    }

    /**
     * Gets error message
     *
     * @return string
     */
    public function getError()
    {
        return $this->getErrorCode() . ' ' . $this->getErrorMsg();
    }

}
