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
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Resource_Doctrine2cache extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Doctrine instance
     *
     * @var null|Doctrine\ORM\EntityManager
     */
    protected $_d2cache = null;

    /**
     * Initialisation function
     *
     * @return Doctrine\Common\Cache
     */
    public function init()
    {
        return $this->getDoctrine2cache();
    }


    /**
     * Get Doctrine2Cache
     *
     * @return Doctrine\Common\Cache
     */
    public function getDoctrine2cache()
    {
        if( $this->_d2cache === null )
        {
            // Get Doctrine configuration options from the application.ini file
            $config = $this->getOptions();

            if( !isset( $config['autoload_method'] ) )
                $config['autoload_method'] = 'git';
            
            switch( $config['autoload_method'] )
            {
                case 'pear':
                    require_once( $config['path'] . '/Tools/Setup.php' );
                    Doctrine\ORM\Tools\Setup::registerAutoloadPEAR();
                    break;
                    
                case 'dir':
                    require_once( $config['path'] . '/Tools/Setup.php' ); // FIXME
                    Doctrine\ORM\Tools\Setup::registerAutoloadDirectory();
                    break;

                case 'composer':
                    break;
                                        
                default:
                    require_once( $config['path'] . '/lib/Doctrine/ORM/Tools/Setup.php' );
                    Doctrine\ORM\Tools\Setup::registerAutoloadGit( $config['path'] );
            }
            
            if( $config['type'] == 'ApcCache' )
                $cache = new \Doctrine\Common\Cache\ApcCache();
            elseif( $config['type'] == 'MemcacheCache' )
            {
                $memcache = new Memcache();
                
                for( $cnt = 0; $cnt < count( $config['memcache']['servers'] ); $cnt++ )
                {
                    $server = $config['memcache']['servers'][$cnt];
                
                    $memcache->addServer(
                        isset( $server['host'] )         ? $server['host']         : '127.0.0.1',
                        isset( $server['port'] )         ? $server['port']         : 11211,
                        isset( $server['persistent'] )   ? $server['persistent']   : false,
                        isset( $server['weight'] )       ? $server['weight']       : 1,
                        isset( $server['timeout'] )      ? $server['timeout']      : 1,
                        isset( $server['retry_int'] )    ? $server['retry_int']    : 15
                    );
                }
                
                $cache = new \Doctrine\Common\Cache\MemcacheCache();
                $cache->setMemcache( $memcache );
            }
            else
                $cache = new \Doctrine\Common\Cache\ArrayCache();
            
            if( isset( $config['namespace'] ) )
                $cache->setNamespace( $config['namespace'] );
            
            
            // stick the cache in the registry
            Zend_Registry::set( 'd2cache', $cache );
            $this->setDoctrine2Cache( $cache );
        }

        return $this->_d2cache;
    }

    /**
     * Set the classes $_d2cache member
     *
     * @param Doctrine\Common\Cache $c The object to set
     * @return void
     */
    public function setDoctrine2Cache( $c )
    {
        $this->_d2cache = $c;
    }


}
