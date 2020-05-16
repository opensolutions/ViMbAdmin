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

class OSS_Validate_OSSDomainNAme extends Zend_Validate_Abstract
{

    const INVALID_REC = 'invalidRec';

    /**
     * Possible error messages
     *
     * @var array
     */
    protected $_messages = array(
        self::INVALID_REC => 'Invalid domain name.'
    );

    /**
     * It will return true if given value is valid domain
     *
     * e.g. Valid names: exmaple.com, 02exmaple.com, test.example.ie
     *      Invalid names ex$ample.ie, example.ie23, test.example, test..ie
     *
     * @param strig $value Date string
     * @param null|mixed $context
     * @return bool
     */
    public function isValid( $value, $context = null )
    {
        $parts = explode( '.', strtolower( $value ) );
        
        if( count( $parts ) < 2 )
            return false;
        
        foreach( $parts as $ix => $part )
        {
            if( strlen( $part ) < 1 )
                return false;
                
            if( $ix == count( $parts ) - 1 )
            {
                if( !preg_match( '/^[a-z]+$/', $part ) || strlen( $part ) > 6 || strlen( $part ) < 2 )
                    return false;
            }
            
            if( !preg_match( '/^[a-z0-9\-]+$/', $part ) )
                return false;
                
            if( substr( $part, 0, 1 ) == '-' || substr( $part, -1, 1 ) == '-' )
                return false;
        }
    
        return true;
    }

}
