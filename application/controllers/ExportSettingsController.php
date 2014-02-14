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
 * @copyright Copyright (c) 2011 - 2014 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/**
 * The mailbox controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class ExportSettingsController extends ViMbAdmin_Controller_PluginAction
{

    public function thunderbirdAction()
    {   
        if( !isset( $this->_options['defaults']['export_settings']['disabled'] ) || $this->_options['defaults']['export_settings']['disabled'] )
            $this->redirect( "error/404" );
            
        if( isset( $this->_options['defaults']['export_settings']['allowed_subnet'] ) )
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            $valid = false;
            foreach( $this->_options['defaults']['export_settings']['allowed_subnet'] as $pattern )
            {
                if( substr( $ip, 0, strlen( $pattern ) ) == $pattern )
                {
                    $valid = true;
                    break;
                }
            }
            
            if( !$valid )
                $this->redirect( "error/404" );
        }
        else
        {
            // no seriously - you must restrict by IP address
            $this->redirect( "error/404" );   
        }
        
        if( !$this->getParam( 'email', false ) )
            throw new ViMbAdmin_Exception( "Unable to load mailbox. No mailbox id was given." );
        $this->_mailbox = $this->loadMailboxByUsername( $this->getParam( 'email' ), false, false );
        
        $urlUsername= urlencode( $this->getMailbox()->getUsername() );
        
        $data = "";
        $data .= "//Identity\n";
        $data .= "defaultPref(\"mail.identity.id1.overrideGlobal_Pref\", false);\n";
        $data .= "defaultPref(\"mail.identity.id1.fullName\", \"{$this->getMailbox()->getName()}\");\n";
        $data .= "defaultPref(\"mail.identity.id1.useremail\", \"{$this->getMailbox()->getUsername()}\");\n";
        $data .= "defaultPref(\"mail.identity.id1.use_custom_prefs\", true);\n";
        $data .= "defaultPref(\"mail.identity.id1.smtpServer\", \"smtp1\");\n";
        $data .= "defaultPref(\"mail.identity.id1.organization\", \"{$this->_options['identity']['orgname']}\");\n";
        $data .= "defaultPref(\"mail.identity.id1.doBcc\", false);\n";
        $data .= "defaultPref(\"mail.identity.id1.compose_html\", true);\n";
        if( $this->getMailbox()->getName() )
            $data .= "defaultPref(\"mail.identity.id1.htmlSigText\", \"Kind regards,\\n{$this->getMailbox()->getName()}\\n\\n{$this->_options['identity']['orgname']}\");\n";
        $data .= "defaultPref(\"mail.account.account1.identities\", \"id1\");\n";
        $data .= "defaultPref(\"mail.account.account1.server\", \"server1\");\n";
        $data .= "defaultPref(\"mail.account.account2.server\", \"server2\");\n";

        $data .= "defaultPref(\"mail.accountmanager.defaultaccount\", \"account1\");\n";
        $data .= "defaultPref(\"mail.accountmanager.localfoldersserver\", \"server2\");\n";    
        
        if( isset( $this->_options['server']['imap']['enabled'] ) && $this->_options['server']['imap']['enabled'] )
        {
            $host = preg_replace( "/%d/", $this->getMailbox()->getDomain()->getDomain(), $this->_options['server']['imap']['host'] );
            $data .= "defaultPref(\"mail.imap.min_chunk_size_threshold\", 712704);\n";
            $data .= "defaultPref(\"mail.last_msg_movecopy_was_move\", false);\n";
            $data .= "defaultPref(\"mail.openMessageBehavior.version\", 1);\n";
            $data .= "defaultPref(\"mail.preferences.advanced.selectedTabIndex\", 3);\n";
            $data .= "defaultPref(\"mail.preferences.security.selectedTabIndex\", 4);\n";
            $data .= "defaultPref(\"mail.rights.version\", 1);\n";
        
            $data .= "//IMAP Settings\n";
            $data .= "defaultPref(\"mail.server.server1.userName\", \"{$this->getMailbox()->getUsername()}\");\n";
            $data .= "defaultPref(\"mail.server.server1.port\", {$this->_options['server']['imap']['port']});\n";
            $data .= "defaultPref(\"mail.server.server1.socketType\", 3);\n";
            $data .= "defaultPref(\"mail.server.server1.hostname\", \"{$host}\");\n";
            $data .= "defaultPref(\"mail.server.server1.directory-rel\", \"[ProfD]ImapMail/{$host}\");\n";
            $data .= "defaultPref(\"mail.server.server1.name\", \"{$this->getMailbox()->getUsername()}\");\n";
            $data .= "defaultPref(\"mail.server.server1.spamActionTargetAccount\", \"imap://{$urlUsername}@{$host}\");\n";
            $data .= "defaultPref(\"mail.server.server1.spamActionTargetFolder\", \"mailbox://nobody@Local%20Folders/Junk\");\n";
            $data .= "defaultPref(\"mail.server.server1.type\", \"imap\");\n";
            $data .= "defaultPref(\"mail.server.server1.timeout\", 29);\n";
            
            $data .= "defaultPref(\"mail.server.server1.ageLimit\", 1);\n";
            $data .= "defaultPref(\"mail.server.server1.cacheCapa.acl\", false);\n";
            $data .= "defaultPref(\"mail.server.server1.cacheCapa.quota\", false);\n";
            $data .= "defaultPref(\"mail.server.server1.capability\", 101213733);\n";
            $data .= "defaultPref(\"mail.server.server1.login_at_startup\", true);\n";
            $data .= "defaultPref(\"mail.server.server1.storeContractID\", \"@mozilla.org/msgstore/berkeleystore;1\");\n";

            $data .= "defaultPref(\"mail.identity.id1.archive_folder\", \"imap://{$urlUsername}@{$host}/Archives\");\n";
            $data .= "defaultPref(\"mail.identity.id1.draft_folder\", \"imap://{$urlUsername}@{$host}/Drafts\");\n";
            $data .= "defaultPref(\"mail.identity.id1.drafts_folder_picker_mode\", \"0\");\n";
            $data .= "defaultPref(\"mail.identity.id1.fcc_folder\", \"imap://{$urlUsername}@{$host}/Sent\");\n";
            $data .= "defaultPref(\"mail.identity.id1.fcc_folder_picker_mode\", \"0\");\n";
            $data .= "defaultPref(\"mail.identity.id1.stationery_folder\", \"imap://{$urlUsername}@{$host}/Templates\");\n";
            $data .= "defaultPref(\"mail.identity.id1.tmpl_folder_picker_mode\", \"0\");\n";
     
        }

        $data .= "//Local mail folders\n";
        $data .= "defaultPref(\"mail.server.server2.directory-rel\", \"[ProfD]Mail/Local Folders\");\n";
        $data .= "defaultPref(\"mail.server.server2.hostname\", \"Local Folders\");\n";
        $data .= "defaultPref(\"mail.server.server2.name\", \"Local Folders\");\n";
        $data .= "defaultPref(\"mail.server.server2.spamActionTargetAccount\", \"mailbox://nobody@Local%20Folders\");\n";
        $data .= "defaultPref(\"mail.server.server2.storeContractID\", \"@mozilla.org/msgstore/berkeleystore;1\");\n";
        $data .= "defaultPref(\"mail.server.server2.type\", \"none\");\n";
        $data .= "defaultPref(\"mail.server.server2.userName\", \"nobody\");\n"; 
        
        if( isset( $this->_options['server']['smtp']['enabled'] ) && $this->_options['server']['smtp']['enabled'] )
        {
            $data .= "//SMTP Settings\n";
            $data .= "defaultPref(\"mail.smtpserver.smtp1.authMethod\", 3);\n";
            $data .= "defaultPref(\"mail.smtpserver.smtp1.description\", \"SMTP Mail\");\n";
            $host = preg_replace( "/%d/", $this->getMailbox()->getDomain()->getDomain(), $this->_options['server']['smtp']['host'] );
            $data .= "defaultPref(\"mail.smtpserver.smtp1.hostname\", \"{$host}\");\n";
            $data .= "defaultPref(\"mail.smtpserver.smtp1.port\", {$this->_options['server']['smtp']['port']});\n";
            $data .= "defaultPref(\"mail.smtpserver.smtp1.try_ssl\", 3);\n";
            $user = preg_replace( "/%m/", $this->getMailbox()->getUsername(), $this->_options['server']['smtp']['user'] );
            $data .= "defaultPref(\"mail.smtpserver.smtp1.username\", \"{$user}\");\n";   
        }
        
        $data .= "defaultPref(\"mail.smtpservers\", \"smtp1\");\n";
        $srvCnt = "2";
        $accCnt = "2";
        
        
        // FIXME Temporary for time constraints. Really needs a plugin.
        $data .= "\n\n//Sieve Out of Office\n";
        $host = preg_replace( "/%d/", $this->getMailbox()->getDomain()->getDomain(), $this->_options['server']['imap']['host'] );
        $oooAcc = urlencode( $this->getMailbox()->getUsername() ) . "@" . $host;
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.TLS\", true);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.activeHost\", 0);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.activeLogin\", 1);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.compile\", true);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.compile.delay\", 500);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.debug.flags\", 0);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.enabled\", true);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.keepalive\", true);\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.keepalive.interval\", \"1800000\");\n";
        $data .= "defaultPref(\"extensions.sieve.account.{$oooAcc}.port\", 4190);\n\n\n";
        
        
        $file =  sprintf( "%s/settings_%s.js", $this->_options['temporary_directory'], $this->getMailbox()->getId() );
        
        file_put_contents( $file, $data );
        
        $this->notify( 'export_settings', 'thunderbird', 'preSetSettings', $this, [ 'file' => $file, 'options' => $this->_options, 'server_count' => &$srvCnt, 'account_count' => &$accCnt ] );
        
        $accs = [];
        for( $acc = 1; $acc <= $accCnt; $acc++ )
            $accs[] = "account{$acc}";
        
        $accs = implode( ',' , $accs );         
        $data = "defaultPref(\"mail.accountmanager.accounts\", \"{$accs}\");\n";        
                  
        file_put_contents( $file, $data, FILE_APPEND );
        
        $content = file_get_contents( $file );
        unlink( $file );
        header('Content-type: text/javascript');
        die( $content );
        
    }
  
}
