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

/**
 * ViMbAdmin's version of Zend_Controller_Action, implementing custom functionality.
 * All application controlers subclass this rather than Zend's version directly.
 *
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Controller_PluginAction extends ViMbAdmin_Controller_Action implements OSS_Plugin_Observable
{
    /**
     * An array of observers for the plugin architecture
     * @var OSS_Plugin_Observer[]
     */
    private $observers = [];


    /**
     * Set by the add/edit actions, else null. Can be used by plugins to know if this is an
     * add or edit operation.
     *
     * @var bool
     */
    protected $isEdit = null;




    /**
     * Override the Zend_Controller_Action's constructor (which is called
     * at the very beginning of this function anyway).
     *
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function __construct(
        Zend_Controller_Request_Abstract  $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = null )
    {
        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        $this->registerObservers();
    }


    /**
     * Registers any plugins found in the main plugin directory as well as modules.
     *
     * FIXME This is 'autoload' functionality which can be expensive. Keep found plugin list in memcache?
     * FIXME Ordering - allow option to specify desired plugins in config
     */
    protected function registerObservers()
    {
        // find system plugins
        foreach( $this->loadObservers( APPLICATION_PATH . '/plugins' ) as $plugin )
            $this->attach( $plugin );

        // find any module plugins
        $modsdir = APPLICATION_PATH . '/modules';
        foreach( scandir( $modsdir ) as $module )
        {
            if( substr( $module, 0, 1 ) != '.' && is_dir( "{$modsdir}/{$module}/plugins" ) )
            {
                foreach( $this->loadObservers( "{$modsdir}/{$module}/plugins" ) as $plugin )
                    $this->attach( $plugin );
            }
        }
    }


    /**
     * Loads any found plugin and instaniates it (unless disabled by configuration).
     */
    protected function loadObservers( $path )
    {
        $files = scandir( $path );
        $plugins = [];

        foreach( $files as $f )
        {
            if( substr( $f, -4, 4 ) == '.php' && substr( $f, 0, 1 ) != '.' )
            {
                require_once "{$path}/{$f}";
                $pname = substr( $f, 0, strlen( $f ) - 4 );

                // make sure it has not been disabled!
                if( !isset( $this->_options['vimbadmin_plugins'][$pname]['disabled'] ) || !$this->_options['vimbadmin_plugins'][$pname]['disabled'] )
                {
                    $pname = 'ViMbAdminPlugin_' . $pname;
                    $plugins[] = new $pname( $this );
                }
            }
        }

        return $plugins;
    }

    /**
     * Attach an instaniated observer
     */
    public function attach( OSS_Plugin_Observer $observer )
    {
        $this->observers[] = $observer;
    }

    /**
     * Detach an observer
     */
    public function detach( OSS_Plugin_Observer $observer )
    {
        $newObservers = [];

        foreach( $this->observers as $o )
            if( $o !== $observer )
                $newObservers[] = $o;

        $this->observers = $newObservers;
    }

    /**
     * Give any observers a chance to execute their plugin code
     *
     * @param string $controller The controller name (e.g. 'mailbox', 'alias', etc
     * @param string $action The action name (e.g. 'add', 'edit', etc)
     * @param string $hook The name of the hook to add (e.g. 'preSave')
     * @param OSS_Controller_Action $controllerObject The controller object
     * @param object $params An optional anonymous object ( http://www.barryodonovan.com/index.php/2012/07/05/anonymous-objects-in-php )
     */
    public function notify( $controller, $action, $hook, OSS_Controller_Action $controllerObject, $params = null )
    {
        foreach( $this->observers as $o ) {
            if( $o->update( $controller, $action, $hook, $controllerObject, $params ) === false ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Getter method for isEdit
     * @see $isEdit
     * @return bool
     */
    public function isEdit()
    {
        return $this->isEdit;
    }
}
