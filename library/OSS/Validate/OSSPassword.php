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
class OSS_Validate_OSSPassword extends Zend_Validate_Abstract
{

    const DEFAULT_STRENGTH = 2;

    const NOT_VALID = 'notValid';

    /**
     * List of regular expressions (character classes) that the password is checked against
     * @var array
     */
    protected static $REGEXPS = array(
        'LC_LETTER'   => '/[a-z]{1,}+/u',
        'UC_LETTER'   => '/[A-Z]{1,}+/u',
        'DIGIT'       => '/[0-9]{1,}+/u',
        'PUNCTUATION' => '/[\!\"\£\$\%\^\&\*\(\)\-\=\_\+\{\}\[\]\;\’\#\:\@\~\,\.\/\<\>\?\|]{1,}+/u'
    );

    /**
     * Error message templates
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => 'The given password is not strong enough. Please choose a password that is a mixture of upper and lower case letters or also contains a number of punctuation mark.'
    );

    /**
     * The number of character classes to test for
     *
     * @var int
     */
    private $_strength;

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'strength' => scalar, number of different character classes to test for
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct( $options = null )
    {
        if( $options instanceof Zend_Config )
        {
            $options = $options->toArray();
        }

        if( isset( $options['strength'] ) )
            $this->setStrength( $options['strength'] );
        else
            $this->setStrength( self::DEFAULT_STRENGTH );

    }


    /**
     * Set the password validator strength.
     *
     * @param int $s The strength to test for.
     * @throws OSS_Validate_Exception
     * @return void
     */
    public function setStrength( $s = self::DEFAULT_STRENGTH )
    {
        if( !is_numeric( $s ) || $s <= 0 || $s > count( self::$REGEXPS ) )
        {
            throw new OSS_Validate_Exception( 'Invalid strength provided' );
        }

        $this->_strength = $s;
    }

    /**
     * Get the password validator strength.
     *
     * @return int
     */
    public function getStrength()
    {
        return $this->_strength;
    }

    /**
     * Returns true if $valid (the password) meets the minimum strength requirements.
     *
     * @param  string $value
     * @return bool
     */
    public function isValid( $value )
    {
        $this->_setValue( $value );

        $strength = 0;

        foreach( self::$REGEXPS as $regexp )
        {
            if( Zend_Validate::is( $value, 'Regex', array( $regexp ) ) ) $strength++;
        }

        if( $strength < $this->getStrength() )
        {
            $this->_error( self::NOT_VALID );
            return false;
        }

        return true;
    }

}
