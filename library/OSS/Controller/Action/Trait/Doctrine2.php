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
 * Controller: Action - Trait for Doctine2
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Doctrine2
{
    /**
     * A variable to hold the Doctrine2 entity manager
     *
     * @var \Doctrine\ORM\EntityManager An instance of the Doctrine2 entity manager
     */
    static private $_d2em = null;


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
    public function OSS_Controller_Action_Trait_Doctrine2_Init( $request, $response, $invokeArgs )
    {
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Doctrine2' );
    }

    /**
     * Returns an instance of the Doctrine2 entity manager
     *
     * @param string $db Which database entity manager to get (for use when using multiple databases)
     * @return Doctrine\ORM\EntityManager The Doctrine2 Entity Manager
     */
    public function getEntityManager( $db = 'default' )
    {
        if( self::$_d2em === null || !isset( self::$_d2em[ $db ] ) )
        {
            $plugin = new OSS_Resource_Doctrine2( $this->_options['resources']['doctrine2'] );
            $this->getBootstrap()->registerPluginResource( $plugin );
            self::$_d2em[ $db ] = $plugin->getDoctrine2( $db );
            Zend_Registry::set( 'd2em', self::$_d2em );
        }

        return self::$_d2em[ $db ];
    }

    /**
     * Alias for getEntityManager()
     *
     * @see getEntityManager()
     * @param string $db Which database entity manager to get (for use when using multiple databases)
     * @return Doctrine\ORM\EntityManager The Doctrine2 Entity Manager
     */
    public function getD2EM( $db = 'default' )
    {
        return $this->getEntityManager( $db );
    }

    /**
     * Returns an instance of the requested Doctrine2 repository class
     *
     * @param string $repository Which repository to instantiate
     * @param string $db Which database entity manager to get (for use when using multiple databases)
     * @return Doctrine\ORM\EntityRepository The Doctrine2 Entity Repository
     */
    public function getD2R( $repository, $db = 'default' )
    {
        return $this->getEntityManager( $db )->getRepository( $repository );
    }

    /**
     * Returns an instance of the Doctrine2 entity manager
     *
     * @param string $db Which database entity manager to get (for use when using multiple databases)
     * @return Doctrine\ORM\EntityManager The Doctrine2 Entity Manager
     */
    public static function getEntityManagerStatic( $db = 'default' )
    {
        if( self::$_d2em === null || !isset( self::$_d2em[ $db ] ) )
        {
            $plugin = Zend_Registry::get( 'bootstrap' )->getPluginResource( 'doctrine2' );

            if( $plugin == null )
            {
                $plugin = new OSS_Resource_Doctrine2( Zend_Registry::get( 'options' )['resources']['doctrine2'] );
                $this->getBootstrap()->registerPluginResource( $plugin );
            }

            self::$_d2em[ $db ] = $plugin->getDoctrine2( $db );
            Zend_Registry::set( 'd2em', self::$_d2em );
        }

        return self::$_d2em[ $db ];
    }

    /**
     * Alias for getEntityManagerStatic()
     *
     * @see getEntityManagerStatic()
     * @param string $db Which database entity manager to get (for use when using multiple databases)
     * @return Doctrine\ORM\EntityManager The Doctrine2 Entity Manager
     */
    public static function getD2EMS( $db = 'default' )
    {
        return self::getEntityManagerStatic( $db );
    }


    /**
     * Clear an entity manager instance from the local static property
     *
     * This is needed specifically in the case where one catches a Doctrine2 exception and wishes
     * to used the EntityManager again - the exception closes the entity manager so it needs to be
     * cleared and then recreated.
     *
     * @param string $db Which database entity manager to clear (for use when using multiple databases)
     * @return Zend_Controller_Action For fluent interfaces
     */
    public static function clearEntityManagerPropertyStatic( $db = 'default' )
    {
        if( isset( self::$_d2em[ $db ] ) )
        {
            self::$_d2em[ $db ] = null;
            unset( self::$_d2em[ $db ] );
        }

        return $this;
    }

    /**
     * Clear an entity manager instance from the local static property
     *
     * @see clearEntityManagerPropertyStatic()
     *
     * @param string $db Which database entity manager to clear (for use when using multiple databases)
     * @return Zend_Controller_Action For fluent interfaces
     */
    public static function clearEntityManagerProperty( $db = 'default' )
    {
        return self::clearEntityManagerPropertyStatic( $db );
    }

}

