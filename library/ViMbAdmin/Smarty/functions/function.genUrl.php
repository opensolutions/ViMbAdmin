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
 * Generates a url compatible with Zend dispatcher.
 *
 * @package ViMbAdmin
 * @subpackage Smarty_Functions
 */

    /**
     * Function to generate a Zend Controller URL from Smarty templates.
     *
     * The URL is made up of parameters as supplied in the $params associative array.
     * 'controller' and 'action' are special parameters which indicate the controller
     * and action to call. Any other parameters are added as additional name / value
     * pairs.
     *
     * @param array $params An array of the parameters to make up the URL
     * @param Smarty $smarty A reference to the Smarty template object
     * @return string The URL to use
     */
    function smarty_function_genUrl( $params, &$smarty )
    {
        $url = $smarty->getTemplateVars( 'pagebase' );

        if( isset( $params['controller'] ) )
        {
            $url .= "/{$params['controller']}";
            unset( $params['controller'] );
        }

        if( isset( $params['action'] ) )
        {
            $url .= "/{$params['action']}";
            unset( $params['action'] );
        }

        foreach( $params as $var => $value ) $url .= "/{$var}/{$value}";

        return $url;
    }
