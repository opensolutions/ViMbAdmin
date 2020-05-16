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
class OSS_PaymentProcessor_Realex_Receipt_Exception extends Zend_Exception
{
    const ERR_UNABLE_COMMUNICATE       = 'Payment failed. We are unable to communicate with our merchant or your bank at this time.';
    const ERR_NON_EXISTANT_CREDIT_CARD = 'Payment failed. You have not added any payment method.';
    const ERR_PAYMENT_DECLINE          = 'Payment declined by your bank.';

    private $_msg      = self::ERR_UNABLE_COMMUNICATE;
    private $_type     = OSS_Message::ERROR;
    private $_logMsg   = '';
    private $_logLevel = OSS_Log::CRIT;


    function __construct( $rtrans, $message )
    {
        parent::__construct();
        $this->_logMsg = "Payment with [CCTRANS {$rtrans->getId()}] was unsuccessful, got response code {$rtrans->getId()} and message '{$message}'";
        $this->setParams( $rtrans->getResult() );
    }


    public function toString()
    {
        return $this->_msg;
    }


    public function __toString()
    {
        return $this->_msg;
    }


    public function messageType()
    {
        return $this->_type;
    }


    public function logMessage()
    {
        return $this->_logMsg;
    }


    public function logLevel()
    {
        return $this->_logLevel;
    }

    private function setParams( $code )
    {
        switch( $code )
        {
            case '80':
            case '101':
            case '102':
            case '103':
            case '106':
            case '107':
            case '108':
            case '109':
                $this->_msg      = self::ERR_PAYMENT_DECLINE;
                $this->_type     = OSS_Message::ALERT;
                $this->_logLevel = OSS_Log::INFO;
                break;

            case '200':
            case '202':
            case '205':
            case '301':
            case '302':
            case '304':
            case '305':
                $this->_logLevel = OSS_Log::INFO;
                break;

            default:
                $this->_msg      = self::ERR_UNABLE_COMMUNICATE;
                $this->_type     = OSS_Message::ERROR;
                $this->_logLevel = OSS_Log::DEBUG;
                break;
        }

        $this->message = $this->_msg;
        $this->code = (int) $code;
    }

}
