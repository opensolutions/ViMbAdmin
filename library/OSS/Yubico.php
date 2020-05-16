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
 * @package    OSS_Yubico
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Yubico
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Yubico
{

    const ERR_NO_OTP_PROVIDED = -1;
    const ERR_CORRUPT_OTP = -2;
    const ERR_DATABASE_READ = -3;
    const ERR_NO_SUCH_USER = -4;
    const ERR_WRONG_AES_KEY = -5;
    const ERR_USER_ID_MISMATCH = -6;
    const ERR_REPLAY_ATTACK = -7;
    const ERR_DATABASE_WRITE = -8;


    /**
     * Contains the possible validation error codes and messages.
     * @var array
     */
    public static $VALIDATION_ERROR = array(
        -1 => 'No OTP provided',
        -2 => 'Corrupt OTP',
        -3 => 'Database error while trying to read user data',
        -4 => 'User does not exist',
        -5 => 'Wrong AES key',
        -6 => 'User ID mismatch',
        -7 => 'Replay attack (session counter)',
        -8 => 'Database error while trying to update user data'
    );


    /**
     * Converts a hexadecimal number (string) to its binary number representation.
     *
     * @param string $hex
     * @return string
     */
    public static function hex2bin( $hex )
    {
        if( !is_string( $hex ) )
            return null;

        $r = '';

        for( $a = 0; $a < strlen( $hex ); $a += 2 )
            $r .= chr( hexdec( $hex[$a] . $hex[$a + 1] ) );

        return $r;
    }


    /**
     * Converts a ModHex string to hexadecimal.
     *
     * @param string $pModHex
     * @return string
     */
    public static function modhex2hex( $modHex )
    {
        return strtr( $modHex, 'cbdefghijklnrtuv', '0123456789abcdef' );
    }


    /**
     * Converts a hexadecimal string to ModHex.
     *
     * @param string $hex
     * @return string
     */
    public static function hex2modhex( $hex )
    {
        return strtr( $hex, '0123456789abcdef', 'cbdefghijklnrtuv' );
    }


    /**
     * Converts a hexadecimal string to Base64.
     *
     * @param string $hex
     * @return string
     */
    public static function hex2base64( $hex )
    {
        return base64_encode( pack( "H*", $hex ) );
    }


    /**
     * Converts a ModHex string to Base64.
     *
     * @param string $modHex
     * @return string
     */
    public static function modhex2base64( $modHex )
    {
        return self::hex2base64( self::modhex2hex( $modHex ) );
    }


    /**
     * Decrypts an AES128-ECB encrypted string.
     *
     * @param string $cipherText the encrypted string, a string of hexadecimal values
     * @param string $key the AES key, a string of hexadecimal values
     * @return string a string of hexadecimal values
     */
    public static function aes128EcbDecrypt( $cipherText, $key)
    {
        $mcrypt = mcrypt_module_open ( MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '' );
        mcrypt_generic_init( $mcrypt, self::hex2bin( $key ), self::hex2bin( '00000000000000000000000000000000' ) );

        $decrypted = mdecrypt_generic( $mcrypt, self::hex2bin( $cipherText ) );

        mcrypt_generic_deinit( $mcrypt );
        mcrypt_module_close( $mcrypt );

        return bin2hex( $decrypted );
    }


    /**
     * Calculates the CRC16 value of a string.
     *
     * @param string $string
     * @return string the CRC16 value as a hexadecimal number
     */
    public static function crc16( $string )
    {
        $crc = 0xffff;

        for( $i = 0; $i < 16; $i++ )
        {
            $b = hexdec( $string[$i * 2] . $string[( $i * 2 ) + 1] );
            $crc = $crc ^ ( $b & 0xff );

            for( $j = 0; $j < 8; $j++ )
            {
                $n = $crc & 1;
                $crc = $crc >> 1;

                if( $n != 0 )
                    $crc = $crc ^ 0x8408;
            }
        }

        return $crc;
    }


    /**
     * Checks if the CRC16 value is correct, and returns boolean true or false, respectively.
     *
     * @param string $string
     * @return bool
     */
    public static function isCrcGood( $string )
    {
        return ( self::crc16( $string ) == 0xf0b8 );
    }


    /**
     * Validates the YubiKey OTP. Checks it against rules and the database, too.
     * Returns with an associative array containing the following fields (extracted from the OTP): secret_id, session_counter, session_use, clock, random
     * Retruns with a negative integer on error.
     *
     * -1: No OTP provided
     * -2: Corrupt OTP
     * -3: DB failure while trying to read user data
     * -4: User does not exist
     * -5: Wrong AES key
     * -6: User ID mismatch
     * -7: Replay attack
     * -8: DB failure while trying to update user data
     *
     * @param string $pOTP
     * @return array|integer array on success, negative integer on error
     */
    public static function validate($pOTP)
    {
        $pOTP = trim( $pOTP );

        if ($pOTP == '') return self::ERR_NO_OTP_PROVIDED;

        /*
        TODO : see the document YubiKey_Configuration_Manual_xxxx.pdf and come up with a proper validation

        "Mix upper- and lower case", "Mix characters and numeric digits" and both together

        jfdkibjkegielgbgjkbejktuhurirnjt
        JFdkibjkegielgbgjkbejktuhurirnjt
        j53kibjkegielgbgjkbejktuhurirnjt
        J53Kibjkegielgbgjkbejktuhurirnjt
        1U1Cccccccccjjeuufllhfkckvullcbbnbhfjvbglbcv
        */

        //if (!preg_match("/^([cbdefghijklnrtuv]{0,16})([cbdefghijklnrtuv]{32})$/", $pOTP, $matches)) return self::ERR_CORRUPT_OTP;
        if (!preg_match("/^([12345678cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{0,16})([12345678cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{32})$/", $pOTP, $matches)) return self::ERR_CORRUPT_OTP;

        $id = $matches[1]; // public id
        $modhex_ciphertext = $matches[2];

        try
        {
            $row = Doctrine::getTable('User_Yubikey')->findOneByPublicId( $id ); // find by public id
        }
        catch(Exception $e)
        {
            return self::ERR_DATABASE_READ;
        }

        if (!$row) return self::ERR_NO_SUCH_USER;

        $aeskey = $row->aes_key;

        $ciphertext = self::modhex2hex($modhex_ciphertext);
        $plaintext = self::aes128EcbDecrypt( $ciphertext, $aeskey );

        if (self::isCrcGood($plaintext) == false) return self::ERR_WRONG_AES_KEY; // wrong CRC means wrong AES key

        $secret_id = hexdec( substr($plaintext, 0, 12) );
        $session_counter = hexdec( substr( $plaintext, 14, 2 ) . substr( $plaintext, 12, 2 ) );
        $session_use = hexdec( substr( $plaintext, 22, 2 ) );
        $clock = substr( $plaintext, 18, 2 ) . substr( $plaintext, 16, 2 );
        $random = substr( $plaintext, 20, 2 );

        //print "plain text: {$plaintext} | secret id: {$secret_id} | session counter: {$session_counter} | session use: {$session_use} | clock: {$clock} | random: {$random} |";
        //die();

        if ($row->User_id != $secret_id) return self::ERR_USER_ID_MISMATCH;
        if ($row->session_counter > $session_counter) return self::ERR_REPLAY_ATTACK; // replay attack
        if ( ($row->session_counter == $session_counter) && ($row->session_use >= $session_use) ) return self::ERR_REPLAY_ATTACK; // replay attack

        try
        {
            if ($row->status == 'A_NEW') $row->status = 'A_ACTIVE';

            $row->session_counter = $session_counter;
            $row->session_use = $session_use;
            $row->save();
        }
        catch(Exception $e)
        {
            return self::ERR_DATABASE_WRITE;
        }

        return array(
                    'secret_id' => $secret_id,
                    'session_counter' => $session_counter,
                    'session_use' => $session_use,
                    'clock' => $clock,
                    'random' => $random,
                );
    }


    /**
     * Returns error text by given code
     *
     * @param int $errorCode Eror code is negative value from one to eight
     * @return string
     */
    public static function getErrorMessage( $errorCode )
    {
        return self::$VALIDATION_ERROR[$errorCode];
    }

}
