#!/usr/bin/env php
<?php
/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2012 Open Source Solutions Limited
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
 * @copyright Copyright (c) 2011 - 2012 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */
                               
defined( 'APPLICATION_PATH' ) || define( 'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application' ) );

$whatToCompress = 'all';

if( in_array( 'css', $argv ) && !in_array( 'js', $argv ) )
    $whatToCompress = 'css';

if( in_array( 'js', $argv ) && !in_array( 'css', $argv ) )
    $whatToCompress = 'js';

$version = false;
foreach( $argv as $i => $v )
{
    if( $v == '--version' )
    {
        $version = $argv[$i+1];
        break;
    }
}

if( in_array( $whatToCompress, array( 'all', 'js' ) ) )
{
    print "\n\nMinifying 'public/js':\n\n";

    $files = glob( APPLICATION_PATH . '/../public/js/[0-9][0-9][0-9]-*.js' );
    sort( $files, SORT_STRING );

    $numFiles = sizeof( $files );
    $count = 0;

    foreach( $files as $oneFileName )
    {
        $count++;

        print "    [{$count}] " . basename( $oneFileName ) . " => min." . basename( $oneFileName ) . "\n";

        exec(   "java -jar " . APPLICATION_PATH . "/../bin/compiler.jar --compilation_level WHITESPACE_ONLY --warning_level QUIET" .
                " --js {$oneFileName} --js_output_file " . APPLICATION_PATH . "/../public/js/min." . basename( $oneFileName )
        );
    }

    $mergedJs = '';

    print "\n    Combining...";
    foreach( $files as $fileName )
        $mergedJs .= file_get_contents( APPLICATION_PATH . "/../public/js/min." . basename( $fileName) );

    if( $version )
        file_put_contents( APPLICATION_PATH . "/../public/js/min.bundle-v{$version}.js", $mergedJs );
    else
        file_put_contents( APPLICATION_PATH . "/../public/js/min.bundle.js", $mergedJs );

    print " done\n\n";
}

if( in_array( $whatToCompress, array( 'all', 'css' ) ) )
{

    print "\nMinifying 'public/css':\n";

    $files = glob( APPLICATION_PATH . '/../public/css/[0-9][0-9][0-9]-*.css' );
    sort( $files, SORT_STRING );

    $numFiles = sizeof( $files );
    $count = 0;

    foreach( $files as $oneFileName )
    {
        $count++;

        print "    [{$count}] " . basename( $oneFileName ) . " => min." . basename( $oneFileName ) . "\n";

        exec( "java -jar " . APPLICATION_PATH . "/../bin/yuicompressor.jar {$oneFileName} -o " . APPLICATION_PATH . "/../public/css/min." . basename( $oneFileName ) . " -v --charset utf-8" );
    }

    $mergedCss = '';

    print "\n    Combining...";
    foreach( $files as $fileName )
        $mergedCss .= file_get_contents( APPLICATION_PATH . "/../public/css/min." . basename( $fileName ) );

    if( $version )
        file_put_contents( APPLICATION_PATH . "/../public/css/min.bundle-v{$version}.css", $mergedCss );
    else
        file_put_contents( APPLICATION_PATH . '/../public/css/min.bundle.css', $mergedCss );

    print " done\n\n";
}

print "\n\n";

if( $version )
{
    echo "****** VERSION NUMBER WAS SPECIFIED - DON'T FORGET TO UPDATE HEADER FILES!! ******\n\n";
}
