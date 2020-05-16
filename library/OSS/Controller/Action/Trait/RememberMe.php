<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2014, Open Source Solutions Limited, Dublin, Ireland
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
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2014, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action - Trait for remember me actions
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2014, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_RememberMe
{
    /**
     * Check if remember me cookies are enabled and correctly configured in `application.ini`
     *
     * @return bool True if everything is configured correctly and enabled
     */
    protected function _rememberMeEnabled()
    {
        if( isset( $this->_options['resources']['auth']['oss']['rememberme'] ) )
        {
            $conf = $this->_options['resources']['auth']['oss']['rememberme'];

            if( isset( $conf['enabled'] ) && $conf['enabled'] )
            {
                if( !isset( $conf['timeout'] ) || !$conf['timeout'] )
                {
                    $this->getLogger()->err( 'Remember Me cookies enabled but misconfigured: timeout not defined' );
                    return false;
                }

                if( !isset( $conf['salt'] ) || !strlen( $conf['salt'] ) )
                {
                    $this->getLogger()->err( 'Remember Me cookies enabled but misconfigured: salt not defined' );
                    return false;
                }

                if( !isset( $conf['secure'] ) )
                {
                    $this->getLogger()->err( 'Remember Me cookies enabled but misconfigured: secure not defined' );
                    return false;
                }

                return true;
            }
        }

        return false;
    }


    /**
     * Process the user's cookies, if any, for valid 'remember me' authentication
     *
     * This will also update the user's cookie key to ensure it changes everytime it's used
     *
     * @return \Entities\User A user object if we have a valid cookie (otherwise false)
     */
    protected function _processRememberMeCookies()
    {
        if( !isset( $_COOKIE['aval'] ) || !isset( $_COOKIE['bval'] ) || !$_COOKIE['aval'] || !$_COOKIE['bval'] )
            return false;

        $cookie = $this->getD2EM()->getRepository( '\\Entities\\RememberMe' )->load( $_COOKIE['aval'], $_COOKIE['bval'] );

        if( !$cookie )
            return false;

        $user = $cookie->getUser();

        if( $cookie->getExpires() < new DateTime() )
        {
            $user->getRememberMes()->removeElement( $cookie );
            $this->getEntityManager()->remove( $cookie );
            $this->getEntityManager()->flush();
            return false;
        }

        // we have a valid combination. Update the user's cookies
        $this->_setRememberMeCookie( $user, $cookie );

        return $user;
    }



    /**
     * Set cookies for Remember Me functionality
     *
     * The username is stored as a salted SHA1 hashed value to protect the user's username
     * The key is a random 40 charater string
     *
     * @param \Entitues\User $user The user enitiy
     * @param \Entities\RememberMe $rememberme The remember me entity with cookie details (or null to create one)
     */
    protected function _setRememberMeCookie( $user, $rememberme = null )
    {
        if( $rememberme == null )
        {
            $rememberme = new \Entities\RememberMe();
            $rememberme->setUser( $user );
            $rememberme->setCreated( new DateTime() );
            $rememberme->setOriginalIp( $_SERVER['REMOTE_ADDR'] );
            $rememberme->setUserhash( $this->_generateCookieUserhash( $user ) );
            $this->getD2EM()->persist( $rememberme );
        }

        $expire = time() + $this->_options['resources']['auth']['oss']['rememberme']['timeout'];

        $rememberme->setExpires( new DateTime( "@{$expire}" ) );

        $rememberme->setLastUsed( new DateTime() );
        $rememberme->setCkey( OSS_String::random( 40, true, true, true, '', '' ) );

        $this->getD2EM()->flush();

        setcookie( 'aval', $rememberme->getUserhash(),  $expire, '/', '', $this->_options['resources']['auth']['oss']['rememberme']['secure'], true );
        setcookie( 'bval', $rememberme->getCkey(),      $expire, '/', '', $this->_options['resources']['auth']['oss']['rememberme']['secure'], true );
    }


    /**
     * Generate the user hash in a secure format for storage in a client side 'remember cookie' cookie
     *
     * @param \Entities\User $user The user entitiy to generate the hash for
     * @return string The `sha1()` hash
     */
    protected function _generateCookieUserhash( $user )
    {
        return sha1( $user->getId() . '+' . $user->getUsername() . '/' . $this->_options['resources']['auth']['oss']['rememberme']['salt'] );
    }



    /**
     * Delete all stored RememberMe cookies for a user (server and client side)
     *
     * @param \Entities\User $user The user entity
     * @return int The number of remember mes deleted from the DB (or 0 if none). Can be safely ignored.
     */
    protected function _deleteRememberMeCookie( $user )
    {
        if( !$this->_rememberMeEnabled() )
            return;
            
        setcookie( 'aval', '', time() - 100000, '/', '', $this->_options['resources']['auth']['oss']['rememberme']['secure'], true );
        setcookie( 'bval', '', time() - 100000, '/', '', $this->_options['resources']['auth']['oss']['rememberme']['secure'], true );

        return $this->getD2EM()->createQuery( "DELETE \\Entities\\RememberMe me WHERE me.User = ?1" )
                    ->setParameter( 1, $user )
                    ->execute();
    }

}
