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
 * A base class for creating and verifying software licenses.
 *
 * @category   OSS
 * @package    OSS_License
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
abstract class OSS_License_Abstract
{
    /**
     * @var array The licnese key value pairs
     */
    protected $_license = null;
    
    public function __construct( $license = [] )
    {
        $this->_license = $license;
    }
    
    
    /**
     * Set a license parameter
     *
     * @param string $p The parameter name (INI compatible value)
     * @param string $v The parameter value (INI compatible value)
     * @return OSS_License_Abstract Instance of the license for fluent interfaces
     */
    public function setParam( $p, $v )
    {
        $this->_license[ $p ] = $v;
        return $this;
    }
    
    /**
     * Get a license parameter
     *
     * @param string $p The parameter name (INI compatible value)
     * @return string The parameter value (or null)
     */
    public function getParam( $p )
    {
        return isset( $this->_license[ $p ] ) ? $this->_license[ $p ] : null;
    }

    /**
     * Get all license parameters as an array
     *
     * @return array The license parameters
     */
    public function getParams()
    {
        return $this->_license;
    }

    /**
     * Create a single string of all license parameters for key verification / generation
     *
     * @return string
     */
    protected function _amalgamate()
    {
        // amalgamate all license parameters
        $l = '';
        foreach( $this->_license as $p => $v )
            if( $p != 'Key' ) $l .= "{$p}:{$v};";
        
        return $l;
    }
    
    /**
     * Create an INI format of the license
     *
     * @return string
     */
    protected function _createIni()
    {
        $l = '';
        foreach( $this->_license as $p => $v )
            $l .= "{$p} = \"{$v}\"\n";
        
        return $l;
    }
    
    /**
     * Verify that the license is valid
     *
     * @throws OSS_License_Exception An exception with a public error message
     * @return bool True if it is valid (exception on failure)
     */
    abstract function verify();

    /**
     * Generate the INI license
     *
     * @return string The license
     */
    abstract function generate();
}
