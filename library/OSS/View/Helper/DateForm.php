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
 * Helper to render Date form element
 *
 * @category   OSS
 * @package    OSS_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_View_Helper_DateForm extends Zend_View_Helper_FormElement
{

    /**
     * Helper for date form 
     *
     * @param string $name
     * @param null|strint $value
     * @param null|array $attribs
     * @return string
     */
    public function dateForm( $name, $value = null, $attribs = null )
    {
        // are we in a subform?
        if( mb_strpos( $name, ']' ) !== false )
        {
            $dateId   = mb_substr( $name, strrpos( $name, '[' ) + 1, -1 );
            $dateName = mb_substr( $name, 0, -1 );
        }
        else
        {
            $dateId   = $name;
            $dateName = $name;
        }

        if( !isset( $attribs['data-dateformat'] ) )
            $attribs['data-dateformat'] = OSS_Date::DF_EUROPEAN;

        $attribs['placeholder'] = OSS_Date::getFormat( $attribs['data-dateformat'] );
        $jqDateFormat = OSS_Date::getDatepickerFormat( $attribs['data-dateformat'] );

        $html = $this->view->formText( $dateName, $value,
                    array_merge(
                        $attribs,
                        array(
                        	'id' => $dateId,
                        	'style' => 'width: 90px;',
                            'maxlength' => 10
                        )
                    )
                );

        $html .= '
			<script type="text/javascript" id="' . $dateId . '_script">
			 $( document ).ready( function(){
                $( "#' . $dateId . '" ).datepicker({
                    dateFormat: "' . $jqDateFormat . '",
                    constrainInput: true,
                    changeMonth: true,
                    changeYear: true,';

        if( isset( $attribs['data-mindate'] ) )
            $html .= '
                    minDate: "' . $attribs['data-mindate'] . '",';

        if( isset( $attribs['data-maxdate'] ) )
            $html .= '
                    maxDate: "' . $attribs['data-maxdate'] . '",';

        if( !isset( $attribs['data-yearRange'] ) )
            $html .= '
                    yearRange: "2012:'. date( 'Y' ) . '"';
        else
            $html .= '
                    yearRange: "' . $attribs['data-yearRange'] . '"';

        $html .= '
                });

                $( "#' . $dateId . '_date_pick" ).bind( "click", function( e ){
                    $( "#' . $dateId . '" ).datepicker( "show" );
                });
            });
			</script>';

        return $html;
    }

}
