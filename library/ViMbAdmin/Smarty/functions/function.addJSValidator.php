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

/*
 * Adds a JavaScript front-end validator to views.
 *
 * @package ViMbAdmin
 * @subpackage Smarty_Functions
 */


    /**
     * Iterates through a form or subform object and analyzes it, and fills up the $validationRules array with the
     * supported validators and their properties.
     *
     * @param object $formObj
     * @param array &$validationRules
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

<script type=\"text/javascript\"> /* <![CDATA[ */

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
                                error.appendTo( '#help-' + element.attr('id') );
                             }
        });
    });

/* ]]> */ </script>

";

        return $ruleStr;
    }
