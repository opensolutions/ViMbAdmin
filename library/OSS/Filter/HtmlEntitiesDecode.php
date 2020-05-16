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
 * @package    OSS_Filter
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Filter
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Filter_HtmlEntitiesDecode implements Zend_Filter_Interface
{
    /**
     * Corresponds to the second html_entity_decode() argument
     *
     * @var integer
     */
    protected $_quoteStyle;

    /**
     * Corresponds to the third html_entity_decode() argument
     *
     * @var string
     */
    protected $_encoding;

    /**
     * Constructor
     *
     * Sets filter options
     *
     * @param array $options
     * @return void
     */
    public function __construct( $options = array() )
    {
        if( $options instanceof Zend_Config )
        {
            $options = $options->toArray();
        }
        else if( !is_array( $options ) )
        {
            $options = func_get_args();
            $temp['quotestyle'] = array_shift( $options );
            if( !empty( $options ) )
            {
                $temp['charset'] = array_shift( $options );
            }

            $options = $temp;
        }

        if( !isset( $options['quotestyle'] ) )
            $options['quotestyle'] = ENT_COMPAT;

        if( !isset( $options['encoding'] ) )
            $options['encoding'] = 'UTF-8';

        if( isset( $options[ 'charset'] ) )
            $options['encoding'] = $options['charset'];

        $this->setQuoteStyle($options['quotestyle']);
        $this->setEncoding($options['encoding']);
    }

    /**
     * Returns the quoteStyle option
     *
     * @return int
     */
    public function getQuoteStyle()
    {
        return $this->_quoteStyle;
    }

    /**
     * Sets the quoteStyle option
     *
     * @param  integer $quoteStyle
     * @return OSS_Filter_HtmlEntitiesDecode
     */
    public function setQuoteStyle( $quoteStyle )
    {
        $this->_quoteStyle = $quoteStyle;
        return $this;
    }


    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
         return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param  string $value
     * @return OSS_Filter_HtmlEntitiesDecode
     */
    public function setEncoding( $value )
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Returns the charSet option
     *
     * Proxies to {@link getEncoding()}
     *
     * @return string
     */
    public function getCharSet()
    {
        return $this->getEncoding();
    }

    /**
     * Sets the charSet option
     *
     * Proxies to {@link setEncoding()}
     *
     * @param  string $charSet
     * @return OSS_Filter_HtmlEntitiesDecode
     */
    public function setCharSet ($charSet )
    {
        return $this->setEncoding( $charSet );
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, converting HTML entities to their characters 
     * equivalents where they exist
     *
     * @param  string $value
     * @return string
     */
    public function filter( $value )
    {
        return html_entity_decode( (string) $value, $this->getQuoteStyle(), $this->getEncoding() );
    }
}
