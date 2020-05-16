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
class OSS_Validate_OSSPSN extends Zend_Validate_Abstract
{

    const NOT_VALID = 'notValid';

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => 'This is not a valid PPS number.'
    );


    /**
     * Validates a given PPS number for the correct format. This
     * includes number and position of letters / digits and MOD23.
     *
     * @link    http://en.wikipedia.org/wiki/Personal_Public_Service_Number
     * @link    http://pear.php.net/package/Validate_IE/docs/latest/__filesource/fsource_Validate_IE__Validate_IE-1.0.2ValidateIE.php.html#a443
     *
     * @param string $value The tax number to validate
     * @return bool
     */
    public function isValid( $value )
    {
        // 1234567T or 1234567TW are valid (includes one or two letters either at the end)

        if( preg_match( '/^\d{7}[a-zA-Z]$/i', $value ) )
        {
            if( self::checkMOD23( $value ) === false )
            {
                $this->_error( self::NOT_VALID );
                return false;
            }

            return true;
        }

        if( preg_match( '/^\d{7}[a-zA-Z][\ wtxWTX]$/i', $value ) )
        {
            if( self::checkMOD23( substr( $value, 0, 8 ) ) === false )
            {
                $this->_error( self::NOT_VALID );
                return false;
            }

            return true;
        }

        if( preg_match( '/^\d{7}[a-zA-Z][a-zA-Z]$/i', $value ) )
        {
            if( self::checkMOD23( $value ) === false )
            {
                $this->_error( self::NOT_VALID );
                return false;
            }

            return true;
        }

        $this->_error( self::NOT_VALID );
        return false;
    }


    /**
     * Returns true if the checksum in the specified PPSN or tax number,
     * without the 'IE' prefix, is valid.
     *
     * @param string $value Value to perform modulus 23 checksum on.
     * @return bool
     */
    private static function checkMOD23( $value )
    {
        $total = 0;

        for( $i = 0; $i < 7; ++$i )
            $total += (int) $value[$i] * ( 8 - $i );

        if( strlen( $value ) == 9 )
            $total += ( ord( strtoupper( $value[8] ) ) - 64 ) * 9;

        $mod = $total % 23;

        if( $mod === 0 ) $mod = 23;

        return( chr( 64 + $mod ) == strtoupper( $value[7] ) );
    }

}
