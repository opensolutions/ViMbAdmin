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
class OSS_Form_Auth
{
    const USERNAME_TYPE_EMAIL    = 0;
    const USERNAME_TYPE_NONEMAIL = 1;


    /**
     * A utility function for creating a standard username element for login forms.
     *
     * You must specify the type (`USERNAME_TYPE_EMAIL` or `USERNAME_TYPE_NONEMAIL` and
     * this will add additional validators, etc.
     *
     * @param string $type The type of username expected
     * @param string $name The element name
     * @return Zend_Form_Element_Text The username element
     */
    public static function createUsernameElement( $type = self::USERNAME_TYPE_NONEMAIL, $name = 'username' )
    {
        $un = new Zend_Form_Element_Text( $name );

        $un->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' )
            ->addFilter( 'StripTags' )
            ->addFilter( 'StringToLower' )
            ->addFilter( 'StringTrim' );

        switch( $type )
        {
            case self::USERNAME_TYPE_EMAIL:
                $un->addValidator( 'StringLength', false, array( 5, 255 ) )
                   ->addValidator( 'EmailAddress', true, array( 'mx' => false ) )
                   ->setAttrib( 'class', 'span5 required' )
                   ->setLabel( _( 'Email' ) )
                   ->setAttrib( 'title', _( 'Email' ) );
               break;

            case self::USERNAME_TYPE_NONEMAIL:
                $un->setLabel( _( 'Username' ) )
                   ->setAttrib( 'title', _( 'Username' ) )
                   ->setAttrib( 'class', 'span3 required' );
                break;

            default:
                die( 'Unknown username element type in OSS_Form_Auth_Login::getUsernameElement' );
        }

        $un->getValidator( 'NotEmpty' )->setMessage( _( 'You must enter your username' ), Zend_Validate_NotEmpty::IS_EMPTY );

        return $un;
    }


    /**
     * A utility function for creating a standard password element for login forms.
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Password The password element
     */
    public static function createPasswordElement( $name = 'password' )
    {

        $pw = new Zend_Form_Element_Password( $name );

        return $pw->setLabel( _( 'Password' ) )
            ->setAttrib( 'title', _( 'Password' ) )
            ->setAttrib( 'size', 30 )
            ->setAttrib( 'class', 'span3 required' )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'StringLength', true, array( 1, 1024 ) )
            ->addFilter( 'HtmlEntitiesDecode' )
            ->addFilter( 'StripSlashes' )
            ->addFilter( 'StringTrim' );
    }

    /**
     * A utility function for creating a standard password confirmation element
     *
     * Also adds a validator to match it to the password field
     *
     * @param string $name The element name
     * @param string $pwname The matching password element name
     * @return Zend_Form_Element_Password The password element
     */
    public static function createPasswordConfirmElement( $name = 'confirm_password', $pwname = 'password' )
    {

        $pwc = new Zend_Form_Element_Password( $name );

        $pwc->setLabel( _( 'Confirm Password' ) )
            ->setAttrib( 'title', _( 'Confirm Password' ) )
            ->setAttrib( 'size', 30 )
            ->setAttrib( 'class', 'span3 required' )
            ->setRequired( true )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'OSSIdenticalField', true, array( 'fieldName' => $pwname, 'fieldTitle' => _( 'the password' ) ) )
            ->addFilter( 'StripSlashes' );

        $pwc->getValidator( 'NotEmpty' )->setMessage( _( 'The confirmation password is required and must match the password' ), Zend_Validate_NotEmpty::IS_EMPTY );

        return $pwc;
    }


    /**
     * A utility function for creating a standard password reset token element
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Text The password reset token element
     */
    public static function createPasswordResetTokenElement( $name = 'token' )
    {

        $token = new Zend_Form_Element_Text( $name );

        return $token->setLabel( _( 'Token' ) )
            ->setAttrib( 'title', _( 'Token' ) )
            ->setAttrib( 'size', 44 )
            ->setAttrib( 'class', 'span3 required' )
            ->setAttrib( 'maxlength', 40 )
            ->setRequired( true )
            ->addValidator( 'StringLength', true, array( 40, 40 ) )
            ->addFilter( 'StringTrim' )
            ->addFilter( 'StripSlashes' );
    }


    /**
     * A utility function for creating a standard 'remember me' element for login forms.
     *
     * @param string $name The element name
     * @return Zend_Form_Element_Checkbox The remember me element
     */
    public static function createRememberMeElement( $name = 'rememberme' )
    {
        $rm = new Zend_Form_Element_Checkbox( $name );

        return $rm->setLabel( _( 'Remember me on this computer' ) )
            ->setRequired( false )
            ->addFilter( 'Int' );
    }


    /**
     * A utility function for creating a standard 'lost password' button link.
     *
     * @param string $name The element name
     * @return OSS_Form_Element_Buttonlink - The button link element
     */
    public static function createLostPasswordElement( $name = 'lost_password' )
    {
        $fpw = new OSS_Form_Element_Buttonlink( $name );
        return $fpw->setAttrib( 'href', OSS_Utils::genUrl( 'auth', 'lost-password' ) )
            ->setAttrib( 'label', _( 'Lost Password' ) );
    }

    /**
     * A utility function for creating a standard 'lost username' button link.
     *
     * @param string $name The element name
     * @return OSS_Form_Element_Buttonlink - The button link element
     */
    public static function createLostUsernameElement( $name = 'lost_username' )
    {
        $fpw = new OSS_Form_Element_Buttonlink( $name );
        return $fpw->setAttrib( 'href', OSS_Utils::genUrl( 'auth', 'lost-username' ) )
            ->setAttrib( 'label', _( 'Lost Username' ) );
    }

    /**
     * A utility function for creating a standard 'return to login' button link.
     *
     * @param string $name The element name
     * @return OSS_Form_Element_Buttonlink - The button link element
     */
    public static function createReturnToLoginElement( $name = 'return_to_login' )
    {
        $fpw = new OSS_Form_Element_Buttonlink( $name );
        return $fpw->setAttrib( 'href', OSS_Utils::genUrl( 'auth', 'login' ) )
            ->setAttrib( 'label', _( 'Return to Login' ) );
    }
}
