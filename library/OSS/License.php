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
 * @category   OSS
 * @package    OSS_License
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_License
{
    /**
     * Load a given license file and return with the appropriate license object
     *
     * @param string $file The full path to the license file
     * @throws OSS_License_Exception
     * @return OSS_License_Abstract An instance of an appropriate license type
     */
    public static function load( $file )
    {
        if( ( $license = @parse_ini_file( $file ) ) === false )
            throw new OSS_License_Exception( 'Could not open / parse license file' );
        
        
        if( !isset( $license['Type'] ) )
            throw new OSS_License_Exception( 'Invalid license file format.' );
        
        if( !isset( $license['Key'] ) )
            throw new OSS_License_Exception( 'Invalid license file format.' );
        
        switch( $license['Type'] )
        {
            case 'OSS_License_MD5':
                return new OSS_License_MD5( $license );
                break;
                
            default:
                throw new OSS_License_Exception( 'Unsupprted license type.' );
        }
    }
}
