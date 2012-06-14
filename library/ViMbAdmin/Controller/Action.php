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
class ViMbAdmin_Controller_Action extends Zend_Controller_Action
{

    /**
     * A variable to hold the identity object
     *
     * @var object An instance of the user's identity or false
     */
    protected $_auth = false;

    /**
     * A variable to hold an identify of the user
     *
     * Will be !false if there is a valid identity
     *
     * @var object An instance of the user's identity or false
     */
    protected $_identity = false;

    /**
     * A variable to hold the admin record
     *
     * @var object An instance of the user record
     */
    protected $_admin = false;

    /**
    * A variable to hold an instance of the bootstrap object
    *
    * @var object An instance of the bootstrap object
    */
    protected $_bootstrap;

    /**
    * A variable to hold an instance of the configuration object
    *
    * @var object An instance of the configuration object
    */
    protected $_config = null;

    /**
    * A variable to hold an instance of the logger object
    *
    * @var object An instance of the logger object
    */
    protected $_logger = null;

    /**
     * A variable to hold the mailer
     *
     * @var object An instance of the mailer
     */
    protected $_mailer = null;

    /**
     * A variable to hold the session namespace
     *
     * @var object An instance of the session namespace
     */
    protected $_session = null;

    /**
     * A variable to hold the Doctrine manager
     *
     * @var object An instance of the Doctrine manager
     */
    protected $_doctrine = null;

    /**
     * @var array an array representation of the application.ini
     */
    protected $_options = null;



    /**
     * The domain object from a 'id' parameter passed to the controller
     *
     * Set to false by default which is a requirement of preDispatch()
     * @see DomainController::preDispatch()
     */
    protected $_domain = false;

    /**
     * The admin object from an 'admin' parameter passed to the controller
     *
     * Set to false by default which is a requirement of preDispatch()
     * @see AdminController::preDispatch()
     */
    protected $_targetAdmin = false;

    /**
     * The alias object from a 'id' parameter passed to the controller
     *
     * Set to false by default which is a requirement of preDispatch()
     * @see AliasController::preDispatch()
     */
    protected $_mailbox = false;

    /**
     * The alias object from a 'id' parameter passed to the controller
     *
     * Set to false by default which is a requirement of preDispatch()
     * @see AliasController::preDispatch()
     */
    protected $_alias = false;


