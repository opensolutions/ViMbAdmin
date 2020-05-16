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
 * Controller: Action - Trait for Doctine2User
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Doctrine2User
{
    /**
     * A variable to hold the user record
     *
     * @var \Entities\User An instance of the user record
     */
    protected $_user = false;
    
    
    /**
     * The entity that represents a 'user'
     */
    protected $_trait_doctrine2user_entity = '\\Entities\\User';
    
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
    public function OSS_Controller_Action_Trait_Doctrine2User_Init( $request, $response, $invokeArgs )
    {
        // check if we have defined an alternative entity object than \Entities\User
        if( isset( $this->_options['resources']['auth']['oss']['entity'] ) )
            $this->_trait_doctrine2user_entity = $this->_options['resources']['auth']['oss']['entity'];
        
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Doctrine2User' );
    }
    
    /**
     * Get the user ORM object.
     *
     * Returns the instance of the Doctrine User object for the logged in user.
     *
     * @return User The user object or false.
     */
    protected function getUser()
    {
        if( $this->_user === false )
        {
            try
            {
                $this->_user = $this->getD2EM()->createQuery(
                        "SELECT u FROM {$this->_trait_doctrine2user_entity} u WHERE u.id = ?1" )
                    ->setParameter( 1, $this->getIdentity()['id'] )
                    ->useResultCache( true, 3600, 'oss_d2u_user_' . $this->getIdentity()['id'] )
                    ->getSingleResult();
            }
            catch( \Doctrine\ORM\NoResultException $e )
            {
                if( session_status() == PHP_SESSION_ACTIVE )
                {
                    session_unset();
                    session_destroy();
                }    
                die( 'User expected but none found...  Please reload the page...' );
            }
        }
    
        return $this->_user;
    }
    
    /**
     * Clear the user ORM object from the cache.
     *
     * @param int $id The ID of the user to clear from the cache
     */
    protected function clearUserFromCache( $id = null )
    {
        if( $id === null )
            $id = $this->getUser()->getId();

        $this->getD2Cache()->delete( 'oss_d2u_user_' . $id );
    }
    
}

