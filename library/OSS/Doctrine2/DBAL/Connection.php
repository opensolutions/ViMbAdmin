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
 * A trait for classes that want to use DBAL connections
 *
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Doctrine2_DBAL_Connection
{
    /**
     * Doctrine2 DBAL Connections
     * @var \Doctrine\DBAL\Connection[]
     */
    private $_dbalConnections = [];

    /**
     * DBAL uses named connections and we must try to avoid a clash with 'default'
     * which is what the main application may already be using.
     */
    private $_dbalAltDefaultName = null;
    
    /**
     * Instantiates a new DBAL connection (or returns an existing one.
     *
     * @param array|Zend_Config $params The Doctrine2 DBAL params (@see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html)
     * @param string $name The name of the connection (used especially when managing multiple connections). Default: ''default''
     * @returns \Doctrine\DBAL\Connection
     * @throws OSS_Doctrine2_Exception
     */
    protected function getDBAL( $params = null, $name = null )
    {
        // resolve name
        if( $name === null )
        {
            if( $this->_dbalAltDefaultName === null )
                $this->_dbalAltDefaultName = '__OSS_FW_' . OSS_String::random( 32, true, true, true, '', '' );
            
            $name = $this->_dbalAltDefaultName;
        }
        
        if( !isset( $this->_dbalConnections[ $name ] ) )
        {
            if( $params === null )
                throw new OSS_Doctrine2_Exception( "No parameters for new DBAL connection" );

            if( $params instanceof Zend_Config )
                $params = $params->toArray();

            $config = new \Doctrine\DBAL\Configuration();
            $this->_dbalConnections[ $name ] = \Doctrine\DBAL\DriverManager::getConnection( $params, $config );
        }

        return $this->_dbalConnections[ $name ];
    }

}
