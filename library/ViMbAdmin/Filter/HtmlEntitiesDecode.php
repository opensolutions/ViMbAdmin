<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 Open Source Solutions Limited
 *
 * ViMbAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ViMbAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ViMbAdmin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Open Source Solutions Limited T/A Open Solutions
 *   147 Stepaside Park, Stepaside, Dublin 18, Ireland.
 *   Barry O'Donovan <barry _at_ opensolutions.ie>
 *
 * @copyright Copyright (c) 2011 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/**
 * html_entity_decode() filter for Zend_Form_Element-s
 *
 * @package ViMbAdmin
 * @subpackage Filter
 */
class ViMbAdmin_Filter_HtmlEntitiesDecode implements Zend_Filter_Interface
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
     * Sets filter options
     *
     * @param  integer|array $quoteStyle
     * @param  string  $charSet
     * @return void
     */
    public function __construct( $options = array() )
    {
        if ($options instanceof Zend_Config)
        {
            $options = $options->toArray();
        }
        elseif( !is_array( $options ) )
        {
            $options = func_get_args();
            $temp['quotestyle'] = array_shift( $options );

            if ( !empty( $options ) )
                $temp['charset'] = array_shift( $options );

            $options = $temp;
        }

        if( !isset( $options['quotestyle'] ) )
            $options['quotestyle'] = ENT_COMPAT;

        if( !isset( $options['encoding'] ) )
            $options['encoding'] = 'UTF-8';

        if( isset( $options['charset'] ) )
            $options['encoding'] = $options['charset'];

        $this->setQuoteStyle( $options['quotestyle'] );
        $this->setEncoding( $options['encoding'] );
    }


    /**
     * Returns the quoteStyle option
     *
     * @return integer
     */
    public function getQuoteStyle()
    {
        return $this->_quoteStyle;
    }


    /**
     * Sets the quoteStyle option
     *
     * @param  integer $quoteStyle
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setQuoteStyle($quoteStyle)
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
     * @return Zend_Filter_HtmlEntities
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
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setCharSet( $charSet )
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
