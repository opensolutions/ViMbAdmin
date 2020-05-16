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
class OSS_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Doctrine instance
     *
     * @var null|Doctrine_Manager
     */
    protected $_doctrine;

    /**
     * Initialisation function
     * 
     * @return Doctrine_Manager
     */
    public function init()
    {
        // Return Doctrine so bootstrap will store it in the registry
        return $this->getDoctrine();
    }


    /**
     * Get doctrine
     * 
     * @return Doctrine_Manager
     */
    public function getDoctrine()
    {
        if ( null === $this->_doctrine )
        {
            // Get Doctrine configuration options from the application.ini file
            $doctrineConfig = $this->getOptions();

            /**
             * @see Doctrine
             */
            require_once 'Doctrine.php';

            $loader = Zend_Loader_Autoloader::getInstance();
            $loader->pushAutoloader( array( 'Doctrine', 'autoload' ) );
            $loader->pushAutoloader( array( 'Doctrine', 'modelsAutoload' ) );
            
            $manager = Doctrine_Manager::getInstance();
            
            if( isset( $doctrineConfig['extensions_path'] ) && is_array( $doctrineConfig['extensions'] ) )
            {
                Doctrine_Core::setExtensionsPath( $doctrineConfig['extensions_path'] );
                $loader->pushAutoloader( array( 'Doctrine', 'extensionsAutoload' ) );
                
                foreach( $doctrineConfig['extensions'] as $e )
                    $manager->registerExtension( $e );    
            }
            
            $manager->setAttribute( Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE );
            $manager->setAttribute( Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true );
            $manager->setAttribute( Doctrine::ATTR_USE_DQL_CALLBACKS, true );

            $manager->setCollate( 'utf8_unicode_ci' );
            $manager->setCharset( 'utf8' );

            Doctrine::loadModels( $doctrineConfig['models_path'] );

            $db_profiler = new Doctrine_Connection_Profiler();

            $manager->openConnection( $doctrineConfig['connection_string'] );
            $manager->connection()->setListener( $db_profiler );

            $manager->connection()->setCollate('utf8_unicode_ci');
            $manager->connection()->setCharset('utf8');

            Zend_Registry::set( 'db_profiler', $db_profiler );

            $this->_doctrine = $manager;
        }

        return $this->_doctrine;
    }

    /**
     * Set the classes $_doctrine member
     *
     * @param Doctrine_Manager $doctrine The object to set
     * @return void
     */
    public function setDoctrine( $doctrine )
    {
        $this->_doctrine = $doctrine;
    }


}
