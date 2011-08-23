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
class AdminTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object AdminTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable( 'Admin' );
    }


    /**
     * Hashes and salts a password.
     *
     * @param string $password the password to hash
     * @param string $salt default '' the salt to use
     * @return string the hashed and salted password
     */
    public static function hashPassword( $password, $salt = '' )
    {
        return sha1( $password . $salt );
    }


    /**
     * Returns with the number of admins.
     *
     * @param void
     * @return int
     */
    public static function getCount()
    {
        $retVal = Doctrine_Query::create()
                    ->select( 'count(*) as howmany' )
                    ->from( 'Admin' )
                    ->fetchArray();

        return (int) $retVal[0]['howmany'];
    }


    /**
     * Returns with boolean true if the Admin table is empty, otherwise with false.
     *
     * @param void
     * @return boolean
     */
    public static function isEmpty()
    {
        return ( self::getCount() == 0 );
    }

}