    /**
     * Override the Zend_Controller_Action's constructor (which is called
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
        // get the bootstrap object
        $this->_bootstrap = $invokeArgs['bootstrap'];

        // load up the options
        $this->_options = $this->_bootstrap->getOptions();
        Zend_Registry::set( 'options', $this->_options );

        // and from the bootstrap, we can get other resources:
        $this->_config   = $this->_bootstrap->getResource( 'config' );
        $this->_logger   = $this->_bootstrap->getResource( 'logger' );
        $this->_session  = $this->_bootstrap->getResource( 'namespace' );
        $this->_doctrine = $this->_bootstrap->getResource( 'doctrine' );
        $this->_auth     = $this->_bootstrap->getResource( 'auth' );
        $this->_identity = $this->_auth->getIdentity();
        $this->_admin    = $this->_identity['admin'];

        // Smarty must be set during bootstrap
        try
        {
            $this->view = $this->createView();

            $this->view->session     = $this->_session;
            $this->view->options     = $this->_options;
            $this->view->auth        = $this->_auth;
            $this->view->hasIdentity = $this->_auth->hasIdentity();
            $this->view->identity    = $this->_identity;
            $this->view->inColorBox  = false;
        }
        catch( Zend_Exception $e )
        {
            echo _( 'Caught exception' ) . ': ' . get_class( $e ) . "\n";
            echo _( 'Message' ) . ': ' . $e->getMessage() . "\n";

            die( "\n\n" . _( 'You must set-up Smarty in the bootstrap code.' ) . "\n\n" );
        }

        $this->view->addHelperPath( 'ViMbAdmin/View/Helper', 'ViMbAdmin_View_Helper' );

        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        $this->view->controller = $this->getRequest()->getParam( 'controller' );
        $this->view->action     = $this->getRequest()->getParam( 'action'     );

        $this->view->doctype( 'XHTML1_TRANSITIONAL' );
        $this->view->headMeta()->appendHttpEquiv( 'Content-Type', 'text/html; charset=utf-8' );

        // if we issue a redirect, we want it to exit immediatly
        $this->getHelper( 'Redirector' )->setExit( true );


        // SECURITY and other stuff for logged in users
        if( $this->_auth->hasIdentity() )
        {
            // version check
            if( !( isset( $this->_options['skipVersionCheck'] ) && $this->_options['skipVersionCheck'] ) )
                if( $this->getAdmin()->isSuper() )
                    $this->checkVersion();
            
            
            // SECURITY
            $params = $this->_getAllParams();

            if( isset( $params['aid'] ) && $params['aid'] )
            {
                if( !( $this->_targetAdmin = $this->loadAdmin( $params['aid'] ) ) )
                {
                    // domain id parameter specified but invalid or non-existant
                    $this->addMessage( _( "Invalid or non-existant admin." ), ViMbAdmin_Message::ERROR );
                    $this->_redirect( 'admin/list' );
                    die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                }

                // can only act on a target admin if we're a super admin (or ourselves!)!
                if( $this->getAdmin()->id != $this->_targetAdmin['id'] )
                    $this->authorise( true );
            }

            if( isset( $params['did'] ) && $params['did'] )
            {
                if( !( $this->_domain = $this->loadDomain( $params['did'] ) ) )
                {
                    // domain id parameter specified but invalid or non-existant
                    $this->addMessage( _( "Invalid or non-existant domain." ), ViMbAdmin_Message::ERROR );
                    $this->_redirect( 'domain/list' );
                    die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                }

                // is this user allowed to admin the given domain?
                $this->authorise( false, $this->_domain );
            }

            if( isset( $params['mid'] ) && $params['mid'] )
            {
                if( !( $this->_mailbox = $this->loadMailbox( $params['mid'] ) ) )
                {
                    // mailbox id parameter specified but invalid or non-existant
                    $this->addMessage( _( "Invalid or non-existant mailbox." ), ViMbAdmin_Message::ERROR );
                    $this->_redirect( 'mailbox/list' );
                    die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                }

                // is this user allowed to admin the given mailbox?
                if( !$this->_domain )
                {
                    if( !( $this->_domain = Doctrine::getTable( 'Domain' )->findOneByDomain( $this->_mailbox['domain'] ) ) )
                    {
                        $this->addMessage( _( "Invalid or non-existant domain." ), ViMbAdmin_Message::ERROR );
                        $this->_redirect( 'domain/list' );
                        die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                    }
                }

                $this->authorise( false, $this->_domain );
            }

            if( isset( $params['alid'] ) && $params['alid'] )
            {
                if( !( $this->_alias = $this->loadAlias( $params['alid'] ) ) )
                {
                    // alias id parameter specified but invalid or non-existant
                    $this->addMessage( _( "Invalid or non-existant alias." ), ViMbAdmin_Message::ERROR );
                    $this->_redirect( 'alias/list' );
                    die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                }

                // is this user allowed to admin the given mailbox?
                if( !$this->_domain )
                {
                    if( !( $this->_domain = Doctrine::getTable( 'Domain' )->findOneByDomain( $this->_alias['domain'] ) ) )
                    {
                        $this->addMessage( _( "Invalid or non-existant domain." ), ViMbAdmin_Message::ERROR );
                        $this->_redirect( 'domain/list' );
                        die( 'ViMbAdmin_Controller_Action:preDispatch() - should not execute' );
                    }
                }

                $this->authorise( false, $this->_domain );
            }



        }
        else
        {

        }
    }


    /**
     * A utility method to get a named resource.
     *
     * @param string $resource
     */
    public function getResource( $resource )
    {
        return $this->_bootstrap->getResource( $resource );
    }


