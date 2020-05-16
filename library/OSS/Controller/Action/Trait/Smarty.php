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
 * Controller: Action - Trait for Smarty
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Smarty
{

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
    public function OSS_Controller_Action_Trait_Smarty_Init( $request, $response, $invokeArgs )
    {
        $this->view = $this->createView();
        
        if( $this->traitIsInitialised( 'OSS_Controller_Action_Trait_Namespace' ) )
            $this->view->session = $this->getSessionNamespace();
            
        $this->view->options = $this->_options;
        $this->view->addHelperPath( 'OSS/View/Helper', 'OSS_View_Helper' );
        $this->view->module     = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action     = $this->getRequest()->getActionName();
        $this->view->basepath = Zend_Controller_Front::getInstance()->getBaseUrl();

        
        $this->view->getEngine()->loadFilter( "pre", 'whitespace_control' );
        
        if( substr( $request->getActionName(), 0, 4 ) == 'ajax' || substr( $request->getActionName(), 0, 3 ) == 'cli' )
        {
            Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
        }
        else
        {
            $this->view->doctype( 'HTML5' );
            $this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        }
        
        if( $this->traitIsInitialised( 'OSS_Controller_Action_Trait_Auth' ) )
        {
            $this->view->auth    = $this->getAuth();
            
            $this->view->hasIdentity = $this->getAuth()->hasIdentity();
            $this->view->identity    = $this->getIdentity();
            
            if( $this->getAuth()->hasIdentity() && method_exists( $this, 'getUser' ) )
                $this->view->user        = $this->getUser();
        }
                
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Smarty' );
    }
    
    /**
     * Creates and returns with a new Smarty view object.
     *
     * @param void
     * @return OSS_View_Smarty
     */
    public function createView()
    {
        $view = $this->getBootstrap()->getResource( 'smarty' );
    
        $view->pagebase = '';
    
        if( isset( $_SERVER['SERVER_NAME'] ) )
            $view->pagebase = 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://'
                . $_SERVER['SERVER_NAME']
                . Zend_Controller_Front::getInstance()->getBaseUrl();
    
        $view->basepath = Zend_Controller_Front::getInstance()->getBaseUrl();

        $view->___SKIN = $view->getSkin();
    
        return $view;
    }
    
    
    /**
     * Get the view object
     *
     * @return OSS_View_Smarty
     */
    public function getView()
    {
        return $this->view;
    }
    
}

