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
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */

    /**
     * Function to generate a login form based on a ZendForm object.
     *
     * @category   OSS
     * @package    OSS_Smarty
     * @subpackage Functions
     *
     * @param array $params
     * @param Smarty $smarty A reference to the Smarty template object
     * @return string
     */
    function smarty_function_LoginForm( $params, &$smarty )
    {
        $url = Zend_Controller_Front::getInstance()->getBaseUrl();

        $loginForm = new OSS_Form_User_Login;

        $loginForm
                ->setAction( $url . '/user/login' )
                ->setDecorators(
                    array(
                        array( 'ViewScript', array( 'viewScript' => 'form/login.phtml' ) )
                    )
                )
                ->setMethod('post');

        $loginForm->getElement('username')->setLabel('E-Mail');
        $loginForm->addElement('submit', 'signup', array('label' => 'Sign Up'));

        if( isset( $params['layout'] ) && $params['layout'] = 'sidebar' )
        {
            $loginForm->username->setAttrib( 'size', 20 );

            $loginForm->setDecorators(
                array(
                    array( 'ViewScript', array( 'viewScript' => 'form/login-sidebar.phtml' ) )
                )
            );
        }
        else
        {
            $loginForm->username->setAttrib( 'size', 40 );
            $loginForm->addElement( 'submit', 'forgottenPassword', array('label' => 'Forgotten Password?'));
        }

        return $loginForm->render();
    }
