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
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Resource_Paygate extends Zend_Application_Resource_ResourceAbstract
{
    protected $_session;

    /**
     * Holds the Logger instance
     *
     * @var null|OSS_PaymentProcessor
     */
    protected $_paygate = null;


    /**
     * Initialisation function
     * 
     * @return OSS_PaymentProcessor
     */
    public function init()
    {
        die( "init" );
        return $this->getPaygate();
    }


    /**
     * get paygate
     * 
     * @return OSS_PaymentProcessor
     */
    public function getPaygate( $logger = null)
    {
        if( !$this->_paygate )
        {
            // Get Doctrine configuration options from the application.ini file
            $configs = $this->getOptions();

            $paygate = 'OSS_PaymentProcessor_' . $configs["name"];
           
            $options = Zend_Registry::get('options')[ strtolower( $configs["name"] ) ];
           
            $this->_paygate = new $paygate( $options, $logger );
        }

        return $this->_paygate;
    }

}
