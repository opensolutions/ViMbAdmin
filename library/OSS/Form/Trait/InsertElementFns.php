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
 * Functionality for adding elements at specific positions
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Form_Trait_InsertElementFns
{
    /**
     * Inserts an element into the form after an already existing element.
     *
     * @param string|object $afterElement an element name or an instance of Zend_Form_Element, the new element will be placed after that one
     * @param string|object $element an element name or an instance of Zend_Form_Element, the new element which will be inserted
     * @param string $name default null the name for the new element
     * @param array $options default null the options for the new element
     * @param int $order default null if there are ordered subforms in the form, then passing an order number might be necessary to position the element correctly
     * @return void
     */
    public function addElementAfter( $afterElement, $element, $name = null, $options = null, $order = null )
    {
        $this->addElement( $element, $name, $options );

        if( $afterElement instanceof Zend_Form_Element )
            $aename = $afterElement->getName();
        else
            $aename = $afterElement;

        if( $element instanceof Zend_Form_Element )
            $ename = $element->getName();
        else
            $ename = $element;

        unset( $this->_order[$ename] );

        $newOrder = array();
        foreach( $this->_order as $iname => $item )
        {
            $newOrder[$iname] = $item;

            if( $iname == $aename )
                $newOrder[$ename] = $order;
        }

        $this->_order = $newOrder;
    }


    /**
     * Inserts an element into the form before an already existing element.
     *
     * @param string|object $beforeElement an element name or an instance of Zend_Form_Element, the new element will be placed before that one
     * @param string|object $element an element name or an instance of Zend_Form_Element, the new element which will be inserted
     * @param string $name default null the name for the new element
     * @param array $options default null the options for the new element
     * @param int $order default null if there are ordered subforms in the form, then passing an order number might be necessary to position the element correctly
     * @return void
     */
    public function addElementBefore( $beforeElement, $element, $name = null, $options = null, $order = null )
    {
        $this->addElement( $element, $name, $options );

        if( $beforeElement instanceof Zend_Form_Element )
            $bename = $beforeElement->getName();
        else
            $bename = $beforeElement;

        if( $element instanceof Zend_Form_Element )
            $ename = $element->getName();
        else
            $ename = $element;

        unset( $this->_order[$ename] );

        $newOrder = array();

        foreach( $this->_order as $iname => $item)
        {
            if( $iname == $bename )
                $newOrder[$ename] = $order;

            $newOrder[$iname] = $item;
        }

        $this->_order = $newOrder;
    }

}
