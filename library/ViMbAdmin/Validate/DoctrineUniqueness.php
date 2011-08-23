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

/*
 * Class to validate for an item's uniqueness in a Doctrone backend database column.
 *
 * @package ViMbAdmin
 * @subpackage Validator
 */
class ViMbAdmin_Validate_DoctrineUniqueness extends Zend_Validate_Abstract
{

    const NOT_UNIQUE = 'notUnique';
    const NO_TABLE = 'noTable';
    const NO_COLUMN = 'noColumn';

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_UNIQUE => "'%value%' already exists in the database",
        self::NO_TABLE => "Table name is a required parameter in DoctrineUniqueness",
        self::NO_COLUMN => "Column name is a required parameter in DoctrineUniqueness",
    );

    /**
     * The database table to use
     * @var string
     */
    protected $_table;

    /**
     * The table column to use
     * @var string
     */
    protected $_column;


    /**
     * Uniqueness constructor.
     *
     * $table and $column are required parameters.
     *
     * @param string $table The database table to use
     * @param string $column The column in the table to check for uniqueness
     * @throws ViMbAdmin_Validate_Exception
     */
    public function __construct( $pParams )
    {
        if( $pParams['table'] == '' )
        {
            $this->_error( self::NO_TABLE );
            return false;
        }

        if( $pParams['column'] == '' )
        {
            $this->_error( self::NO_COLUMN );
            return false;
        }

        $this->setTable( $pParams['table'] );
        $this->setColumn( $pParams['column'] );
    }


    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is unique
     *
     * @param  string $value
     * @return boolean
     * @throws Doctrine_Exception
     */
    public function isValid( $value )
    {
        $this->_setValue( $value );

        $table = Doctrine::getTable( $this->getTable() );

        $col = $this->getColumn();

        if( !$table->hasColumn( $col ) )
            throw new Doctrine_Exception( "Column {$col} does not exist on table " . $this->getTable() );

        $fn = "findBy{$col}";
        $rows = $table->$fn( $value );

        if( $rows->count() != 0 )
        {
            $this->_error( self::NOT_UNIQUE );
            return false;
        }

        return true;
    }


    /**
     * Setter method for $_column
     * @param $_column the $_column to set
     */
    public function setColumn($_column)
    {
        $this->_column = $_column;
    }


    /**
     * Setter method for $_table
     * @param $_table the $_table to set
     */
    public function setTable($_table)
    {
        $this->_table = $_table;
    }


    /**
     * Getter method for $_column
     * @return the $_column
     */
    public function getColumn()
    {
        return $this->_column;
    }


    /**
     * Getter method for $_table
     * @return the $_table
     */
    public function getTable()
    {
        return $this->_table;
    }

}
