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
 */

/*
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Plugin
{
    /**
     * The plugin configuration
     * @var Zend_Config_Ini
     */
    private $config = null;

    /**
     * The plugin name - should be unique among plugins
     * @var string
     */
    private $name = null;

    public function __construct( OSS_Controller_Action $controller, $classname )
    {
        // set the plugin name
        $this->name = strtolower( substr( $classname, 16 ) );
    }

    /**
     * The function which is called by observed classes
     *
     * @param string $controller
     * @param string $action
     * @param string $hook
     * @param OSS_Controller_Action $controllerObject
     */
    public function update( $controller, $action, $hook, OSS_Controller_Action $controllerObject, $params = null )
    {
        // typically the update() function will be pretty simple
        $hookfn = "{$controller}_{$action}_{$hook}";
        if( method_exists( $this, $hookfn ) )
            return $this->$hookfn( $controllerObject, $params );
        return true;
    }



    /**
     * Get the plugin name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the configuration
     * @param Zend_Config_Ini $config
     */
    public function setConfig( $config )
    {
        $this->config = $config;
    }

    /**
     * Get the configuration
     * @return Zend_Config_Ini $config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Load the specified configuration file
     * @return Zend_Config_Ini $config
     */
    protected function loadConfig( $file )
    {
        try
        {
            $this->config = new Zend_Config_Ini( $file, APPLICATION_ENV );
        }
        catch( Exception $e )
        {
            throw new ViMbAdmin_Exception( "Unable to load and parse configuratin file [{$file}]" );
        }

        // add it to the registry also as it may be useful
        Zend_Registry::set( 'plugin_jabber_config', $this->getConfig() );
    }


}
