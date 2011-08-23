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
 * Doctrine auth adapter.
 *
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Auth_DoctrineAdapter implements Zend_Auth_Adapter_Interface
{

    /**
     * The username
     *
     * @var string The username
     */
    private $_username = null;

    /**
     * The password
     *
     * @var string The password
     */
    private $_password = null;


    /**
     * Sets username and password for authentication
     *
     * @throws Zend_Auth_Adapter_Exception If parameters are incorrect / not present
     * @return void
     */
    public function __construct( $username, $password )
    {
        if ( ($username == null) || ($username == '') || ($password == null) || ($password == '') )
            throw new Zend_Auth_Adapter_Exception( _( 'No username / password specified.' ) );

        $this->_username = $username;
        $this->_password = $password;
    }


    /**
    * Performs an authentication attempt
    *
    * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
    * @return Zend_Auth_Result
    */
    public function authenticate()
    {
        $admin = Doctrine::getTable( 'Admin' )->findOneByUsername( $this->_username );
        // $admin === false if no record

        $result = array(
                        'code'  => Zend_Auth_Result::FAILURE,
                        'identity' => array( 'username' => $this->_username ),
                        'messages' => array()
        );

        if( ( $admin === false ) || ( $admin->password != $this->_password ) )
        {
            $result['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $result['messages'][] = _( 'Username / password invalid' );
        }
        elseif( $admin->active == 0 )
        {
            $result['code'] = Zend_Auth_Result::FAILURE;
            $result['messages'][] = _( 'The account is inactive.' ) ;
        }
        else
        {
            $result['code']     = Zend_Auth_Result::SUCCESS;
            $result['identity'] = array(
                                        'username' => $this->_username,
                                        'admin'     => $admin
                                  );
        }

        return new Zend_Auth_Result( $result['code'], $result['identity'], $result['messages'] );
    }

}
