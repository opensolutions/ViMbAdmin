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
class OSS_Resource_Zfdebug extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the ZFDebug Instance
     * 
     * @var null|ZFDebug_Controller_Plugin_Debug
     */
    protected $_zfdebug;


    /**
     * Initialisation function
     * 
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function init()
    {
        // Return session so bootstrap will store it in the registry
        return $this->getZfdebug();
    }


    /**
     * Get Zfdebug
     * 
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function getZfdebug()
    {
        if( null === $this->_zfdebug ) 
        {
            $this->getBootstrap()->bootstrap( 'Session' );

            // Get Zfdebug configuration options from the application.ini file
            $zfdebugConfig = $this->getOptions();

            if( $zfdebugConfig['enabled'] )
            {
                $autoloader = Zend_Loader_Autoloader::getInstance();
                $autoloader->registerNamespace('ZFDebug');

                $options = array(
                    'plugins' => $zfdebugConfig['plugins']
                );

                # Instantiate the database adapter and setup the plugin.
                # Alternatively just add the plugin like above and rely on the autodiscovery feature.
                if( $this->getBootstrap()->hasPluginResource( 'db' ) ) 
                {
                    $this->getBootstrap()->bootstrap('db');
                    $db = $this->getBootstrap()->getPluginResource( 'db' )->getDbAdapter();
                    $options['plugins']['Database']['adapter'] = $db;
                }

                # Setup the cache plugin
                if( $this->getBootstrap()->hasPluginResource( 'cache' ) ) 
                {
                    $this->getBootstrap()->bootstrap( 'cache' );
                    $cache = $this->getBootstrap()->getPluginResource( 'cache' )->getDbAdapter();
                    $options['plugins']['Cache']['backend'] = $cache->getBackend();
                }

                $this->getBootstrap()->bootstrap( 'OSSAutoLoader' );
                $this->getBootstrap()->bootstrap( 'Doctrine' );
                $options['plugins']['OSS_ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine']['manager'] 
                    = $this->getBootstrap()->getResource( 'Doctrine' );

                $this->_zfdebug = new ZFDebug_Controller_Plugin_Debug( $options );

                $this->getBootstrap()->bootstrap( 'FrontController' );
                $frontController = $this->getBootstrap()->getResource('FrontController');
                $frontController->registerPlugin($this->_zfdebug);
            }        
        }

        return $this->_zfdebug;
    }    


} 
