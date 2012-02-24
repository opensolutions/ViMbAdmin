/*
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
 * @package ViMbAdmin
 */

/*
 * Extends the jQuery validate plugin with new features.
 *
 * inArray
 * notEmpty
 * equal
 * lessThan
 * greaterThan
 * isChecked
 * integer
 * betweenIn
 * betweenEx
 * macAddress
 * requiredIf
 */

    jQuery.validator.messages.remote = "Fix this field.";
    jQuery.validator.messages.email = "Invalid email address.";
    jQuery.validator.messages.url = "Enter a valid URL.";
    jQuery.validator.messages.date = "Enter a valid date.";
    jQuery.validator.messages.dateISO = "Enter a valid date.";
    jQuery.validator.messages.number = "Enter a valid number.";
    jQuery.validator.messages.digits = "Enter only digits.";
    jQuery.validator.messages.creditcard = "Invalid credit card number.";
    jQuery.validator.messages.equalTo = "Enter the same value again.";
    jQuery.validator.messages.accept = "Enter a value with a valid extension.";
    jQuery.validator.messages.maxlength = $.validator.format("Enter no more than {0} characters.");
    jQuery.validator.messages.minlength = $.validator.format("Enter at least {0} characters.");
    jQuery.validator.messages.rangelength = $.validator.format("Must be between {0} and {1} characters long.");
    jQuery.validator.messages.range = $.validator.format("Enter a value between {0} and {1}.");
    jQuery.validator.messages.max = $.validator.format("Enter a value less than or equal to {0}.");
    jQuery.validator.messages.min = $.validator.format("Enter a value greater than or equal to {0}.");


    /**
    * overwrite the default defaultMessage() method to 1st try to return with a custom message, then with the default one
    * prototype method
    */
    jQuery.validator.prototype.defaultMessage = function( element, method )
    {
        return this.findDefined(
            this.customMessage( element.name, method ),
            $.validator.messages[method],
            this.customMetaMessage( element, method ),
            // title is never undefined, so handle empty string as undefined
            !this.settings.ignoreTitle && element.title || undefined,
            "<b>Warning: No message defined for " + element.name + "</b>"
        );
    };


    /*
    // use this as a base for other new validators
    jQuery.validator.addMethod("math", function(value, element, param) {
     return value == param[0] + param[1];
    }, jQuery.format("Please enter the correct value for {0} + {1}"));
    */


    /**
    * checks if the value of the input field is in the specified array
    */
    jQuery.validator.addMethod("inArray", function(value, element, param) {
            // indexOf() exists since JavaScript 1.6 and it is not supported in IE 7 or below and Firefox below 1.5
            return this.optional(element) || (jQuery.inArray(value, param) != -1) || (jQuery.inArray(parseInt(value), param) != -1);
        },
        'Invalid value.'
    );


    /**
    * checks if the input field is not empty
    */
    jQuery.validator.addMethod("notEmpty", function(value, element, param) {
            return this.optional(element) || (value != '');
        },
        'This field is required.'
    );


    /**
    * checks if the value of the input field is equal to the specified value
    */
    jQuery.validator.addMethod("equal", function(value, element, param) {
            return this.optional(element) || (value == param);
        },
        'Invalid value.'
    );


    /**
    * checks if the value of the input field is less than X
    */
    jQuery.validator.addMethod("lessThan", function(value, element, param) {
            return (value < param);
        },
        jQuery.validator.format("The number must be less than {0}.")
    );


    /**
    * checks if the value of the input field is greater than X
    */
    jQuery.validator.addMethod("greaterThan", function(value, element, param) {
            return this.optional(element) || (value > param);
        },
        jQuery.validator.format("The number must be greater than {0}.")
    );


    /**
    * checks if the checkbox or radio button is checked
    */
    jQuery.validator.addMethod("isChecked", function(value, element, param) {
            // we can do that because id == name in Zend Form, unless the form decorators and viewhelpers are heavily overridden
            return this.optional(element) || ($('#' + element.name).attr('checked'));
        },
        'You must tick this checkbox.'
    );


    /**
    * checks if the value of the input field is an integer (can include plus and minus signs)
    */
    jQuery.validator.addMethod("integer", function(value, element) {
            return this.optional(element) || /^[+\-]?\d+$/.test(value);
        },
        'A positive or negative integer please.'
    );


    /**
    * checks if the value of the input field is between X and Y (inclusive)
    */
    jQuery.validator.addMethod("betweenIn", function(value, element, param) {
            return this.optional(element) || ( value >= param[0] && value <= param[1] );
        },
        jQuery.validator.format("Please enter a value between {0} and {1} (inclusive).")
    );


    /**
    * checks if the value of the input field is between X and Y (exclusive)
    */
    jQuery.validator.addMethod("betweenEx", function(value, element, param) {
            return this.optional(element) || ( value > param[0] && value < param[1] );
        },
        jQuery.validator.format("Please enter a value between {0} and {1} (exclusive).")
    );


    /**
    * checks if the value of the input field is a valid MAC address in the accepted formats
    * valid MAC address formats: 0123456789AB | 01-23-45-67-89-AB | 01:23:45:67:89:AB | 0123.4567.89AB
    */
    jQuery.validator.addMethod("macAddress", function(value, element) {
            vValue = value.toUpperCase();

            // valid MAC address formats: 0123456789AB | 01-23-45-67-89-AB | 01:23:45:67:89:AB | 0123.4567.89AB
            vMatch0 = vValue.search( /^[0-9A-F]{12}$/ );
            vMatch1 = vValue.search( /^([0-9A-F]{2}\-){5}([0-9A-F]{2})$/ );
            vMatch2 = vValue.search( /^([0-9A-F]{2}\:){5}([0-9A-F]{2})$/ );
            vMatch3 = vValue.search( /^([0-9A-F]{4}\.){2}([0-9A-F]{4})$/ );

            return this.optional(element) || ( (vMatch0 + vMatch1 + vMatch2 + vMatch3) > -4 );
        },
        'Please enter a valid MAC address.'
    );


    /**
    * Makes a field required if the condition is met.
    *
    * param is an object, having these properties:
    * field: other field's id
    * condition: a comparison operator: == , > , > , <= , >= , !=
    * value: the other field's value we are looking for
    */
    jQuery.validator.addMethod("requiredIf", function(value, element, param)
        {
            vEvalStr = "$('#" + param['field'] + "').val() " + param['condition'] + " '" + param['value'] + "';";
            return !( (eval(vEvalStr) == true) && (this.getLength(value, element) == 0) );
        },
        'This field is required.'
    );


    /**
    * checks if the value of the input field is a valid hostname, like "domain.com"
    */
    jQuery.validator.addMethod("hostname", function(value, element, param)
        {
            return /^[A-Za-z0-9-_]+(\.[A-Za-z0-9]{2,}){1,}$/.test( value );
        },
        'Please enter a valid hostname.'
    );
