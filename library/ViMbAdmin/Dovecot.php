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
     * function to generate a password hash. if supported, the hash is calculated in php, otherwise
     * Dovecot password generator is called
     *
     * @param string $scheme The Dovecot scheme to use
     * @param string $pass The password
     * @param string $user The username (required by some schemes)
     * @throws ViMbAdmin_Exception
     * @return string The encrypted / hased password
     */
    function generate_password_hash($scheme, $pass, $user){
    	if(in_array($scheme, array('SSHA','SSHA256','SSHA512'))){
    		$algo = 'sha512';
    		if($scheme == 'SSHA'){
    			$algo = 'sha1';
    		}
    		if($scheme == 'SSHA256'){
    			$algo = 'sha256';
    		}
    		
  			$salt = ViMbAdmin_Dovecot::generate_salt();
  			$hash = hash($algo, $pass.$salt,true);
    		return base64_encode($hash.$salt);
    	}
    	
    	return ViMbAdmin_Dovecot::shell_passwd($scheme, $pass, $user);
    }
    
    /**
     * function to check a password for it's validity
     *
     * @param string $scheme The Dovecot scheme to use
     * @param string $pass The password
     * @param string $user The username (required by some schemes)
     * @param string $pwhash The password hash
     * @throws ViMbAdmin_Exception
     * @return string The encrypted / hased password
     */
    function check_password($scheme, $pass, $user, $pwhash){
    	if(in_array($scheme, array('SSHA','SSHA256','SSHA512'))){
			$algo = 'sha512';
			$bitlen = 64;
			if($scheme == 'SSHA'){
				$algo = 'sha1';
				$bitlen = 20;
			}
			if($scheme == 'SSHA256'){
				$algo = 'sha256';
				$bitlen = 32;
			}
    		
    		$check_pwd = substr(base64_decode($pwhash), 0, $bitlen);
	    	$salt = substr(base64_decode($pwhash), $bitlen);
	    
	    	if(hash($algo, $pass.$salt, true) == $check_pwd){
	    		return true;
	    	}
	    	return false;
    	}
    	
    	if(ViMbAdmin_Dovecot::shell_passwd($scheme, $pass, $user) == $pwhash){
    		return true;
    	}
    	
    	return false;
    }
    
    /**
     * function to get password from dovecote generator
     *
     * @param string $scheme The Dovecot scheme to use
     * @param string $pass The password
     * @param string $user The username (required by some schemes)
     * @throws ViMbAdmin_Exception
     * @return string The encrypted / hased password
     */
    function shell_passwd($scheme, $pass, $user){
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
    /**
     * generate a salt for password hashing
     * 
     * @param integer $len
     * @return string the salt
     */
    function generate_salt($len=10){
    	return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz?!/#$-_.;\"'§:ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $len);
    }
    
}


