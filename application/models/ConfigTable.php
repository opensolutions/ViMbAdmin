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
class ConfigTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @param void
     * @return object ConfigTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable( 'Config' );
    }


    /**
     * Get a value for a key / value pair from the config table
     *
     * @param string $name The key of the value to load
     * @return string|boolean The value or false if it does not exist
     */
    public static function getValue( $name )
    {
        $a = Doctrine_Query::create()
                ->from( 'Config c' )
                ->select( 'c.value' )
                ->where( 'c.name = ?', $name )
                ->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );

        if( count( $a ) )
            return $a;

        return false;
    }


    /**
     * Creates or updates a key-value pair in the config table.
     *
     * @param string $name the key
     * @param string $value the value
     * @return void
     */
    public static function setValue( $name, $value )
    {
        $model = new Config();
        $model->name = $name;
        $model->value = $value;
        $model->replace();
    }


    /**
     * Deletes a given key from the config table.
     *
     * @param string $name The key to delete
     * @return void
     */
    public static function clearValue( string $name )
    {
        Doctrine_Query::create()
            ->delete( 'Config c' )
            ->where( 'c.name = ?', $name )
            ->execute();
    }

}
