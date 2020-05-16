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
 * Controller: Action - Trait for AuthRequired
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_AuthRequired
{

    /**
     * The trait's initialisation method.
     *
     * This function is called from the Action's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * Works best when `OSS_Controller_Action_Trait_Messages`, `OSS_Controller_Action_Trait_Namespace`
     * and `OSS_Controller_Action_Trait_Smarty` are available.
     *
     * @see OSS_Controller_Action_Trait_Auth
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function OSS_Controller_Action_Trait_AuthRequired_Init( $request, $response, $invokeArgs )
    {
        if( !$this->traitIsInitialised( 'OSS_Controller_Action_Trait_Auth' ) )
            die( 'OSS_Controller_Action_Trait_Auth required for OSS_Controller_Action_Trait_AuthRequired (in OSS_Controller_Action_Trait_AuthRequired_Init()' );
        
        
        if( !$this->getAuth()->hasIdentity() )
        {
            if( $this->traitIsInitialised( 'OSS_Controller_Action_Trait_Messages' ) )
                $this->addMessage( "Please login below.", OSS_Message::ERROR );
            
            if( $this->traitIsInitialised( 'OSS_Controller_Action_Trait_Namespace' ) )
                $this->getSessionNamespace()->postAuthRedirect = $this->getRequest()->getPathInfo();
            
            $this->redirectAndEnsureDie( 'auth/login' );
        }
        
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_AuthRequired' );
    }

}

