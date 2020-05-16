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
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Functionality for setting, getting and creating 'Cancel' buttons
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Form_Trait_CancelLocation
{
    /**
     * Where to go it the add / edit is cancelled.
     * @var string Where to go it the add / edit is cancelled.
     */
    public $cancelLocation = '';

    /**
     * The trait's initialisation method.
     *
     * This function is called from the form's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param  null|array $options An array of options
     * @param  bool $isEdit True if the form is for editing as opposed to adding
     */
    public function OSS_Form_Trait_CancelLocation_Init( $options )
    {
        if( is_array( $options ) && isset( $options['cancelLocation'] ) )
            $this->cancelLocation = $options['cancelLocation'];
    }
    
    /**
     * A utility function for creating a standard cancel button for forms.
     *
     * @param string $name The element name
     * @param string $cancelLocation The cancel location URL
     * @return Zend_Form_Element_Submit The cancel element
     */
    public function createCancelElement( $name = 'cancel', $cancelLocation = null )
    {
        if( $cancelLocation === null )
            $cancelLocation = $this->cancelLocation;
    
        $cancel = new OSS_Form_Element_Buttonlink( $name );
    
        return $cancel->setAttrib( 'href', $cancelLocation )
            ->setAttrib( 'label', _( 'Cancel' ) );
    }
    
    /**
     * Set / change the cancel location
     *
     * @param string $cancelLocation The cancel location URL
     * @param string $name The element name
     * @return Zend_Form The form object for fluent interfaces
     */
    public function updateCancelLocation( $cancelLocation, $name = 'cancel' )
    {
        $this->getElement( $name )->setAttrib( 'href', $cancelLocation );
        $this->cancelLocation = $cancelLocation;
        return $this;
    }
}
