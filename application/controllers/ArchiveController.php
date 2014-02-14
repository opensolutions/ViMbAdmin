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
class ArchiveController extends ViMbAdmin_Controller_PluginAction
{
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
        if( $this->getRequest()->getActionName() != 'cli-restore-pendings' && 
            $this->getRequest()->getActionName() != 'cli-delete-pendings' &&
            $this->getRequest()->getActionName() != 'cli-archive-pendings' && 
            !$this->getDomain()
        )
            $this->authorise();

        if( $this->getRequest()->getActionName() == "list" || $this->getRequest()->getActionName() == "index" )
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
     * Lists all archives available to the admin (superadmin sees all) or to the specified domain.
     */
    public function listAction()
    {
        $this->view->archives = $this->getD2EM()->getRepository( "\\Entities\\Archive" )->loadForArchiveList( $this->getAdmin(), $this->getDomain() );
        $this->view->statuses = \Entities\Archive::$ARCHIVE_STATUS;
        $this->view->allowCancel = [ \Entities\Archive::STATUS_PENDING_ARCHIVE, \Entities\Archive::STATUS_PENDING_RESTORE, \Entities\Archive::STATUS_PENDING_DELETE ];
        $this->view->allowDelete = [ \Entities\Archive::STATUS_ARCHIVED ];
        $this->view->allowRestore = [ \Entities\Archive::STATUS_ARCHIVED ];
    }

    /**
     * Creates archive entry and purges a mailbox from the system, with all the related entries in other tables.
     * After add status archive status is archive pending
     */
    public function addAction()
    {
        if( !$this->getMailbox() )
            return $this->forward( 'list' );

        $this->view->mailbox   = $this->getMailbox();
        $this->view->aliases   = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForMailbox( $this->getMailbox(), $this->getAdmin() );
        $this->view->inAliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadWithMailbox( $this->getMailbox(), $this->getAdmin() );

        if( isset( $_POST['archive'] ) && ( $_POST['archive'] == 'archive' ) )
        {
            $this->notify( 'archive', 'add', 'preSerialize', $this ); 

            $data = $this->_serialzeMailbox( $this->getMailbox() );

            $archive = new \Entities\Archive();
            $archive->setArchivedBy( $this->getAdmin() );
            $archive->setArchivedAt( new \DateTime() );
            $archive->setDomain( $this->getMailbox()->getDomain() );
            $archive->setUsername( $this->getMailbox()->getUsername() );
            $archive->setStatus( \Entities\Archive::STATUS_PENDING_ARCHIVE );
            $archive->setStatusChangedAt( new \DateTime() );
            $archive->setData( $data );
            $this->getD2EM()->persist( $archive );
            
            try{
                $this->notify( 'archive', 'add', 'preFlushAdd', $this ); 
                $this->getD2EM()->flush();
                $this->notify( 'archive', 'add', 'postFlushAdd', $this ); 
            }
            catch( Exception $e )
            {
                $this->getLogger()->err( "ArchiveController::addAction() : " . $e->getMessage() );
                $this->addMessage( _( 'This mailbox was not marked for archival.' ), OSS_Message::ERROR );
                $this->redirect( 'mailbox/list' );
            }

            $this->notify( 'archive', 'add', 'prePurge', $this ); 
            $this->getD2EM()->getRepository( "\\Entities\\Mailbox" )->purgeMailbox( $this->getMailbox(), $this->getAdmin() );

            $this->notify( 'archive', 'add', 'preFlushPurge', $this, [ 'mailbox' => $this->getMailbox() ] );
            $this->getD2EM()->flush();
            $this->notify( 'archive', 'add', 'postFlushPurge', $this, [ 'mailbox' => $this->getMailbox() ] );

            $this->addMessage( _( 'This mailbox has been marked for archival. You will be notified by email when this is complete.' ), OSS_Message::SUCCESS );
            $this->redirect( 'mailbox/list' );

        }
    }

