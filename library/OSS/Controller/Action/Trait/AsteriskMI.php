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
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action - Trait for PHPAGI's Asterisk Manager Interface
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_AsteriskMI
{

    /**
     * A variable to hold instances of Asterisk Manager Interface(s)
     *
     * @var AGI_AsteriskManager[] Instances of Asterisk Manager Interface(s)
     */
    protected $_asteriskMIs = null;
    
    
    /**
     * The trait's initialisation method.
     *
     * This function is called from the Action's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function OSS_Controller_Action_Trait_StatsD_Init( $request, $response, $invokeArgs )
    {
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_AsteriskMI' );
    }
    
    /**
     * Returns an Asterisk MI object
     *
     * Requires a configuration block such as:
     *
     *     ondemand_rescoures.ami.default.host     = 'yourhost'
     *     ondemand_rescoures.ami.default.username = 'username'
     *     ondemand_rescoures.ami.default.secret   = 'secret'
     *
     * @param string $asterisk The Asterisk server to query (which corresponds to a configuration block); by default `default`
     * @return AGI_AsteriskManager The AMI object
     */
    public function getAsteriskMI( $asterisk = 'default' )
    {
        if( $this->_asteriskMIs[ $asterisk ] === null || !isset( $this->_asteriskMIs[ $asterisk ] ) )
        {
            $plugin = new OSS_Resource_AsteriskMI( $this->getOptions()['ondemand_resources']['ami'][$asterisk] );
            $this->getBootstrap()->registerPluginResource( $plugin );
    
            $this->_asteriskMIs[ $asterisk ] = $plugin->getInstance();
        }
    
        return $this->_asteriskMIs[ $asterisk ];
    }
    
    
}

