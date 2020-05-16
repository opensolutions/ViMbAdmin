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
 * Controller: A generic trait to implement basic functionality in a ProfileController
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Trait_Profile
{
    use OSS_Controller_Action_Trait_RememberMe;

    /**
     * Return the appropriate change password form for your application
     */
    protected function _getFormChangePassword()
    {
        throw new OSS_Exception( 'You must override this function to return a Zend_Form for password changing' );
    }

    /**
     * Action to allow a user to change their password
     *
     */
    public function changePasswordAction()
    {
        $this->view->passwordForm = $form = $this->_getFormChangePassword();

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            if( !OSS_Auth_Password::verify( $form->getValue( 'current_password' ), $this->getUser()->getPassword(), $this->_options['resources']['auth']['oss'] ) )
            {
                $form->getElement( 'current_password' )->addError(
                    'Invalid current password'
                );
                return $this->forward( 'index' );
            }

            // update the users password
            $this->getUser()->setPassword( OSS_Auth_Password::hash( $form->getValue( 'new_password' ), $this->_options['resources']['auth']['oss'] ) );
            $this->getD2EM()->flush();

            if( $this->_rememberMeEnabled() )
                $this->_deleteRememberMeCookie( $this->getUser() );

            $this->changePasswordPostFlush();
            $form->reset();

            $this->getLogger()->info( "User {$this->getUser()->getUsername()} changed password" );
            $this->addMessage( _( 'Your password has been changed.' ), OSS_Message::SUCCESS );
            $this->redirect( 'profile/index' );
        }

        $this->forward( 'index' );
    }

    /**
     * Hook that can be overridden to perform application specific actions after a password is
     * saved to the database (e.g. delete cached object)
     */
    protected function changePasswordPostFlush()
    {}

}
