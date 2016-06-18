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
    private $defaultMapping;

     public function __construct(OSS_Controller_Action $controller) {
         parent::__construct($controller, get_class() );

         // read config parameters
         $this->defaultAliases = $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultAliases'];
         $this->defaultMapping = $controller->getOptions()['vimbadmin_plugins']['MailboxAutomaticAliases']['defaultMapping'];
     }

     /**
      * Is called after a mailbox is created. It ensures that required aliases are created.
      *
      * @param $controller
      * @param $options
      */
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
                     if($this->defaultMapping[$item]) {
                        $alias->setGoto($this->defaultMapping[$item]);
                     } else {
                         $alias->setGoto($mailbox);
                     }
                     $alias->setDomain($controller->getDomain());
                     $alias->setActive(1);
                     $alias->setCreated(new \DateTime());
                     $controller->getD2EM()->persist($alias);
                     // Increase alias count for domain
                     $controller->getDomain()->increaseAliasCount();
                     $controller->getD2EM()->flush();
                     $controller->addMessage( sprintf(_("Auto-Created alias %s -> %s."), $alias->getAddress(), $alias->getGoto()));
                 }
             }
         }
     }

     /**
      * Check if the aliases is allowed to be removed. If not return false, else return true.
      *
      * @param $controller
      * @param $options
      * @return bool
      */
     public function alias_delete_preRemove($controller, $options) {
         // get alias that should be deleted
         $alias = $controller->getAlias()->getAddress();
         $domain = $controller->getDomain()->getDomain();

         // check if the alias to delete is not enforced by the plugin
         if($this->defaultAliases) {
             foreach($this->defaultAliases as $key => $item) {
                 if($alias == $item.'@'.$domain) {
                     // not allowed to delete, show error message and stop delete
                     $controller->addMessage( sprintf( _("Alias %s is required and cannot be deleted. See <a href=\"https://www.ietf.org/rfc/rfc2142.txt\" target=\"page\">RFC2142</a>"), $alias), OSS_Message::ERROR);
                     return false;
                 }
             }
         }
         return true;
     }

     public function alias_toggleActive_preToggle($controller, $options) {
         // get alias that should be deleted
         $alias = $controller->getAlias()->getAddress();
         $domain = $controller->getDomain()->getDomain();

         if($options['active'] == 'true') {
             // we have to check if it is allowed to disable this alias
             if($this->defaultAliases) {
                 foreach($this->defaultAliases as $key => $item) {
                     if($alias == $item.'@'.$domain) {
                         // not allowed to delete, show error message and stop delete
                         print( sprintf( _("Alias %s is required and cannot be disabled. See <a href=\"https://www.ietf.org/rfc/rfc2142.txt\" target=\"page\">RFC2142</a>"), $alias));
                         exit(0);
                     }
                 }
             }

         }
        return true;
     }

     /**
      * Is called after an alias is created. It ensures that required aliases are created.
      *
      * @param $controller
      * @param $options
      */
     public function alias_add_addPreflush($controller, $options) {
         // get domain
         $domainId = $controller->getDomain()->getId();
         $domain = $controller->getDomain()->getDomain();

         // get alias
         $aliasGoto = $controller->getalias()->getGoto();

         // check if domain has enforced aliases or do we have to create them?
         if($this->defaultAliases) {
             foreach($this->defaultAliases as $key => $item) {
                 $aliasList = $controller->getD2EM()->getRepository( "\\Entities\\Alias" )->filterForAliasList( $item . '@' . $domain, $controller->getAdmin(), $domainId, true );
                 if(count($aliasList) == 0) {
                     $alias = new \Entities\Alias();
                     $alias->setAddress($item.'@'.$domain);
                     if($this->defaultMapping[$item]) {
                         $alias->setGoto($this->defaultMapping[$item]);
                     }else{
                         $alias->setGoto($aliasGoto);
                     }
                     $alias->setDomain($controller->getDomain());
                     $alias->setActive(1);
                     $alias->setCreated(new \DateTime());
                     $controller->getD2EM()->persist($alias);
                     // Increase alias count for domain
                     $controller->getDomain()->increaseAliasCount();
                     $controller->getD2EM()->flush();
                     $controller->addMessage( sprintf(_("Auto-Created alias %s -> %s."), $alias->getAddress(), $alias->getGoto()));
                 }
             }
         }
     }
 }