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
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Form extends Zend_Form
{
    
    // possible traits
    //
    use OSS_Form_Trait;                   // *** REQUIRED if using traits
    // use OSS_Form_Trait_CancelLocation;
    use OSS_Form_Trait_GenericElements;
    // use OSS_Form_Trait_InsertElementFns;
    // use OSS_Form_Trait_IsEdit;
    // use OSS_Form_Trait_Doctrine1Mapping;
    // use OSS_Form_Trait_Doctrine2Mapping;
    
    /**
     * Constructor
     *
     * @param  null|array $options An array of options
     * @param  bool $isEdit True if the form is for editing as opposed to adding
     * @return void
     */
    public function __construct( $options = null )
    {
        $this->addElementPrefixPath( 'OSS_Filter',   'OSS/Filter/',   'filter' );
        $this->addElementPrefixPath( 'OSS_Validate', 'OSS/Validate/', 'validate' );
        
        parent::__construct( $options );
        
        if( method_exists( $this, 'initialiseTraits' ) )
            $this->initialiseTraits( $options );
    }
    
}
