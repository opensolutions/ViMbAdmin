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
class TokenTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object TokenTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable( 'Token' );
    }


    /**
    * Creates a random string of a given length from [a-zA-Z0-9], but some letters are excluded
    * from the character set: 1, 0, O, I, l
    *
    * @param int $length default 6 the length of the password to be generated
    * @return string the random string
    */
    public static function createRandomString( $length = 6 )
    {
        $charSet = "abcdefghijkmnopqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        return substr( str_shuffle( "{$charSet}{$charSet}" ), 0, $length );
    }


    /**
    * Adds a new token to the admin, and returns with the id of the newly created Doctrine model.
    *
    * @param object $admin an Admin model object
    * @param string $tokenType
    * @param string $token default null if null then one will be generated
    * @param string $rid default null if null then one will be generated
    * @return object the new Token model object
    */
    public static function addToken( $admin, $tokenType, $token = null, $rid = null )
    {
        $model = new Token();

        $model->username = $admin['username'];
        $model->type     = $tokenType;
        $model->token    = ( $token === null ? self::createRandomString() : $token );
        $model->rid      = ( $rid === null ? self::createRandomString( 32 ) : $rid );

        $model->save();

        return $model;
    }


    /**
    * Deletes tokens from the table, and returns with the number of deleted rows. If the $tokenType is null,
    * then it will delete all the tokens of the admin, otherwise only the tokes of the specified type.
    *
    * @param object $admin an Admin object
    * @param string $tokenType default null
    * @return int
    */
    public static function deleteTokens( $admin, $tokenType = null )
    {
        $query = Doctrine_Query::create()
                    ->delete()
                    ->from( 'Token' )
                    ->where('username = ?', $admin['username'] );

        if( $tokenType !== null )
            $query->addWhere( 'type = ?', $tokenType );

        return $query->execute();
    }

}
