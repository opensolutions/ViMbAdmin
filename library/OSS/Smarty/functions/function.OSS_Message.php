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
     * Function to display OSS_Message for user
     *
     * @category   OSS
     * @package    OSS_Smarty
     * @subpackage Functions
     *
     * @param array $params
     * @param Smarty $smarty A reference to the Smarty template object
     * @return string
     */
    function smarty_function_OSS_Message( $params, &$smarty )
    {
        $ossms = $smarty->getTemplateVars( 'OSS_Messages' );

        if( $ossms === null ) $ossms = array();

        if( isset( $_SESSION['Application']['OSS_Messages'] ) && is_array( $_SESSION['Application']['OSS_Messages'] )
                && sizeof( $_SESSION['Application']['OSS_Messages'] ) > 0 )
        {
            $ossms = array_merge($ossms, $_SESSION['Application']['OSS_Messages']);
            unset($_SESSION['Application']['OSS_Messages']);
        }

        if ( $ossms == array() ) return '';

        $count = 0;
        $message = '';
        
        foreach( $ossms as $ossm )
        {
            if( isset( $params['randomid'] ) && $params['randomid'] )
                $count = mt_rand();

            if( $ossm instanceof OSS_Message_Block )
            {
                $message .= <<<END_MESSAGE

    <div class="alert alert-block alert-{$ossm->getClass()} fade in" id="oss-message-{$count}">
        <a class="close" href="#" data-dismiss="alert">×</a>
        {$ossm->getMessage()}
END_MESSAGE;
                if( count( $ossm->getActions() ) )
                {
                    $message .= "        <div class=\"alert-actions\">\n";

                    foreach( $ossm->getActions() as $a )
                        $message .= $a . "\n";

                    $message .= "        </div>\n";
                }

                $message .= <<<END_MESSAGE
    </div>

END_MESSAGE;
            }
            else if( $ossm instanceof OSS_Message_Pop_Up )
            {

                $items = $ossm->getMessage();

                if( !is_array( $items ) )
                    $items = array( $items );

                foreach( $items as $item )
                {
                        $message .= <<<END_MESSAGE

        <script type="text/javascript">
            $( document ).ready( function()
            {
                bootbox.alert( '{$item}' );
            })
        </script>

END_MESSAGE;
                }
            }
            else
            {

                $items = $ossm->getMessage();

                if( !is_array( $items ) )
                    $items = array( $items );
                
                foreach( $items as $item )
                {
                        $message .= <<<END_MESSAGE

        <div class="alert alert-{$ossm->getClass()} fade in" id="oss-message-{$count}">
            <a class="close" href="#" data-dismiss="alert">×</a>
            {$item}
        </div>

END_MESSAGE;
                }
            } // end inner foreach

            $count++;
        } // end foreach()


        return $message;
    }
