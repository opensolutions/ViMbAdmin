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
 * @package ViMbAdmin
 * @subpackage Models
 */
class Mailbox extends BaseMailbox
{
    /**
     * Set the maildir
     *
     * Replaces the following characters in the $maildir parameter:
     *
     * %u - the local part of the username (email address)
     * %d - the domain part of the username (email address)
     * %m - the username (email address)
     *
     * @param string $maildir The maildir format
     * @return string The newly created maildir (also set in the object)
     */
    public function formatMaildir( $maildir = '' )
    {
        $this['maildir'] = self::substitute( $this['username'], $maildir );
        return $this['maildir'];
    }

    /**
     * Replaces the following characters in the $str parameter:
     *
     * %u - the local part of the username (email address)
     * %d - the domain part of the username (email address)
     * %m - the username (email address)
     *
     * @param string $email An email address used to extract the domain name
     * @param string $str The format string
     * @return string The newly created maildir (also set in the object)
     */
    public static function substitute( $email, $str )
    {
        list( $un, $dn ) = explode( '@', $email );

        $str = str_replace ( '%u', $un,    $str );
        $str = str_replace ( '%d', $dn,    $str );
        $str = str_replace ( '%m', $email, $str );

        return $str;
    }

    /**
     * Set the password using appropriate hash
     *
     * @param string $scheme The hashing scheme
     * @param string $password The password to hash
     * @param string $salt The salt to use. Default: none
     * @return string The newly hashed password (also set on object)
     */
    public function hashPassword( $scheme, $password, $salt = '' )
    {
        switch( $scheme )
        {
            case 'md5':
                $this['password'] = md5( $password );
                break;

            case 'md5.salted':
                $this['password'] = md5( $password . $salt );
                break;

            // MD5 based salted password hash nowadays commonly used in /etc/shadow.
            case 'md5.crypt':
                if( strlen( $salt ) >= 8 )
                    $s = '$1$' . substr( $salt, 0, 8 ) . '$';
                else
                    throw new ViMbAdmin_Exception( sprintf( _( 'This hashing function requires a hash of at least %d character(s) to be defined in application.ini (defaults.mailbox.password_hash)' ), 8 ) );

                $this['password'] = crypt( $password, $s );
                break;

            case 'sha1':
                $this['password'] = sha1( $password );
                break;

            case 'sha1.salted':
                $this['password'] = sha1( $password . $salt );
                break;

            case 'plain':
                $this['password'] = $password;
                break;

            // Standard DES hash compatible with MySQL ENCRYPT()
            case 'crypt':
                if( strlen( $salt ) >= 2 )
                    $s = substr( $salt, 0, 2 );
                else
                    throw new ViMbAdmin_Exception( sprintf( _( 'This hashing function requires a hash of at least %d character(s) to be defined in application.ini (defaults.mailbox.password_hash)' ), 2 ) );

                $this['password'] = crypt( $password, $s );
                break;

            default:
                die( 'Invalid password hash scheme in models/Mailbox.php hashPassword()' );
        }

        return $this['password'];
    }

}
