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
class OSS_PaymentProcessor_Stripe extends OSS_PaymentProcessor_BaseProcessor
{
    const CARD_TYPE_VISA         = 'visa';
    const CARD_TYPE_MASTERCARD   = 'mastercard';

    /**
    * Valid credit/debit card types
    * @var array
    */
    public static $CARD_TYPES = [
        self::CARD_TYPE_VISA => 'visa',
        self::CARD_TYPE_MASTERCARD => 'mc'
    ];


    /**
     * An array of the mandatory application.ini parameters.
     *
     * @var array The mandatory application.ini parameters
     */
    public static $REQUIRED_PARAMS = [ 'ak_secret', 'currency'  ];

    /**
     * An array of the optional application.ini parameters (defaults set with their definitions).
     *
     * @var array The optional application.ini parameters
     */
    public static $OPTIONAL_PARAMS = [ 'fake_transactions', 'ak_public', 'keep_request_data', 'account' ];

    /**
     * The Doctrine2 entity manager
     * @var EntityManager The Doctrine2 entity manager
     */
    private $_d2em = null;

    /**
     * The Stripe API secret key
     * @var string The Stripe API secret key
     */
    private $_ak_secret;

    /**
     * The Stripe API public key
     * @var string The Stripe API public key
     */
    private $_ak_public;

    /**
     * The currency then charging payer ( only 3 characters )
     * @var string The currency then charging payer
     */
    private $_currency;

    /**
     * The currency then charging payer ( only 3 characters )
     * @var string The currency then charging payer
     */
    private $_account = '';


