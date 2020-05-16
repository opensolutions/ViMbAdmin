<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * All rights reserved.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 *
 * Contact: Barry O'Donovan - info (at) opensolutions (dot) ie
 *          http://www.opensolutions.ie/
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 *     http://www.opensolutions.ie/licenses/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@opensolutions.ie so we can send you a copy immediately.
 *
 * @category   OSS
 * @package    OSS_Crypt
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Gibberish AES encryption tools.
 *
 * Based on https://github.com/mdp/gibberish-aes (MIT) and notes by
 * nbari at dalmp dot com on 15-Jan-2012 07:52 at
 * http://www.php.net/manual/en/function.openssl-decrypt.php
 *
 * @link https://github.com/mdp/gibberish-aes
 * @link http://www.php.net/manual/en/function.openssl-decrypt.php
 * @category   OSS
 * @package    OSS_Crypt
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Crypt_GibberishAES
{

    /**
     * Encrypt the given `$data` using AES 256 symmetrical cipher
     *
     * See `tests/OSS/Crypt/GibberishAESTest.php` for examples
     *
     * @param string $data The data to encrypt
     * @param string $password The symmectrical password to use for the encryption
     * @return string Base64 encrypted data with embedded (random) salt
     */
    public static function encrypt( $data, $password )
    {
        $salt = openssl_random_pseudo_bytes( 8 );
    
        $salted = '';
        $dx = '';
        
        // Salt the key(32) and iv(16) = 48
        while( strlen( $salted ) < 48 )
        {
            $dx = md5( $dx . $password . $salt, true );
            $salted .= $dx;
        }
    
        $key = substr( $salted, 0, 32 );
        $iv  = substr( $salted, 32,16 );
    
        $encrypted_data = openssl_encrypt( $data, 'aes-256-cbc', $key, true, $iv );
        return base64_encode( 'Salted__' . $salt . $encrypted_data );
    }
    
    
    /**
     * Decrypt the given `$edata` using AES 256 symmetrical cipher
     *
     * See `tests/OSS/Crypt/GibberishAESTest.php` for examples
     *
     * @param string $edata The encrypted data to decrypt
     * @param string $password The symmectrical password used for the encryption
     * @return string The original data decrypted (or false on failure)
     */
    public static function decrypt( $edata, $password )
    {
        $data = base64_decode($edata);
        $salt = substr( $data, 8, 8 );
        $ct   = substr( $data, 16 );
        
        /**
         * From https://github.com/mdp/gibberish-aes
         *
         * Number of rounds depends on the size of the AES in use
         * 3 rounds for 256
         *        2 rounds for the key, 1 for the IV
         * 2 rounds for 128
         *        1 round for the key, 1 round for the IV
         * 3 rounds for 192 since it's not evenly divided by 128 bits
        */
        $rounds = 3;
        $data00 = $password.$salt;
        $md5_hash = array();
        $md5_hash[0] = md5($data00, true);
        $result = $md5_hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $md5_hash[$i] = md5($md5_hash[$i - 1].$data00, true);
            $result .= $md5_hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);
    
        return openssl_decrypt( $ct, 'aes-256-cbc', $key, true, $iv );
    }

}