    /**
    * Creates and returns with a new view object.
    *
    * @param void
    * @return object
    */
    public function createView()
    {
        $vView = (
                    $this->_bootstrap->getResource( 'view' ) === null
                        ? $this->_bootstrap->getResource( 'smarty' )
                        : $this->_bootstrap->getResource( 'view' )
        );

        $vView->pagebase = '';

        if( isset( $_SERVER['SERVER_NAME'] ) )
            $vView->pagebase = 'http' 
                . ( ( isset( $_SERVER['HTTPS'] && !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) || ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] == 443 ) ) ? 's' : '' ) 
                . '://'
                . $_SERVER['SERVER_NAME']
                . Zend_Controller_Front::getInstance()->getBaseUrl();

        $vView->basepath = Zend_Controller_Front::getInstance()->getBaseUrl();

        return $vView;
    }


    /**
     * Returns the logger object
     *
     * @return Zend_Log The Zend_Log object
     */
    public function getLogger()
    {
        return $this->_logger;
    }


    /**
     * Load a configuration value
     *
     * @param string $key The associate array key to get from the config array
     * @return mixed The configuration value for the given key
     */
    public function getConfigValue( $key )
    {
        return $this->_config[ $key ];
    }

    
    public function checkVersion()
    {
        // only check once per 24 hours per session
        if( isset( $this->_session->versionChecked ) && $this->_session->versionChecked > ( time() - 86400 ) )
            return;
            
        // only check once in a 24h period for each user
        $lastCheck = ConfigTable::getValue( 'version_last_check_at.' . $this->getAdmin()->id );
        if( $lastCheck && $lastCheck > time() - 86400 )
        {
            $this->_session->versionChecked = $lastCheck;
            return;
        }
               
        // is there a new version available?
        if( ViMbAdmin_Version::compareVersion( ViMbAdmin_Version::getLatest() ) == 1 )
        {
            $this->addMessage(
                sprintf(
                    _( 'Current version is: %s. There is a new version available: %s. See the <a href="https://github.com/opensolutions/ViMbAdmin/blob/master/CHANGELOG">change log</a>.' ),
                    ViMbAdmin_Version::VERSION,
                    ViMbAdmin_Version::getLatest()
                ),
                ViMbAdmin_Message::INFO
            );
        }
        
        $this->_session->versionChecked = time();
        ConfigTable::setValue( 'version_last_check_at.' . $this->getAdmin()->id, $this->_session->versionChecked );
    }

    /**
     * Adds a message to the session. Useful when you need a message to be displayed after a _redirect(), which normally gets rid of all messages as the messages by default
     * go to a view variable, while this goes into the session, and the Smarty function will clear it out just after showing the message.
     *
     * @param string $message the message text
     * @param string $class the message class, ViMbAdmin_Message::INFO|ALERT|SUCCESS|ERROR|...
     * @return void
     */
    public function addMessage( $message, $class )
    {
        $this->_session->ViMbAdmin_Messages[] = new ViMbAdmin_Message( $message, $class );
    }


    /**
     * Adds messages to the session.
     *
     * @see addMessage
     * @param string $messages the array of messages
     * @param string $class the message class, ViMbAdmin_Message::INFO|ALERT|SUCCESS|ERROR|...
     * @return void
     */
    public function addMessages( $messages, $class )
    {
        if( !is_array( $messages ) )
            $messages = array( $messages );

        foreach( $messages as $msg )
            $this->addMessage( $msg, $class );
    }


    /**
     * Return the Zend_Auth instance.
     * @return Zend_Auth The Zend_Auth instance or false
     */
    protected function getAuth()
    {
        return $this->_auth;
    }


    /**
     * Returns the identify object for the Zend_Auth session.
     *
     * Will be !false if there is a valid identity
     *
     * @return array The Zend_auth identity object or false
     */
    protected function getIdentity()
    {
        return $this->_identity;
    }


    /**
     * Get the user ORM object.
     *
     * Returns the instance of the Doctrine User object for the logged in user.
     *
     * @return Admin The admin object or false.
     */
    protected function getAdmin()
    {
        return $this->_admin;
    }


    /**
     * Get the namespace (session).
     *
     * @return Zend_Session_Namespace The session namespace.
     */
    protected function getSession()
    {
        return $this->_session;
    }


    /**
     * A function to generate a URL with the given parameters.
     *
     * This is a useful function as no knowledge of the application's path is required.
     *
     * @param string|bool $controller The controller to call.
     * @param string|bool $action     The action to call (controller must be set if setting action)
     * @param string|bool $module      The module to use. Set to false to ignore.
     * @param string|bool $params     An array of key value pairs to add to the URL.
     */
    public function genUrl( $controller = false, $action = false, $module = false, $params = array() )
    {
        $url = $this->getFrontController()->getBaseUrl();

        // when the webpage is directly under "xyz.com/", and not in "xyz.com/wherever"
        // an empty href attribute in an anchor tag means "the current URL", which is not always good
        if ($url == '')
        {
            $url = 'http';
            if ( ( isset($_SERVER['HTTPS']) ) && ( $_SERVER['HTTPS'] == 'on' ) ) $url .= 's';
            $url .= "://{$_SERVER['HTTP_HOST']}";
        }

        if( $module )
            $url .= "/{$module}";

        if( $controller )
            $url .= "/{$controller}";

        if( $action )
            $url .= "/{$action}";

        foreach( $params as $var => $value )
            $url .= "/{$var}/{$value}";

        return $url;
    }


    /**
     * Load an Admin object from a user supplied parameter.
     *
     * @param int $id The admin to load
     * @return bool|Admin Either false or the admin object
     */
    public function loadAdmin( $id = null )
    {
        if( !$id || $id == null || $id == 0 || !is_numeric( $id ) || $id == '0' || !strlen( $id ) )
            return false;

        return Doctrine::getTable( 'Admin' )->find( $id );
    }


    /**
     * Load a Domain object from a user supplied parameter.
     *
     * @param int $id The domain to load
     * @return bool|Domain Either false or the domain object
     */
    public function loadDomain( $id = null )
    {
        if( !$id || $id == null || $id == 0 || !is_numeric( $id ) || $id == '0' || !strlen( $id ) )
            return false;

        return Doctrine::getTable( 'Domain' )->find( $id );
    }


    /**
     * Load an Alias object from a user supplied parameter.
     *
     * @param int $id The alias to load
     * @return Alias Either false or the alias object
     */
    public function loadAlias( $id = null )
    {
        if( !$id || $id == null || $id == 0 || !ctype_digit( $id ) || $id == '0' || !strlen( $id ) )
            return false;

        return Doctrine::getTable( 'Alias' )->find( $id );
    }


    /**
     * Load an Mailbox object from a user supplied parameter.
     *
     * @param int $id The mailbox to load
     * @return Mailbox Either false or the mailbox object
     */
    public function loadMailbox( $id = null )
    {
        if( !$id || $id == null || $id == 0 || !ctype_digit( $id ) || $id == '0' || !strlen( $id ) )
            return false;

        return Doctrine::getTable( 'Mailbox' )->find( $id );
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
                $this->addMessage( _( 'You must be logged in to perform the requested action.' ), ViMbAdmin_Message::INFO );
                $this->_redirect( 'auth/login' );
            }
            else
                return false;

            // should not be executed:
            die( _( 'Security Issue' ) . ': ' . $this->getHelper( 'Redirector' )->setExit( true ) . _( 'is not set' ) . '.' );
        }

        if( $this->getAdmin()->isSuper() )
            return true; // a superadmin can do everything

        if( $super ) // user should be a super but is not
        {
            if( $redirect )
            {
                $this->addMessage( _( 'You must be a superadmin to perform this function.' ), ViMbAdmin_Message::ALERT );
                $this->_redirect( 'auth/login' );
            }
            else
                return false;

            // should not be executed:
            die( _( 'Security Issue' ) . ': ' . $this->getHelper( 'Redirector' )->setExit( true ) . _( 'is not set' ) . '.' );
        }

        if( $domain ) // if not [null, false, 0] ( 0 is 'add new' as every id >= 1 )
        {
            try
            {
                if( is_string( $domain ) && strlen( $domain ) )
                    $domain = Doctrine::getTable( 'Domain' )->findOneByDomain( $domain );
                else if( ctype_digit( $domain ) && $domain )
                    $domain = Doctrine::getTable( 'Domain' )->find( $domain );

                if( !( $domain instanceof Domain ) || !$domain['id'] )
                {
                    if( $redirect )
                    {
                        $this->addMessage( _( "You do not have the required privileges to perform this action." ), ViMbAdmin_Message::INFO );
                        $this->_redirect( 'auth/login' );
                    }
                    else
                        return false;
                }

                $canEdit = Doctrine_Query::create()
                            ->from( 'DomainAdmin' )
                            ->where( 'username = ?', $this->getAdmin()->username )
                            ->andWhere( 'domain = ?', $domain['domain'] )
                            ->fetchArray();

                if( sizeof( $canEdit ) == 0 )
                {
                    if( $redirect )
                    {
                        $this->addMessage( _( "You do not have the required privileges to perform this action." ), ViMbAdmin_Message::INFO );
                        $this->_redirect( 'auth/login' );
                    }
                    else
                        return false;

                    // should not be executed:
                    die( _( 'Security Issue' ) . ': ' . $this->getHelper( 'Redirector' )->setExit( true ) . _( 'is not set' ) . '.' );
                }

                return true;
            }
            catch( Exception $e )
            {
                $this->_logger->err( "Exception in Action::authorise(): " . $e->getMessage() );

                if( $redirect )
                {
                    $this->addMessage(
                        _( 'System error during login - please see system logs or contact your system administrator.' ),
                        ViMbAdmin_Message::ERROR
                    );

                    $this->_redirect( 'auth/login' );
                }
                else
                    return false;

                // should not be executed:
                die( _( 'Security Issue' ) . ': ' . $this->getHelper( 'Redirector' )->setExit( true ) . _( 'is not set' ) . '.' );
            }
        }

        return true;
    }

}
