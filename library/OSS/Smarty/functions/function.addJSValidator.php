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
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */

    /**
     * Get form elements
     *
     * @category   OSS
     * @package    OSS_Smarty
     * @subpackage Functions
     *
     * @param OSS_Form $formObj Form to get elements
     * @param array &$validationRules Array of rules for validate elements
     * @return void
     */
    function __vimbadmin_getFormElements( $formObj, &$validationRules )
    {
        foreach( $formObj->getElements() as $oneElement )
        {
            $fieldId = $oneElement->getID();

            if( get_class($oneElement) == 'Zend_Form_Element_Checkbox' )
                $validationRules[$fieldId]['field_type'] = get_class( $oneElement );

            if( $oneElement->isRequired() == true )
                $validationRules[$fieldId]['required'] = true;

            foreach( $oneElement->getValidators() as $oneValidator )
            {
                switch( get_class($oneValidator) )
                {
                    case 'Zend_Validate_NotEmpty':
                            $validationRules[$fieldId]['notEmpty'] = true;
                            break;

                    case 'Zend_Validate_StringLength':
                            $validationRules[$fieldId]['minlength'] = $oneValidator->getMin();
                            $validationRules[$fieldId]['maxlength'] = $oneValidator->getMax();
                            break;

                    case 'Zend_Validate_EmailAddress':
                            $validationRules[$fieldId]['email'] = true;
                            break;

                    case 'Zend_Validate_Digits':
                            $validationRules[$fieldId]['digits'] = true;
                            break;

                    case 'Zend_Validate_Int':
                            $validationRules[$fieldId]['integer'] = true;
                            break;

                    case 'Zend_Validate_Float':
                            $validationRules[$fieldId]['number'] = true;
                            break;

                    case 'Zend_Validate_Ccnum':
                            $validationRules[$fieldId]['creditcard'] = true;
                            break;

                    case 'Zend_Validate_InArray':
                            $validationRules[$fieldId]['inArray'] = $oneValidator->getHaystack();
                            break;

                    case 'Zend_Validate_Between':
                            if( $oneValidator->getInclusive() )
                                $validationRules[$fieldId]['betweenIn'] = array( $oneValidator->getMin() , $oneValidator->getMax() );
                            else
                                $validationRules[$fieldId]['betweenEx'] = array( $oneValidator->getMin() , $oneValidator->getMax() );
                            break;

                    case 'Zend_Validate_LessThan':
                            $validationRules[$fieldId]['lessThan'] = $oneValidator->getMax();
                            break;

                    case 'Zend_Validate_GreaterThan':
                            $validationRules[$fieldId]['greaterThan'] = $oneValidator->getMin();
                            break;

                    case 'Zend_Validate_Hostname':
                            $validationRules[$fieldId]['hostname'] = true;
                            break;

                    case 'ViMbAdmin_Validate_IdenticalField':
                            $validationRules[$fieldId]['equalTo'] = '#' . $oneValidator->getFieldName();
                            break;

                    default:
                            break;
                } // switch ( validator class )
            } // foreach validators
        } // foreach elements
    }


    /**
     * Function to add the JQuery form validator to a form.
     *
     * the parameters in $params are:
     *
     * 'form' - the form object, must be a Zend_Form or an inherited class of that
     *
     * @category   OSS
     * @package    OSS_Smarty
     * @subpackage Functions
     *
     * @param array $params An array of the parameters to make up the URL
     * @param Smarty $smarty A reference to the Smarty template object
     * @return string
     */
    function smarty_function_addJSValidator( $params, &$smarty )
    {
        if( !isset( $params['form'] ) || !is_object( $params['form'] ) || !is_subclass_of( $params['form'], 'Zend_Form' ) )
            return '';

        $validationRules = array();
        $formObj = $params['form'];

        __vimbadmin_getFormElements( $formObj, $validationRules );

        if( sizeof( $formObj->getSubForms() ) != 0 )
        {
            foreach( $formObj->getSubForms() as $subForm )
                __vimbadmin_getFormElements( $subForm, $validationRules );
        }

        $ruleStr = "

<script type=\"text/javascript\">

    $(document).ready(function() {
        $('#" . $formObj->getId() . "').validate({
            rules: {\n";

        foreach( $validationRules as $fieldId => $ruleSet )
        {
            $ruleStr .= "                '{$fieldId}': {";

            foreach( $ruleSet as $ruleKey => $ruleValue )
            {
                if( $ruleKey == 'field_type' ) continue;
                if ( !isset( $ruleSet['required'] ) ) $ruleSet['required'] = false;

                if( isset( $ruleSet['field_type'] ) &&
                    ( $ruleSet['field_type'] == 'Zend_Form_Element_Checkbox' ) &&
                    ( $ruleSet['required'] )
                )
                {
                    $ruleStr .= " isChecked: true,";
                    continue;
                }

                $ruleStr .= " {$ruleKey}: ";

                switch( getType( $ruleValue ) )
                {
                    case 'boolean':
                            $ruleStr .= ($ruleValue == true ? 'true' : 'false');
                            break;

                    case 'integer':
                    case 'double':
                            $ruleStr .= $ruleValue;
                            break;

                    case 'array':
                            if( sizeof( $ruleValue ) )
                            {
                                $ruleStr .= '[ ';

                                foreach( $ruleValue as $key => $oneValue )
                                {
                                    switch( getType( $oneValue ) )
                                    {
                                        case 'integer':
                                        case 'double':
                                                $ruleStr .= $oneValue . ', ';
                                                break;

                                        case 'boolean':
                                                $ruleStr .= ( $oneValue == true ? 'true' : 'false' ) . ', ';
                                                break;

                                        case 'string':
                                        default:
                                                $ruleStr .= "'{$oneValue}', ";
                                                break;
                                    }
                                }

                                $ruleStr = substr( trim( $ruleStr ), 0, -1 ) . ' ]';
                            }
                            else
                            {
                                $ruleStr .= '[ ]';
                            }

                            break;

                    case 'object':
                            $ruleStr .= '{ ';

                            foreach( $ruleValue as $key => $oneValue )
                            {
                                $ruleStr .= $key . ': ';

                                switch( getType( $oneValue ) )
                                {
                                    case 'integer':
                                    case 'double':
                                            $ruleStr .= $oneValue . ', ';
                                            break;

                                    case 'boolean':
                                            $ruleStr .= ( $oneValue == true ? 'true' : 'false' ) . ', ';
                                            break;

                                    case 'string':
                                    default:
                                            $ruleStr .= "'{$oneValue}', ";
                                            break;
                                }
                            }

                            $ruleStr = substr( trim( $ruleStr ), 0, -1 ) . ' }';
                            break;

                    case 'string':
                    default:
                            $ruleStr .= "'{$ruleValue}'";
                            break;
                }

                $ruleStr .= ",";
            }

            $ruleStr = mb_substr( trim( $ruleStr ), 0, -1 );
            $ruleStr .= " },\n";
        }

        $ruleStr = mb_substr( trim( $ruleStr ), 0, -1 ) . "\n";
        $ruleStr .= "            },
        errorElement: 'span',
        errorPlacement: function (error, element) {
                                var id = element.attr('id');
                                error.appendTo( '#help-' + id );
                             },
        highlight: function (element, errorClass ) {
                                var id = element['id'];
                                var wraper = $( '#' + id ).closest( '.control-group' );
                                wraper.addClass( 'error' );
                                var parent = false;
                                if( wraper.parent().hasClass( 'tab-pane' ) )
                                    parent = wraper.parent();
                                else if( ( wraper.parent().parent().hasClass( 'row' ) || wraper.parent().parent().hasClass( 'row-fluid' ) ) && wraper.parent().parent().parent().hasClass( 'tab-pane' ) )
                                    parent = wraper.parent().parent().parent();

                                if( parent )
                                    $( 'a[href|=\"#' + parent.attr( 'id' ) + '\"]' ).addClass( 'text-error' );
                             },
        unhighlight: function(element, errorClass, validClass){
                                var id = element['id'];
                                var wraper = $( '#' + id ).closest( '.control-group' );
                                wraper.removeClass( 'error' );
                                var parent = false
                                if( wraper.parent().hasClass( 'tab-pane' ) )
                                    parent = wraper.parent();
                                else if( ( wraper.parent().parent().hasClass( 'row' ) || wraper.parent().parent().hasClass( 'row-fluid' ) ) && wraper.parent().parent().parent().hasClass( 'tab-pane' ) )
                                    parent = wraper.parent().parent().parent();

                                wraper.find( '.help-block' ).each( function(){
                                    if( $( this ).is( 'span' ) )
                                        $( this ).remove();
                                });

                                if( parent )
                                {
                                    if( !parent.find( '.error' ).find( '.control-label' ).length )
                                        $( 'a[href|=\"#' + parent.attr( 'id' ) + '\"]' ).removeClass( 'text-error' );
                                }
                            }                    
        });
    });

</script>

";

        return $ruleStr;
    }
