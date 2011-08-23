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
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 * @package ViMbAdmin
 */

    $whatToCompress = 'all';

    if( in_array( 'css', $argv ) && !in_array( 'js', $argv ) )
        $whatToCompress = 'css';

    if( in_array( 'js', $argv ) && !in_array( 'css', $argv ) )
        $whatToCompress = 'js';

    if( in_array( $whatToCompress, array( 'all', 'js' ) ) )
    {
        print "\n\nMinifying JavaScript using Google Closure Compiler\n\n";

        file_put_contents( '../public/js/javascript.js', '' ); // empty the current file

        $files = glob( '../public/js-dev/*.js' );
        sort( $files, SORT_STRING );

        $numFiles = sizeof( $files );
        $count = 0;
        $minifiedFileName = '../var/tmp/minified.js';

        foreach( $files as $oneFileName )
        {
            $count++;

            print sprintf( "%-6s", "{$count}/{$numFiles}" ) . basename( $oneFileName ) . "\n";

            exec(   "java -jar compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS --warning_level QUIET" .
                    " --js {$oneFileName} --js_output_file {$minifiedFileName}"
                );

            file_put_contents( '../public/js/javascript.js', file_get_contents($minifiedFileName), FILE_APPEND );
        }

        unlink( $minifiedFileName );

        print "\n\nDone.";
    }

    if( in_array( $whatToCompress, array( 'all', 'css' ) ) )
    {
        print "\n\nMinifying CSS using YUI Compressor\n\n";

        file_put_contents( '../public/css/style.css', '' ); // empty the current file

        $files = glob( '../public/css-dev/*.css' );
        sort( $files, SORT_STRING );

        $numFiles = sizeof( $files );
        $count = 0;
        $minifiedFileName = '../var/tmp/minified.css';

        foreach( $files as $oneFileName )
        {
            $count++;

            print sprintf( "%-6s", "{$count}/{$numFiles}" ) . basename($oneFileName) . "\n";

            exec( "java -jar yuicompressor.jar {$oneFileName} -o {$minifiedFileName} -v --charset utf-8" );

            if ( preg_match("/^\d\d\-/", basename( $oneFileName ) ) )
            {
                file_put_contents( '../public/css/style.css', file_get_contents($minifiedFileName), FILE_APPEND );
            }
            else
            {
                $MinifiedFile = '../public/css/' . str_replace( '.css', '.min.css', basename( $oneFileName ) );
                file_put_contents( $MinifiedFile, file_get_contents( $minifiedFileName ) );
            }
        }

        unlink( $minifiedFileName );

        print "\n\nDone.";
    }

    print "\n\n";
