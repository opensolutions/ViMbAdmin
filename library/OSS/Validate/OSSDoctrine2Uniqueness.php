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
class OSS_Validate_OSSDoctrine2Uniqueness extends Zend_Validate_Abstract
{
    const NO_ENTMGR   = 'noEntityManager';
    const NOT_UNIQUE  = 'notUnique';
    const NO_ENTITY   = 'noEntity';
    const NO_PROPERTY = 'noProperty';

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NO_ENTMGR   => "There is no Doctrine2 Entity Manager available",
        self::NOT_UNIQUE  => "'%value%' already exists in the database",
        self::NO_ENTITY   => "Entity name is a required parameter in OSSDoctrineUniqueness",
        self::NO_PROPERTY => "Property name is a required parameter in OSSDoctrineUniqueness",
    );

    /**
     * The entity to use
     * @var string
     */
    protected $_entity;

    /**
     * The entity property to use
     * @var string
     */
    protected $_property;

    /**
     * The database to use
     * @var string
     */
    protected $_database = 'default';
    
    
    /**
     * Constructor
     *
     * $table and $column are required parameters.
     *
     * @param string $entity The database entity to use
     * @param string $property The entity property to check for uniqueness
     * @throws OSS_Validate_Exception
     * @return void
     */
    public function __construct( $params )
    {
        if( $params['entity'] == '' )
        {
            $this->_error( self::NO_ENTITY );
            return false;
        }

        if( $params['property'] == '' )
        {
            $this->_error( self::NO_PROPERTY );
            return false;
        }

        if( isset( $params['database'] ) )
            $this->_database = $params['database'];

        $this->setEntity(  $params['entity']    );
        $this->setProperty( $params['property'] );
    }


    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is unique
     *
     * @param  string $value
     * @throws Doctrine_Exception
     * @retrun bool
     */
    public function isValid( $value )
    {
        $this->_setValue( $value );

        $em = Zend_Registry::get( "d2em" )[ $this->_database ];
        
        $users = $em->getRepository( $this->getEntity() )
                    ->findBy( [ $this->getProperty() => $value ] );

        if( count( $users ) )
        {
            $this->_error( self::NOT_UNIQUE );
            return false;
        }

        return true;
    }


    /**
     * Setter method for $_property
     *
     * @param $p The property to set
     * @return void
     */
    public function setProperty( $p )
    {
        $this->_property = $p;
    }


    /**
     * Setter method for $_entity
     *
     * @param $e The entity to set
     * @return void
     */
    public function setEntity( $e )
    {
        $this->_entity = $e;
    }


    /**
     * Getter method for $_column
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->_property;
    }


    /**
     * Getter method for $_table
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->_entity;
    }

}
