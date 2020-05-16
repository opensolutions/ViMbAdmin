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
 * @package    OSS_Auth
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Auth
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Auth_DoctrineAdapter implements Zend_Auth_Adapter_Interface
{

    /**
     * The username
     *
     * @var string
     */
    private $_username = null;

    /**
     * The password
     *
     * @var string
     */
    private $_password = null;

    /**
     * The model
     *
     * @var string
     */
    private $_model = null;

    /**
     * Sets username and password for authentication
     *
     * @param string $username
     * @param string $password
     * @param string $model
     * @throws Zend_Auth_Adapter_Exception If parameters are incorrect / not present
     * @return void
     */
    public function __construct( $username, $password, $model )
    {
        if ( ($username == null) || ($username == '') || ($password == null) || ($password == '') )
        {
            throw new Zend_Auth_Adapter_Exception("No username / password specified");
        }

        $this->_username = $username;
        $this->_password = $password;
        $this->_model    = $model;

    }


    /**
    * Performs an authentication attempt
    *
    * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
    * @return Zend_Auth_Result
    */
    public function authenticate()
    {
        $user = Doctrine::getTable( $this->_model )->findOneByUsername( $this->_username );
        // $user === false if no record

        $result = array(
                        'code'  => Zend_Auth_Result::FAILURE,
                        'identity' => array( 'username' => $this->_username ),
                        'messages' => array()
        );

        if( $user === false )
        {
            $result['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $result['messages'][] = 'Username / password invalid';
        }
        else if( $user['password'] != $this->_password )
        {

            if( $this->_model == 'User' )
                $maxLogin = User::$LOGIN_ATTEMPTS_ALLOWED;
            else
                $maxLogin = MPSUser::$LOGIN_ATTEMPTS_ALLOWED;

            if( $user['failed_logins'] < $maxLogin )
            {
                $user['failed_logins']++;
                $user->save();
            }

            $result['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $result['messages'][] = 'Username / password invalid';
        }
        else if( $user->password == $this->_password )
        {
            if( $this->_model == 'User' )
                $maxLogin = User::$LOGIN_ATTEMPTS_ALLOWED;
            else
                $maxLogin = MPSUser::$LOGIN_ATTEMPTS_ALLOWED;

            if( $user['failed_logins'] >= $maxLogin )
            {
                $result['code'] = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
                $result['messages'][] = 'Your account has been locked out due to an excessive number of bad login attempts. Please follow the forgotten password link to set a new password.';
            }
            else
            {
                if( $user['failed_logins'] != 0 )
                {
                    $user['failed_logins'] = 0;
                    $user->save();
                }

                $result['code']     = Zend_Auth_Result::SUCCESS;
                $result['identity'] = array(
                                            'username' => $this->_username,
                                            'user'     => $user
                                      );
            }
        }

        return new Zend_Auth_Result( $result['code'], $result['identity'], $result['messages'] );
    }

}