    /**
     * Cancels archive pending status to previous.
     */
    public function cancelAction()
    {
        if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_PENDING_ARCHIVE )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_ARCHIVED, \Entities\Archive::STATUS_PENDING_ARCHIVE );
            if( $result )
            {
                try{
                    $data = $this->_unserialzeMailbox( $this->getArchive()->getData() );
                    $this->getD2EM()->flush();
                }
                catch( Exception $e )
                {
                    $this->addMessage( "Pending was was not canceled.", OSS_Message::ERROR );
                    $this->redirect( 'archive/list' );
                }

                $archive = $this->getD2EM()->getRepository( "\\Entities\\Archive" )->find( $this->getArchive()->getId() );
                $this->getD2EM()->remove( $archive );
                $this->notify( 'archive', 'cancel', 'preFlushRestore', $this );
                $this->getD2EM()->flush();
                $this->notify( 'archive', 'cancel', 'postFlushRestore', $this, $data );
                $this->addMessage( "Pending archive was canceled successfully.", OSS_Message::SUCCESS );   
                $this->notify( 'archive', 'cancel', 'mailboxRestored', $this, $data );
            }
            else
                $this->addMessage( "State was changed during canceling. Cancel action cannot be performed at this state.", OSS_Message::INFO );   
            
        }
        else if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_PENDING_RESTORE )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_ARCHIVED, \Entities\Archive::STATUS_PENDING_RESTORE );
            if( $result )
                $this->addMessage( "Pending restore was canceled successfully.", OSS_Message::SUCCESS );   
            else
                $this->addMessage( "State was changed during canceling. Cancel action cannot be performed at this state.", OSS_Message::INFO );   
        }
        else if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_PENDING_DELETE )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_ARCHIVED, \Entities\Archive::STATUS_PENDING_DELETE );
            if( $result )
            {
                if( !$this->getArchive()->getHomedirServer() )
                    $this->getArchive()->setStatus( \Entities\Archive::STATUS_PENDING_ARCHIVE );

                $this->addMessage( "Pending delete was canceled successfully.", OSS_Message::SUCCESS );    
            }    
            else
                $this->addMessage( "State was changed during canceling. Cancel action cannot be performed at this state.", OSS_Message::INFO );   
        }
        else 
            $this->addMessage( "Cancel action cannot be performed at this state.", OSS_Message::INFO );

        $this->getD2EM()->flush();
        $this->redirect( 'archive/list' );
    }

    /**
     * Sets archive status to delete pending
     */
    public function deleteAction()
    {
        if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_PENDING_ARCHIVE )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_PENDING_DELETE, \Entities\Archive::STATUS_PENDING_ARCHIVE );
            if( $result )
                $this->addMessage( "Archive status changed to delete pending.", OSS_Message::SUCCESS );         
            else
                $this->addMessage( "State was changed during deletion. Delete action cannot be performed at this state.", OSS_Message::INFO );   

        }
        else if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_ARCHIVED )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_PENDING_DELETE, \Entities\Archive::STATUS_ARCHIVED );
            if( $result )
                $this->addMessage( "Archive status changed to delete pending.", OSS_Message::SUCCESS );   
            else
                $this->addMessage( "State was changed during deletion. Delete action cannot be performed at this state.", OSS_Message::INFO );   
        }
        else
            $this->addMessage( "Restore action cannot be performed at this state.", OSS_Message::INFO );

        $this->getD2EM()->flush();
        $this->redirect( 'archive/list' );
    }

    /**
     * Sets archive status to restore pending
     */
    public function restoreAction()
    {
        if( $this->getArchive()->getStatus() == \Entities\Archive::STATUS_ARCHIVED )
        {
            $result = $this->_archiveStateChange( $this->getArchive(), \Entities\Archive::STATUS_PENDING_RESTORE, \Entities\Archive::STATUS_ARCHIVED );
            if( $result )
                $this->addMessage( "Archive status changed to restore pending.", OSS_Message::SUCCESS );         
            else
                $this->addMessage( "State was changed during restore. Restore action cannot be performed at this state.", OSS_Message::INFO );   
         }
        else
            $this->addMessage( "Restore action cannot be performed at this state.", OSS_Message::INFO );   

        $this->getD2EM()->flush();
        $this->redirect( 'archive/list' );
    }

    /**
     * Create archive files to archive entry and set state to archived
     */
    public function cliArchivePendingsAction()
    {
        $archives = $this->getD2EM()->getRepository( "\\Entities\\Archive" )->findBy( [ 'status' => \Entities\Archive::STATUS_PENDING_ARCHIVE ] );
        $uow = $this->getD2EM()->getUnitOfWork();

        if( !count( $archives ) && $this->getParam( 'verbose' ) )
        {
            echo "No pending archives found.\n";
            return;
        }
                
        $archivedir = $this->_options['archive']['path'];  
            
        if( !is_dir( $archivedir ) )
        {
            $this->getLogger()->notice( "ArchiveController::cliArchivePendingsAction - archive directory {$archivedir} does not exist!" );
            echo "ERROR: Archive directory {$archivedir} does not exist!\n";
            return;
        }
            
        foreach( $archives as $archive )
        {
            //Locking archive by setting it status to archiving
            $result = $this->_archiveStateChange( $archive, \Entities\Archive::STATUS_ARCHIVING, \Entities\Archive::STATUS_PENDING_ARCHIVE );
            if( !$result )
                continue;
                
            $data = unserialize( $archive->getData() );
            if( !isset( $data['mailbox']['params'] ) || !isset( $data['mailbox']['className'] ) )
               continue;
           
            if( $this->getParam( 'verbose' ) ) echo "\nArchiving {$data['mailbox']['params']['username']}... ";
             
            $mparams = $data['mailbox']['params'];
            $homedir = $mparams['homedir'];
            $maildir = \Entities\Mailbox::cleanMaildir( $mparams['maildir'] );
            $home_orig_size = $this->_checkSize( $homedir );
            if( $home_orig_size === false )
                $home_orig_size = 0;
            
            if( $homedir != $maildir && $home_orig_size != 0 )
            {
                $mail_orig_size = $this->_checkSize( $maildir );
                if( $mail_orig_size === false )
                    $mail_orig_size = 0;
            }
            else
                $mail_orig_size = 0;
                
            if( $home_orig_size == 0 )
            {
                $htar_name = false;
                $mtar_name = false;
                $mtar_size = 0;
                $htar_suze = 0;
            }
            else if( $homedir != $maildir )
            {
                $mtar_name = sprintf( "maildir-%d", $archive->getId() ); 
                $htar_name = sprintf( "homedir-%d", $archive->getId() );

                $mtar_size = $this->_tarDirectory( $archivedir, $mtar_name, $maildir );
                if( $mtar_size === false )
                    continue;
                
                $htar_size = $this->_tarDirectory( $archivedir, $htar_name, $homedir );
                if( $htar_size === false )
                    continue;;
            }
            else
            {
                $htar_name = sprintf( "homedir-%d", $archive->getId() );
                
                $htar_size = $this->_tarDirectory( $archivedir, $htar_name, $homedir );
                if( $htar_size === false )
                    continue;

                $mtar_name = false;
                $mtar_size = 0;
            }
            
            $archive->setHomedirServer( $this->_options['server_id'] );
            $archive->setMaildirServer( $this->_options['server_id'] );

            $archive->setHomedirOrigSize( $home_orig_size ); 
            if( $htar_name )
            {
                $archive->setHomedirFile( sprintf( "%s/%s.tar", $archivedir, $htar_name ) );
                $archive->setHomedirSize( $htar_size );
            }

            $archive->setMaildirOrigSize( $mail_orig_size );
            if( $mtar_name )
            {
                $archive->setMaildirSize( $mtar_size );
                $archive->setMaildirFile( sprintf( "%s/%s.tar", $archivedir, $mtar_name ) );
            }

            if( $this->getParam( 'verbose' ) )
            {
                echo " DONE\n";
                echo " - Original home directory size: {$home_orig_size}\n";
                echo " - Archived home directory size: {$htar_size}\n";
                if( $homedir != $maildir )
                {
                    echo " - Original mail directory size: {$mail_orig_size}\n";
                    echo " - Archived mail directory size: {$mtar_size}\n";
                }
            }
            
            $archive->setStatus( \Entities\Archive::STATUS_ARCHIVED );
            $archive->setStatusChangedAt( new \DateTime() );
            $this->getD2EM()->flush();
            $this->_notifyAdmin( $archive->getArchivedBy(), "archive/email/archive-ready.txt", "Mailbox archived", $data['mailbox']['params']['username'] );
            if( $this->getParam( 'verbose' ) ) echo "\n";
        }
    }

    /**
     * Delete archives with status pending delete. Removes archive entry and archives.
     */
    public function cliDeletePendingsAction()
    {
        $archives = $this->getD2EM()->getRepository( "\\Entities\\Archive" )->findBy( [ 'status' => \Entities\Archive::STATUS_PENDING_DELETE ] );
        foreach( $archives as $archive )
        {
            //Locking archive by setting it status to deleting
            $result = $this->_archiveStateChange( $archive, \Entities\Archive::STATUS_DELETING, \Entities\Archive::STATUS_PENDING_DELETE );
            if( !$result )
                continue;

            if( $archive->getHomedirFile() )
            {
                if( file_exists( $archive->getHomedirFile() . ".bz2" ) )
                    unlink( $archive->getHomedirFile() . ".bz2" );
                else if( file_exists( $archive->getHomedirFile() ) )
                    unlink( $archive->gethomedirFile() );
            }
            
            if( $archive->getMaildirFile() )
            {
                if( file_exists( $archive->getMaildirFile() . ".bz2" ) )
                    unlink( $archive->getMaildirFile() . ".bz2" );
                else if( file_exists( $archive->getMaildirFile() ) )
                    unlink( $archive->getMaildirFile() );
            }
            $this->notify( 'archive', 'deletePendings', 'preRemove', $this );
            $this->getD2EM()->remove( $archive );
            $this->notify( 'archive', 'deletePendings', 'postRemove', $this, [ 'username' => $archive->getUsername() ] );
        }
        $this->getD2EM()->flush();
    }

    /**
     * Restore archived files and unserialize mailbox. Removes archive entry
     */
    public function cliRestorePendingsAction()
    {
        $archives = $this->getD2EM()->getRepository( "\\Entities\\Archive" )->findBy( 
            [ 'status' => \Entities\Archive::STATUS_PENDING_RESTORE ] 
        );
        
        if( !count( $archives ) && $this->getParam( 'verbose' ) )
        {
            echo "No pending archives for restoration found.\n";
            return;
        }
        
        foreach( $archives as $archive )
        {
            //Locking archive by setting it status to restoring
            if( !$this->_archiveStateChange( $archive, \Entities\Archive::STATUS_RESTORING, \Entities\Archive::STATUS_PENDING_RESTORE ) )
                continue;
                
            $data = unserialize( $archive->getData() );

            if( !isset( $data['mailbox']['params'] ) || !isset( $data['mailbox']['className'] ) )
            {
                $this->getLogger()->alert( "Bad archive parameters for {$data['mailbox']['params']['username']}" );
                continue;
            }
            
            $mparams = $data['mailbox']['params'];
            $homedir = $mparams['homedir'];
            $maildir = \Entities\Mailbox::cleanMaildir( $mparams['maildir'] );
            
            if( $this->getParam( 'verbose' ) ) echo "\nRestoring archive for {$data['mailbox']['params']['username']}... ";
            
            if( $archive->getHomedirFile() )
            {          
                if( !$this->_untarDir( $archive->getHomedirFile(), $homedir ) )
                {
                    $this->getLogger()->notice( "ArchiveController::cliRestorePendingsAction - could not untar homedir for {$data['mailbox']['params']['username']}" );
                    continue;
                }
                unlink( $archive->getHomedirFile() );

                        
                if( $maildir != $homedir && !$this->_untarDir( $archive->getMaildirFile(), $maildir ) )
                {
                    $this->getLogger()->notice( "ArchiveController::cliRestorePendingsAction - could not untar maildir for {$data['mailbox']['params']['username']}" );
                    continue;
                }
                unlink( $archive->getMaildirFile() );
            }
           
            if( $this->getParam( 'verbose' ) ) echo "DONE\n";

            $data = $this->_unserialzeMailbox( $archive->getData() );

            $this->notify( 'archive', 'restorePendings', 'preFlushRestore', $this );
            $this->getD2EM()->flush();
            $this->notify( 'archive', 'restorePendings', 'postFlushRestore', $this, $data['mailbox'] );

            $this->_notifyAdmin( $archive->getArchivedBy(), "archive/email/archive-restored.txt", "Mailbox restored", 
                $data['mailbox']->getUsername() );

            $this->getD2EM()->remove( $this->getD2EM()->getRepository( "\\Entities\\Archive" )->find( $archive->getId() ) );
            $this->getD2EM()->flush();


            $this->notify( 'archive', 'restorePendings', 'restored', $this, $data['mailbox'] );

            if( $this->getParam( 'verbose' ) ) echo "\n";
        }
    }

    /**
     * Notifies administrator.
     * 
     * Send notification email for admin about archive created or restore state change.
     *
     * @param \Entities\Admin $admin     Admin to notify
     * @param string          $viescript Path to tar file
     * @param string          $subject   Destination file
     * @param string          $musername Archived / restored mailbox username
     * @return void
     */
    private function _notifyAdmin( $admin, $viewScript, $subject, $musername )
    {
        if( !$admin )
        {
            $this->getLogger()->debug( "ArchiveController: Admin was not found admin notification failed. " );
            return false;
        }

        $mailer = $this->getMailer();
        $mailer->setFrom( $this->_options['identity']['autobot']['email'], $this->_options['identity']['autobot']['name'] )
            ->addTo( $admin->getUsername(), "ViMbAdmin Administrator" )
            ->setSubject( $this->_options['identity']['sitename'] . " - " . $subject );

        $this->view->mailbox = $musername;
        $mailer->setBodyText( $this->view->render( $viewScript ) );
        $mailer->send();
    }

    /**
     * Untar directory 
     * 
     * Untars mailbox homedir or maildir.
     *
     * First function looks for bz2 file and if it finds it runs bunzip2 command to get tar file
     * if bz2 exist and bunzip2 fails it return false. Then it checks if tar file exist on 
     * provided path if not returns false. If exists calls _exctractTar function and if it fails 
     * returns false. If tar was extracted function chown privileges to default mailbox uid and gid.
     * Function returns true even if chown was not successfully.
     *
     * @param string $tarPath     Path to tar file
     * @param string $destinatiom Destination file
     * @return int|bool
     */
    private function _untarDir( $tarPath, $destination )
    {
        if( file_exists( $tarPath . ".bz2" ) )
        {
            $command = sprintf( "%s %s.bz2",  $this->_options['binary']['path']['bunzip2_q'], $tarPath );
            exec( $command, $output, $result );
            if( !$result === 0 )
            {
                $this->getLogger->debug( "bunzip2 failed path '{$tarPath}.bz2' not found" );
                return false;       
            }
        }
        
        if( !file_exists( $tarPath ) )
        {
            $this->getLogger->debug( "'{$tarPath}' was not found for extracting tar" );
            return false;
        }
        
        $result = $this->_exctractTar( $tarPath, $destination, $this->_options['defaults']['mailbox']['uid'], $this->_options['defaults']['mailbox']['gid'] );
        if( !$result )
            return false;

        return true;
    }
    

    /**
     * Extracts tar to destination 
     * 
     * Extracting tar file to given destination
     *
     * @param string $tarPath     Path to tar file
     * @param string $destination Destination file
     * @param int    $uid         User id to set owner.
     * @param int    $gid         Group id to set owner. 
     * @return bool
     */
    private function _exctractTar( $tarPath, $destination, $uid, $gid )
    {
        $rdest = substr( $destination, 0, strrpos( $destination, "/" ) );
                
        if( !is_dir( $rdest ) )
           $this-> _makedirOwned( $rdest, $uid, $gid );
    
        $command = sprintf( "%s %s -C %s", $this->_options['binary']['path']['tar_xf'], $tarPath, $rdest );
        exec( $command, $ouput, $result );
        
        $command = sprintf( "%s %d:%d %s", $this->_options['binary']['path']['chown_R'],
                         $uid, $gid, $destination
                    );
        exec( $command, $output, $result1 );
        if( $result1 !== 0 )
            $this->getLogger->debug( "chown command for '{$destiantion}' failed" );  

        if( $result === 0 && is_dir( $destination ) )
            return true;
        else
            return false;
    }
    
    /**
     * Make directory owned by $uid and $gid.
     *
     * It checks if previously directory exists if not it call _makedirOwned reucrsively.
     * if exists then returns. It creates directory wiht 0755 privileges and changes owner
     * to $uid:$gid.
     *
     * @params string $path Path to create folder
     * @param int     $uid  User id to set owner.
     * @param int     $gid  Group id to set owner. 
     * @return void
     */
    private function _makedirOwned( $path, $uid, $gid )
    {
        if( strrpos( $path, "/" ) <= 1 )
            return; 
        
        $rpath = substr( $path, 0, strrpos( $path, "/" ) );
            
        if( !is_dir( $rpath ) )
            $this->_makedirOwned( $rpath, $uid, $gid );
        
        mkdir( $path, 0755 );
        $command = sprintf( "%s %d:%d %s", $this->_options['binary']['path']['chown_R'],
                     $uid, $gid, $path
                );
        exec( $command, $output, $result );
        if( $result !== 0 )
            $this->getLogger->debug( "chown command for '{$path}' failed" );
            
    }

    /**
     * Creates tar for directory
     * 
     * Creates tar, removes directory and calls bzip2 on tar file.
     * Returns tar size before bzip2 call or false if tar command was not successful.
     *
     * @param string $archivedir Path where to store archive.
     * @param string $tarName    Name for tar file
     * @param string $pat        Path to folder for tar.
     * @return int|bool
     */
    private function _tarDirectory( $archivedir, $tarName, $path )
    {
        $size = $this->_createTar( $archivedir, $tarName, $path );
        if( $size == false )
            $this->getLogger()->err( "Cannot create tar for path '{$path}'" );
        
        $command = sprintf( "%s %s", $this->_options['binary']['path']['rm_rf'], $path );
        exec( $command, $output, $result );
        if( $result !== 0 )
            $this->getLogger()->debug( "Cannot remove '{$path}'" );

        $command = sprintf( "%s %s/%s.tar", $this->_options['binary']['path']['bzip2_q'], $archivedir, $tarName );
        exec( $command, $output, $result );
        if( $result !== 0 )
            $this->getLogger()->debug( "bzip2 failed for '{$path}'" );

        return $size;
    }
    
    /**
     * Creates tar file for given path.
     *
     * If $fullPath set to true than tar will be created with full directory tree, else
     * it will include only last folder fro given path. For example if path /usr/local/test/
     * and $fullPath is true tar will contain usr, local, test folders, else it will contain 
     * only test folder.
     *
     * Return tar size in bytes if command executed successfully, otherwise it return false.
     *
     * @param string $archiveDir Directory for putting archived tar files
     * @param string $tarName Name of tar file.
     * @param string $path Path to directory to tar.
     * @param bool   $fullPath Include full file tree in tar
     * @return int|bool
     */
    private function _createTar( $archiveDir, $tarName, $path, $fullPath = false )
    {     
        if( !is_dir( $archiveDir ) )
            mkdir( $archiveDir, 0755, true );

        if( $fullPath )
        {
            $command = sprintf( "%s %s/%s.tar %s 2>&1", 
                        $this->_options['binary']['path']['tar_cf'],
                        $archiveDir, $tarName,
                        $path
                );
        }
        else
        {
            $dir = substr( $path, strrpos( $path, "/" ) + 1 );
            $command = sprintf( "cd %s/../\n%s %s/%s.tar %s 2>&1", 
                        $path, $this->_options['binary']['path']['tar_cf'],
                        $archiveDir, $tarName, $dir
                );           
        }
        exec( $command, $ouput, $result );
                
        if( $result === 0 )
           return $this->_checkSize( sprintf( "%s/%s.tar", $archiveDir, $tarName ) );
        else
            return false;
    }
    
    /**
     * Check file or directory size.
     *
     * Function checks if given path is directory if it is then it runs du -sh command
     * result is filtered to bytes and function returns int. Else it checks if path is not 
     * file if it is it return filesize. If it was not directory or file function returns false.
     *
     * NOTICE: Directory size is sum of all subdirectories and file sizes inside recursively.
     *
     * @param string $path Path to file or directory for checking the file.
     * @return int|bool
     */
    private function _checkSize( $path )
    {
        if( is_dir( $path ) )
            return OSS_DiskUtils::du( $path );
        else if( file_exists( $path ) )
            return filesize( $path );
        else
            return false;
    }

    /**
     * Set archive state and return true or false.
     *
     * Executes DQL to update archive to new state but only if expected state is there.
     *
     * @param \Entities\Archive $archive Archive to change state
     * @param string $newState Ne state for archive
     * @param string $stateExcpected State which expected to be in archive when changing
     * @return bool
     */
    private function _archiveStateChange( $archive, $newState, $stateExcpected )
    {
        $query = $this->getD2EM()->createQuery(
            sprintf( "UPDATE \\Entities\\Archive a SET a.status = '%s', a.status_changed_at = '%s' WHERE a.status = '%s' AND a.id = %d",
                        $newState,
                        date( "Y-m-d H:i:s" ),
                        $stateExcpected,
                        $archive->getId()
                    )
            );
        
        return $query->getResult() != 1 ? false : true;
    }

    /**
     * Serialize mailbox and aliases related with mailbox entities 
     *
     * First it serialize mailbox, then mailbox preferences. Then it iterates through all aliases where 
     * goto equals to mailbox email and serialize them with their preferences. Then it iterates through
     * all aliases where goto field includes mailbox email and serialize them with their preferences.
     *
     * Returns serialized array which have structure like this:
     * [
     *   'mailbox' => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *   'mailbox_preferneces' => 
     *       [
     *           0 => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *           ...
     *           n => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *       ]
     *   'aliases' =>
     *       [
     *           0 => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *           ...
     *           n => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *       ]
     *   'aliases_prefrences'=> 
     *       [
     *           'alias_key' =>
     *               [
     *                   0 => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *                   ...
     *                   n => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *               ],
     *           'alias_key2' .....
     *       ]
     *   'inAliases' =>
     *       [
     *           0 => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *           ...
     *           n => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *       ]
     *   'inAliases_prefrences'=> 
     *       [
     *           'alias_key' =>
     *               [
     *                   0 => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *                   ...
     *                   n => [ 'classNme' => "entity class name", "params" => array( serialized mailbox array ) ],
     *               ],
     *           'alias_key2' .....
     *       ]
     * ]
     *
     * @param /Entities/Mailbox $mailbox Mailbox entity to serialize
     * @return string Serialize mailbox.
     */
    private function _serialzeMailbox( $mailbox )
    {
        $data = [];        
        $serializer = new OSS_Doctrine2_EntitySerializer( $this->getD2EM() );

        $data['mailbox'] = [ "className" => get_class( $mailbox ), "params" => $serializer->toArray( $mailbox ) ];

        foreach( $mailbox->getPreferences() as $pref )
            $data['mailbox_prefrences'][] = [ "className" => get_class( $pref ), "params" => $serializer->toArray( $pref ) ];

        /*
        
            The code to archive aliases is buggy right now so we've disabled this.

        $aliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForMailbox( $mailbox, $this->getAdmin(), true );
        foreach( $aliases as $key => $alias )
        {
            $data['aliases'][$key] = [ "className" => get_class( $alias ), "params" => $serializer->toArray( $alias ) ];
            foreach( $alias->getPreferences() as $pref )
                $data['alias_prefrences'][$key][] = [ "className" => get_class( $pref ), "params" => $serializer->toArray( $pref ) ];
        }

        $inAliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadWithMailbox( $mailbox, $this->getAdmin() );
        foreach( $inAliases as $key => $alias )
        {
            $data['inAliases'][$key] = [ "className" => get_class( $alias ), "params" => $serializer->toArray( $alias ) ];
            foreach( $alias->getPreferences() as $pref )
                $data['inAliases_prefrences'][$key][] = [ "className" => get_class( $pref ), "params" => $serializer->toArray( $pref ) ];

        }

        */
        return serialize( $data );
    }

    /**
     * Unserialize archive data serialized structure.
     *
     * Function unserialize data array. And by create mailbox object form parameters.
     * Then it creates mailbox preferences. Create aliases and their preferences, iterates
     * through inAliases and if aliases exists append their got field by mailbox email.
     * Then it persists mailbox and aliases set their domains, increase domain mailbox / alias counters.
     * Persist all preferences.
     *
     * Returns restored mailbox and aliases array. Array format:
     *
     * $return = [ 'mailbox' => $mailbox
     *       'aliases' => [
     *           1 => $alias1,
     *           2 => $alias2,
     *           .....
     *       ]
     *  ]
     *
     * @param string $data Serialized mailbox structure from archive data field.
     * @return array;
     */
    private function _unserialzeMailbox( $data )
    {
        $debug = $this->getParam( 'debug', false );
        $this->getD2EM()->flush();
        $data = unserialize( $data );

        if( $debug ) echo "[DEBUG] Unserialising {$data['mailbox']['params']['username']}\n";
        $params = $data['mailbox']['params'];

        $mailbox = new \Entities\Mailbox;
        $mailbox->setUsername( $params['username'] );
        $mailbox->setPassword( $params['password'] );
        $mailbox->setName( $params['name'] );
        $mailbox->setAltEmail( $params['alt_email'] );
        $mailbox->setQuota( $params['quota'] );
        $mailbox->setLocalPart( $params['local_part'] );
        $mailbox->setActive( $params['active'] );
        $mailbox->setAccessRestriction( $params['access_restriction'] );
        $mailbox->setHomeDir( $params['homedir'] );
        $mailbox->setMailDir( $params['maildir'] );
        $mailbox->setUid( $params['uid'] );
        $mailbox->setGid( $params['gid'] );
        $mailbox->setHomedirSize( $params['homedir_size'] );
        $mailbox->setMaildirSize( $params['maildir_size'] );
        $mailbox->setCreated( new \DateTime( $params['created']['date'] ) );
        $mailbox->setSizeAt( new \DateTime( $params['size_at']['date'] ) );
        $mailbox->setModified( new \DateTime() );

        if( !( $domain = $this->getD2EM()->getRepository( "\\Entities\\Domain" )->find( $params['domain']['id'] ) ) )
        {
            $this->getLogger()->alert( "ArchiveController::_unserialzeMailbox -> domain not found for {$data['mailbox']['params']['username']}" );
            throw new ViMbAdmin_Exception( "Domain was not found" );
        }

        $mailbox->setDomain( $domain );
        $this->getD2EM()->persist( $mailbox );        
        $this->getD2EM()->flush();

        if( isset( $data['mailbox_prefrences'] ) )
        { 
            if( $debug ) echo "[DEBUG] Unserialising preferences for {$data['mailbox']['params']['username']}\n";
            foreach( $data['mailbox_prefrences'] as $mprefs )
            {
                $pr = $this->getD2EM()->getUnitOfWork()->createEntity( $mprefs['className'], $mprefs['params'] );
                $pr->setMailbox( $mailbox );
                if( $debug ) echo "[DEBUG]        {$pr->getAttribute()} {$pr->getOp()} {$pr->getValue()}\n";
                $this->getD2EM()->persist( $pr );
                $this->getD2EM()->flush();
            }
        }

        if( isset( $this->_options['mailboxAliases'] ) && $this->_options['mailboxAliases'] )
        {
            $alias = new \Entities\Alias;
            $alias->setAddress( $mailbox->getUsername() );
            $alias->setGoto( $mailbox->getUsername() );
            $alias->setActive( 1 );
            $alias->setCreated( new \Datetime() );
            $alias->setDomain( $domain );
            $this->getD2EM()->persist( $alias );
            $this->getD2EM()->flush();
        }
                
        /*
        
            The code to archive aliases is buggy right now so we've disabled this.

        if( isset( $data["aliases"] ) )
        { 
            if( $debug ) echo "[DEBUG] Unserialising aliases for {$data['mailbox']['params']['username']}\n";
            foreach( $data["aliases"] as $idx => $dalias )
            {
                if( !isset( $dalias['params'] ) || !isset( $dalias['className'] ) )
                    continue;
                
                $uow->clear();
                $alias = $uow->createEntity( $dalias['className'], $dalias['params'] );
                $alias->setCreated( new \DateTime( $dalias['params']['created']['date'] ) );
                $alias->setModified( new \DateTime() );
                $domains[] = $dalias['params']['domain']['id'];
                $newEnts[] = $alias;

                if( $debug ) echo "[DEBUG]    {$alias->getAddress()} => {$alias->getGoto()}\n";

                if( isset( $data["alias_prefrences"][$idx] ) )
                {
                    foreach( $data["alias_prefrences"][$idx] as $ap )
                    {
                        if( !isset( $ap['params'] ) || !isset( $ap['className'] ) )
                            continue;
                        
                        $uow->clear();
                        $pr = $uow->createEntity( $ap['className'], $ap['params'] );
                        $pr->setAlias( $alias );
                        $newprefs[] = $pr;
                        if( $debug ) echo "[DEBUG]        {$pr->getAttribute()} {$pr->getOp()} {$pr->getValue()}\n";
                    }
                }
            }
        }

        if( isset( $data["inAliases"] ) )
        {
            if( $debug ) echo "[DEBUG] Unserialising 'in' aliases for {$data['mailbox']['params']['username']}\n";

            foreach( $data["inAliases"] as $idx => $dalias )
            {
                if( !isset( $dalias['params'] ) || !isset( $dalias['className'] ) )
                    continue;

                $params = $dalias['params'];
                $alias = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->find( $params['id'] );
                
                //FIXME: Now we skipping if alias was removed.
                if( $alias )
                {
                    $alias->setGoto( $alias->getGoto() . "," . $mailbox->getUsername() );
                    if( $debug ) echo "[DEBUG]    {$alias->getAddress()} => {$alias->getGoto()}\n";
                }
            }
        }
        */

        if( $debug ) echo "\n";
        return [ 'mailbox' => $mailbox, 'aliases' => [] ];
    }
}
