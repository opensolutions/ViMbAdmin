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
 * @package    OSS_Validate
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Validate
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Validate_OSSDoctrineUniqueness extends Zend_Validate_Abstract
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
        self::NO_TABLE => "Table name is a required parameter in OSSDoctrineUniqueness",
        self::NO_COLUMN => "Column name is a required parameter in OSSDoctrineUniqueness",
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
     * Constructor
     *
     * $table and $column are required parameters.
     *
     * @param array $params Array of params kontainig table and column
     * @throws OSS_Validate_Exception
     * @return bool|void
     */
    public function __construct( $params )
    {
        if( $params['table'] == '' )
        {
            $this->_error( self::NO_TABLE );
            return false;
        }

        if( $params['column'] == '' ) 
        {
            $this->_error( self::NO_COLUMN );
            return false;
        }

        $this->setTable( $params['table']) ;
        $this->setColumn( $params['column']) ;
    }


    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is unique
     *
     * @param  string $value
     * @throws Doctrine_Exception
     * @return bool
     */
    public function isValid( $value )
    {
        $this->_setValue( $value );

        $table = Doctrine::getTable( $this->getTable() );

        $col = $this->getColumn();

        if ( !$table->hasColumn( $col ) ) throw new Doctrine_Exception( "Column {$col} does not exist on table " . $this->getTable() );

        $fn = "findBy{$col}";
        $rows = $table->$fn( $value );

        if ( $rows->count() != 0 )
        {
            $this->_error(self::NOT_UNIQUE);
            return false;
        }

        return true;
    }


    /**
     * Setter method for $_column
     *
     * @param $_column the $_column to set
     * @return void
     */
    public function setColumn($_column)
    {
        $this->_column = $_column;
    }


    /**
     * Setter method for $_table
     *
     * @param $_table the $_table to set
     * @return void
     */
    public function setTable($_table)
    {
        $this->_table = $_table;
    }


    /**
     * Getter method for $_column
     * @return string
     */
    public function getColumn()
    {
        return $this->_column;
    }


    /**
     * Getter method for $_table
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

}
