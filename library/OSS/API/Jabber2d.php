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
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * A Jabber2d API via direct database manipulation.
 *
 * @see http://jabberd2.org/
 *
 * @category   OSS
 * @package    OSS_API
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_API_Jabber2d
{
    // use DBAL connections for database manipulation
    use OSS_Doctrine2_DBAL_Connection;

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
     * Get all users registered in the database as an array.
     *
     * Returns:
     *
     *     array (size=n)
     *         0 =>
     *             array (size=3)
     *                 'username' => string 'johndoe' (length=5)
     *                 'realm' => string 'example.com' (length=16)
     *                 'password' => string 'soopersecret' (length=9)
     *         1 =>
     *             ...
     *
     * @param void
     * @return array All users registered in the database
     * @access public
     */
    public function getAllUsers()
    {
        return $this->getDBAL()->fetchAll( 'SELECT * FROM authreg' );
    }


    /**
     * Returns with a user's authreg entry as an assciative array, or with false if it wasn't found.
     *
     * @param string $username
     * @param string $realm
     * @return array|boolean
     * @access public
     */
    public function getAuthReg( $username, $realm )
    {
        return $this->getDBAL()->fetchAssoc( 'SELECT * FROM authreg WHERE username = ? AND realm = ?',
            array( $username, $realm )
        );
    }


    /**
     * Adds an authreg entry.
     *
     * @param string $username
     * @param string $realm
     * @param string $password
     * @return int
     * @access public
     */
    public function addAuthReg( $username, $realm, $password )
    {
        return $this->getDBAL()->insert( 'authreg',
            [ 'username' => $username, 'realm' => $realm, 'password' => $password ]
        );
    }


    /**
     * Updates an authreg entry.
     *
     * @param string $username
     * @param string $realm
     * @param string $password
     * @return int
     * @access public
     */
    public function updateAuthReg( $username, $realm, $password )
    {
        return $this->getDBAL()->update( 'authreg',
            [ 'password' => $password ],
            [ 'username' => $username, 'realm' => $realm ]
        );
    }


    /**
     * Deletes a user's authreg entry, and all related entries.
     *
     * Encapsulates all the delete statements in one transaction.
     *
     * @param string $username
     * @param string $realm
     * @return void
     * @access public
     */
    public function deleteAuthReg( $username, $realm )
    {
        $co = "{$username}@{$realm}";

        $this->getDBAL()->beginTransaction();
        $this->getDBAL()->delete( 'active',              [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`disco-items`',       [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'logout',              [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`motd-message`',      [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`motd-times`',        [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`privacy-default`',   [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`privacy-items`',     [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'private',             [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'queue',               [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`roster-groups`',     [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`roster-items`',      [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'status',              [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( '`vacation-settings`', [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'vcard',               [ '`collection-owner`' => $co ] );
        $this->getDBAL()->delete( 'authreg',             [ 'username' => $username, 'realm' => $realm ] );
        $this->getDBAL()->commit();
    }

}
