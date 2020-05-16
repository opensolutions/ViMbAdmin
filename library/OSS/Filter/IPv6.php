<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
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
 * @package    OSS_Filter
 * @subpackage DNS
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @subpackage DNS
 * @package    OSS_Filter
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Filter_IPv6 implements Zend_Filter_Interface
{
    /**
     * @var int $_type Type of the IPv6 address we want to get
     * @see OSS_Net_IPv6
     */
    protected $_type = OSS_Net_IPv6::TYPE_SHORT;

    /**
     * Set type
     *
     * @param int $type Set new type of the IPv6 address we want to get
     * @see OSS_Net_IPv6  
     */
    public function setType( $type )
    {
        $this->_type = $type;
    }

    /**
     * Get type
     *
     * @return int
     * @see OSS_Net_IPv6  
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Filtes IPv6 address.
     * 
     * 2001:07f8:0018:0002:0000:0000:0000:0147 filters to 2001:7f8:18:2::147
     * 2A01:7F8:18:0:0:0:0:0147 filters to 2a01:7f8:18::147
     *
     * @param string $value String to parse size in bytes
     * @return string
     */
    public function filter( $value )
    {
        try{
            $value = OSS_Net_IPv6::formatAddress( $value, $this->_type );
        }
        catch( Exception $e )
        {
        }
        return $value;
    }

}
