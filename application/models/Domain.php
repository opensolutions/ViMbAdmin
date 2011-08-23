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
class Domain extends BaseDomain
{

    /**
     * Count the number of aliases this domain has
     *
     * @param boolean $excludeMailboxAliases Defaults to true. Excludes aliases where address == goto from mailboxes.
     * @return int The number of mailboxes
     */
    public function countAliases( $excludeMailboxAliases = true )
    {
        $q = Doctrine_Query::create()
                ->select( 'count(domain)' )
                ->from( 'Alias' )
                ->where( 'domain = ?', $this['domain'] );

        if( $excludeMailboxAliases )
            $q->andWhere( 'address != goto' );

        return $q->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );
    }


    /**
     * Count the number of mailboxes this domain has
     *
     * @param void
     * @return int The number of mailboxes
     */
    public function countMailboxes()
    {
        return Doctrine_Query::create()
                    ->select( 'count(domain)' )
                    ->from( 'Mailbox' )
                    ->where( 'domain = ?', $this['domain'] )
                    ->execute( null, Doctrine_Core::HYDRATE_SINGLE_SCALAR );
    }


    /**
     * Adds an admin to the domain, and returns with new DomainAdmin object.
     *
     * @param int $adminId
     * @return object DomainAdmin
     */
    public function addAdmin( $admin )
    {
        if( ( $admin instanceof Admin ) && $admin->id )
            $adminUsername = $admin->username;
        else
            $adminUsername = Doctrine::getTable( 'Admin' )->find( $admin )->rawGet( 'username' );

        $model = new DomainAdmin;
        $model->username = $adminUsername;
        $model->domain = $this->domain;
        $model->save();

        return $model;
    }

}
