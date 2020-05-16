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
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Functionality for setting and getting FileSize filter options
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Form_Trait_FileSize
{
    /**
     * FilterSize default multplier
     * @var string
     */
    private $filter_filesize_multiplier = OSS_Filter_FileSize::SIZE_BYTES;

    /**
     * Sets multiplier for FileSize filter
     *
     * Valid multiplier options: B, KB, MB, GB. Where case is not sensitive.
     *
     * @param string $multiplier Sets default multiplier it's not set by user.
     * @return void
     * @throws OSS_Exception If multiplier is not one of $SIZE_MULTIPLIERS KEY.
     */
    public function setFilterFileSizeMultiplier( $multiplier )
    {
        if( array_key_exists( strtoupper( $multiplier ), OSS_Filter_FileSize::$SIZE_MULTIPLIERS ) )
            $this->filter_filesize_multiplier = strtoupper( $multiplier );
        else
            throw new OSS_Exception( "Trying to set unknown multiplier for FileSize filter." );

        foreach( $this->getElements() as $name => $element )
        {
            if( $element->getFilter( 'FileSize' ) )
            {
                $element->removeFilter( 'FileSize' );
                $element->addFilter( new OSS_Filter_FileSize( $this->getFilterFileSizeMultiplier() ) );
            }
        }
    }

    /**
     * Return multiplier for FileSize filter
     *
     * @param void
     * @return string
     */
    public function getFilterFileSizeMultiplier()
    {
        return $this->filter_filesize_multiplier;
    }
}
