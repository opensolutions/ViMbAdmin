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
class OSS_Resource_Freshbooks extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Frehsbook service instance
     *
     * @var null|OSS_Service_Freshbooks
     */
    protected $_fbooks = null;


    /**
     * Initialisation function
     * 
     * @return OSS_Service_Freshbooks
     */
    public function init()
    {
        // Return Doctrine so bootstrap will store it in the registry
        return $this->getFreshbooks();
    }


    /**
     * Get Freshbooks
     * 
     * @return OSS_Service_Freshbooks
     */
    public function getFreshbooks()
    {
        if( !$this->_fbooks )
        {
            // Get Freshbooks configuration options from the application.ini file
            $fbooksConfig = $this->getOptions();

            if( $fbooksConfig['enabled'] )
            {
                if( !isset( $fbooksConfig['accessToken'] ) || !file_exists( $fbooksConfig['accessToken'] ) )
                    die( 'Freshbooks resource enabled but mis-configured - no access token available' );
                
                $fbooksConfig['accessToken'] = unserialize( file_get_contents( $fbooksConfig['accessToken'] ) );
                $this->_fbooks = new OSS_Service_Freshbooks( $fbooksConfig );
            }
        }
        
        return $this->_fbooks;
    }

}
