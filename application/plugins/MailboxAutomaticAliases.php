<?php

/**
 * @copyright Copyright (c) 2014 Matthias Fechner
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Matthias Fechner <matthias _at_ fechner.net>
 */

/**
 * The Mailbox Automatic Aliases Plugin
 * 
 * The plugin ensures that a required set of aliases for a domain are existent.
 * Required aliases are:
 *   postmaster@domain.tld
 *   abuse@domain.tld
 * Optional aliases are:
 *   webmaster@domain.tld
 *   hostmaster@domains.tld
 *   
 * See https://github.com/idefix6/vimbadmin-mailbox-automatic-aliases
 *
 * Add the following lines to configs/application.ini:
 * vimbadmin_plugins.MailboxAutomaticAliases.disabled = false
 * vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "postmaster"
 * vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "abuse"
 * vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "hostmaster"
 * vimbadmin_plugins.MailboxAutomaticAliases.defaultAliases[] = "webmaster"
 *
 * @package ViMbAdmin
 * @subpackage Plugins
 */
 class ViMbAdminPlugin_MailboxAutomaticAliases extends ViMbAdmin_Plugin implements OSS_Plugin_Observer {
    private $defaultAliases;
     
     public function __construct(OSS_Controller_Action $controller) {
         parent::__construct($controller, get_class() );

         // read config parameters
         $this->defaultAliases = $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultAliases'];
     }
     
     public function mailbox_add_addPostflush($controller, $options) {
         // get domain
         $domainId = $controller->getDomain()->getId();
         $domain = $controller->getDomain()->getDomain();

         // get mailbox
         $mailbox = $controller->getMailbox()->getUsername();

         // check if domain has enforced aliases or do we have to create them?
         if($this->defaultAliases) {
             foreach($this->defaultAliases as $key => $item) {
                 $aliasList = $controller->getD2EM()->getRepository( "\\Entities\\Alias" )->filterForAliasList( $item . '@' . $domain, $controller->getAdmin(), $domainId, true );
                 if(count($aliasList) == 0) {
                     $alias = new \Entities\Alias();
                     $alias->setAddress($item.'@'.$domain);
                     $alias->setGoto($mailbox);
                     $alias->setDomain($controller->getDomain());
                     $alias->setActive(1);
                     $alias->setCreated(new \DateTime());
                     $controller->getD2EM()->persist($alias);
                     // Increase alias count for domain
                     $controller->getDomain()->increaseAliasCount();
                     $controller->getD2EM()->flush();
                     $controller->addMessage( sprintf(_("Auto-Created alias %s@%s -> %s."), $item, $domain, $mailbox));
                 }
             }
         }
     }
 }