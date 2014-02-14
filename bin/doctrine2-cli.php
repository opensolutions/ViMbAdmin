#!/usr/bin/env php
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
 */
                               
/**
 * Doctrine CLI script
 */

//ini_set('memory_limit', -1);

require_once( dirname( __FILE__ ) . '/../vendor/autoload.php' );
require_once( dirname( __FILE__ ) . '/utils.inc' );

if( isset( $_SERVER['argv'][1] ) && $_SERVER['argv'][1] == '--database' )
{
    $db = $_SERVER['argv'][2];
    array_splice( $_SERVER['argv'], 1, 2 );
}
else
    $db = 'default';

$application = get_zend_application();
$em = get_doctrine2_entity_manager( $application, $db );


$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper( $em->getConnection() ),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper( $em )
);

$cli = new \Symfony\Component\Console\Application( 'Doctrine Command Line Interface', Doctrine\Common\Version::VERSION );
$cli->setCatchExceptions(true);
$helperSet = $cli->getHelperSet();
foreach ($helpers as $name => $helper) {
    $helperSet->set($helper, $name);
}

Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands( $cli );

$cli->run();


