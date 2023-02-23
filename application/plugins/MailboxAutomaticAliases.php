<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2014 Open Source Solutions Limited
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
 * @copyright Copyright (c) 2014 Matthias Fechner
 * @copyright Copyright (c) 2022 Daniel Rudolf
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Matthias Fechner <matthias _at_ fechner.net>
 * @author Daniel Rudolf <vimbadmin _at_ daniel-rudolf.de>
 */

/**
 * The Mailbox Automatic Aliases Plugin
 *
 * The plugin ensures that a required set of aliases for a domain are existent.
 *
 * Required aliases:
 *   postmaster@domain.tld
 *   abuse@domain.tld
 * Optional aliases:
 *   webmaster@domain.tld
 *   hostmaster@domains.tld
 *
 * Add the following lines to configs/application.ini:
 *   vimbadmin_plugins.MailboxAutomaticAliases.disabled = false
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "postmaster"
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "abuse"
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "hostmaster"
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "webmaster"
 *
 * Automatic aliases are created when a new mailbox or alias is created. They
 * either use a configured defaultMapping, or the just created mailbox or alias
 * as goto address. See configs/application.ini:
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultMapping.postmaster = "@other.tld"
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultMapping.abuse = "postmaster"
 *   vimbadmin_plugins.MailboxAutomaticAliases.defaultMapping.* = "root@domain.tld"
 *
 * @package ViMbAdmin
 * @subpackage Plugins
 */

class ViMbAdminPlugin_MailboxAutomaticAliases extends ViMbAdmin_Plugin implements OSS_Plugin_Observer
{

    /** @var string<int, string> */
    private $defaultAliases;

    /** @var array<string, string> */
    private $defaultMapping;

