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
 * @package    OSS_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */
/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper to render button-link element.
 *
 * @category   OSS
 * @package    OSS_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_View_Helper_Buttonlink extends Zend_View_Helper_FormElement
{
    /**
     * Generates a link button.
     *
     * @param string|array $name If a string, the element name.  If an array,
     *    all other parameters are ignored, and the array elements are
     *    extracted in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function buttonlink( $name, $value = null, $attribs = null )
    {
        $info = $this->_getInfo( $name, $value, $attribs );
        extract( $info ); // name, value, attribs, options, listsep, disable, id
        
        // check if disabled
        $disabled = '';
        if( $disable )
            $disabled = ' disabled="disabled"';

        if( $id )
            $id = ' id="' . $this->view->escape( $id ) . '"';

        $label = $attribs["label"];
        unset( $attribs["label"] );

        if( isset( $attribs["class"] ) )
        {
            $class = ' class="' . $attribs["class"] . '" ';
            unset( $attribs["class"] );
        }
        else
            $class = ' class="btn" ';

        // Render the button.
        $xhtml = '<a ' . $class . $id . $disabled . $this->_htmlAttribs( $attribs )
                 . $this->getClosingBracket() . $label . '</a>';
        
        return $xhtml;
    }
}
