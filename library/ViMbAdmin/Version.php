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
 * Class to store and retrieve the version of ViMbAdmin.
 *
 * @package    ViMbAdmin
 * @subpackage Library
 * @copyright  Copyright (c) 2011 Open Source Solutions Limited
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 */
final class ViMbAdmin_Version
{
    /**
     * Version identification - see compareVersion()
     */
    const VERSION = '3.0.7';

    /**
     * Database schema version
     */
    const DBVERSION = 1;

    /**
     * Database schema version
     */
    const DBVERSION_NAME = 'Venus';

    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $_lastestVersion = null;

    /**
     * Compare the specified version string $version
     * with the current ViMbAdmin_Version::VERSION.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion( $version )
    {
        return version_compare( $version, self::VERSION );
    }

    /**
     * Fetches the version of the latest stable release
     *
     * @link http://framework.zend.com/download/latest
     * @return string
     */
    public static function getLatest()
    {
        if( null === self::$_lastestVersion ) 
        {
            self::$_lastestVersion = 'not available';

            $handle = fopen( 'http://www.opensolutions.ie/open-source/vimbadmin/latest-v3', 'r' );
            if( $handle !== false ) 
            {
                self::$_lastestVersion = trim( stream_get_contents( $handle ) );
                fclose( $handle );
            }
        }

        return self::$_lastestVersion;
    }
}
