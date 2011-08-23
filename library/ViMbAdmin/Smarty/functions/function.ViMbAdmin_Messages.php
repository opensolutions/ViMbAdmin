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
 * Displays system messages from session.
 *
 * @return string the JavaScript code
 *
 * @package ViMbAdmin
 * @subpackage Smarty_Functions
 */
function smarty_function_ViMbAdmin_Messages( $params, &$smarty )
{
    $messages = $smarty->getTemplateVars( 'ViMbAdmin_Messages' );

    if( $messages === null ) $messages = array();

    if( isset( $_SESSION['Application']['ViMbAdmin_Messages'] ) && is_array( $_SESSION['Application']['ViMbAdmin_Messages'] )
            && sizeof( $_SESSION['Application']['ViMbAdmin_Messages'] ) > 0 )
    {
        $messages = array_merge( $messages, $_SESSION['Application']['ViMbAdmin_Messages'] );
        unset( $_SESSION['Application']['ViMbAdmin_Messages'] );
    }

    if ( $messages == array() ) return '';

    $message = '<div id="vimbadmin_messages">' . "\n";

    $count = 0;

    foreach( $messages as $oneMessage )
    {
        if( isset( $params['randomid'] ) && $params['randomid'] ) $count = mt_rand();

        $items = $oneMessage->getMessage();

        if( !is_array( $items ) )
            $items = array( $items );

        foreach( $items as $item )
        {
            $message .= <<<END_MESSAGE

<div id="vimbadmin-message-{$count}">
    <div class="vimbadmin-message vimbadmin-message-{$oneMessage->getClass()}">
        <p>
END_MESSAGE;

            switch( $oneMessage->getClass() )
            {
                case ViMbAdmin_Message::ERROR:
                    $message .= '                <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>' . "\n";
                    break;

                default:
                    break;
            }

            $message .= <<<END_MESSAGE
            {$item}
            <span id="vimbadmin-message-close-icon-{$count}" class="ui-state-default ui-corner-all vimbadmin-message-icon" title="Close">
                <span class="ui-icon ui-icon-close"></span>
            </span>
        </p>
    </div>
</div>

END_MESSAGE;

        } // end inner foreach

        $count++;
    } // end foreach()

    // add JavaScript
    $message .= <<<END_JS

<script type="text/javascript"> /* <![CDATA[ */

    jQuery( document ).ready( function()
    {
        $("span[id^='vimbadmin-message-close-icon-']").hover( function() {
                var theid= $(this).attr('id').substr(29);
                $("span[id='vimbadmin-message-close-icon-" + theid + "'] > span").addClass( 'ui-state-hover' );
            },
            function() {
                var theid= $(this).attr('id').substr(29);
                $("span[id='vimbadmin-message-close-icon-" + theid + "'] > span").removeClass( 'ui-state-hover' );
            }
        );

        $("span[id^='vimbadmin-message-close-icon-']").click( function() {
                var theid= $(this).attr('id').substr(29);

                $("div[id='vimbadmin-message-" + theid + "']").slideUp( 'fast', function() {
                    $("div[id='vimbadmin-message-" + theid + "']").remove()
                } );
        } );
    } );

/* ]]> */ </script>

END_JS;

    return $message . "</div>\n";
}
