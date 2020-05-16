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
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Doctrine2_Utils
{
    
    /**
     * Assign values to Doctrine2 entities from an array.
     * 
     * The array indexes must match the setter function names less 'set'.
     * 
     * E.g. with array( 'Name' => 'Joe' ) we'd call setName()
     * 
     * @param Doctrine\ORM\Mapping $entity The entity to assign values to
     * @param array $array The associative array to take values from
     * @param bool $throw If no setter method exists in the entity for an array index, throw an exception
     * @throws OSS_Doctrine2_Exception If $trow param is passed and is true
     * @return Doctrine\ORM\Mapping The entity as passed for fluent interfaces
     */
    static public function assignFromArray( $entity, $array, $throw = true )
    {
        foreach( $array as $k => $v )
        {
            $fn = 'set' . $k;
            
            if( !method_exists( $entity, $fn ) )
            {
                if( $throw )
                    throw new OSS_Doctrine2_Exception( "Property / setter $fn does not exist." );
                else
                    continue;
            }

            $entity->$fn( $v );
        }
        
        return $entity;
    }
    
}
