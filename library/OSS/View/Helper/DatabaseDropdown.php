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
 * Helper to render database dropdown form element
 *
 * NOTICE: It requires chosen library
 *
 * @category   OSS
 * @package    OSS_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_View_Helper_DatabaseDropdown extends Zend_View_Helper_FormElement
{

    /**
     * Helper for database dropdown
     *
     * @param string $name
     * @param null|strint $value
     * @param null|array $attribs
     * @return string
     */
    public function databaseDropdown( $name, $value = null, $attribs = null )
    {
        // are we in a subform?
        if( mb_strpos( $name, ']' ) !== false )
        {
            $elId   = mb_substr( $name, strrpos( $name, '[' ) + 1, -1 );
            $elName = mb_substr( $name, 0, -1 );
        }
        else
        {
            $elId   = $name;
            $elName = $name;
        }


        if( isset( $attribs['data-osschzn-options'] ) )
        {
            $options = json_decode( $attribs['data-osschzn-options'] );
            unset( $attribs['data-osschzn-options'] );
        }
        else
            $options = [];

        $html = $this->view->formText( $elName, $value,
                    array_merge(
                        $attribs,
                        array(
                            'id' => $elId
                        )
                    )
                );
        if( count( $options ) && ( !isset( $attribs['disabled'] ) || $attribs['disabled'] = false ) && ( !isset( $attribs['class'] ) || strpos( $attribs['class'], "disable" ) === false ) )
        {
            $html .= '
            <div id="' . $elId . '_tmp">
                <script type="text/javascript" id="' . $elId . '_script">
                    var tmp = $( "#' . $elId . '_tmp" ).html();
                    if( !tmp )
                    {
                        tmp = $("#' . $elId . '").parent().html();
                        $("#' . $elId . '").parent().html("<div id=\"' . $elId . '_append\" class=\"input-append\">" + tmp + "<span id=\"' . $elId . '_open\" class=\"btn\"><b class=\"caret\"></b></span></div>");
                        $("#' . $elId . '").parent().after( "<br /><select id=\"' . $elId . '_osschzn\">\\
                            ';
                        foreach( $options as $key => $value )
                        {
                            $html .= '<option val=\"' . $key . '\" label=\"' . $value. '\">' . $value. '</opion>\\
                            ';
                        }
                        $html .= '</select>" );
                        var pos = $( "#' . $elId . '" ).position();
                        $( "#' . $elId . '" ).width( $( "#' . $elId . '" ).width() - 20 );

                        $( "#' . $elId . '_osschzn" ).width( $( "#' . $elId . '" ).parent().width() ).chosen();

                        var ' . $elName . '_chosen_id = $( "#' . $elId . '_osschzn_chzn" ).length ? "#' . $elId . '_osschzn_chzn" : "#' . $elId . '_osschzn_chosen";
                        $( ' . $elName . '_chosen_id ).css( "position", "absolute" ).css( "top", pos.top ).hide();

                        $( "#' . $elId . '_open" ).on( "click",
                            function(){
                                $( "#' . $elId . '_osschzn" ).val( "" ).trigger( "liszt:updated" ).trigger( "chosen:updated" );
                                $( ' . $elName . '_chosen_id ).show( "fast", function(){
                                    $( ' . $elName . '_chosen_id ).trigger( "mousedown" );
                                    $( "#' . $elId . '_append" ).hide();
                                });
                                return;
                        });

                        $( "#' . $elId . '_osschzn" ).on( "change", function(){
                            $( "#' . $elId . '" ).val( $( this ).val() );
                        });

                        $( "#' . $elId . '_osschzn" ).on( "liszt:hiding_dropdown", function( event ){
                            $( ' . $elName . '_chosen_id ).hide();
                            $( "#' . $elId . '_append" ).show();
                        });

                        $( "#' . $elId . '_osschzn" ).on( "chosen:hiding_dropdown", function( event ){
                            $( ' . $elName . '_chosen_id ).hide();
                            $( "#' . $elId . '_append" ).show();
                        });

                        $( "#' . $elId . '_osschzn" ).on( "liszt:showing_dropdown", function( event ){
                            $( ' . $elName . '_chosen_id ).show();
                        });

                        $( "#' . $elId . '_osschzn" ).on( "chosen:showing_dropdown", function( event ){
                            $( ' . $elName . '_chosen_id ).show();
                        });

                        $( window ).resize(function() {
                            if( $( ' . $elName . '_chosen_id ).width() == 0 ) {
                                $( ' . $elName . '_chosen_id ).width( $( "#' . $elId . '" ).parent().width() );
                            }
                            $( ' . $elName . '_chosen_id ).hide();
                            $( "#' . $elId . '_append" ).show();
                            pos = $( "#' . $elId . '" ).position();
                            $( ' . $elName . '_chosen_id ).css( "top", pos.top );
                        });

                        var height = $(this).height();

                        $(document).bind( "DOMSubtreeModified", function() {
                            if($(this).height() != height ) {
                                height = $(this).height();
                                $( ' . $elName . '_chosen_id ).hide();
                                $( "#' . $elId . '_append" ).show();
                                pos = $( "#' . $elId . '" ).position();
                                $( ' . $elName . '_chosen_id ).css( "top", pos.top );
                            }
                        });
                    }
                    else
                    {
                        $( "#' . $elId . '_tmp" ).remove();
                        $( "#' . $elId . '" ).parent().after( tmp );
                    }
                </script>
            </div>';
        }

        return $html;
    }

}
