<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
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
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * A RoundCube API via direct database manipulation.
 *
 * @category   OSS
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_API_RoundCube
{
    // use DBAL connections for database manipulation
    use OSS_Doctrine2_DBAL_Connection;

    /**
     * Stores users preference default language
     * @var string $_language
     */
    private $_language = "en_GB";
    
    /**
     * Stores users identity standard default value
     * @var int $_standard
     */
    private $_standard = 1;

    /**
     * Constructor - creates a new DBAL connection.
     *
     * @param array $dbparams
     * @return void
     */
    public function __construct( $dbparams )
    {
        $this->getDBAL( $dbparams );
    }


    /**
     * Get all users registered in the RoundCube database as an array.
     *
     * @param void
     * @return array All users registered in the database
     * @access public
     */
    public function getAllUsers()
    {
        return $this->getDBAL()->fetchAll( 'SELECT * FROM users' );
    }


    /**
     * Get users data from the RoundCube database as an array.
     *
     * @param strint $username Users username
     * @return array|bool
     * @access public
     */
    public function getUser( $username )
    {
        return $this->getDBAL()->fetchAssoc( 'SELECT * FROM users WHERE username = ?',
            array( $username )
        );
    }


    /**
     * Creates RoundCube user, and also creates identity if $identity array is passed.
     *
     * NOTICE: If $identity array is passed it must contain $identity['mail'].
     *
     * @param string $username Users username
     * @param string $mail_host IMAP addres where email is stored
     * @param array $preferences Users preferences such as davical details.
     * @param array $identity Users identity details.
     * @return bool
     * @access public
     * @throw Identity mail is not set.
     * @see addIdentity()
     */
    public function addUser( $username, $mail_host, $preferences, $identity = null )
    {
        $result = $this->getDBAL()->insert( 'users', [ 
            'username' => $username, 
            'mail_host' => $mail_host,
            'language' => $this->_language,
            'created'  => date( 'Y-m-d H:i:s' ),
            'preferences' => serialize( $preferences ) ]
        );

        if( $identity && $result )
        {
            $user = $this->getUser( $username );
            if( !isset( $identity['email'] ) )
                throw new OSS_Exception( "Identity mail is not set." );

            $reuslt = $this->addIdentity( $user, $identity['email'], $identity );
        }


        return $result;
    }

    /**
     * Creates identity for user.
     * 
     * $identity array sturcture:
     * [
     *   'replay_to'      => 'replay_to',
     *   'bcc'            => 'bcc',
     *   'name'           => 'name',
     *   'organization'   => 'organization',
     *   'signature'      => 'signature',
     *   'html_signature' => 'html_signature'
     * ]
     *
     * @param array $user User array loaded form OSS_API_RoundCube
     * @param string $email Email address
     * @param array $identity
     * @return bool
     * @access public
     */
    public function addIdentity( $user, $email, $identity )
    {
        return $this->getDBAL()->insert( 'identities', [
                    'user_id' => $user['user_id'], 
                    'changed' => date( 'Y-m-d H:i:s' ),
                    'del' => 0,
                    'standard'  => $this->_standard,
                    '`reply-to`' => isset( $identity['reply_to'] ) ? $identity['reply_to'] : "",
                    'bcc' => isset( $identity['bcc'] ) ? $identity['bcc'] : "",
                    'email' => $email,
                    'name' => isset( $identity['name'] ) ? $identity['name'] : "",
                    'organization' => isset( $identity['organization'] ) ? $identity['organization'] : "",
                    'signature' => isset( $identity['signature'] ) ? $identity['signature'] : NULL,
                    'html_signature' => isset( $identity['html_signature'] ) ? $identity['html_signature'] : 0,
                ]
            );
    }

    /**
     * Updates RoundCube users data, and also updates identity data if $identity array is passed.
     *
     * @param string $username Users username
     * @param string $mail_host IMAP addres where email is stored
     * @param array $preferences Users preferences such as davical details.
     * @param array $identity Users identity details.
     * @return bool
     * @access public
     */
    public function updateUser( $username, $mail_host, $preferences, $identity = null )
    {
        $result = $this->getDBAL()->update( 'users', [ 
            'mail_host' => $mail_host,
            'language' => $this->_language,
            'created'  => date( 'Y-m-d H:i:s' ),
            'preferences' => serialize( $preferences ) ],
            [ 'username' => $username ]
        );

        if( $identity && $reuslt )
        {
            $user = $this->getUser( $username );
            $reuslt = $this->updateIdentity( $user, $identity );
        }


        return $result;
    }

    /**
     * Creates identity for user.
     * 
     * $identity array sturcture:
     * [
     *   'email'          => email,  
     *   'replay_to'      => 'replay_to',
     *   'bcc'            => 'bcc',
     *   'name'           => 'name',
     *   'organization'   => 'organization',
     *   'signature'      => 'signature',
     *   'html_signature' => 'html_signature'
     * ]
     *
     * @param array $user User array loaded form OSS_API_RoundCube
     * @param string $email Email address
     * @param array $identity
     * @return bool
     * @access public
     */
    public function updateIdentity( $user, $identity )
    {
        $identity['changed'] = date( 'Y-m-d H:i:s' );
        if( isset( $identity['replay_to'] ) )
        {
            $identity['`replay-to`'] = $identity['replay_to'];
            unset( $identity['replay_to'] );
        }
        
        return $this->getDBAL()->insert( 'identities', $identity,
            [ 'user_id' => $user['user_id'] ] 
        );
    }

    /**
     * Implementation of RoundCube's encryption function.
     *
     * Based on source code from the program/include/rcmail.php
     * rcmail::encrypt() function.
     *
     * @param string $clear  The cleartext string to encrypt
     * @param string $key    The encryption key 
     * @param string $base64 Return the ciphertext as a base64 encoded string
     * @return string The ciphertext
     */
    public static function encrypt( $clear, $key, $base64 = true )
    {
        $clear = pack( "a*H2", $clear, "80" );
        
        $td = mcrypt_module_open( MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, "" );
        
        $iv = self::createIV( mcrypt_enc_get_iv_size( $td ) );
        mcrypt_generic_init( $td, $key, $iv );
        $cipher = $iv . mcrypt_generic( $td, $clear );
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);  

        return $cipher = ( $base64 ? base64_encode( $cipher ) : $cipher );
    }

    /**
     * Implementation of RoundCube's decryption function.
     *
     * Based on source code from the program/include/rcmail.php
     * rcmail::decrypt() function.
     *
     * @param string $cipher The ciphertext string to decrypt
     * @param string $key    The encryption key 
     * @param string $base64 Indicates if the ciphertext is a base64 encoded string
     * @return string The cleartext
     */
    public static function decrypt( $cipher, $key, $base64 = true )
    {
        $cipher = ( $base64 ? base64_decode( $cipher ) : $cipher );
        
        $td = mcrypt_module_open( MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, "" );
        
        $iv_size = mcrypt_enc_get_iv_size( $td );
        $iv = substr( $cipher, 0, $iv_size );
                                
        $cipher = substr( $cipher, $iv_size );
        mcrypt_generic_init( $td, $key, $iv );
        $clear = mdecrypt_generic( $td, $cipher );
        mcrypt_generic_deinit( $td );
        mcrypt_module_close( $td );
        
        $clear = substr(rtrim($clear, "\0"), 0, -1);
        
        return $clear;
    }

    /**
     * Implementation of RoundCube's create_iv function.
     *
     * Based on source code from the program/include/rcmail.php
     * rcmail::create_iv() function.
     *
     * @param int $size The size of the initialisation vector
     * @return string The initialisation vector
     */
    public static function createIV( $size )
    {
        $iv = '';
        for( $i = 0; $i < $size; $i++ )
            $iv .= chr( mt_rand( 0, 255 ) );
            
        return $iv;
    }


    /** 
     * Sets language
     *
     * By default is en_GB
     *
     * @param string $lang Users preference language
     * @return $this for fluent interface
     */
    public function setLanguage( $lang )
    {
        $this->_language = $lang;
        return $this;
    }

    /** 
     * Sets standard
     *
     * By default is 1
     *
     * @param int $standard Identity standard
     * @return $this for fluent interface
     */
    public function setStandard( $standard )
    {
        $this->_standard = $standard;
        return $this;
    }
}
