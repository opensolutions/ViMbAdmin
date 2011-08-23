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
 * @subpackage Models
 */
class DomainAdminTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object DomainAdminTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable( 'DomainAdmin' );
    }


    /**
     * Get a list of domain IDs that an admin user is allowed to edit
     *
     * @param Admin|int The admin object or id to get the domains for.
     * @return array An array of domain names
     */
    public static function getAllowedDomains( $admin = null )
    {
        if( ctype_digit( $admin ) && $admin )
            $admin = Doctrine_Core::getTable( 'Admin' )->find( $admin );

        if( $admin instanceof Admin && $admin['id'] )
            $adminUsername = $admin['username'];
        else
            return array();

        $domainAdmins = Doctrine_Query::create()
                            ->select( 'domain' )
                            ->from( 'DomainAdmin' )
                            ->where( 'username = ?', $adminUsername )
                            ->fetchArray();

        $domains = array();

        foreach( $domainAdmins as $item )
            $domains[] = $item['domain'];

        return $domains;
    }

}
