<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 Open Source Solutions Limited
 *
 * ViMbAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ViMbAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ViMbAdmin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Open Source Solutions Limited T/A Open Solutions
 *   147 Stepaside Park, Stepaside, Dublin 18, Ireland.
 *   Barry O'Donovan <barry _at_ opensolutions.ie>
 *
 * @copyright Copyright (c) 2011 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/*
 * The Doctrine resource.
 *
 * @package ViMbAdmin
 * @subpackage Resource
 */
class ViMbAdmin_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Doctrine instance
     *
     * @var
     */
    protected $_doctrine;


    public function init()
    {
        // Return Doctrine so bootstrap will store it in the registry
        return $this->getDoctrine();
    }


    public function getDoctrine()
    {
        if ( null === $this->_doctrine )
        {
            try 
            {
                // Get Doctrine configuration options from the application.ini file
                $doctrineConfig = $this->getOptions();
    
                require_once 'Doctrine.php';
    
                $loader = Zend_Loader_Autoloader::getInstance();
                $loader->pushAutoloader( array( 'Doctrine', 'autoload' ) );
                $loader->pushAutoloader( array( 'Doctrine', 'modelsAutoload' ) );
    
                $manager = Doctrine_Manager::getInstance();
    
                if( isset( $doctrineConfig['extensions_path'] ) && isset( $doctrineConfig['extensions'] ) && is_array( $doctrineConfig['extensions'] ) )
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
    
                $manager->openConnection( $doctrineConfig['connection_string'] );
    
                $manager->connection()->setCollate('utf8_unicode_ci');
                $manager->connection()->setCharset('utf8');
    
                $this->_doctrine = $manager;
            }
            catch( Exception $e )
            {
                echo "<html><body><pre>\nERROR: Your Doctrine set-up is not working.\n\n";
                echo $e->getMessage() . "\n\n";
                echo $e->getTraceAsString();
                echo "</pre></body></html>";
                die();
                
            }
        }

        return $this->_doctrine;
    }

    /**
     * Set the classes $_doctrine member
     *
     * @param $doctrine The object to set
     */
    public function setDoctrine( $doctrine )
    {
        $this->_doctrine = $doctrine;
    }


}
