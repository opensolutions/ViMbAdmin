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

/*
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Dovecot
{

    /**
     * Utility function to call Dovecot password generator
     *
     * @param string $scheme The Dovecot scheme to use
     * @param string $pass The password
     * @param string $user The username (required by some schemes)
     * @throws ViMbAdmin_Exception
     * @return string The encrypted / hashed password
     */
    public static function password( $scheme, $pass, $user )
    {
        // binary should be available from options in the registry
        $options = Zend_Registry::get( 'options' );
        
        if( !isset( $options['defaults']['mailbox']['dovecot_pw_binary'] ) )
            throw new ViMbAdmin_Exception( sprintf( _( 'Configuration param "defaults.mailbox.dovecot_pw_binary" not defined' ) ) );

        $cmd = $binary = $options['defaults']['mailbox']['dovecot_pw_binary'];
        if( strpos( $cmd, ' ' ) )
            $binary = substr( $cmd, 0, strpos( $cmd, ' ' ) );
        
        if( !file_exists( $binary ) || !is_executable( $binary ) )
            throw new ViMbAdmin_Exception( sprintf( _( 'Dovecot binary [%s] does not exist or is not executable' ), $binary ) );
        
        $cmd .= ' -s ' . escapeshellarg( $scheme ) . ' -u ' . escapeshellarg( $user ) . ' -p ' . escapeshellarg( $pass );
        $a = exec( $cmd, $output, $retval );
        
        if( $retval != 0 )
            throw new ViMbAdmin_Exception( sprintf( _( 'Error executing Dovecot password command: ' . $cmd ) ) );
        
        return trim( substr( $a, strlen( $scheme ) + 2 ) );
    }

}
