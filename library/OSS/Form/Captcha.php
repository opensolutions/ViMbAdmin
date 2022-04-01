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
 * Untility methods to add common Auth/Login elements to an authentication form
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Form_Captcha
{
    /**
     * A utility function for creating a standard CAPTCHA id element
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Hidden The CAPTCHA id element
     */
    public static function createIdElement( $name = 'captchaid' )
    {
        $captchaid = new Zend_Form_Element_Hidden( $name );
        return $captchaid ->setValue( '' )
            ->setRequired( false );
    }
    
    /**
     * A utility function for creating a standard request new CAPTCHA image element
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Hidden The CAPTCHA id element
     */
    public static function createRequestNewImageElement( $name = 'requestnewimage' )
    {
        $requestNewImg = new Zend_Form_Element_Hidden( $name );
        return $requestNewImg->setValue( '0' )
            ->setRequired( false );
    }
        
    /**
     * A utility function for creating a standard CAPTCHA input element
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Text The CAPTCHA input element
     */
    public static function createInputElement( $name = 'captchatext' )
    {
        $captchaText = new Zend_Form_Element_Text( $name );
        
        return $captchaText->setLabel( 'Copy the text from the image above' )
            ->setAttrib( 'title', 'CAPTCHA' )
            ->setAttrib( 'size', 32)
            ->setAttrib( 'maxlength', 10 )
            ->setRequired( true )
            ->addValidator( 'StringLength', true, array( 6, 10 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' );
    }

    /**
     * Add the required CAPTCHA elements to a form
     *
     * @param OSS_Form $form The form to add the elements to
     * @return OSS_Form The same form for fluent interfaces
     */
    public static function addCaptchaElements( $form )
    {
        $form->addElement( self::createIdElement() );
        $form->addElement( self::createRequestNewImageElement() );
        $form->addElement( self::createInputElement() );
        return $form;
    }
    
    
}
