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
 * @package    OSS_PaymentProcessor
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_PaymentProcessor
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_PaymentProcessor_Realex extends OSS_PaymentProcessor_BaseProcessor
{
    const CARD_TYPE_VISA         = 'visa';
    const CARD_TYPE_LASER        = 'laser';
    const CARD_TYPE_MASTERCARD   = 'mastercard';

    /**
    * Valid credit/debit card types
    * @var array
    */
    public static $CARD_TYPES = [
        self::CARD_TYPE_VISA => 'visa',
        Self::CARD_TYPE_LASER => 'laser',
        self::CARD_TYPE_MASTERCARD => 'mc'
    ];

    /**
     * An error code to indicate if the response hash is invalid
     *
     * @var integer An error code to indicate if the response hash is invalid
     */
    const ERR_RESPONSE_INVALID_HASH = 905;

    /**
     * An array of error strings for the ERR_RESPONSE... codes
     * @var array Array of error strings for the ERR_RESPONSE... codes
     */
    public static $ERR_TEXT = array(
        self::ERR_RESPONSE_INVALID_HASH => 'Response security hash invalid'
    );

    /**
     * An array of the mandatory application.ini parameters.
     *
     * @var array The mandatory application.ini parameters
     */
    public static $REQUIRED_PARAMS = [
        'cgi_url', 'user_agent', 'merchant_id', 'merchant_secret', 'account', 'refund_password'
    ];

    /**
     * An array of the optional application.ini parameters (defaults set with their definitions).
     *
     * @var array The optional application.ini parameters
     * @see $_cgi_timeout
     * @see $_fake_transactions
     * @see $_keep_request_xml
     */
    public static $OPTIONAL_PARAMS = [ 'fake_transactions', 'cgi_timeout', 'keep_request_data' ];

    /**
     * The Doctrine2 entity manager
     * @var EntityManager The Doctrine2 entity manager
     */
    private $_d2em = null;

    /**
     * The CGI Url
     * @var string The CGI Url
     */
    private $_cgi_url;

    /**
     * @var string The user agent to report to the CGI
     */
    private $_user_agent;

    /**
     * @var string Our assigned merchant ID
     * @see getMerchantId()
     */
    private $_merchant_id;

    /**
     * @var string Our assigned shared secret
     */
    private $_merchant_secret;

    /**
     * @var string Our assigned refund password
     */
    private $_refund_password;

    /**
     * @var string The account to use for receipt-in / receipt-out
     */
    private $_account;

    /**
     * @var int The maximum number of seconds for the cURL operation
     */
    private $_cgi_timeout = 10;

    /**
     * For security reasons, we clear the request data from the database after a sucessful
     * Realex API/CGI call.
     *
     * Some transactions require non-stored data (e.g. credit card number) to replay a failed
     * transaction (such as createCreditCard()) which we will store and delete when we
     * successfully replay the transaction. Setting this to true means we don't remove it.
     *
     * <strong>WARNING:</strong> Set to true only for testing!
     *
     * @var bool Whether we should clear the request data or not
     */
    private $_keep_request_data = false;

    /**
     * Fake transactions allows all operations to appear to succeed including
     * updates to local database tables but it never contacts Realex and fakes
     * all operations.
     *
     * @var bool Fake all transactions and never contact Realex
     * @see _completeFakeTransaction()
     */
    private $_fake_transactions = false;

    /**
     * An instance of OSS_Log for logging.
     *
     * @var OSS_Log An instance of OSS_Log for logging.
     */
    private $_logger = null;


    /**
     * Realex Payment gateway processor
     *
     * @param array $config An associated array of realex.* parameters from application.ini
     * @param OSS_Log $logger An optional instance of an OSS_Log object if you want logging
     * @throws OSS_PaymentProcessor_Realex_Exception
     */
    public function __construct( array $config, OSS_Log $logger = null )
    {
        // check for required parameters and set member variables accordingly
        foreach( self::$REQUIRED_PARAMS as $param )
        {
            if( !isset( $config["{$param}"] ) )
            {
                throw new OSS_PaymentProcessor_Realex_Exception(
                    OSS_PaymentProcessor_Realex_Exception::ERR_REQUIRED_PARAMETER_MISSING . ' - ' . $param
                );
            }

            $member = "_{$param}";

            $this->$member = $config[ $param ];
        }

        // check for optional parameters and set member variables accordingly
        foreach( self::$OPTIONAL_PARAMS as $param )
        {
            if( isset( $config[ "{$param}" ] ) )
            {
                $member = "_{$param}";
                $this->$member = $config[ $param ];
            }
        }

        $this->_logger = $logger;
    }

    /**
     * Access method for the merchant ID
     *
     * @return string The merchant ID
     * @see $_merchant_id
     */
    protected function getD2EM()
    {
        if( !$this->_d2em )
            $this->_d2em = Zend_Registry::get( "d2em" )[ 'default' ];

        return $this->_d2em;
    }


    /**
     * Access method for the merchant ID
     *
     * @return string The merchant ID
     * @see $_merchant_id
     */
    protected function getMerchantId()
    {
        return $this->_merchant_id;
    }


    /**
     * Access method for the merchant secret
     *
     * @return string The merchant secret
     * @see $_merchant_secret
     */
    protected function getMerchantSecret()
    {
        return $this->_merchant_secret;
    }


    /**
     * Access method for the refund password
     *
     * @return string The refund password
     * @see $_refund_password
     */
    protected function getRefundPassword()
    {
        return $this->_refund_password;
    }


    /**
     * Returns with a YYYYmmddhhmmss format timestamp used in Realex transactions.
     *
     * @return string YYYYmmddhhmmss formated timestamp
     */
    static public function getTimeStamp()
    {
        return date( 'YmdHis' );
    }


    /**
     * Log a message if we have an instance of a logger
     *
     * @param string $msg The log message
     * @param int $pri The priority of the message (defaults: OSS_Log::DEBUG)
     */
    private function _log( $msg, $pri = OSS_Log::DEBUG )
    {
        if( $this->_logger instanceof OSS_Log )
            $this->_logger->log( $msg, $pri );
    }

    /**
     * Create a unique Realex order id.
     *
     * Every Realex transaction must have a unique order id. We use the integer primary
     * key of the \Entities\RealexTransaction table (which records all Realex transactions)
     * for this.
     *
     * @param \Entities\RealexTrasaction $rtrans An instance of a save()'d object
     * @return string The unique transaction ID
     * @see \Entities\RealexTransaction
     * @throws OSS_PaymentProcessor_Realex_Exception
     */
    static public function createOrderId( $rtrans )
    {
        if( !( $rtrans instanceof \Entities\RealexTransaction ) )
            throw new OSS_PaymentProcessor_Realex_Exception( OSS_PaymentProcessor_Realex_Exception::ERR_BAD_OBJECT_FOR_REF );

        // Realex has a limitation of a maximum of 40 characters. I don't think this'll be an issue...
        return 'O_' . $rtrans->getId();
    }


    /**
     * Creates a unique Realex payer reference ID
     *
     * All payers that we create must be identifiable by us with a unique ID. For this we
     * use the integer primary key from the \Etnities\Customer object.
     *
     * Later this ID can be used in RealEFT transactions for recurring payments, etc.
     *
     * @param \Entities\Customer $customer The instance of the payer's \Entities\Customer object
     * @return string The unique payer ID
     */
    static public function createPayerRef( $customer )
    {
         if( !( $customer instanceof \Entities\Customer ) )
            throw new OSS_PaymentProcessor_Realex_Exception( OSS_PaymentProcessor_Realex_Exception::ERR_BAD_OBJECT_FOR_REF );

        // maximum 50 characters
        return 'P_' . $customer->getId();
    }


    /**
     * Created a unique Relaex credit card reference ID
     *
     * All cards added to the Realex store (linked to a payer) must be
     * uniquely identifiable with a unique key. We use the integer primary
     * key from the \Entities\RealexCard table.
     *
     * These cards are added to payers in the Realex system.
     *
     * @param \Entities\RealexCard $card The instance of the \Entities\RelaexCard object
     * @return string The unqiue card ID
     * @see OSS_PaymentProcessor_Realex::createCreditCardRef()
     */
    static public function createCardRef( $card )
    {
         if( !( $card instanceof \Entities\RealexCard ) )
            throw new OSS_PaymentProcessor_Realex_Exception( OSS_PaymentProcessor_Realex_Exception::ERR_BAD_OBJECT_FOR_REF );

        // maximum 30 characters
        return 'C_' . $card->getId();
    }


    /**
     * Sends an XML formatted request to Realex using cURL
     *
     * This function does the heavy lifting for sending a request to
     * Realex and parsing and processing the reponse. Specifically:
     *
     *  - creates the cURL object (and throws an exception if it cannot)
     *  - sends the request to Realex
     *
     *  If the request fails:
     *   - updates the $rtrans object as STATE_FAILED or STATE_TIMEOUT
     *   - returns false
     *
     *  If the request succeeds:
     *   - parses and processes the response (@see _parseResponse())
     *   - returns true
     *
     * @param \Entities\RealexTransaction $rtrans The instance of \Entities\RealexTransaction to send to Realex
     * @param string $reqXML The XML request package to send to Realex
     * @return bool True if the API/CGI request succeeded (IT DOES NOT MEAN THE REALEX TRANSACTION SUCCEEDED!)
     * @throws OSS_PaymentProcessor_Realex_Exception
     */
    private function _sendRequest( $rtrans, $reqXML )
    {
        $curl = @curl_init();

        if( $curl === false )
            throw new OSS_PaymentProcessor_Realex_Exception( OSS_PaymentProcessor_Realex_Exception::ERR_CURL_INSTANTIATION_FAILED );

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::_sendRequest() - cURL initialised" );

        @curl_setopt( $curl, CURLOPT_URL,            $this->_cgi_url     );
        @curl_setopt( $curl, CURLOPT_POST,           1                   );
        @curl_setopt( $curl, CURLOPT_USERAGENT,      $this->_user_agent  );
        @curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1                   );
        @curl_setopt( $curl, CURLOPT_POSTFIELDS,     $reqXML             );
        @curl_setopt( $curl, CURLOPT_TIMEOUT,        $this->_cgi_timeout );

        $respXML = @curl_exec( $curl );

        if( $respXML === false )
        {
            if( @curl_getinfo( $curl, CURLINFO_TOTAL_TIME ) >= $this->_cgi_timeout )
            {
                $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::_sendRequest() - cURL request timeout.", OSS_Log::ALERT );
                $rtrans->setState( \Entities\RealexTransaction::STATE_TIMEOUT );
            }
            else
            {
                $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::_sendRequest() - cURL request failed.", OSS_Log::ALERT );
                $rtrans->setState( \Entities\RealexTransaction::STATE_FAILED );
            }

            $rtrans->setUpdated( new \DateTime() );
            $this->getD2EM()->flush();

            @curl_close( $curl );
            return false;
        }

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::_sendRequest() - cURL request executed. Response:\n\n{$respXML}\n\n" );
        @curl_close( $curl );

        $this->_parseResponse( $respXML, $rtrans );

        return true;
    }


    /**
     * Parse the XML response from Realex and update the transaction object
     *
     * This function:
     *
     *  - transforms the XML response to an array
     *  - validates the response hash
     *  - updates the authcode, pasref, state and response fields of the \Entities\RealexTransaction object
     *
     * @param string $xml The XML response from Realex for processing
     * @param \Entities\RealexTransaction $rtrans An instance of the \Entities\RealexTransaciton object
     * @return \Entities\ReleaxTransaction The updated $rtrans object for fluent interface
     * @throws OSS_PaymentProcessor_Realex_Receipt_Exception
     */
    private function _parseResponse( $xml, $rtrans )
    {
        $resp = OSS_Array::objectToArray( OSS_Utils::parseXML( $xml ) );

        $rtrans->setResult( $this->_checkResponse( $resp ) );

        if( isset( $resp['authcode'] ) )
            $rtrans->setAuthcode( $resp['authcode'] );

        //if( isset( $resp['pasref'] ) )
           // $rtrans['pasref'] = $resp['pasref'];

        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::_parseResponse() - Realex result: {$rtrans->getResult()}" );

        //FIXME: Barry
        if( $rtrans->isSuccessful() && !$this->_keep_request_data )
            $rtrans->setRequest( '' );

        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        //FIXME Transaction stats.
        //if( !CreditcardTransactionStatsTable::update( $cctrans['request_type'], $cctrans['result'], $cctrans['amount'] ) )
            //$this->_log( "CreditcardTrasactionStatTable::update() failed for [CCTRANS: {$cctrans['id']}].", OSS_Log::ALERT );

         if( in_array( $rtrans->getRequestType(), array( 'receipt-in', 'payment-out' ) ) && !$rtrans->isSuccessful() )
            throw new OSS_PaymentProcessor_Realex_Receipt_Exception( $rtrans, $resp['message'] );

        return $rtrans;
    }


    /**
     * Takes a Realex response and returns with its response code after additional checks
     *
     * Outside of the possible checks that Realex performs, we need to allow for one more: an invalid
     * hash of the received XML response indicated a bad transmission / man in the middle attack.
     *
     * @param string $resp The parsed Realex XML response as an array
     * @param string The response code (NB: STRING)
     */
    private function _checkResponse( $resp )
    {
        /* WARNING: This part of the Realex response is kind of messy, because the 'sha1hash' field is either present or not, so
        we cannot depend on it's existence. If the code below ( most likely in OSS_PaymentProcessor_Realex_Hash::response() ) still
        fails, then use this instead:

        if( $resp['result'] != '00' )
            return $resp['result'];

        if( OSS_PaymentProcessor_Realex_Hash::response( $resp, $this->_merchant_secret ) != $resp['sha1hash'] )
            return self::ERR_RESPONSE_INVALID_HASH; // Response Authentication Failed
        */

        if( isset( $resp['sha1hash'] ) && ( OSS_PaymentProcessor_Realex_Hash::response( $resp, $this->_merchant_secret ) != $resp['sha1hash'] ) )
            return self::ERR_RESPONSE_INVALID_HASH; // Response Authentication Failed

        return $resp['result'];
    }


    /**
     * Set fake CreditcardTransactions entries for a complete fake transaction
     *
     * If <var>$this->_fake_transactions</var> is set to true, the system will
     * not perform any Realex requests but <em>fake</em> the transactions in
     * the database meaning we:
     *
     *  - set <var>is_fake</var> to true
     *  - set <var>state</var> to STATE_COMPLETE
     *  - set <var>pasref</var> and <var>authcode</var> to <code>FAKETRANS</var>
     *
     * @param \Entities\RealexTransaction $rtrans The transaction object to create the fake entries in
     * @return \Entities\RealexTransaction The modified (and save()'d) transaction object
     */
    private function _completeFakeTransation( $rtrans )
    {
        $rtrans->setIsFake( true );
        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        if( !$this->_keep_request_data )
            $rtrans->setRequest( '' );

        //$cctrans['pasref']    = 'FAKETRANS';  //FIXME Pasref
        $rtrans->setAuthcode( 'FAKETRANS' );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setResult( '00' );

        $this->getD2EM()->flush();

        //FIXME
        //if( !CreditcardTransactionStatsTable::update( $cctrans['request_type'], $cctrans['result'], $cctrans['amount'] ) )
            //$this->_log( "CreditcardTrasactionStatTable::update() failed for[CCTRANS: {$cctrans['id']}].", OSS_Log::ALERT );

        return $rtrans;
    }

    /**
     * Creates a new "payer" in Realex's systems.
     *
     * Sends a 'payer-new' request to Realex which creates a new payer at Realex
     * which later can be used by RealEFT for recurring payments ('receipt-in').
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * It doesn't matter if this transaction fails. All the information we need is in
     * the database. The end user does not want to know about it. We'll pick up
     * unsuccessful 'payer-new' in offline processing. Any serious / critical issues
     * will have thrown an exception.
     *
     * An example usage is:
     *
     * <code>
     *     $payer = $this->getD2Em()->getRepostory( '\\Entities\\RealexPayer' )->find( $id );
     *     ...
     *     $rtrans = $this->getPaygate()->newPayer( $payer );
     *
     *     // if we're interested in the Realex result then:
     *     if( $rtrans->isSuccessful() )
     *         // do something
     * </code>
     *
     * @param \Entities\Realex $payer The payer who will be associated as payer in Realex
     * @return \Entities\RealexTransaction The resultant \Entities\RealexTransaction object
     * @throws OSS_PaymentProcessor_Exception
     */
    public function newPayer( $payer )
    {
        $timestamp  = $this->getTimeStamp();

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setPayer( $payer);
        $rtrans->setRequestType( 'payer-new' );
        $rtrans->setAccount( $this->_account );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );

        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newPayer() - transaction set to STATE_INIT" );

        // we're about to create a Realex payer which means the paygate state should be none
        if( $payer->getState() != \Entities\RealexPayer::STATE_NONE )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $orderId  = self::createOrderId( $rtrans );
        $payerRef = $payer->getPayerref();
        $hash     = OSS_PaymentProcessor_Realex_Hash::payer( $timestamp, $this->getMerchantId(), $orderId, $payerRef, $this->getMerchantSecret() );

        $reqXML =  "<request type='payer-new' timestamp='{$timestamp}'>
                        <merchantid>{$this->getMerchantId()}</merchantid>
                        <orderid>{$orderId}</orderid>
                        <payer type='subscriber' ref='{$payerRef}'>
                            <firstname>{$payer->getFirstname()}</firstname>
                            <surname>{$payer->getLastname()}</surname>
                        </payer>
                        <sha1hash>{$hash}</sha1hash>
                    </request>";

                    //FIXME: payer first name and last name

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newPayer() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newPayer() - faking transaction" );
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }
        
        $this->_sendRequest( $rtrans, $reqXML );

        if( $rtrans->isSuccessful() )
        {
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }


    /**
     * Creates a new "credit card" in Realex's systems.
     *
     * Sends a 'card-new' request to Realex which creates a new credit card at Realex
     * which later can be used by RealEFT for recurring payments ('receipt-in').
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * It doesn't matter if this transaction fails. All the information we need is in
     * the database. The end user does not want to know about it. We'll pick up
     * unsuccessful 'card-new' in offline processing. Any serious / critical issues
     * will have thrown an exception.
     *
     * @param \Entities\RealexCard $card The credit card object to add to Realex
     * @param string $cardNumber the credit card number, any non digit character is removed from it in the method
     * @return \Entities\RealexTransaction The resultant \Entities\RealexTransaction object
     * @throws OSS_PaymentProcessor_Exception
     */
    public function newCard( $card )
    {
        // we need a payer to add a credit card to
        if( $card->getPayer()->getState() == \Entities\RealexPayer::STATE_NONE )
            $this->newPayer( $card->getPayer() );

        $rqTimeStamp = $this->getTimeStamp();

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setRequestType( 'card-new' );
        $rtrans->setPayer( $card->getPayer() );
        $rtrans->setCard( $card );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setAccount( $this->_account );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );
        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newCard() - transaction set to STATE_INIT" );

        // we're about to create a Realex credit card which means the paygate state should be none
        if( $card->getState() != \Entities\RealexCard::STATE_NONE )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $rqOrderId  = self::createOrderId( $rtrans );
        $cardRef    = $card->getCardref();
        $payerRef   = $card->getPayer()->getPayerref();
        $expiryDate = $card->getValidTo()->format( "my");
        $cardType   = $this->getCardType( $card->getType() );
        $rqHash     = OSS_PaymentProcessor_Realex_Hash::creditCard(
            $rqTimeStamp,
            $this->getMerchantId(),
            $rqOrderId,
            $payerRef,
            $card->getHolder(),
            $card->getNumber(),
            $this->getMerchantSecret()
        );

        $reqXML = "<request type='card-new' timestamp='{$rqTimeStamp}'>
                       <merchantid>{$this->getMerchantId()}</merchantid>
                       <orderid>{$rqOrderId}</orderid>
                       <card>
                           <ref>{$cardRef}</ref>
                           <payerref>{$payerRef}</payerref>
                           <number>{$card->getNumber()}</number>
                           <expdate>{$expiryDate}</expdate>
                           <chname>{$card->getHolder()}</chname>
                           <type>{$cardType}</type>
                           </card>
                       <sha1hash>{$rqHash}</sha1hash>
                   </request>";

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setRequest( $card->getNumber() );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newCard() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::newCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML );

        if( $rtrans->isSuccessful() )
        {
            $card->setState( \Entities\RealexCard::State_INSYNC );
            $card->setNumber( substr( $card->getNumber(), 0, 4 ) . '...' . substr( $card->getNumber(), -3 ) );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }

    /**
     * Creates a new "payment" in Realex's systems.
     *
     * Sends a 'receipt-in' request to Realex which creates a new payment at Realex.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * @param \Entities\RealexCard $card
     * @param int|float $amount the amount to be withdrawn from the card, in the biggest unit of the currency,
     *    e.g. in euro or dollar and not in cent, then conversion is made inside the method
     * @return \Entities\RealexTransaction The resultant \Entities\RealexTransaction object
     * @throws OSS_PaymentProcessor_Exception
     */
    public function receiptIn( $card, $amount )
    {
        $rqTimeStamp = $this->getTimeStamp();

        // we're about to perform payment which means that the cerdit card should exist
        if( !( $card instanceof \Entities\RealexCard ) )
        {
            $this->_log( "PROG ERROR - expecting instance of \Entities\RealexCard", OSS_Log::ERR );
            throw new OSS_PaymentProcessor_Exception( 'System Failure - our technical staff have been notified' );
        }

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setRequestType( 'receipt-in' );
        $rtrans->setPayer( $card->getPayer() );
        $rtrans->setCard( $card );
        $rtrans->setAmount( $amount );
        $rtrans->setAccount( $this->_account );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );
        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::receiptIn() - transaction set to STATE_INIT" );

        // we're about to perform payment which means that the cerdit card paygate state should be insync
        if( $card->getState() != \Entities\RealexCard::STATE_INSYNC )
        {
            $rtrans->setResult( 999 );
            throw new OSS_PaymentProcessor_Realex_Receipt_Exception( $rtrans, OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );
        }

        $amount    = (int) ( $amount * 100 );
        $rqOrderId = self::createOrderId( $rtrans );
        $payerRef  = $card->getPayer()->getPayerref();
        $cardRef   = $card->getCardref();
        $rqHash    = OSS_PaymentProcessor_Realex_Hash::payment(
                        $rqTimeStamp,
                        $this->getMerchantId(),
                        $rqOrderId,
                        $amount,
                        'EUR',
                        $payerRef,
                        $this->getMerchantSecret()
                    );

        $reqXML = "<request type='receipt-in' timestamp='{$rqTimeStamp}'>
                      <merchantid>{$this->getMerchantId()}</merchantid>
                      <account>{$this->_account}</account>
                      <orderid>{$rqOrderId}</orderid>
                      <amount currency='EUR'>{$amount}</amount>
                      <payerref>{$payerRef}</payerref>
                      <paymentmethod>{$cardRef}</paymentmethod>
                      <autosettle flag=\"1\" />
                      <sha1hash>{$rqHash}</sha1hash>
                  </request>";

        $rtrans->setRequest( $amount );
        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::receiptIn() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::receiptIn() - faking transaction" );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML ); // can throw OSS_PaymentProcessor_Realex_Receipt_Exception()

        return $rtrans;
    }


    /**
     * Creates a new "refund" in Realex's systems.
     *
     * Sends a 'receipt-out' request to Realex which creates a new payment at Realex.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * @param \Entities\RealexCard $card
     * @param int|float $amount the amount to be withdrawn from the card, in the biggest unit of the currency,
     *    e.g. in euro or dollar and not in cent, then conversion is made inside the method
     * @return CreditcardTransaction The resultant CreditcardTransaction object
     * @throws OSS_PaymentProcessor_Exception
     */
    public function paymentOut( $card, $amount )
    {
        $rqTimeStamp = $this->getTimeStamp();

        // we're about to perform payment which means that the cerdit card should exist
        if( !( $card instanceof Creditcard ) )
        {
            $this->_log( "PROG ERROR - expecting instance of Creditcard", OSS_Log::ERR );
            throw new OSS_PaymentProcessor_Exception( 'System Failure - our technical staff have been notified' );
        }

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setRequestType( 'payment-out' );
        $rtrans->setPayer( $card->getPayer() );
        $rtrans->getcard( $card );
        $rtrans->setAmount( $amount );
        $rtrans->setAccount( $this->_account );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );
        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::paymentOut() - transaction set to STATE_INIT" );

        // we're about to perform payment which means that the cerdit card paygate state should be insync
        if( $card->etState() != \Entities\RealexCard::STATE_INSYNC )
        {
            $rtrans->setResult( 999 );
            throw new OSS_PaymentProcessor_Realex_Receipt_Exception( $rtrans, OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );
        }

        $amount    = (int) ( $amount * 100 );
        $rqOrderId = self::createOrderId( $rtrans );
        $payerRef  = $card->getPayer()->getPayerref();
        $cardRef   = $card->getCardref();
        $rqHash    = OSS_PaymentProcessor_Realex_Hash::payment(
                        $rqTimeStamp,
                        $this->getMerchantId(),
                        $rqOrderId,
                        $amount,
                        'EUR',
                        $payerRef,
                        $this->getMerchantSecret()
        );

        $refundHash = OSS_PaymentProcessor_Realex_Hash::refund( $this->getRefundPassword(), $this->getMerchantSecret() );

        $reqXML ="<request type='payment-out' timestamp='{$rqTimeStamp}'>
                      <merchantid>" . $this->getMerchantId() . "</merchantid>
                      <account>{$this->_account}</account>
                      <orderid>{$rqOrderId}</orderid>
                      <amount currency='EUR'>{$amount}</amount>
                      <payerref>{$payerRef}</payerref>
                      <paymentmethod>{$cardRef}</paymentmethod>
                      <sha1hash>{$rqHash}</sha1hash>
                      <refundhash>{$refundHash}</refundhash>
                  </request>";

        $rtrans->setRequest( $amount );
        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::paymentOut() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::paymentOut() - faking transaction" );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML ); // can throw OSS_PaymentProcessor_Realex_Receipt_Exception()

        return $rtrans;
    }

    /**
     * Updates a "payer" in Realex's systems.
     *
     * Sends a 'edit-payer' request to Realex which updates payer data in Realex.
     *
     * This also logs the transaction into the \Entities\ReaelxTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * It doesn't matter if this transaction fails. All the information we need is in
     * the database. The end user does not want to know about it. We'll pick up
     * unsuccessful 'edit-payer' in offline processing. Any serious / critical issues
     * will have thrown an exception.
     *
     * @param \Entities\RealexPayer $payer
     * @return boolean|int the result (true on success, otherwise integer on error)
     */
    public function editPayer( $payer )
    {
        $rqTimeStamp = $this->getTimeStamp();

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setPayer( $payer );
        $rtrans->setRequestType( 'payer-edit' );
        $rtrans->setAccount( $this->_account );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );
        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::editPayer() - transaction set to STATE_INIT" );

        // we're about to update a Realex payer which means the paygate state should be dirty
        if( $payer->getState() != \Entities\RealexPayer::STATE_DIRTY )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $payerRef  = $payer->getPayerref();
        $rqOrderId = self::createOrderId( $rtrans );
        $rqHash    = OSS_PaymentProcessor_Realex_Hash::payer( $rqTimeStamp, $this->getMerchantId(), $rqOrderId, $payerRef, $this->getMerchantSecret() );

        $reqXML = "<request type='payer-edit' timestamp='{$rqTimeStamp}'>
                        <merchantid>{$this->getMerchantId()}</merchantid>
                        <orderid>{$rqOrderId}</orderid>
                        <payer type='subscriber' ref='{$payerRef}'>
                            <firstname>{$payer->getFirstname()}</firstname>
                            <surname>{$payer->getLastname()}</surname>
                        </payer>
                        <sha1hash>{$rqHash}</sha1hash>
                    </request>";
                //FIXME: Firstname Lastname

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::editPayer() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::editPayer() - faking transaction" );
            $payer->SetState( \Entities\RealexPayer::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML );

        if( $rtrans->isSuccessful() )
        {
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }

    /**
     * Updates a "credit card" in Realex's systems.
     *
     * Sends a 'update-card' request to Realex which updates credit card data in Realex.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * It doesn't matter if this transaction fails. All the information we need is in
     * the database. The end user does not want to know about it. We'll pick up
     * unsuccessful 'edit-payer' in offline processing. Any serious / critical issues
     * will have thrown an exception.
     *
     * @param \Entities\RealexCard $card
     * @return boolean|int true on success, otherwise an error code
     */
    public function updateCard( $card )
    {
        $rqTimeStamp = $this->getTimeStamp();

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setPayer( $card->getPayer() );
        $rtrans->setCard( $card );
        $rtrans->setRequestType( 'card-update-card' );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setAccount( $this->_account );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );
        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::updateCard() - transaction set to STATE_INIT" );

        // we're about to update a Realex credit card which means the paygate state should be dirty
        if( $card->getState() != \Entities\RealexCard::STATE_DIRTY )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $expiryDate = $expiryDate = $card->getValidTo()->format( "my" );
        $cardRef    = $card->getCardref();
        $payerRef   = $card->getPayer()->getPayerref();
        $cardType   = $this->getCardType( $card->getType() );
        $rqHash     = OSS_PaymentProcessor_Realex_Hash::updateCreditCard(
                            $rqTimeStamp,
                            $this->getMerchantId(),
                            $payerRef,
                            $cardRef,
                            $expiryDate,
                            $this->getMerchantSecret()
        );

        $reqXML = "<request type='card-update-card' timestamp='{$rqTimeStamp}'>
                       <merchantid>{$this->getMerchantId()}</merchantid>
                       <card>
                           <ref>{$cardRef}</ref>
                           <payerref>{$payerRef}</payerref>
                           <chname>{$card->getHolder()}</chname>
                           <expdate>{$expiryDate}</expdate>
                           <type>{$cardType}</type>
                       </card>
                       <sha1hash>{$rqHash}</sha1hash>
                   </request>";

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::updateCard() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::updateCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML );

        if( $rtrans->isSuccessful() )
        {
            $card->stateState( \Entitites\RealexCard::STATE_INSYNC );
            $this->getD2EM()->flush();
        }

        return $rtrans;

    }

    /**
     * Removes a "card" from Realex's systems.
     *
     * Sends a 'card-cancel-card' request to Realex which remove credit card from Realex.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Realex request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * @param \Entities\RealexCard $card
     * @return CreditcardTrasaction $rtrans
     */
    public function cancelCard( $card )
    {
        $rqTimeStamp = $this->getTimeStamp();

        $rtrans = new \Entities\RealexTransaction();
        $rtrans->setPayer( $card->getPayer() );
        $rtrans->setCard( $card );
        $rtrans->setRequestType( 'card-cancel-card' );
        $rtrans->setState( \Entities\RealexTransaction::STATE_INIT );
        $rtrans->setAccount( $this->_account );
        $rtrans->setRequest( "" );
        $rtrans->setCreated( new \DateTime() );
        $rtrans->setUpdated( new \DateTime() );
        $rtrans->setIsFake( 0 );

        if( $card->getState() == \Entities\RealexCard::STATE_NONE )
        {
            $rtrans->setResult( '00' );
            $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

            return $rtrans;
        }

        $this->getD2EM()->persist( $rtrans );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::cancelCard() - transaction set to STATE_INIT" );

        $cardRef  = $card->getCardref();
        $payerRef = $card->getPayer()->getPayerref();
        $rqHash   = OSS_PaymentProcessor_Realex_Hash::removeCreditCard( $rqTimeStamp, $this->getMerchantId(), $payerRef, $cardRef, $this->getMerchantSecret() );

        $reqXML = "<request type='card-cancel-card' timestamp='{$rqTimeStamp}'>
                       <merchantid>{$this->getMerchantId()}</merchantid>
                           <card>
                           <ref>{$cardRef}</ref>
                           <payerref>{$payerRef}</payerref>
                           <chname>{$card->getHolder()}</chname>
                       </card>
                       <sha1hash>{$rqHash}</sha1hash>
                   </request>";

        $rtrans->getState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::cancelCard() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Realex::cancelCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_NONE );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML );

        if( $rtrans->isSuccessful() )
        {
            $card->setState( \Entities\RealexCard::STATE_NONE );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }


    /**
     * Returns with the realEx code for credit card type.
     *
     * @param string $pCardType see Creditcard::$CREDIT_CARD_TYPES array keys
     * @return string
     */
    public function getCardType( $cardType )
    {
        return isset( self::$CARD_TYPES[ mb_strtolower( trim( $cardType ) ) ] ) ? self::$CARD_TYPES[ mb_strtolower( trim( $cardType ) ) ] : false;
    }

    /**
     * Takes money from "payer" in Realex's systems.
     *
     * It's wrapper function for receiptIn function. It takes three parameters. Payer from ho
     * it will take money, the amount of money and arrow to transaction object which will be
     * created in receiptIn function.
     *
     * function will return true or false, depends on transaction status.
     *
     * An example usage is:
     *
     * <code>
     *     $rtrans = null;
     *     $payer = $this->getD2Em()->getRepostory( '\\Entities\\RealexPayer' )->find( $id );
     *     ...
     *
     *     if( $this->getPaygate()->getMoneyFromCustomer( $payer, 100, $rtrans ) )
     *         // do something
     *     else
     *          $result = $rtrans->getResult(); //for other actions.
     * </code>
     *
     * @param \Entities\RealexPayer $payer The payer to take money
     * @param int|float $amonut The amount of money to transfer
     * @param mixed &$rtrans Arrow to new \Entities\RealexTransaction
     * @return bool
     * @throws OSS_PaymentProcessor_Exception
     * @see self::receiptIn()
     */
    public function getMoneyFromCustomer( $payer, $amount, &$rtrans )
    {
        $rtrans = $this->receiptIn( $payer->getCard(), $amount );

        if( $rtrans->isSuccessful() )
            return true;

        return false;
    }

    /**
     * Gives money from "payer" in Realex's systems.
     *
     * It's wrapper function for paymentOut function. It takes three parameters. Payer for ho
     * it will give money, the amount of money and arrow to transaction object which will be
     * created in receiptIn function.
     *
     * function will return true or false, depends on transaction status.
     *
     * An example usage is:
     *
     * <code>
     *     $rtrans = null;
     *     $payer = $this->getD2Em()->getRepostory( '\\Entities\\RealexPayer' )->find( $id );
     *     ...
     *
     *     if( $this->getPaygate()->getMoneyFromCustomer( $payer, 100, $rtrans ) )
     *         // do something
     *     else
     *          $result = $rtrans->getResult(); //for other actions.
     * </code>
     *
     * @param \Entities\RealexPayer $payer The payer to give money
     * @param int|float $amonut The amount of money to transfer
     * @param mixed &$rtrans Arrow to new \Entities\RealexTransaction
     * @return bool
     * @throws OSS_PaymentProcessor_Exception
     * @see self::paymentOut()
     */
    public function giveMoneyToCustomer( $payer, $amount, &$rtrans )
    {
        $rtrans = $this->paymentOut( $payer->getCard(), $amount );

        if( $rtrans->isSuccessful() )
            return true;

        return false;
    }

}