    /**
     * For security reasons, we clear the request data from the database after a sucessful
     * Stripe API/CGI call.
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
     * updates to local database tables but it never contacts Stripe and fakes
     * all operations.
     *
     * @var bool Fake all transactions and never contact Stripe
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
     * Stripe Payment gateway processor
     *
     * @param array $config An associated array of stripe.* parameters from application.ini
     * @param OSS_Log $logger An optional instance of an OSS_Log object if you want logging
     * @throws OSS_PaymentProcessor_Realex_Exception
     */
    public function __construct( array $config, OSS_Log $logger = null )
    {
        if( !class_exists( 'Stripe' ) )
                require_once( 'Stripe.php' );

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
        Stripe::setApiKey( $this->_ak_secret );
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
     * Returns with a YYYYmmddhhmmss format timestamp used in Stripe transactions.
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
     * Creates a unique Stripe payer reference ID which will be replaced after adding it to Stripe system
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
     * Created a unique Stripe credit card reference ID which will be replaced after adding it to Stripe system
     *
     * All cards added to the Stripe store (linked to a payer) must be
     * uniquely identifiable with a unique key. We use the integer primary
     * key from the \Entities\RealexCard table.
     *
     * These cards are added to payers in the Stripe system.
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
     * Set fake CreditcardTransactions entries for a complete fake transaction
     *
     * If <var>$this->_fake_transactions</var> is set to true, the system will
     * not perform any Stripe requests but <em>fake</em> the transactions in
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
     * Stripe Exception handler
     *
     * Function will check the exception is instance of Stripe_ERROR. If it is instance of Stripe_ERROR then
     * function will chek the type. If type is card_error then it sets result to 80 and saves the message to rtrans
     * reference. otherwise it sets to 85 other stripe issue. If exception is not stripe one set result to 90. 
     * 
     * @param Exception $e        Exception object
     * @param string    $function function name to complete the logs.
     * @return void
     */
    private function exceptionHendler( $e, $rtrans, $function )
    {
        $error = $e->getJsonBody()['error'];
        if(  $e instanceof Stripe_Error)
        {
            if( $error['type'] == 'card_error' )
            {
                $rtrans->setResult( '80' );
                $rtrans->setReference( $error['message'] );    
            }
            else
                $rtrans->setResult( '85' );
        }
        else
            $rtrans->setResult( '90' );

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::{$function}() - result: exception: " . print_r( $error, true ) );
    }

    /**
     * Creates a new "payer" in Stripe's systems.
     *
     * Sends a 'payer-new' request to Stripe which creates a new payer at Stripe
     * which later can be used by Stripe for recurring payments ('receipt-in').
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Stripe request and returns with
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
     *     // if we're interested in the Stripe result then:
     *     if( $rtrans->isSuccessful() )
     *         // do something
     * </code>
     *
     * @param \Entities\RealexPayer $payer The payer who will be associated as payer in Stripe
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newPayer() - transaction set to STATE_INIT" );

        // we're about to create a Realex payer which means the paygate state should be none
        if( $payer->getState() != \Entities\RealexPayer::STATE_NONE )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $reqData = [
            'description' => "{{$payer->getCustomer()->getName()}, cid {$payer->getId()}}"
        ];

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newPayer() - transaction set to STATE_PRESEND\n\n" . print_r( $reqData, true ) . "\n\n" );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newPayer() - faking transaction" );
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }
        
        try{ 
            $res = Stripe_Customer::create( $reqData );
            $rtrans->setResult( '00' );

            $payer->setPayerref( $res->id );
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newPayer() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        {
            $this->exceptionHendler( $e, $rtrans, "newPayer" );
        }
        
        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );
        
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $rtrans->isSuccessful() )
        {
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            $payer->setUpdated( new \DateTime() );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }


    /**
     * Creates a new "credit card" in Stripe's systems.
     *
     * Sends a 'card-new' request to Stripe which creates a new credit card at Stripe
     * which later can be used by RealEFT for recurring payments ('receipt-in').
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Stripe request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * It doesn't matter if this transaction fails. All the information we need is in
     * the database. The end user does not want to know about it. We'll pick up
     * unsuccessful 'card-new' in offline processing. Any serious / critical issues
     * will have thrown an exception.
     *
     * @param \Entities\RealexCard $card The credit card object to add to Stripe
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newCard() - transaction set to STATE_INIT" );

        // we're about to create a Realex credit card which means the paygate state should be none
        if( $card->getState() != \Entities\RealexCard::STATE_NONE )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $reqData = [ 
            'card' => [
                'number'    => $card->getNumber(),
                'exp_month' => $card->getValidTo()->format( "m" ),
                'exp_year'  => $card->getValidTo()->format( "y" ),
                'cvc'       => $card->getCvv(),
                'name'      => $card->getHolder()
            ]
        ];

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setRequest( $card->getNumber() );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newCard() - transaction set to STATE_PRESEND\n\n" . print_r( $reqData, true ) . "\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        try{ 
            $cu = Stripe_Customer::retrieve( $card->getPayer()->getPayerref() );
            $res = $cu->cards->create( $reqData );
            $rtrans->setResult( '00' );

            $card->setCardref( $res->id );
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::newCard() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        { 
            $this->exceptionHendler( $e, $rtrans, "newCard" );
        } 
        
        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        //FIXME: Barry
        if( $rtrans->isSuccessful() && !$this->_keep_request_data )
            $rtrans->setRequest( '' );

        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $rtrans->isSuccessful() )
        {
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            $card->setNumber( substr( $card->getNumber(), 0, 4 ) . '...' . substr( $card->getNumber(), -4 ) );
            $card->setUpdated( new \DateTime() );
            $this->getD2EM()->flush();
        }

        return $rtrans;
    }

    /**
     * Creates a new "payment" in Stripe's systems.
     *
     * Sends a 'receipt-in' request to Stripe which creates a new payment at Stripe.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Stripe request and returns with
     * success after performing all normal database operations as if the request was sent.
     * @see $_fake_transactions
     *
     * @param \Entities\RealexCard $card
     * @param int|float $amount the amount to be withdrawn from the card, in the biggest unit of the currency,
     *    e.g. in euro or dollar and not in cent, then conversion is made inside the method
     * @param string $description Payment description. e.g. For services of period from 08/13 unti 09/13
     * @return \Entities\RealexTransaction The resultant \Entities\RealexTransaction object
     * @throws OSS_PaymentProcessor_Exception
     */
    public function receiptIn( $card, $amount, $description = "" )
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::receiptIn() - transaction set to STATE_INIT" );

        // we're about to perform payment which means that the cerdit card paygate state should be insync
        if( $card->getState() != \Entities\RealexCard::STATE_INSYNC )
        {
            $rtrans->setResult( 999 );
            throw new OSS_PaymentProcessor_Realex_Receipt_Exception( $rtrans, OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );
        }

        $reqData = [
            'amount' => (int) ( $amount * 100 ),
            'currency' => strtolower( $this->_currency ),
            'card' => $card->getCardref(),
            'customer' => $card->getPayer()->getPayerref(),
            'capture' => true,
            'description' => $description
        ];

        $rtrans->setRequest( $amount );
        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::receiptIn() - transaction set to STATE_PRESEND\n\n" . print_r( $reqData, true ) . "\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::receiptIn() - faking transaction" );
            return $this->_completeFakeTransation( $rtrans );
        }

