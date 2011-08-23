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
class DomainTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @param void
     * @return object DomainTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable( 'Domain' );
    }


    /**
     * Returns with the domain, or with NULL if no record found.
     *
     * @param int $id
     * @return null|string
     */
    public static function getDomain( $id )
    {
        $model = Doctrine::getTable( 'Domain' )->find( $id );

        return ( $model ? $model->domain : null );
    }


    /**
     * Returns with a list of domains available to the admin, as array( id -> domain, ... )
     *
     * @param object Admin
     * @return array
     */
    public static function getDomains( $admin )
    {
        if( $admin->isSuper() )
        {
            $tempDomainList = Doctrine_Query::create()
                                ->from( 'Domain' )
                                ->orderBy( 'domain ASC' )
                                ->fetchArray();
        }
        else
        {
            $tempDomainList = Doctrine_Query::create()
                                ->select( 'd.*' )
                                ->from( 'Domain d' )
                                ->leftJoin( 'DomainAdmin da' )
                                ->where( 'da.domain = d.domain' )
                                ->andWhere( 'da.username = ?', $admin['username'] )
                                ->orderBy( 'd.domain ASC' )
                                ->fetchArray();
        }

        $domainList = array();

        foreach( $tempDomainList as $oneDomain )
            $domainList[ $oneDomain['id'] ] = $oneDomain['domain'];

        return $domainList;
    }

}
