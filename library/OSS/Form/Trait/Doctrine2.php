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
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English;
use Doctrine\Inflector\RulesetInflector;




/**
 * Functionality for creating / editing elements and other functionality using a Doctrine2 backend
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Form_Trait_Doctrine2
{
    /**
     * Assigns form to entity ( Doctrine 2 ).
     *
     * @param object $entity The Doctrine2 Entity
     * @param OSS_Controller_Action $controller An instance of OSS_Controller_Action
     * @param bool $isEdit
     * @return object The Doctrine2 Entity
     */
    public function assignFormToEntity( $entity, $controller, $isEdit )
    {
        $fields  = $controller->getD2EM()->getClassMetadata( get_class( $entity ) )->getFieldNames();
    
        foreach( $this->getElements() as $eName => $eConfig )
        {
            if( in_array( $eName, $fields ) && !$eConfig->getAttrib( 'readonly' ) )
            {
                $fn = 'set' . static::inflector()->classify( $eName );
                $entity->$fn( $this->getValue( $eName ) );
            }
        }
    
        return $entity;
    }
    
    
    /**
     * Assigns entity to form ( Doctrine 2 ).
     *
     * @param object $entity The Doctrine2 Entity
     * @param OSS_Controller_Action $controller An instance of OSS_Controller_Action
     * @param bool $isEdit
     * @return OSS_Form The form object
     */
    public function assignEntityToForm( $entity, $controller, $isEdit = true )
    {
        $fields  = $controller->getD2EM()->getClassMetadata( get_class( $entity ) )->getFieldNames();
    
        foreach( $this->getElements() as $eName => $eConfig )
        {
            if( in_array( $eName, $fields ) )
            {
                $fn = 'get' . static::inflector()->classify( $eName );
                
                if( $entity->$fn() instanceof DateTime )
                    $this->getElement( $eName )->setValue( $entity->$fn()->format( 'Y-m-d H:i:s' ) );
                else
                    $this->getElement( $eName )->setValue( $entity->$fn() );
            }
        }
    
        return $this;
    }
    
    
    /**
     * Populate a Zend_Form SELECT element from a database table
     *
     * This function essential crafts a basic query and then calls `populateSelectFromDatabaseQuery()`
     *
     * @see populateSelectFromDatabaseQuery()
     * @param Zend_Form_Element_Select $element The form element to populate
     * @param string $entity The Doctrine2 entity class to select items from
     * @param string $indexElement The element with which to set the select value attributes with (typically `id`)
     * @param string|array $displayElements If a string, then the database column element to show in the select
     *         dropdown. If an array, the contents of these elements will be concatenated with dashes
     * @param string $orderBy The element to order by
     * @param string $orderDir The order direction
     * @return int The maximum value of the $indexElement (asuming integer!)
     */
    public static function populateSelectFromDatabase( $element, $entity, $indexElement, $displayElements, $orderBy = null, $orderDir = 'ASC' )
    {
        if( !is_array( $displayElements ) )
            $displayElements = [ $displayElements ];

        $select = "e.{$indexElement} AS {$indexElement}";
        foreach( $displayElements as $idx => $de )
        {
            if( is_array( $de ) )
                $select .= ", e.{$idx} AS {$idx}";
            else
                $select .= ", e.{$de} AS {$de}";
        }
        
        $qb = Zend_Registry::get( 'd2em' )['default']->createQueryBuilder()
            ->select( $select )->from( $entity, 'e' );

        if( $orderBy !== null )
            $qb->orderBy( "e.{$orderBy}", $orderDir == 'DESC' ? 'DESC' : 'ASC' );

        return self::populateSelectFromDatabaseQuery( $qb->getQuery(), $element, $entity, $indexElement, $displayElements, $orderBy, $orderDir );
    }
    
    /**
     * Populate a Zend_Form SELECT element from a database table
     *
     * @param \Doctrine\ORM\Query $query The query to for the database select
     * @param Zend_Form_Element_Select $element The form element to populate
     * @param string $entity The Doctrine2 entity class to select items from
     * @param string $indexElement The element with which to set the select value attributes with (typically `id`)
     * @param string|array $displayElements If a string, then the database column element to show in the select
     *         dropdown. If an array, the contents of these elements will be concatenated with dashes
     * @param string $orderBy The element to order by
     * @param string $orderDir The order direction
     * @return int The maximum value of the $indexElement (asuming integer!)
     */
    public static function populateSelectFromDatabaseQuery( $query, $element, $entity, $indexElement, $displayElements, $orderBy = null, $orderDir = 'ASC' )
    {
        if( !is_array( $displayElements ) )
            $displayElements = [ $displayElements ];

        $rows = $query->getResult();

        $options = array( '0' => '' );
        $maxId = 0;
    
        foreach( $rows as $r )
        {
            $text = '';
    
            foreach( $displayElements as $idx => $de )
            {
                if( is_array( $de ) )
                {
                    switch( $de['type'] )
                    {
                        case 'STRING':
                            $str = $r[$idx];
                            break;
                            
                        case 'DATE':
                        case 'TIME':
                        case 'DATETIME':
                            $str = $r[$idx]->format( $de['format'] );
                            break;
                            
                        default:
                            die( 'Unhandled type in OSS/Form/Trait/Doctrine2::populateSelectFromDatabaseQuery()' );
                    }
                }
                else
                    $str = $r[$de];
                
                $text .= "{$str} - ";
            }
    
            $text = substr( $text, 0, strlen( $text ) - 3 );
    
            $options[ $r[$indexElement] ] = $text;
    
            if( $r[$indexElement] > $maxId )
                $maxId = $r[$indexElement];
        }
    
        $element->setMultiOptions( $options );
    
        return( $maxId );
    }



    protected static function inflector()
    {
        return new Inflector(
            new CachedWordInflector(new RulesetInflector(
                English\Rules::getSingularRuleset()
            )),
            new CachedWordInflector(new RulesetInflector(
                English\Rules::getPluralRuleset()
            ))
        );
    }

    
}
