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
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action - Trait for Freshbooks
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Messages
{
    
    /**
     * The trait's initialisation method.
     *
     * This function is called from the Action's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function OSS_Controller_Action_Trait_Messages_Init( $request, $response, $invokeArgs )
    {
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Messages' );
    }
    

    /**
     * Adds a message to the session for display on the rendered page.
     *
     * Works with _redirect()
     *
     * @param string $message The message text
     * @param int $class the message class, OSS_Message::INFO|ALERT|SUCCESS|ERROR|...
     * @param int $type The message type from OSS_Message::TYPE_MESSAGE|BLOCK
     * @return void
     */
    public function addMessage( $message, $class = OSS_Message::SUCCESS, $type = OSS_Message::TYPE_MESSAGE )
    {
        $msg = null;
    
        switch( $type )
        {
            case OSS_Message::TYPE_BLOCK:
                $msg = new OSS_Message_Block( $message, $class );
                break;
    
            case OSS_Message::TYPE_POP_UP:
                $msg = new OSS_Message_Pop_Up( $message, $class );
                break;
    
            default:
                $msg = new OSS_Message( $message, $class );
        }
    
        $this->_session->OSS_Messages[] = $msg;
        return $msg;
    }
    
    
    /**
     * Adds messages to the session.
     *
     * @see addMessage
     * @param string $pMessagesArray the array of messages
     * @param string $pClass the message class, OSS_Message::INFO|ALERT|SUCCESS|ERROR|...
     * @return void
     */
    public function addMessages( $messages, $class, $type = OSS_Message::TYPE_MESSAGE )
    {
        if( !is_array( $messages ) )
            $messages = array( $messages );
    
        foreach( $messages as $msg )
            $this->addMessage( $msg, $class, $type );
    }
    
    
    
}

