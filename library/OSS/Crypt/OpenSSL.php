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
 * OpenSSL public/private key generation tools.
 * 
 * NB: I keep getting bit by: https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=586202
 * 
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Crypt
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Crypt_OpenSSL
{
    /**
     * A variable to store a Passphrase to encrypt / decrypt the key pair
     *
     * @var string Passphrase to encrypt / decrypt the key pair
     */
    private $_passphrase = null;
    
    /**
     * A variable to hold a key pair resource
     *
     * @var resource A key pair resource
     */
    private $_keypair = null;
    
    /**
     * A variable to hold a certificate resource
     *
     * @var resource A certificate resource
     */
    private $_cert = null;

    /**
     * Sets passphrase.
     *
     * Sets passphrase which will be used for key pair encryption.
     * 
     * @param string $passphrase The passphrase for key pair encryption
     * @return void
     */
    public function setPassphrase( $passphrase )
    {
        $this->_passphrase = $passphrase;
    }

    /**
     * Sets key pair.
     *
     * Sets key pair from key pair file content
     * 
     * @param string $keypair The key pair file content
     * @return resource of key pair
     */
    public function setKeyPair( $keypair )
    {
        $this->_keypair = $keypair;
        return $this->_keypair;
    }


    /**
     * Generates key pair.
     *
     * Generates public and private key pair for encryption / decryption.
     * 
     * @param array $options The generator options.
     * @return resource of key pair
     */
    public function genKeyPair( $options )
    {
        $this->_keypair = openssl_pkey_new( $options );
        return $this->_keypair;
    }

    /**
     * Exports key pair.
     *
     * Exports key pair which is actually private key which store public key information.
     * If encrypt is set to true then export resource will be encrypted with $_passphrase
     * else it will return the actual key without encryption. By default $encrypt is true.
     * 
     * @param boo $encrypt The encrypt flag to encrypt or not the returning resource.
     * @return resource of key pair
     * @throws OSS_Crypt_Exception if $_keypair is not generated, or if encryption required 
     *         and $_passphrase is not unset.
     */
    public function exportKeyPair( $encrypt = true )
    {
        if( !$this->_keypair )
            throw new OSS_Crypt_Exception( 'To export key pair, you need to generate it first.' 
                . "\n\nDid you comment out multiple instances of subjectAltName in openssl.conf?" );
        
        if( $encrypt )
        {
            if( !$this->_passphrase )
                throw new OSS_Crypt_Exception( 'Encrypt key pair fails because no passphrase is set.' );
            openssl_pkey_export( $this->_keypair, $out, $this->_passphrase );
        }
        else
            openssl_pkey_export( $this->_keypair, $out );

        return $out;
    }

    /**
     * Exports public.
     *
     * Exports public key from $_keypair.
     * 
     * @return resource of public key
     * @throws OSS_Crypt_Exception if $_keypair is not generated.
     */
    public function exportPublicKey()
    {
        if( !$this->_keypair )
            throw new OSS_Crypt_Exception( 'To export public key, you need to generate key pair first.' );
        
        return openssl_pkey_get_details( $this->_keypair )[ 'key' ];
    } 
    
    /**
     * Generates sertificate for given information
     *
     * @param array      $dn      Certificate information
     * @oaram array|null $options Options for creating certificate
     * @return resource
     */
    public function genCertificate( $dn, $options = null )
    {
        if( !$this->_keypair )
            throw new OSS_Crypt_Exception( 'To create certificate, you need to generate key pair first.' );
          
        return openssl_csr_new( $dn, $this->_keypair, $options );
    }

    /**
     * Generates self signed certificate and returns it.
     * 
     * @param array      $dn      Certificate information
     * @param int        $days    Days until certificate expires
     * @param array|null $options Options for creating certificate
     * @param int        $serial  Serial number by default is 0
     * @return resource of certificate.
     */
    public function genSelfSignedCert( $dn, $days, $options = null, $serial = 0 )
    {
        $this->_cert = openssl_csr_sign( $this->genCertificate( $dn, $options ), null, $this->_keypair, $days, $options, $serial );
        return $this->_cert;
    }
    
    /**
     * Generates self signed certificate and returns it.
     * 
     * @param array      $dn      Certificate information
     * @param int        $days    Days until certificate expires
     * @param string     $cacert  Encrypted issuer certifcate
     * @param string     $cakey   Encrypted issuer private key
     * @param array|null $options Options for creating certificate
     * @param int        $serial  Serial number by default is 0
     * @return resource of certificate.
     */
    public function genSignedCert( $dn, $days, $cacert, $cakey, $options = null, $serial = 0  )
    {
        $this->_cert = openssl_csr_sign( $this->genCertificate( $dn, $options ), $cacert, $cakey, $days, $options, $serial );
        return $this->_cert;
    }

    /**
     * Exports self signed certificate as string.
     *
     * Converts self signed certificate to string, and returns it.
     * 
     * @return string
     * @throws OSS_Crypt_Exception if $_cert is not generated.
     */
    public function exportCert()
    {
        if( !$this->_cert )
            throw new OSS_Crypt_Exception( 'To export self signed certificate, you need to generate it first.' );
        
        openssl_x509_export( $this->_cert, $out );
        return $out;
    }
    
    /**
     * Parses encrypted certificate
     *
     * @param string $cert Encrypted certificate
     * @return array
     */
    public static function parseCertificate( $cert )
    {     
        return openssl_x509_parse( $cert );
    }
}