    public function __construct( OSS_Controller_Action $controller )
    {
        parent::__construct( $controller, get_class() );

        // read config parameters
        $this->defaultAliases = isset( $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultAliases'] )
            ? $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultAliases'] : [];

        $this->defaultMapping = isset( $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultMapping'] )
           ? $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultMapping'] : [];
    }

    /**
     * Create automatic aliases after a mailbox was created
     *
     * @param MailboxController $controller
     * @param array|null $options
     */
    public function mailbox_add_addPostflush( MailboxController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $mailbox = $controller->getMailbox()->getUsername();

        if( $this->defaultAliases ) {
            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return;
            }

            foreach( $this->defaultAliases as $item ) {
                // automatic alias exists
                if( $this->getAlias( $controller, $item . '@' . $domain ) !== null ) {
                    continue;
                }

                // create automatic alias
                $alias = $this->createAutomaticAlias( $controller, $item, $mailbox );

                $message = _( 'Auto-created alias %s -> %s.' );
                $controller->addMessage( sprintf( $message, $alias->getAddress(), $alias->getGoto() ) );
            }
        }
    }

    /**
     * Checks whether a mailbox is an automatic alias' goto mailbox and
     * prevents its deletion
     *
     * @param MailboxController $controller
     * @param array|null $options
     * @return bool
     */
    public function mailbox_purge_preRemove( MailboxController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $mailbox = $controller->getMailbox()->getUserName();

        if( $this->defaultAliases ) {
            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return true;
            }

            foreach( $this->defaultAliases as $item ) {
                // prevent deletion of an automatic alias' goto mailbox
                $alias = $this->getAlias( $controller, $item . '@' . $domain );
                if( $alias !== null && $alias['goto'] === $mailbox ) {
                    $message = _( 'Mailbox %s is used to fullfill automatic alias %s. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                        . 'If you want to delete it, update the alias to use a different goto address first.' );
                    $controller->addMessage( sprintf( $message, $mailbox, $alias['address'] ), OSS_Message::ERROR );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether a mailbox is an automatic alias' goto mailbox and
     * prevents disabling the mailbox
     *
     * @param MailboxController $controller
     * @param array|null $options
     * @return bool
     */
    public function mailbox_toggleActive_preToggle( MailboxController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $mailbox = $controller->getMailbox()->getUserName();

        if( $options['active'] && $this->defaultAliases ) {
            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return true;
            }

            foreach( $this->defaultAliases as $item ) {
                // prevent toggling an automatic alias' goto mailbox off
                $alias = $this->getAlias( $controller, $item . '@' . $domain );
                if( $alias !== null && $alias['goto'] === $mailbox ) {
                    $message = _( 'Mailbox %s is used to fullfill automatic alias %s. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                        . 'If you want to disable it, update the alias to use a different goto address first.' );
                    printf( $message, $mailbox, $alias['address'] );
                    exit(0);
                }
            }
        }

        return true;
    }

    /**
     * Create automatic aliases after an alias was created
     *
     * @param AliasController $controller
     * @param array|null $options
     */
    public function alias_add_addPostflush( AliasController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $aliasAddress = $controller->getAlias()->getAddress();

        if( $this->defaultAliases ) {
            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return;
            }

            // create automatic aliases, if required
            foreach( $this->defaultAliases as $item ) {
                // automatic alias exists
                if( $this->getAlias( $controller, $item . '@' . $domain ) !== null ) {
                    continue;
                }

                // create automatic alias
                $alias = $this->createAutomaticAlias( $controller, $item, $aliasAddress );

                $message = _( 'Auto-created alias %s -> %s.' );
                $controller->addMessage( sprintf( $message, $alias->getAddress(), $alias->getGoto() ) );
            }
        }
    }

    /**
     * Checks whether an alias is an automatic alias or an automatic alias'
     * goto alias, and prevents its deletion
     *
     * @param AliasController $controller
     * @param array|null $options
     * @return bool
     */
    public function alias_delete_preRemove( AliasController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $aliasAddress = $controller->getAlias()->getAddress();

        if( $this->defaultAliases ) {
            // if we're about to delete a domain alias, ensure that distinct automatic aliases exist
            if( '@' . $domain === $aliasAddress ) {
                foreach( $this->defaultAliases as $item ) {
                    $alias = $this->getAlias( $controller, $item . '@' . $domain );
                    if( $alias === null || !$alias['active'] ) {
                        $message = _( 'Alias %s is used to fullfill automatic alias %s. '
                            . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                            . 'If you want to delete it, create a distinct alias first.' );
                        $controller->addMessage( sprintf( $message, $aliasAddress, $item . '@' . $domain ), OSS_Message::ERROR );
                        return false;
                    }
                }

                return true;
            }

            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return true;
            }

            foreach( $this->defaultAliases as $item ) {
                // prevent deletion of an automatic alias
                if( $item . '@' . $domain === $aliasAddress ) {
                    $message = _( 'Alias %s is required and cannot be deleted. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>.');
                    $controller->addMessage( sprintf( $message, $aliasAddress ), OSS_Message::ERROR );
                    return false;
                }

                // prevent deletion of an automatic alias' goto alias
                $alias = $this->getAlias( $controller, $item . '@' . $domain );
                if( $alias !== null && $alias['goto'] === $aliasAddress ) {
                    $message = _( 'Alias %s is used to fullfill automatic alias %s. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                        . 'If you want to delete it, update the alias to use a different goto address first.' );
                    $controller->addMessage( sprintf( $message, $aliasAddress, $alias['address'] ), OSS_Message::ERROR );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether an alias is an automatic alias or an automatic alias'
     * goto alias, and prevents disabling the alias
     *
     * @param AliasController $controller
     * @param array|null $options
     * @return bool
     */
    public function alias_toggleActive_preToggle( AliasController $controller, $options )
    {
        $domain = $controller->getDomain()->getDomain();
        $aliasAddress = $controller->getAlias()->getAddress();

        if( $options['active'] && $this->defaultAliases ) {
            // if we're about to disable a domain alias, ensure that distinct automatic aliases exist
            if( '@' . $domain === $aliasAddress ) {
                foreach( $this->defaultAliases as $item ) {
                    $alias = $this->getAlias( $controller, $item . '@' . $domain );
                    if( $alias === null || !$alias['active'] ) {
                        $message = _( 'Alias %s is used to fullfill automatic alias %s. '
                            . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                            . 'If you want to disable it, create a distinct alias first.' );
                        printf( $message, $aliasAddress, $item . '@' . $domain );
                        exit(0);
                    }
                }

                return true;
            }

            // no default aliases are required to exist if the whole domain is aliased
            if( $this->hasActiveDomainAlias( $controller ) ) {
                return true;
            }

            foreach( $this->defaultAliases as $item ) {
                // prevent toggling an automatic alias off
                if( $item . '@' . $domain === $aliasAddress ) {
                    $message = _( 'Alias %s is required and cannot be disabled. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>');
                    printf( $message, $aliasAddress );
                    exit(0);
                }

                // prevent toggling an automatic alias' goto alias off
                $alias = $this->getAlias( $controller, $item . '@' . $domain );
                if( $alias !== null && $alias['goto'] === $aliasAddress ) {
                    $message = _( 'Alias %s is used to fullfill automatic alias %s. '
                        . 'See <a href="https://www.ietf.org/rfc/rfc2142.txt" target="page">RFC2142</a>. '
                        . 'If you want to disable it, update the alias to use a different goto address first.' );
                    printf( $message, $aliasAddress, $alias['address'] );
                    exit(0);
                }
            }
        }

        return true;
    }

    /**
     * Returns an alias' entity
     *
     * @param OSS_Controller_Action $controller
     * @param string $alias
     * @return array|null
     */
    private function getAlias( OSS_Controller_Action $controller, $alias )
    {
        $aliasList = $controller->getD2EM()->getRepository( "\\Entities\\Alias" )
            ->filterForAliasList( $alias, $controller->getAdmin(), $controller->getDomain()->getId(), true );
        return $aliasList ? reset($aliasList) : null;
    }

    /**
     * Checks whether a domain alias exists and is active
     *
     * @param OSS_Controller_Action $controller
     * @return bool
     */
    private function hasActiveDomainAlias( OSS_Controller_Action $controller )
    {
        $alias = $this->getAlias( $controller, '@' . $controller->getDomain()->getDomain() );
        return $alias !== null && $alias['active'];
    }

    /**
     * Creates a new alias
     *
     * @param OSS_Controller_Action $controller
     * @param string $item
     * @param string $goto
     * @return \Entities\Alias|null
     */
    private function createAutomaticAlias( OSS_Controller_Action $controller, $item, $goto )
    {
        $domain = $controller->getDomain()->getDomain();

        if( isset( $this->defaultMapping[$item] ) ) {
            $goto = $this->defaultMapping[$item];
        } elseif( isset( $this->defaultMapping['*'] ) ) {
            $goto = $this->defaultMapping['*'];
        }

        if( strpos( $goto, '@' ) === false ) {
            $goto = $goto . '@' . $domain;
        } elseif( $goto[0] === '@' ) {
            $goto = $item . $goto;
        }

        $alias = new \Entities\Alias();
        $alias->setAddress( $item . '@' . $domain );
        $alias->setGoto( $goto );
        $alias->setDomain( $controller->getDomain() );
        $alias->setActive( 1 );
        $alias->setCreated( new \DateTime() );
        $controller->getD2EM()->persist( $alias );

        $controller->getDomain()->increaseAliasCount();
        $controller->getD2EM()->flush();

        return $alias;
    }

}
