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
 * @package    OSS_License
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * A class for creating and verifying software licenses.
 *
 * **This license model is based on TRUST under the commercial open
 * source model.** It is trivial to work around this license model.
 *
 * @category   OSS
 * @package    OSS_License
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_License_MD5 extends OSS_License_Abstract
{
    
    /**
     * Verify that the license is valid and in date
     *
     * @throws OSS_License_Exception
     * @return boolean True if valid, else an exception is thrown
     */
    public function verify()
    {
        $calcKey = md5( $this->_amalgamate() );
        
        if( $calcKey != strtolower( str_replace( '-', '', $this->getParam( 'Key' ) ) ) )
            throw new OSS_License_Exception( 'Invalid license key' );
        
        if( $this->getParam( 'Expires' ) !== null && $this->getParam( 'Expires' ) != '0' )
        {
            if( new DateTime() > new DateTime( $this->getParam( 'Expires' ) . ' 23:59:59' ) )
                throw new OSS_License_ExpiredException( 'Your license has expired' );
        }
        
        return true;
    }
    
    
    public function generate()
    {
        $this->setParam( 'Type', 'OSS_License_MD5' );
        $key = strtoupper( md5( $this->_amalgamate() ) );
        
        $p = [];
        for( $i = 0; $i < 8; $i++ )
            $p[] = substr( $key, $i * 4, 4 );
        
        $key = implode( '-', $p );
        
        $this->setParam( 'Key', $key );
        return $this->_createIni();
    }
}
