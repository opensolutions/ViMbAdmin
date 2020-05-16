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
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\Common\Util\Inflector,
    Doctrine\ORM\EntityManager;

/**
 * A Doctrine2 Entity Serializer
 *
 * Based on:
 * @link https://github.com/borisguery/bgylibrary/blob/master/library/Bgy/Doctrine/EntitySerializer.php
 * which is licensed under http://sam.zoy.org/wtfpl/COPYING. This is turn was based
 * on the Gist:
 * @link https://gist.github.com/1034079#file_serializable_entity.php
 *
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Doctrine2_EntitySerializer
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * @var int
     */
    protected $_recursionDepth = 0;

    /**
     * @var int
     */
    protected $_maxRecursionDepth = 0;

    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    public function setEntityManager( EntityManager $em )
    {
        $this->_em = $em;

        return $this;
    }

    protected function _serializeEntity( $entity )
    {
        $className = get_class( $entity );
        $metadata = $this->_em->getClassMetadata( $className );

        $data = array();

        foreach( $metadata->fieldMappings as $field => $mapping )
        {
            $value = $metadata->reflFields[$field]->getValue( $entity );
            $field = Inflector::tableize( $field );
            if( $value instanceof \DateTime )
            {
                // We cast DateTime to array to keep consistency with array result
                $data[$field] = (array)$value;
            }
            elseif( is_object( $value ) )
            {
                $data[$field] = (string)$value;
            }
            else
            {
                $data[$field] = $value;
            }
        }
  
        foreach( $metadata->associationMappings as $field => $mapping )
        {
            $key = Inflector::tableize( $field );
            if( $mapping['isCascadeDetach'] )
            {
                $data[$key] = $metadata->reflFields[$field]->getValue( $entity );
                if( null !== $data[$key] )
                {
                    $data[$key] = $this->_serializeEntity($data[$key]);
                }
            }
            elseif( $mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE )
            {
                if( null !== $metadata->reflFields[$field]->getValue( $entity ) )
                {
                    if( $this->_recursionDepth < $this->_maxRecursionDepth )
                    {
                        $this->_recursionDepth++;
                        $data[$key] = $this->_serializeEntity(
                            $metadata->reflFields[$field]
                                ->getValue( $entity )
                            );
                        $this->_recursionDepth--;
                    }
                    else
                    {
                        $data[$key] = $this->getEntityManager()
                            ->getUnitOfWork()
                            ->getEntityIdentifier(
                                $metadata->reflFields[$field]
                                    ->getValue( $entity )
                                );
                    }
                }
                else
                {
                    // In some case the relationship may not exist, but we want
                    // to know about it
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity to an array
     *
     * @param The entity $entity
     * @return array
     */
    public function toArray( $entity )
    {
        return $this->_serializeEntity( $entity );
    }


    /**
     * Convert an entity to a JSON object
     *
     * @param The entity $entity
     * @return string
     */
    public function toJson( $entity )
    {
        return json_encode( $this->toArray( $entity ) );
    }

    /**
     * Convert an entity to XML representation
     *
     * @param The entity $entity
     * @throws OSS_Doctrine2_Exception
     */
    public function toXml( $entity )
    {
        throw new OSS_Doctrine2_Exception( 'Not yet implemented' );
    }

    /**
     * Set the maximum recursion depth
     *
     * @param int $maxRecursionDepth
     * @return void
     */
    public function setMaxRecursionDepth( $maxRecursionDepth )
    {
        $this->_maxRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth
     *
     * @return int
     */
    public function getMaxRecursionDepth()
    {
        return $this->_maxRecursionDepth;
    }

}
