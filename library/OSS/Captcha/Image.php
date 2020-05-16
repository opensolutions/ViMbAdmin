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
 * @package    OSS_Captcha
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Captcha
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Captcha_Image extends Zend_Captcha_Image
{

    /**
     * The constructor generates a Captcha image.
     *
     * @param int $dotNoise  The dot noise level. Default: 100.
     * @param int $lineNoise The line noise level. Default: 5.
     * @param int $wordLen   The length of the Captcha word. Default: 6.
     * @param int $timeout   The timeout in seconds. Default: 1800.
     * @return OSS_Captcha_Image
     */
    public function __construct( $dotNoise = 100, $lineNoise = 5, $wordLen = 6, $timeout = 1800 )
    {
        parent::__construct();

        $captchaDir = OSS_Utils::getTempDir() . '/captchas';

        if( !file_exists( $captchaDir ) )
            mkdir( $captchaDir, 0777, true );

        if( strpos( dirname( __FILE__ ), 'src/' ) === false )
            $font = dirname( __FILE__ ) . '/../../data/font/freeserif.ttf';
        else
            $font = dirname( __FILE__ ) . '/../../../data/font/freeserif.ttf';

        $this->setTimeout( $timeout )
             ->setWordLen( $wordLen )
             ->setHeight( 80 )
             ->setFont( $font )
             ->setFontSize( 40 )
             ->setImgDir( $captchaDir )
             ->setDotNoiseLevel( $dotNoise )
             ->setLineNoiseLevel( $lineNoise );

        return $this;
    }

    /**
     *
     * Validates a captcha with the given ID against the given string.
     *
     * @param string $id The Captcha ID
     * @param string $value The captcha value from the 'user' being tested
     * @return bool
     */
    public static function _isValid( $id, $value )
    {
        if( isset( $_SESSION[ 'Zend_Form_Captcha_' . $id ]['word'] ) && $_SESSION[ 'Zend_Form_Captcha_' . $id ]['word'] == $value )
            return true;

        return false;
    }

}
