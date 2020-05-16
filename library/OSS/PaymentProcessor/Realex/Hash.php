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
 * @subpackage OSS_PaymentProcessor_Realex
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_PaymentProcessor
 * @subpackage OSS_PaymentProcessor_Realex
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_PaymentProcessor_Realex_Hash extends OSS_PaymentProcessor_BaseProcessor
{

    /**
     * Calculate the SHA1 hash from a Realex response (as an array)
     *
     * @param array $resp The parsed Realex XML response as an array
     * @param string $merchantId The merchant ID
     * @param string $secret The shared secret
     * @return string The calculated SHA1 hash
     */
    public static function response( $resp, $secret )
    {
        $str  = $resp['@attributes']['timestamp'] . '.';
        $str .= $resp['merchantid'] . '.';
        $str .= ( isset( $resp['orderid'] ) ? $resp['orderid'] : '' );
        $str .= '.';
        $str .= $resp['result'] . '.';
        $str .= $resp['message'] . '.';
        $str .= $resp['pasref'] . '.';
        $str .= ( isset( $resp['authcode'] ) ? $resp['authcode'] : '' );

        return sha1( sha1( $str ) . '.' . $secret );
    }


    /**
    * Creates an SHA1 hash for 'payer-new' realEx transaction.
    *
    * @param string $timeStamp in YYYYmmddhhmmss format
    * @param string $merchantId this merchant id in Realex
    * @param string $orderId a unique realEx order id
    * @param string $payerRef unique payer reference id
    * @param string $secret The shared secret
    * @return string
    */
    public static function payer( $timeStamp, $merchantId, $orderId, $payerRef, $secret )
    {
        $str  = $timeStamp . '.';
        $str .= $merchantId . '.';
        $str .= $orderId;
        $str .= '...';
        $str .= $payerRef;

        return sha1( sha1( $str ) . '.' . $secret );
    }


    /**
    * Creates an SHA1 hash for a 'card-new' realEx transaction.
    *
    * @param string $timeStamp in YYYYmmddhhmmss format
    * @param string $merchantId this merchant id in Realex
    * @param string $orderId a unique realEx order id
    * @param string $payerRef unique payer reference id
    * @param string $cardHolder the card holder's name
    * @param string $cardNumber the credit card number
    * @param string $secret The shared secret
    * @return string
    */
    public static function creditCard( $timeStamp, $merchantId, $orderId, $payerRef, $cardHolder, $cardNumber, $secret )
    {
        $str  = $timeStamp . '.';
        $str .= $merchantId . '.';
        $str .= $orderId;
        $str .= '...';
        $str .= $payerRef . '.';
        $str .= $cardHolder . '.';
        $str .= preg_replace( "/\D/", '', $cardNumber );
        return sha1( sha1( $str ) . '.' . $secret );
    }


    /**
    * Creates an SHA1 hash for a 'receipt-in' realEx transaction.
    *
    * @param string $timeStamp in YYYYmmddhhmmss format
    * @param string $merchantId this merchant id in Realex
    * @param string $orderId a unique realEx order id
    * @param string|int|float $amount the value of the transaction in the smallest unit of the currency, e.g. in cent for euro or dollar
    * @param string $payerRef unique payer reference id
    * @param string $secret The shared secret
    * @return string
    */
    public static function payment( $timeStamp, $merchantId, $orderId, $amount, $currency, $payerRef, $secret )
    {
        $str  = $timeStamp . '.';
        $str .= $merchantId . '.';
        $str .= $orderId . '.';
        $str .= $amount . '.';
        $str .= $currency . '.';
        $str .= $payerRef;

        return sha1( sha1( $str ) . '.' . $secret );
    }


    /**
    * Creates an SHA1 hash for an 'eft-update-expiry-date' realEx transition.
    *
    * @param string $timeStamp in YYYYmmddhhmmss format
    * @param string $merchantId this merchant id in Realex
    * @param string $payerRef unique payer reference id
    * @param string $creditCardRef unique creditcard reference id
    * @param string $validTo the expiry date of the card in 'YYYY-MM' or 'YYYY-mm-dd' format
    * @param string $secret The shared secret
    * @return string
    */
    public static function updateCreditCard( $timeStamp, $merchantId, $payerRef, $creditCardRef, $validTo, $secret )
    {
        $str  = $timeStamp . '.';
        $str .= $merchantId . '.';
        $str .= $payerRef . '.';
        $str .= $creditCardRef . '.';
        $str .= date( 'm', strtotime( $validTo ) ) . date( 'y', strtotime ($validTo ) ) . '.';
        return sha1( sha1( $str ) . '.' . $secret );
    }


    /**
    * Creates an SHA1 hash for an 'card-cancel-card' realEx transition.
    *
    * @param string $timeStamp in YYYYmmddhhmmss format
    * @param string $merchantId this merchant id in Realex
    * @param string $payerRef unique payer reference id
    * @param string $creditCardRef unique creditcard reference id
    * @param string $secret The shared secret
    * @return string
    */
    public static function removeCreditCard( $timeStamp, $merchantId, $payerRef, $creditCardRef, $secret )
    {
        $str  = $timeStamp . '.';
        $str .= $merchantId . '.';
        $str .= $payerRef . '.';
        $str .= $creditCardRef;

        return sha1( sha1( $str ) . '.' . $secret );
    }

    /**
    * Creates an refund hash for an 'card-cancel-card' realEx transition.
    * @param string $refundPassword refund password assigned by Realex
    * @param string $secret The shared secret
    * @return string
    */
    public static function refund( $refundPassword, $secret )
    {
        return sha1( sha1( $refundPassword ) . '.' . $secret );
    }

}
