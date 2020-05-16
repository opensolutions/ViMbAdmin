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
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{
    protected $_session;

    /**
     * Holds the Logger instance
     *
     * @var null|OSS_Log
     */
    protected $_logger;


    /**
     * Initialisation function
     * 
     * @return OSS_Log
     */
    public function init()
    {
        // Return logger so bootstrap will store it in the registry
        return $this->getLogger();
    }


    /**
     * get Logger
     * 
     * @return OSS_Log
     */
    public function getLogger()
    {
        if( null === $this->_logger )
        {
            // Get Doctrine configuration options from the application.ini file
            $options = $this->getOptions();

            $logger = new OSS_Log();

            if( isset( $options['enabled'] ) && $options['enabled'] )
            {
                foreach( $options['writers'] as $writer => $writerOptions )
                {
                    switch( $writer )
                    {
                        case 'stream':
                            if( isset( $writerOptions['mode'] ) && $writerOptions['mode'] = 'single' )
                            {
                                $log_path = $writerOptions['path'];
                                $log_file = $log_path . DIRECTORY_SEPARATOR . ( isset( $writerOptions['logname'] ) ? $writerOptions['logname'] : 'log.log' );
                            }
                            else
                            {
                                $log_path = $writerOptions['path']
                                            . DIRECTORY_SEPARATOR .  date( 'Y' )
                                            . DIRECTORY_SEPARATOR . date( 'm' );

                                $log_file = $log_path . DIRECTORY_SEPARATOR . date( 'Ymd') . '.log';
                            }

                            if( file_exists( $log_path ) == false )
                            {
                                mkdir(  $log_path, 0755, true              );
                                @chmod( $log_path, 0755                    );
                                @chown( $log_path, $writerOptions['owner'] );
                                @chgrp( $log_path, $writerOptions['group'] );
                            }

                            if( file_exists( $log_file ) == false )
                            {
                                touch(  $log_file                          );
                                @chmod( $log_file, 0777                    );
                                @chown( $log_file, $writerOptions['owner'] );
                                @chgrp( $log_file, $writerOptions['group'] );
                            }

                            $streamWriter = new Zend_Log_Writer_Stream( $log_file );
                            $streamWriter->setFormatter(
                                new Zend_Log_Formatter_Simple(
                                    '%timestamp% %priorityName% (%priority%) ' . (isset($_SERVER['REMOTE_ADDR']) == true ? "[{$_SERVER['REMOTE_ADDR']}]" : "") . ': %message%' . PHP_EOL
                                )
                            );

                            $logger->addWriter( $streamWriter );

                            if ( isset($writerOptions['level']) ) $logger->addFilter( (int)$writerOptions['level'] );

                            break;

                        case 'email':
                            $mail = new Zend_Mail();
                            $mail->setFrom( $writerOptions['from'] )
                                 ->addTo( $writerOptions['to'] );

                            $mailWriter = new Zend_Log_Writer_Mail( $mail );

                            // Set subject text for use; summary of number of errors is appended to the
                            // subject line before sending the message.
                            $mailWriter->setSubjectPrependText( "[{$writerOptions['prefix']}]" );

                            // Only email entries with level requested and higher.
                            $mailWriter->addFilter( (int)$writerOptions['level'] );

                            $logger->addWriter( $mailWriter );
                            break;

                        case 'firebug':
                            if( $writerOptions['enabled'] )
                            {
                                $firebugWriter = new Zend_Log_Writer_Firebug();
                                $firebugWriter->addFilter( (int)$writerOptions['level'] );
                                $logger->addWriter( $firebugWriter );
                            }
                            break;

                        default:
                            try {
                                $logger->log( "Unknown log writer: {$writer}", Zend_Log::WARN );
                            } catch( Zend_Log_Exception $e ) {
                                die( "Unknown log writer [{$writer}] during application bootstrap" );
                            }
                            break;
                    }
                }

            }
            else
            {
                $logger->addWriter( new Zend_Log_Writer_Null() );
            }

            try
            {
                $logger->debug( 'Logger instantiated', Zend_Log::INFO );
            }
            catch( Zend_Log_Exception $e )
            {
                die( "Unknown log writer [{$writer}] during application bootstrap" );
            }

            $this->_logger = $logger;
        }

        return $this->_logger;
    }

}
