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


    /**
    * This function is based on the
    *
    * Email obfuscator script 2.1 by Tim Williams, University of Arizona
    * Random encryption key feature by Andrew Moulden, Site Engineering Ltd
    * This code is freeware provided these four comment lines remain intact
    * A wizard to generate this code is at http://www.jottings.com/obfuscator/
    *
    * Writes the HTML code for a secure email address link into the page.
    *
    * @param string coded the encoded email address
    * @param string key the key
    * @param string linktext the link text to show as thext in the anchor, defaults to the email address itself
    * @return void
    */
    function obfuscatedEmailLink(coded, key, linktext)
    {
        shift = coded.length;
        link = "";

        for (i = 0; i < coded.length; i++)
        {
            if (key.indexOf(coded.charAt(i)) == -1)
            {
                ltr = coded.charAt(i);
                link += (ltr);
            }
            else
            {
                ltr = (key.indexOf(coded.charAt(i)) - shift + key.length) % key.length;
                link += (key.charAt(ltr));
            }
        }

        if ( (linktext === null) || (linktext == undefined) ) linktext = link;

        document.write("E-Mail: <a href='mailto:" + link + "'>" + linktext + "</a>");
    }


    /**
    * Returns with the jQuery UI Dialog button object, or with null.
    *
    * @param string dialog_selector the class of the dialog (use custom, empty or non-existing classes)
    * @param string button_name the name of the button, like 'Cancel' or 'Add'
    * @return object|null
    */
    function getDialogButton( dialog_selector, button_name )
    {
        var buttons = $( dialog_selector + ' .ui-dialog-buttonpane button' );

        for ( var i = 0; i < buttons.length; ++i )
        {
            var jButton = $( buttons[i] );

            if ( jButton.text() == button_name ) return jButton;
        }

        return null;
    }


    /**
    * does what PHP htmlentities() does
    * this is the string object method version (prototyped)
    *
    * @param string (note, this is not a real function parameter, it is a prototype method, so method chaining is happening here)
    * @return string
    * @uses jQuery
    */
    String.prototype.htmlEntity = function()
    {
        return $("<div/>").text( this.substr() ).html();
    }


    /**
    * does what PHP html_entity_decode() does
    * this is the string object method version (prototyped)
    *
    * @param string (note, this is not a real function parameter, it is a prototype method, so method chaining is happening here)
    * @return string
    * @uses jQuery
    */
    String.prototype.htmlEntityDecode = function()
    {
        return $("<div/>").html( this.substr() ).text();
    }


    /**
    * Converts a string to Camel Case
    *
    * @author: Paul Visco
    */
    String.prototype.ucwords = function()
    {
        var arr = this.split(' ');
        var str ='';

        arr.forEach( function(v) { str += v.charAt(0).toUpperCase() + v.slice(1, v.length) + ' ' } );

        return str;
    }


    /**
    * does what PHP htmlentities() does
    * this is the standalone function version
    *
    * @param string str
    * @return string
    * @uses jQuery
    */
    function htmlEntity(str)
    {
        return $( '<div />' ).text( str ).html();
    }


    /**
    * does what PHP html_entity_decode() does
    * this is the standalone function version
    *
    * @param string str
    * @return string
    * @uses jQuery
    */
    function htmlEntityDecode(str)
    {
        return $( '<div />').html( str ).text();
    }


    /**
    * jQuery Unserialize
    *
    * @author: James Campbell
    */
    (function($)
    {
        $.unserialise = function( Data )
        {
            var Data = Data.split("&");
            var Serialised = new Array();

            $.each(Data, function()
            {
                var Properties = this.split("=");
                Serialised[Properties[0]] = Properties[1];
            });

            return Serialised;
        };
    })(jQuery);


    /**
     * Generates a random password of the given length from [a-zA-Z0-9].
     * It makes sure that the generated password contains digits, lowercase and uppercase characters.
     *
     * @author Roland Huszti <roland _at_ opensolutions.ie>
     */
    function randomPassword( pwdLength )
    {
        var charSet = "0123456789abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var password = '';

        while( true )
        {
            for( var x = 0; x < pwdLength; x++ )
                password += charSet.charAt( Math.floor( Math.random() * charSet.length ) );

            // not the same as search('[a-zA-z0-9]') !!!!
            if ( (password.search('[a-z]') != -1) && (password.search('[A-Z]') != -1) && (password.search('[0-9]') != -1) )
                return password;
        }
    }


    /**
     * Checks if the value is a valid email address or not.
     *
     * @param string str
     * @return boolean
     */
    function isValidEmail( str )
    {
        return /^([A-Za-z0-9_\-\+\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test( str );
    }
