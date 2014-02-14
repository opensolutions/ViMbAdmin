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
 */

/**
 * ViMbAdmin's version of Zend_Controller_Action, implementing custom functionality.
 * All application controlers subclass this rather than Zend's version directly.
 *
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Controller_Action extends OSS_Controller_Action
{
    use OSS_Controller_Action_Trait_Namespace;
    use OSS_Controller_Action_Trait_Doctrine2Cache;
    use OSS_Controller_Action_Trait_Doctrine2;
    use OSS_Controller_Action_Trait_Doctrine2User;
    use OSS_Controller_Action_Trait_Auth;
    // use OSS_Controller_Action_Trait_AuthRequired;
    use OSS_Controller_Action_Trait_Mailer;
    use OSS_Controller_Action_Trait_Logger;
    use OSS_Controller_Action_Trait_Smarty;
    use OSS_Controller_Action_Trait_Messages;

    /**
     * The domain object from a 'id' parameter passed to the controller
     *
     * @var \Entities\Domain
     */
    protected $_domain = false;

    /**
     * The admin object from an 'admin' parameter passed to the controller
     *
     * @var \Entities\Admin
     */
    protected $_targetAdmin = false;

    /**
     * The alias object from a 'id' parameter passed to the controller
     *
     * @var \Entities\Mailbox
     */
    protected $_mailbox = false;

    /**
     * The alias object from a 'id' parameter passed to the controller
     *
     * @var \Entities\Alias
     */
    protected $_alias = false;

    /**
     * The archive object from a 'id' parameter passed to the controller
     *
     * @var \Entities\Archive
     */
    protected $_archive = false;

    /**
     * Override the OSS_Controller_Action's constructor (which is called
     * at the very beginning of this function anyway).
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function __construct(
        Zend_Controller_Request_Abstract  $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = null )
    {
        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        // if we issue a redirect, we want it to exit immediatly
        $this->getHelper( 'Redirector' )->setExit( true );

        // SECURITY and other stuff for logged in users
        if( $this->getAuth()->hasIdentity() )
        {
            // version check
            $this->checkVersion();

            // SECURITY and load objects
            if( ( $aid = $this->getParam( 'aid', false ) ) )
                $this->_targetAdmin = $this->loadAdmin( $aid );

            if( ( $did = $this->getParam( 'did', false ) ) )
                $this->_domain = $this->loadDomain( $did );

            if( ( $mid = $this->getParam( 'mid', false ) ) )
                $this->_mailbox = $this->loadMailbox( $mid );

            if( ( $alid = $this->getParam( 'alid', false ) ) )
                $this->_alias = $this->loadAlias( $alid );

            if( ( $arid = $this->getParam( 'arid', false ) ) )
                $this->_archive = $this->loadArchive( $arid );
        }
    }



    public function checkVersion()
    {
        if( isset( $this->_options['skipVersionCheck'] ) && $this->_options['skipVersionCheck'] )
            return;

        if( !$this->getAdmin()->isSuper() )
            return;

        // only check once per 24 hours per session
        if( isset( $this->getSessionNamespace()->versionChecked ) && $this->getSessionNamespace()->versionChecked > ( time() - 86400 ) )
            return;

        // only check once in a 24h period for each user
        $lastCheck = $this->getAdmin()->getPreference( 'version_last_check_at' );

        if( $lastCheck && $lastCheck > time() - 86400 )
        {
            $this->getSessionNamespace()->versionChecked = $lastCheck;
            return;
        }

        // is there a new version available?
        if( ViMbAdmin_Version::compareVersion( ViMbAdmin_Version::getLatest() ) == 1 )
        {
            $this->addMessage(
                sprintf(
                    _( 'Current version is: %s. There is a new version available: %s. '
                        . 'See the <a href="https://github.com/opensolutions/ViMbAdmin/releases list</a>.' 
                    ),
                    ViMbAdmin_Version::VERSION,
                    ViMbAdmin_Version::getLatest()
                ),
                OSS_Message::INFO
            );
        }

        $this->getSessionNamespace()->versionChecked = time();
        $this->getAdmin()->setPreference( 'version_last_check_at', $this->getSessionNamespace()->versionChecked );
        $this->getD2EM()->flush();
    }

    /**
     * Get the user ORM object.
     *
     * Returns the instance of the Doctrine User object for the logged in user.
     *
     * @return \Entities\Admin The admin object or false.
     */
    public function getAdmin()
    {
        return $this->getUser();
    }

    /**
     * Load an Admin object from a user supplied ID parameter.
     *
     * @param int $id The admin to load
     * @param bool $redirect If no admin found, redirect to `admin/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this admin
     * @return \Entities\Admin Either false or the admin object
     */
    public function loadAdmin( $id = null, $redirect = true, $authorise = true )
    {
        $admin = $this->getD2EM()->getRepository( '\\Entities\\Admin' )->find( $id );

        if( !$admin )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant admin." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'admin/list' );
        }

        if( $authorise )
        {
            // can only act on a target admin if we're a super admin (or ourselves!)!
            if( $this->getAdmin()->getId() != $admin->getId() )
                $this->authorise( true );
        }

        return $admin;
    }


    /**
     * Load a Domain object from a user supplied parameter.
     *
     * @param int $id The domain to load
     * @param bool $redirect If no domain found, redirect to `domain/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this domain
     * @return \Entities\Domain Either false or the domain object
     */
    public function loadDomain( $id = null, $redirect = true, $authorise = true )
    {
        $domain = $this->getD2EM()->getRepository( '\\Entities\\Domain' )->find( $id );

        if( !$domain )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant domain." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'domain/list' );
        }

        if( $authorise )
            $this->authorise( false, $domain );

        return $domain;
    }

    /**
     * Load an Alias object from a user supplied parameter.
     *
     * @param int $id The domain to load
     * @param bool $redirect If no alias found, redirect to `alias/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this alias
     * @return \Entities\Alias Either false or the alias object
     */
    public function loadAlias( $id = null, $redirect = true, $authorise = true )
    {
        $alias = $this->getD2EM()->getRepository( '\\Entities\\Alias' )->find( $id );

        if( !$alias )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant alias." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'alias/list' );
        }

        if( $authorise )
            $this->authorise( false, $alias->getDomain() );

        $this->_domain = $alias->getDomain();

        return $alias;
    }


    /**
     * Load a Mailbox object from a user supplied parameter.
     *
     * @param int $id The mailbox to load
     * @param bool $redirect If no mailbox found, redirect to `mailbox/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this mailbox
     * @return \Entities\Mailbox Either false or the mailbox object
     */
    public function loadMailbox( $id = null, $redirect = true, $authorise = true )
    {
        $mailbox = $this->getD2EM()->getRepository( '\\Entities\\Mailbox' )->find( $id );

        if( !$mailbox )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant mailbox." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'mailbox/list' );
        }

        if( $authorise )
            $this->authorise( false, $mailbox->getDomain() );

        $this->_domain = $mailbox->getDomain();

        return $mailbox;
    }
    
    /**
     * Load a Mailbox object from a user supplied parameter.
     *
     * @param string $username The mailbox address
     * @param bool $redirect If no mailbox found, redirect to `mailbox/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this mailbox
     * @return \Entities\Mailbox Either false or the mailbox object
     */
    public function loadMailboxByUsername( $username, $redirect = true, $authorise = true )
    {
        $mailbox = $this->getD2EM()->getRepository( '\\Entities\\Mailbox' )->findOneBy( ['username' => $username ] );

        if( !$mailbox )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant mailbox." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'mailbox/list' );
        }

        if( $authorise )
            $this->authorise( false, $mailbox->getDomain() );

        $this->_domain = $mailbox->getDomain();

        return $mailbox;
    }

    /**
     * Load an Archive object from a user supplied ID parameter.
     *
     * @param int $id The archive id to load
     * @param bool $redirect If no archive found, redirect to `archive/list` rather than returning false
     * @param bool $authorise If true, ensure the current user can act on this admin
     * @return \Entities\Archive Either false or the archive object
     */
    public function loadArchive( $id = null, $redirect = true, $authorise = true )
    {
        $archive = $this->getD2EM()->getRepository( '\\Entities\\Archive' )->find( $id );

        if( !$archive )
        {
            if( !$redirect ) return false;

            $this->addMessage( _( "Invalid or non-existant archive." ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( 'archive/list' );
        }

        if( $authorise )
            $this->authorise( false, $archive->getDomain() );

        $this->_domain = $archive->getDomain();
        return $archive;
    }

    /**
     * Accessor method for the target admin object
     *
     * @return \Entities\Admin Or false
     */
    public function getTargetAdmin()
    {
        return $this->_targetAdmin;
    }

    /**
     * Accessor method for the domain object
     *
     * @return \Entities\Domain Or false
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Accessor method for the mailbox object
     *
     * @return \Entities\Mailbox Or false
     */
    public function getMailbox()
    {
        return $this->_mailbox;
    }

    /**
     * Accessor method for the alias object
     *
     * @return \Entities\Alias Or false
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Accessor method for the archive object
     *
     * @return \Entities\Archive Or false
     */
    public function getArchive()
    {
        return $this->_archive;
    }

    /**
     * A generic authorisation checker for use in the controllers.
     *
     * Checks if the user:
     *    - is logged in
     *    - is a superadmin or, if not:
     *    -  is allowed to access a specific domain.
     *
     * This function performs a _redirect() (and die()) if the authisation conditions are not met.
     *
     * @param boolean $super Set to true to check if the user is a super admin
     * @param int|string|Domain $domain Set to the domain object, domain name or domain ID to check. Defaults to null.
     * @param boolean $redirect default true true if we want to redirect the user, false if we want a boolean return value
     * @return void
     */
    protected function authorise( $super = false, $domain = null, $redirect = true )
    {
        if( !$this->getAuth()->hasIdentity() )
        {
            if( $redirect )
            {
                $this->addMessage( _( 'You must be logged in to perform the requested action.' ), OSS_Message::INFO );
                $this->redirectAndEnsureDie( 'auth/login' );
            }

            return false;
        }

        if( $this->getAdmin()->isSuper() )
        {
            return true; // a superadmin can do everything
        }
        else if( $super ) // user should be a super but is not
        {
            if( $redirect )
            {
                $this->addMessage( _( 'You must be a superadmin to perform this function.' ), OSS_Message::ALERT );
                $this->redirectAndEnsureDie( 'auth/login' );
            }

            return false;
        }

        if( $domain ) // if not [null, false, 0] ( 0 is 'add new' as every id >= 1 )
        {
            if( is_string( $domain ) && strlen( $domain ) )
                $domain = $this->getD2EM()->getRepository( '\\Entities\\Domain' )->findOneBy( [ 'domain' => $domain ] );
            else if( ctype_digit( $domain ) && $domain )
                $domain = $this->getD2EM()->getRepository( '\\Entities\\Domain' )->find( $domain );

            if( !( $domain instanceof \Entities\Domain ) || !$domain->getId() )
            {
                if( $redirect )
                {
                    $this->addMessage( _( "You do not have the required privileges to perform this action." ), OSS_Message::INFO );
                    $this->redirectAndEnsureDie( 'auth/login' );
                }

                return false;
            }

            if( !$this->getAdmin()->canManageDomain( $domain ) )
            {
                if( $redirect )
                {
                    $this->addMessage( _( "You do not have the required privileges to perform this action." ), OSS_Message::INFO );
                    $this->redirectAndEnsureDie( 'auth/login' );
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Add a new log record to the Log table
     *
     * @param $action string The action
     * @param $message string The log message
     * @return \Entities\Log
     */
    protected function log( $action, $message )
    {
        $log = new \Entities\Log();
        $log->setAction( $action );
        $log->setData( $message );
        $log->setAdmin( $this->getAdmin() );

        if( $this->getDomain() )
            $log->setDomain( $this->getDomain() );

        $log->setTimestamp( new DateTime() );
        $this->getD2EM()->persist( $log );

        return $log;
    }

}