        try{ 
            $res = Stripe_Charge::create( $reqData );
            $rtrans->setResult( '00' );
            $rtrans->setReference( $res['id'] );
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::receiptIn() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        { 
            $this->exceptionHendler( $e, $rtrans, "receiptIn" );
        } 

        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        //FIXME: Barry
        if( $rtrans->isSuccessful() && !$this->_keep_request_data )
            $rtrans->setRequest( '' );

        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

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
        /*$rqTimeStamp = $this->getTimeStamp();

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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::paymentOut() - transaction set to STATE_PRESEND\n\n{$reqXML}\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::paymentOut() - faking transaction" );
            return $this->_completeFakeTransation( $rtrans );
        }

        $this->_sendRequest( $rtrans, $reqXML ); // can throw OSS_PaymentProcessor_Realex_Receipt_Exception()

        return $rtrans;*/
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::editPayer() - transaction set to STATE_INIT" );

        // we're about to update a Realex payer which means the paygate state should be dirty
        if( $payer->getState() != \Entities\RealexPayer::STATE_DIRTY )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

         $reqData = [
            'description' => "{$payer->getName()}, cid {$payer->getId()}}"
        ];


        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::editPayer() - transaction set to STATE_PRESEND\n\n" . print_r( $reqData, true ) ."\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::editPayer() - faking transaction" );
            $payer->SetState( \Entities\RealexPayer::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        try{ 
            $cu = Stripe_Customer::retrieve( $card->getPayer()->getPayerref() );
         
            foreach( $reqData as $name => $value )
                $cu->$name = $value;
         
            $cu->save();
            $rtrans->setResult( '00' );
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::editPayer() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        {
            $this->exceptionHendler( $e, $rtrans, "editPayer" );
        }

        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $rtrans->isSuccessful() )
        {
            $payer->setState( \Entities\RealexPayer::STATE_INSYNC );
            $payer->setUpdated( new \DateTime() );
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::updateCard() - transaction set to STATE_INIT" );

        // we're about to update a Realex credit card which means the paygate state should be dirty
        if( $card->getState() != \Entities\RealexCard::STATE_DIRTY )
            throw new OSS_PaymentProcessor_Exception( OSS_PaymentProcessor_Exception::ERR_INCONSISTANT_PAYGATE_STATE );

        $reqData = [ 
            'exp_month' => $card->getValidTo()->format( "m" ),
            'exp_year'  => $card->getValidTo()->format( "Y" ),
            'name'      => $card->getHolder(),
        ];

        $rtrans->setState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::updateCard() - transaction set to STATE_PRESEND\n\n" . print_r( $reqData, true ) ."\n\n" );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::updateCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            return $this->_completeFakeTransation( $rtrans );
        }

        try{ 
            $cu = Stripe_Customer::retrieve( $card->getPayer()->getPayerref() );
            $crd = $cu->cards->retrieve( $card->getCardref() ); 
         
            foreach( $reqData as $name => $value )
                $crd->$name = $value;
         
            $res = $crd->save();
            $rtrans->setResult( '00' );
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::updateCard() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        {
            $this->exceptionHendler( $e, $rtrans, "updateCard" );
        }
        
        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );

        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $rtrans->isSuccessful() )
        {
            $card->setState( \Entities\RealexCard::STATE_INSYNC );
            $card->setUpdated( new \DateTime() );
            $this->getD2EM()->flush();
        }

        return $rtrans;

    }

    /**
     * Removes a "card" from Stripe's systems.
     *
     * Sends a 'card-cancel-card' request to Stripe which remove credit card from Stripe.
     *
     * This also logs the transaction into the \Entities\RealexTransaction table,
     * and returns that object which can be queried for the request and transaction
     * state.
     *
     * If $this->_fake_transactions is true then it skips the Stripe request and returns with
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

        $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::cancelCard() - transaction set to STATE_INIT" );

        $rtrans->getState( \Entities\RealexTransaction::STATE_PRESEND );
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        $this->_log( 
            sprintf( "[RTRANS: %s] Stripe::cancelCard() - transaction set to STATE_PRESEND\n\n [ customer => '%s', card => '%s' ] \n\n",
                $rtrans->getId(),
                $card->getPayer()->getPayerref(),
                $card->getCardref()
            )
        );

        if( $this->_fake_transactions )
        {
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::cancelCard() - faking transaction" );
            $card->setState( \Entities\RealexCard::STATE_NONE );
            return $this->_completeFakeTransation( $rtrans );
        }

        try{ 
            $cu = Stripe_Customer::retrieve( $card->getPayer()->getPayerref() );
            $res = $cu->cards->retrieve( $card->getCardref() )->delete(); 
            $rtrans->setResult( '00' );
    
            $this->_log( "[RTRANS: {$rtrans->getId()}] Stripe::cancelCard() - Stripe result: " . print_r( $res, true ) );
        }
        catch( Exception $e )
        {
            $this->exceptionHendler( $e, $rtrans, "cancelCard" );
        }
        
        $rtrans->setState( \Entities\RealexTransaction::STATE_COMPLETE );
        
        $rtrans->setUpdated( new \DateTime() );
        $this->getD2EM()->flush();

        if( $rtrans->isSuccessful() )
        {
            $card->setState( \Entities\RealexCard::STATE_NONE );
            $card->setUpdated();
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
