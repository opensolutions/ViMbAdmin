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
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Log extends Zend_Log
{

    public function alert($pMessage)
    {
        $vMessage = $pMessage . "

           " . _( 'host' ) . " : {$_SERVER['HTTP_HOST']}
     " . _( 'user agent' ) . " : {$_SERVER['HTTP_USER_AGENT']}
    " . _( 'remote addr' ) . " : {$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}
" . _( 'script filename' ) . " : {$_SERVER['SCRIPT_FILENAME']}
 " . _( 'request method' ) . " : {$_SERVER['REQUEST_METHOD']}
   " . _( 'query string' ) . " : {$_SERVER['QUERY_STRING']}
    " . _( 'request uri' ) . " : {$_SERVER['REQUEST_URI']}
";

        try
        {
            $this->log($vMessage, Zend_Log::ALERT);
        }
        catch(Exception $e)
        {
            $this->debug($e->getMessage());
        }
    }

}
