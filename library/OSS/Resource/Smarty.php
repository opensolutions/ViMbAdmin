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
class OSS_Resource_Smarty extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Holds the View instance
     * 
     * @var null|OSS_View_Smarty
     */
    protected $_view;

    /**
     * Initialisation function
     * 
     * @return OSS_View_Smarty
     */
    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }

    /**
     * Get view
     * 
     * @return OSS_View_Smarty
     */
    public function getView()
    {
        // Get session configuration options from the application.ini file
        $options = $this->getOptions();

        if( $options['enabled'] )
        {
            if( null === $this->_view ) // this cannot be &&'d with the above!
            {
                @include_once( 'Smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php' );

                // Create directories of necessary
                if( !file_exists( $options['cache'] ) )
                {
                    mkdir( $options['cache'], 0770, true );
                    chmod( $options['cache'], 0770       );
                }

                if( !file_exists( $options['compiled'] ) )
                {
                    mkdir( $options['compiled'], 0770, true );
                    chmod( $options['compiled'], 0770       );
                }

                // Initialize view
                $view = new OSS_View_Smarty( 
                    $options['templates'], 
                    array( 
                        'cache_dir'   => $options['cache'], 
                        'config_dir'  => isset( $options['config'] ) ? $options['config'] : null, 
                        'compile_dir' => $options['compiled'], 
                        'plugins_dir' => $options['plugins'] 
                    ) 
                );

                if( isset( $options['skin'] ) && strlen( $options['skin'] ) )
                    $view->setSkin( $options['skin'] );

                $view->getEngine()->debugging = $options['debugging'];

                // Add it to the ViewRenderer
                $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper( 'ViewRenderer' );
                $viewRenderer->setView( $view );

                $this->_view = $view;
            }

            $this->_view->OSS_Messages = array();
            
            return $this->_view;
        }

    } 
}
