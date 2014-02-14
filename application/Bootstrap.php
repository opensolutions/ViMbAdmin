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

/**
 * @package ViMbAdmin
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Register the ViMbAdmin library autoloader
     *
     * This function ensures that classes from library/ViMbAdmin are automatically
     * loaded from the subdirectories where subdirectories are indicated by
     * underscores in the same manner as Zend.
     *
     */
    protected function _initViMbAdminAutoLoader()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace( 'ViMbAdmin' );
    }


    /**
     * Register the OSS library autoloader
     *
     * This function ensures that classes from library/OSS are automatically
     * loaded from the subdirectories where subdirectories are indicated by
     * underscores in the same manner as Zend.
     *
     */
    protected function _initOSSAutoLoader()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace( 'OSS' );
    }

    /**
     * Load the database resource before the session resource is loaded.
     *
     * We're currently using the Zend session handler for storing sessions
     * in MySQL. For this, we need to ensure the DB is initialised before
     * the session resource is loaded.
     */
     protected function _initDbAutoForSessions()
     {
         // load the DB resource if it is required by the session
         if( isset( $this->getOptions()['resources']['session']['saveHandler']['class'] )
             && $this->getOptions()['resources']['session']['saveHandler']['class'] == 'Zend_Session_SaveHandler_DbTable' )
         {
             $this->bootstrap('db');
         }
    }

}
