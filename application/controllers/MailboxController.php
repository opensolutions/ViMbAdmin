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
 * @subpackage Controllers`
 */
class MailboxController extends ViMbAdmin_Controller_PluginAction
{

    /**
     * Local store for the form
     * @var ViMbAdmin_Form_Mailbox_AddEdit
     */
    private $mailboxForm = null;

    /**
     * Most actions in this object will require a domain object to edit / act on.
     *
     * This method will look for an 'id' parameter and, if set, will
     * try to load the domain model and authorise the user to edit / act on
     * it.
     *
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        if( $this->getRequest()->getActionName() != 'cli-get-sizes' 
                && $this->getRequest()->getActionName() != 'cli-delete-pending' 
                && !$this->getMailbox() && !$this->getDomain() )
            $this->authorise();

        if( $this->getRequest()->getActionName() == "list-search" ||$this->getRequest()->getActionName() == "list" || $this->getRequest()->getActionName() == "index" )
        {
            if( $this->getParam( 'unset', false ) )
                unset( $this->getSessionNamespace()->domain );
            else
            {
                if( isset( $this->getSessionNamespace()->domain ) && $this->getSessionNamespace()->domain )
                    $this->_domain = $this->getSessionNamespace()->domain;
                else if( $this->getDomain() )
                    $this->getSessionNamespace()->domain = $this->getDomain();
            }
        }
    }


    /**
     * Jumps to list action.
     */
    public function indexAction()
    {
        $this->forward( 'list' );
    }


    /**
     * Lists all mailboxes available to the admin (superadmin sees all) or to the specified domain.
     *
     * $this->view->mailbox_actions allow to append mailbox list action buttons. %id% will be replaced by
     * mailbox id form the list. below is example array which creates edit mailbox button, and another button
     * with drop down options for purge or edit mailbox. Only one drop down button can be defined per button group, 
     * and it always be appended at the end.
     * $actions = [
     *       [                          //Simple link button
     *           'tagName' => 'a',     //Mandatory parameter for element type. 
     *           'href' => OSS_Utils::genUrl( "mailbox", "edit" ) . "/mid/%id%", //Url for action
     *           'title' => "Edit",
     *           'class' => "btn btn-mini have-tooltip",  //Class for css options.
     *           'id' => "test-%id%",      //If setting id id must have %id% which will be replaced by original mailbox id to avoid same ids.
     *           'child' => [             //Mandatory element if is not array it will be shown as text.
     *               'tagName' => "i",    //Mandatory option if child is array to define element type
     *               'class' => "icon-pencil"  //Icon class
     *           ],
     *       ],
     *       [                              //Drop down button
     *           'tagName' => 'span',       //Mandatory parameter for element type
     *           'title' => "Settings",
     *           'class' => "btn btn-mini have-tooltip dropdown-toggle", //Class dropdown-toggle is mandatory for drop down button
     *           'data-toggle' => "dropdown",          //data-toggle attribute is mandatory for drop down button
     *           'id' => "cog-%id%",
     *           'style' => "max-height: 15px;",
     *           'child' => [
     *               'tagName' => "i",
     *               'class' => "icon-cog"
     *           ],
     *           'menu' => [        //menu array is mandatory then defining drop down button
     *               [ 
     *                   'id' => "menu-edit-%id%",   //Not mandatory attribute but if is set %id% should be use to avoid same ids.
     *                   'text' => "<i class=\"icon-pencil\"></i> Edit",                 //Mandatory for display action text
     *                   'url' =>  OSS_Utils::genUrl( "mailbox", "edit" ) . "/mid/%id%"  //Mandatory to redirect the action.
     *               ],
     *               [ 'text' => "<i class=\"icon-trash\"></i> Purge", 'url' =>  OSS_Utils::genUrl( "mailbox", "purge" ) . "/mid/%id%" ],
     *           ]
     *       ]
     *   ];
     * 
     */
    public function listAction()
    {
        if( isset( $this->_options['defaults']['server_side']['pagination']['enable'] ) && $this->_options['defaults']['server_side']['pagination']['enable'] )
            $this->view->mailboxes = [];
        else
            $this->view->mailboxes = $this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->loadForMailboxList( $this->getAdmin(), $this->getDomain() );
        
        if( isset( $this->_options['defaults']['list_size']['disabled'] ) && !$this->_options['defaults']['list_size']['disabled'] )
        {
            if( isset( $this->_options['defaults']['list_size']['multiplier'] ) && isset( OSS_Filter_FileSize::$SIZE_MULTIPLIERS[ $this->_options['defaults']['list_size']['multiplier'] ] ) )
                $size_multiplier = $this->_options['defaults']['list_size']['multiplier'];
            else
                $size_multiplier = OSS_Filter_FileSize::SIZE_KILOBYTES;

            $this->view->size_multiplier = $size_multiplier;
            $this->view->multiplier      = OSS_Filter_FileSize::$SIZE_MULTIPLIERS[ $size_multiplier ];
        }
        $this->notify( 'mailbox', 'list', 'listPostProcess', $this );
    }

    public function listSearchAction()
    {
        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
        if( !isset( $this->_options['defaults']['server_side']['pagination']['enable'] ) || !$this->_options['defaults']['server_side']['pagination']['enable'] )
            echo "ko";
        else
        {
            $strl_len = isset( $this->_options['defaults']['server_side']['pagination']['min_search_str'] ) ? $this->_options['defaults']['server_side']['pagination']['min_search_str'] : 3;
            $search = $this->_getParam( "search", false );
            if( $search && strlen( $search ) >= $strl_len )
            {
                $mboxes = $this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->filterForMailboxList( $search, $this->getAdmin(), $this->getDomain() );
                $max_cnt = isset( $this->_options['defaults']['server_side']['pagination']['max_result_cnt'] ) ? $this->_options['defaults']['server_side']['pagination']['max_result_cnt'] : false;
                if( $mboxes && ( !$max_cnt || $max_cnt >= count( $mboxes ) ) )
                    echo json_encode( $mboxes );
                else
                    echo "ko";
            }
            else
                echo "ko";    
        }
    }

    /**
     * Instantiate / get the mailbox add-edit form
     * @return ViMbAdmin_Form_Mailbox_AddEdit
     */
    public function getMailboxForm()
    {
        if( $this->mailboxForm == null )
        {
            $form = new ViMbAdmin_Form_Mailbox_AddEdit();

            if( isset( $this->_options['defaults']['quota']['multiplier'] ) )
                $form->setFilterFileSizeMultiplier( $this->_options['defaults']['quota']['multiplier'] );

            if( isset( $this->_options['defaults']['mailbox']['min_password_length'] ) )
                $form->setMinPasswordLength( $this->_options['defaults']['mailbox']['min_password_length'] );

            // populate the domain dropdown with the possible domains for this user
            $form->getElement( "domain" )->setMultiOptions(
                [ "" => "" ] + $this->getD2EM()->getRepository( "\\Entities\\Domain" )->loadForAdminAsArray( $this->getAdmin(), true )
            );

            // if we have a default / preferred domain, set it as selected in the form
            if( $this->getDomain() )
                $form->getElement( 'domain' )->setValue( $this->getDomain()->getId() );


            if( $this->isEdit() )
            {
                $form->removeElement( 'password' );
                $form->removeElement( 'local_part' );
                $form->removeElement( 'domain'     );
            }
            else
                $form->getElement( "quota"  )->setValue( 0 );

            $this->view->form = $this->mailboxForm = $form;

            // call plugins
            $this->notify( 'mailbox', 'add', 'formPostProcess', $this );
        }

        return $this->mailboxForm;
    }

    /**
     * Add a mailbox.
     */
    public function addAction()
    {
        if( !$this->getMailbox() )
        {
            $this->isEdit = $this->view->isEdit = false;
            $form = $this->getMailboxForm();

            $this->_mailbox = new \Entities\Mailbox();
            $this->getD2EM()->persist( $this->getMailbox() );
        }
        else
        {
            $this->isEdit = $this->view->isEdit = true;
            $this->view->mailbox = $this->getMailbox();

            $form = $this->getMailboxForm();
            $form->assignEntityToForm( $this->getMailbox(), $this, $this->isEdit() );
        }

        $this->view->quota_multiplier = $form->getFilterFileSizeMultiplier();

        if( $this->getDomain() )
        {
            $this->view->domain = $this->getDomain();
            if( $form->getElement( 'domain' ) )
                $form->getElement( 'domain' )->setValue( $this->getDomain()->getId() );
        }

        $this->notify( 'mailbox', 'add', 'addPrepare', $this );

        if( $this->getRequest()->isPost() )
        {
            $this->notify( 'mailbox', 'add', 'addPrevalidate', $this );

            if( $form->isValid( $_POST ) )
            {
                $this->notify( 'mailbox', 'add', 'addPostvalidate', $this );

                if( !$this->isEdit() )
                {
                    // do we have available mailboxes?
                    if( !$this->getAdmin()->isSuper() && $this->getDomain->getMaxMailoboxes() != 0 && $this->getDomain()->getMailboxCount() >= $this->getDomain()->getMaxMailboxes() )
                    {
                        $this->addMessage( _( 'You have used all of your allocated mailboxes.' ), OSS_Message::ERROR );
                        return;
                    }

                    if( !$this->getDomain() || $this->getDomain()->getId() != $form->getElement( 'domain' )->getValue() )
                        $this->_domain = $this->loadDomain( $form->getElement( 'domain' )->getValue() );

                    // is the mailbox address valid?
                    $username = sprintf( "%s@%s", $form->getValue( 'local_part' ), $this->_domain->getDomain() );
                    if( !Zend_Validate::is( $username, 'EmailAddress', array( 1, null ) ) )
                    {
                        $form->getElement( 'local_part' )->addError( 'Invalid email address.' );
                        return;
                    }

                    // does a mailbox of the same name exist?
                    if( !$this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->isUnique( $username ) )
                    {
                        $this->addMessage(
                            _( 'Mailbox already exists for' ) . " {$username}",
                            OSS_Message::ERROR
                        );
                        return;
                    }

                    $this->getMailbox()->setUsername( $username );

                    $form->assignFormToEntity( $this->getMailbox(), $this, $this->isEdit() );

                    $this->getMailbox()->setDomain( $this->getDomain() );
                    $this->getMailbox()->setHomedir( $this->_options['defaults']['mailbox']['homedir'] );
                    $this->getMailbox()->setUid( $this->_options['defaults']['mailbox']['uid'] );
                    $this->getMailbox()->setGid( $this->_options['defaults']['mailbox']['gid'] );
                    $this->getMailbox()->formatHomedir( $this->_options['defaults']['mailbox']['homedir'] );
                    $this->getMailbox()->formatMaildir( $this->_options['defaults']['mailbox']['maildir'] );
                    $this->getMailbox()->setActive( 1 );
                    $this->getMailbox()->setDeletePending( false );
                    $this->getMailbox()->setCreated( new \DateTime () );

                    $password = $this->getMailbox()->getPassword();
                    $this->getMailbox()->setPassword(
                         OSS_Auth_Password::hash(
                            $password,
                            [ 
                                'pwhash' => $this->_options['defaults']['mailbox']['password_scheme'],
                                'pwsalt' => isset( $this->_options['defaults']['mailbox']['password_salt'] )
                                                ? $this->_options['defaults']['mailbox']['password_salt'] : null, 
                                'pwdovecot' => isset( $this->_options['defaults']['mailbox']['dovecot_pw_binary'] )
                                                ? $this->_options['defaults']['mailbox']['dovecot_pw_binary'] : null, 
                                'username' => $username
                            ]
                        )
                    );

                    if( $this->_options['mailboxAliases'] == 1 )
                    {
                        $alias = new \Entities\Alias();
                        $alias->setAddress( $this->getMailbox()->getUsername() );
                        $alias->setGoto( $this->getMailbox()->getUsername() );
                        $alias->setDomain( $this->getDomain() );
                        $alias->setActive( 1 );
                        $alias->setCreated( new \DateTime () );
                        $this->getD2EM()->persist( $alias );
                    }

                    $this->getDomain()->setMailboxCount( $this->getDomain()->getMailboxCount() + 1 );

                }
                else
                {
                    $form->removeElement( "local_part" );
                    $form->assignFormToEntity( $this->getMailbox(), $this, $this->isEdit() );
                    $this->getMailbox()->setModified( new \DateTime() );
                }

                //check quota
                if( $this->getDomain()->getMaxQuota() != 0 )
                {
                    if( $this->getMailbox()->getQuota() <= 0 || $this->getMailbox()->getQuota() > $this->getDomain()->getMaxQuota() )
                    {
                        $this->getMailbox()->setQuota( $this->getDomain()->getQuota() );
                        $this->addMessage(
                            _( "Mailbox quota set to ") . $this->getDomain()->getQuota(),
                            OSS_Message::ALERT
                        );
                    }
                }

                $this->log(
                    $this->isEdit() ? \Entities\Log::ACTION_MAILBOX_EDIT : \Entities\Log::ACTION_MAILBOX_ADD,
                    "{$this->getAdmin()->getFormattedName()} " . ( $this->isEdit() ? ' edited' : ' added' ) . " mailbox {$this->getMailbox()->getUsername()}"
                );

                $this->notify( 'mailbox', 'add', 'addPreflush', $this );
                $this->getD2EM()->flush();
                $this->notify( 'mailbox', 'add', 'addPostflush', $this, [ 'options' => $this->_options ] );

                if( $form->getValue( 'welcome_email' ) )
                {
                    if( !$this->_sendSettingsEmail(
                            ( $form->getValue( 'cc_welcome_email' ) ? $form->getValue( 'cc_welcome_email' ) : false ),
                            $password, true )
                    )
                        $this->addMessage( _( 'Could not sent welcome email' ), OSS_Message::ALERT );
                }

                if( $this->getParam( "did", false ) )
                    $this->getSessionNamespace()->domain = $this->getDomain();

                $this->notify( 'mailbox', 'add', 'addFinish', $this );
                $this->addMessage( _( "You have successfully added/edited the mailbox record." ), OSS_Message::SUCCESS );
                $this->redirect( 'mailbox/list' );
            }
        }

    }

    /**
     * Edit a mailbox.
     */
    public function editAction()
    {
        $this->forward( 'add' );
    }

    /**
     * Toggles the active property of the current mailbox. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->getMailbox() )
            print 'ko';

        $this->getMailbox()->setActive( !$this->getMailbox()->getActive() );
        $this->getMailbox()->setModified( new \DateTime() );

        $this->log(
            $this->getMailbox()->getActive() ? \Entities\Log::ACTION_MAILBOX_ACTIVATE : \Entities\Log::ACTION_MAILBOX_DEACTIVATE,
            "{$this->getAdmin()->getFormattedName()} " . ( $this->getMailbox()->getActive() ? 'activated' : 'deactivated' ) . " mailbox {$this->getMailbox()->getUsername()}"
        );

        $this->notify( 'mailbox', 'toggleActive', 'postflush', $this, [ 'active' => $this->getMailbox()->getActive() ] );
        $this->getD2EM()->flush();
        $this->notify( 'mailbox', 'toggleActive', 'postflush', $this, [ 'active' => $this->getMailbox()->getActive() ] );
        print 'ok';
    }


    /**
     * Purges a mailbox from the system, with all the related entries in other tables.
     */
    public function purgeAction()
    {
        if( !$this->getMailbox() )
            return $this->forward( 'list' );

        $this->view->mailbox = $this->getMailbox();
        $this->view->aliases = $aliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForMailbox( $this->getMailbox(), $this->getAdmin() );
        $this->view->inAliases = $inAliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadWithMailbox( $this->getMailbox(), $this->getAdmin() );

        if( isset( $_POST['purge'] ) && ( $_POST['purge'] == 'purge' ) )
        {
            
            $this->notify( 'mailbox', 'purge', 'preRemove', $this );

            $this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->purgeMailbox( $this->getMailbox(), $this->getAdmin(), !$this->getParam( 'delete_files', false ) );
            $this->log(
                \Entities\Log::ACTION_MAILBOX_PURGE,
                "{$this->getAdmin()->getFormattedName()} purged mailbox {$this->getMailbox()->getUsername()}"
            );
            
            $this->notify( 'mailbox', 'purge', 'preFlush', $this );

            if( $this->getParam( 'delete_files', false ) )
            {
                $this->getMailbox()->setDeletePending( true );
                $this->getMailbox()->setActive( false );
            }
            else
                $this->getD2EM()->remove( $this->getMailbox() );

            $this->getD2EM()->flush();
            $this->notify( 'mailbox', 'purge', 'postFlush', $this );

            $this->addMessage( _( 'You have successfully purged the mailbox.' ), OSS_Message::SUCCESS );
            $this->_redirect( 'mailbox/list' );

        }
    }


    /**
     * Lists the aliases of a mailbox, except the default alias (where address == goto).
     */
    public function aliasesAction()
    {
        if( !$this->getMailbox() )
            return $this->forward( 'list' );

        //include maiblox aliases
        $this->view->ima = $ima = $this->getParam( 'ima', 0 );
        $this->view->mailbox = $this->getMailbox();

        $aliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForMailbox( $this->getMailbox(), $this->getAdmin(), $ima );
        $inAliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadWithMailbox( $this->getMailbox(), $this->getAdmin() );

        $this->view->aliases = array_merge( $aliases, $inAliases );
    }


    /**
     * Deletes a mailbox alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function deleteAliasAction()
    {
        if( $this->getMailbox()->getUsername() == $this->getAlias()->getGoto() )
        {
            $this->getD2EM()->remove( $this->getAlias() );

            $this->log(
                \Entities\Log::ACTION_ALIAS_DELETE,
                "{$this->getAdmin()->getFormattedName()} removed alias {$this->getAlias()->getAddress()}"
            );
            $this->getDomain()->setAliasCount( $this->getDomain()->getAliasCount() -1 );
            $this->addMessage( "You have successfully removed the alias." , OSS_Message::SUCCESS );
        }
        else
        {
            $gotos = explode( ',', $this->getAlias()->getGoto() );

            if( count( $gotos ) > 0 )
            {
                foreach( $gotos as $key => $item )
                {
                    $gotos[ $key ] = $item = trim( $item );

                    if( ( $item == $this->getMailbox()->getUsername() ) || ( $item == '' ) )
                        unset( $gotos[ $key ] );
                }
                $this->log(
                    \Entities\Log::ACTION_ALIAS_DELETE,
                    "{$this->getAdmin()->getFormattedName()} removed destination {$this->getMailbox()->getUsername()} from alias {$this->getAlias()->getAddress()}"
                );

                $this->getAlias()->setGoto( implode( ',', $gotos ) );
                $this->addMessage( "You have successfully removed {$this->getMailbox()->getUsername()}from the alias {$this->getAlias()->getAddress()}." , OSS_Message::SUCCESS );
            }
            else
            {
                $this->getD2EM()->remove( $this->getAlias() );
                $this->log(
                    \Entities\Log::ACTION_ALIAS_DELETE,
                    "{$this->getAdmin()->getFormattedName()} removed alias {$this->getAlias()->getAddress()}"
                );
                $this->getDomain()->setAliasCount( $this->getDomain()->getAliasCount() -1 );
                $this->addMessage( "You have successfully removed the alias." , OSS_Message::SUCCESS );
            }
        }

        $this->getD2EM()->flush();
        $this->redirect( "mailbox/aliases/mid/" . $this->getMailbox()->getId() );
    }

    public function emailSettingsAction()
    {
        $form = $this->view->form = new ViMbAdmin_Form_Mailbox_EmailSettings();
        $mailbox = $this->getMailbox( $this->getParam( "mid", false ), false );
        
        if( !$mailbox )
        {
            $this->addMessage( _( 'Unable to load mailbox.' ), OSS_Message::ERROR );
            echo 'error';
            die();
        }
        $this->view->mailbox = $mailbox;
        
        $emails = [ 'username' => $mailbox->getUsername() ];
        if( $mailbox->getAltEmail() )
            $emails[ 'alt_email'] = $mailbox->getAltEmail();
            
        $emails['other']  = "Other";        
        $form->getElement( 'type' )->setMultiOptions( $emails );
        
        if( $this->getRequest()->isPost() && $this->getParam( 'send', false ))
        {
            $form->isValid( $_POST );
            if( $form->getValue( 'type' ) == 'other' )
                $form->getElement( 'email' )->setRequired( true );
            
            $error = false;    
            if( $form->isValid( $_POST ) )
            {
                if( $form->getValue( 'type' ) == 'other' )
                {
                    $emails = explode( ",", $form->getValue( 'email' ) );
                    $email = [];
                    foreach( $emails as $em )
                    {
                        if( !Zend_Validate::is( $em, 'EmailAddress', array( 1, null ) ) )
                        {
                            $form->getElement( 'email' )->addError( "Not valid email address(es)" );
                            $error = true;
                            break;
                        }
                        else
                            $email[] = trim( $em );                            
                    }
                }
                else if( $form->getValue( 'type' ) == 'username' )
                    $email = $mailbox->getUsername();
                else if( $form->getValue( 'type' ) == 'alt_email' )
                    $email = $mailbox->getAltEmail();
                else
                {
                    $this->getLogger()->err( "Unknown email type." );
                    echo "Unknown email type.";
                    die();
                }
                
                if( !$error )
                {
                    if( $this->_sendSettingsEmail( false, '', false, $email ) )
                        $this->addMessage( _( 'Settings email successfully sent' ), OSS_Message::SUCCESS );
                    else
                        $this->addMessage( _( 'Could not send settings email' ), OSS_Message::ERROR );
                    
                    print "ok";
                    die();
                }
            }
        }
    }

    public function cliGetSizesAction()
    {
        if( !isset( $this->_options['defaults']['list_size']['disabled'] ) || $this->_options['defaults']['list_size']['disabled'] )
        {
            $this->getLogger()->info( "MailboxController::cliGetSizesAction: List size option is disabled in application.ini." );
            echo "List size option is disabled in application.ini.\n";
            return;
        }
       
        foreach( $this->getD2EM()->getRepository( "\\Entities\\Domain" )->findAll() as $domain )
        {
            $cnt = 0;
            
            if( $this->getParam( 'verbose' ) )
                echo "Processing {$domain->getDomain()}...\n";
                
            foreach( $domain->getMailboxes() as $mailbox )
            {
                if( $this->getParam( 'debug' ) ) echo "    - {$mailbox->getUsername()}";
                
                $msize = OSS_DiskUtils::du( $mailbox->getCleanedMaildir() );
                if( $msize !== false )
                {
                    if( $this->getParam( 'debug' ) ) echo " [Mail Size: {$msize}]";
                    $mailbox->setMaildirSize( $msize );
                    $hsize = OSS_DiskUtils::du( $mailbox->getHomedir() );
                    if( $hsize !== false )
                    {
                        $mailbox->setHomedirSize( $hsize - $msize );
                        if( $this->getParam( 'debug' ) ) echo " [Home Size: {$hsize}]";
                    }
                    else
                        if( $this->getParam( 'debug' ) ) echo " [Unknown Home Size]";
                }
                else
                {
                    $mailbox->setMaildirSize( null );
                    $mailbox->setHomedirSize( null );
                    if( $this->getParam( 'debug' ) ) echo " [Unknown Mail Size]";
                }

                $mailbox->setSizeAt( new \DateTime() );
                
                if( $this->getParam( 'debug' ) ) echo "\n";
                
                $cnt++;
                if( $cnt % 200 == 0)
                    $this->getD2EM()->flush();
                    
            } // mailboxes
            
            $this->getD2EM()->flush();
            
        } // domains
    }

    public function cliDeletePendingAction()
    {
        $mailboxes = $this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->pendingDelete();

        if( !count( $mailboxes ) )
        {
            if( $this->getParam( 'verbose' ) ) echo "No mailboxes pending deleteion\n";
            return;
        }

        if( $this->getParam( 'verbose' ) ) echo "Deleting " . count( $mailboxes ) . " mailboxes:\n";

        foreach( $mailboxes as $mailbox )
        {
            if( $this->getParam( 'verbose' ) ) echo " - " . $mailbox->getUsername() . "... ";
            
            $homedir = $mailbox->getHomedir();
            $maildir = $mailbox->getCleanedMaildir();

            if( !isset( $this->_options['binary']['path']['rm_rf'] ) )
            {
                echo "ERROR: Deleting mailboxes - you must set 'binary.path.rm_rf' in application.ini\n";
                continue;
            }

            foreach( [ $maildir, $homedir ] as $dir )
            {
                $command = sprintf( "%s %s", $this->_options['binary']['path']['rm_rf'], $dir );
                if( file_exists( $dir ) )
                {
                    exec( $command, $output, $result );
                    if( $result !== 0 )
                        echo "ERROR: Could not delete $dir when deleting mailbox " . $mailbox->getUsername() . "\n";
                }
            }
          
            $this->getD2EM()->remove( $mailbox );  
            $this->getD2EM()->flush();
            if( $this->getParam( 'verbose' ) ) echo "DONE\n";
        } 
    }

    /**
     * Sends email with settings
     *
     * Send Email with settings for $this->_mailbox.
     * If cc is set When additional email is set, then it sends additional emails to cc.
     * If password is set, then password is shown in email.
     * if isWelcome is set to true, adding welcome subject and welcome text to email.
     *
     * @param bool $cc Additional email.
     * @param string $password Password to send for mailbox owner
     * @param bool $isWelcome Defines email is welcome email or not.
     * @return bool
     */
    private function _sendSettingsEmail( $cc = false, $password = '', $isWelcome = false, $email = false )
    {
        $mailer = $this->getMailer();
        
        if( $isWelcome )
            $mailer->setSubject( sprintf( _( "Welcome to your new mailbox on %s" ), $this->getMailbox()->getDomain()->getDomain() ) );
        else
            $mailer->setSubject( sprintf( _( "Settings for your mailbox on %s" ), $this->getMailbox()->getDomain()->getDomain() ) );

        $mailer->setFrom( $this->_options['server']['email']['address'], $this->_options['server']['email']['name'] );
        
        if( !$email )
            $mailer->addTo( $this->getMailbox()->getUsername(), $this->getMailbox()->getName() );
        else
            $mailer->addTo( $email );
        
        if( $cc ) $mailer->addCc( $cc );

        $this->view->mailbox  = $this->getMailbox();
        $this->view->welcome  = $isWelcome;
        $this->view->password = $password;

        $settings = $this->_options['server'];

        foreach( $settings as $tech => $params )
            foreach( $params as $k => $v )
                $settings[$tech][$k] = \Entities\Mailbox::substitute( $this->getMailbox()->getUsername(), $v );

        $this->view->settings = $settings;

        $this->notify( 'mailbox', 'sendSettingsEmail', 'preSetBody', $this );
        $mailer->setBodyText( $this->view->render( 'mailbox/email/settings.phtml' ) );

        try {
            $mailer->send();
        } catch( Exception $e ) {
            return false;
        }

        return true;
    }

    /**
     * Action FOR ADMINS AND SUPERADMINS to change the password of a mailbox.
     */
    public function passwordAction()
    {
        if( !$this->getMailbox() )
        {
            $this->addMessage( _( 'No mailbox id passed.' ), OSS_Message::ERROR );
            $this->redirect( 'list' );
        }

        $this->view->mailbox = $this->_mailbox;

        $this->view->form = $form = new ViMbAdmin_Form_Admin_Password();
        if( isset( $this->_options['defaults']['mailbox']['min_password_length'] ) )
            $form->setMinPasswordLength( $this->_options['defaults']['mailbox']['min_password_length'] );

        if( $this->getRequest()->isPost() && $form->isValid( $_POST ) )
        {
            $this->notify( 'mailbox', 'password', 'postValidation', $this );

            $this->getMailbox()->setPassword(
                OSS_Auth_Password::hash(
                    $form->getValue( 'password' ),
                    [ 
                        'pwhash' => $this->_options['defaults']['mailbox']['password_scheme'],
                        'pwsalt' => isset( $this->_options['defaults']['mailbox']['password_salt'] )
                                        ? $this->_options['defaults']['mailbox']['password_salt'] : null, 
                        'pwdovecot' => isset( $this->_options['defaults']['mailbox']['dovecot_pw_binary'] )
                                        ? $this->_options['defaults']['mailbox']['dovecot_pw_binary'] : null,
                        'username' => $this->getMailbox()->getUsername()
                    ]
                )
            );

            $this->log(
                \Entities\Log::ACTION_MAILBOX_PW_CHANGE,
                "{$this->getAdmin()->getFormattedName()} changed password for mailbox {$this->getMailbox()->getUsername()}"
            );


            $this->notify( 'mailbox', 'password', 'preFlush', $this );
            $this->getD2EM()->flush();
            $this->notify( 'mailbox', 'password', 'postFlush', $this, [ 'options' => $this->_options ] );

            if( $form->getValue( 'email' ) )
            {
                $mailer = $this->getMailer();
                $mailer->setSubject( _( 'New Password for ' . $this->getMailbox()->getUsername() ) );
                $mailer->setFrom( $this->_options['server']['email']['address'], $this->_options['server']['email']['name'] );
                $mailer->addTo( $this->getMailbox()->getUsername(), $this->getMailbox()->getName() );

                $this->view->admin = $this->getAdmin();
                $this->view->newPassword = $form->getValue( 'password' );
                $mailer->setBodyText( $this->view->render( 'mailbox/email/change_password.phtml' ) );

                try
                {
                    $mailer->send();
                }
                catch( Zend_Mail_Exception $vException )
                {
                    $this->getLogger()->debug( $vException->getTraceAsString() );
                    $this->addMessage( _( 'Could not send email.' ), OSS_Message::ALERT );
                    $this->_redirect( 'mailbox/list' );
                }
            }

            $this->addMessage( _( "Password has been sucessfully changed." ), OSS_Message::SUCCESS );
            $this->_redirect( 'mailbox/list' );
        }
    }

}
