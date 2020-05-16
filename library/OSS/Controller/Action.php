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
 * @package    OSS_Controller
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @link       https://github.com/opensolutions/OSS-Framework/wiki/OSS_Controller Online documentation
 * @category   OSS
 * @package    OSS_Controller
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Controller_Action extends Zend_Controller_Action
{
    // traits we want to use
    //
    // use OSS_Controller_Action_Trait_Namespace;
    // use OSS_Controller_Action_Trait_Doctrine2User;
    // use OSS_Controller_Action_Trait_Auth;
    // use OSS_Controller_Action_Trait_AuthRequired;
    // use OSS_Controller_Action_Trait_Doctrine2Cache;
    // use OSS_Controller_Action_Trait_Doctrine2;
    // use OSS_Controller_Action_Trait_Mailer;
    // use OSS_Controller_Action_Trait_License;
    // use OSS_Controller_Action_Trait_Logger;
    // use OSS_Controller_Action_Trait_Smarty;
    // use OSS_Controller_Action_Trait_StatsD;
    // use OSS_Controller_Action_Trait_Freshbooks;
    // use OSS_Controller_Action_Trait_Messages;
    // use OSS_Controller_Action_Trait_News;
    // use OSS_Controller_Action_Trait_PdfGenerator;


    /**
     * A variable to hold an instance of the bootstrap object
     *
     * @var Zend_Application_Bootstrap_Bootstrap An instance of the bootstrap object
     */
    protected $_bootstrap = null;

    /**
     * @var array An array representation of the application.ini
     */
    protected $_options = null;


    /**
     * An array to hold the names of traits that have been initialised
     *
     * This can be used by other traits to check for dependancies.
     *
     * @var array An array to hold the names of traits that have been initialised
     */
    protected $_initialisedTraits = [];



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
        // get the bootstrap object and set it in the registry
        $this->setBootstrap( $invokeArgs['bootstrap'] );

        // get the options and set it in the registry
        $this->_options = $this->getBootstrap()->getOptions();
        Zend_Registry::set( 'options', $this->_options );

        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        // if we issue a redirect, we want it to exit immediatly
        $this->getHelper( 'Redirector' )->setExit( true );

        // ensure CLI actions are only run from the CLI
        if( ( $request->getControllerName() == 'cli' || substr( $request->getActionName(), 0, 3 ) == 'cli' ) && php_sapi_name() != 'cli' )
            die( 'Invalid action - CLI only' );

        $this->initialiseTraits( $request, $response, $invokeArgs );
    }


    /**
     * All declared traits can have their own initialisation method. This function
     * iterates over the declared traits and initialises them if necessary.
     *
     * NB - order of initialisation is order of declaration
     *
     * This function should be called from the contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    private function initialiseTraits( $request, $response, $invokeArgs )
    {
        foreach( get_declared_traits() as $trait )
        {
            $fn = "{$trait}_Init";
            if( method_exists( $this, $fn ) )
                $this->$fn( $request, $response, $invokeArgs );
        }
    }

    /**
     * If a named trait has been initialised, returns true, else false
     *
     * @param string $trait The name of the trait to check for
     * @return bool Whether the trait has been initialised or not
     */
    protected function traitIsInitialised( $trait )
    {
        return isset( $this->_initialisedTraits[ $trait ] ) && $this->_initialisedTraits[ $trait ];
    }

    /**
     * Mark a trait as initialised
     *
     * @param string $trait The name of the trait to check for
     */
    protected function traitSetInitialised( $trait )
    {
        $this->_initialisedTraits[ $trait ] = true;
    }

    /**
     * A utility method to get a named resource.
     *
     * @param string $resource
     * @return Zend_Application_Resource_ResourceAbstract
     */
    public function getResource( $resource )
    {
        return $this->getBootstrap()->getResource( $resource );
    }

    /**
     * Set the Zend application bootstrap in this instance and in the
     * global registry.
     *
     * @param Zend_Application_Bootstrap_Bootstrap $bs
     */
    protected function setBootstrap( $bs )
    {
        $this->_bootstrap = $bs;
        Zend_Registry::set( 'bootstrap', $this->_bootstrap );
    }

    /**
     * Get the Zend application bootstrap
     *
     * @return Zend_Application_Bootstrap_Bootstrap
     */
    protected function getBootstrap()
    {
        if( $this->_bootstrap === null )
            $this->_bootstrap = Zend_Registry::get( 'bootstrap' );

        return $this->_bootstrap;
    }

    /**
     * Get the application options associative array
     *
     * @return array The options array
     */
    public function getOptions()
    {
        return $this->_options;
    }


    /**
     * Wrapper function for Zend's _redirect() to **ensure** we end execution after the redirection.
     *
     * @param string $where Defaults to '' - where to redirect to (e.g. controller/action/p1/v1/...)
     * @param string $from Where you redirected from for logging purposes. Defaults to requested controller/action.
     * @return void This ensures execution ends.
     */
    public function redirectAndEnsureDie( $where = '', $from = null )
    {
        $this->_redirect( $where );

        // if we fall through, catch it
        if( $from === null )
            $from = $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();

        $this->getLogger()->err( "Unexpected fall through from redirect() from {$from}" );
        die( $this->getOptions()['messages']['unexpected_error'] );
    }

}
