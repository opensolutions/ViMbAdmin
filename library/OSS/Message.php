<?php

/**
 * OPEN SOURCE SOLUTIONS LIMITED - CONFIDENTIAL
 * ____________________________________________
 *
 * Copyright (c) 2009-2012 Open Source Solutions Limited
 * All rights reserved.
 *
 * This file is part of Open Source Solutions Limited's "ONLINE
 * PAYROLL MANAGEMENT APPLICATION".
 *
 * Information in this file is strictly confidential and the
 * property of Open Source Solutions Limited and may not be
 * extracted or distributed, in whole or in part, for any
 * purpose whatsoever, without the express written consent
 * from Open Source Solutions Limited.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 * Our registered office is 147 Stepaside Park, Stepaside,
 * Dublin 18, Ireland.
 *
 * Contact us via http://www.opensolutions.ie/
 *    or info@opensolutions.ie   or  +353 1 685 4220.
 */

/**
 * A class to encapsulate messages to be displayed on the webpages.
 * 
 * These are the main required elements for this:
 * 
 * 1. The OSS/Message.php class (this file)
 * 2. The OSS/Smarty/functions/function.OSS_Message.php (to display the message on the view)
 * 3. The relevent CSS classes in public/css/oss.css
 * 
 * To use this, add a message to the view as follows:
 * 
 * public function exampleAction() {
 *     $this->view->ossAddMessage( new OSS_Message( 'This is a info message!', OSS_Message::INFO ) );
 * }
 * 
 * Multiple messages can be added of different kinds (INFO, ALERT, etc).
 *
 * Then to display these messages in your view (Smarty template) just include the following
 * text (i.e. Smarty function):
 * 
 * {OSS_Message}
 * 
 */
 
 /**
 * OSS: Message
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @author Roland Huszti <roland@opensolutions.ie>
 * @author Nerijus Barauskas <nerijus@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Message
 * @copyright  Copyright (c) 2009 - 2012 Open Source Solutions Limited, Dublin, Ireland
 */
class OSS_Message
{

    const INFO    = 'info';
    const WARNING = 'warning';
    const ALERT   = 'warning';
    const SUCCESS = 'success';
    const ERROR   = 'error';

    const TYPE_MESSAGE = 0;
    const TYPE_BLOCK   = 1;
    const TYPE_POP_UP  = 2;
    
    
    /**
     * The type of OSS_Message
     *
     * @var int
     */
    protected $type = self::TYPE_MESSAGE;
    
    
    /**
     * A variable to hold the message (either scalar string or array of strings )
     *
     * @var mixed 
     */
    protected $message;

    /**
     * A variable to hold the appropriate HTML class (e.g. error, success, info)
     *
     * @var string
     */
    protected $class = '';

    /**
     * A variable to indicate whether the message is HTML or not
     *
     * @var bool
     */
    protected $isHTML = true;

    /**
     * The constructor
     *
     * @param string $request The message
     * @param string $response The HTML div class
     * @param bool $invokeArgs Is the message HTML? (default: true)
     * @return void
     */
    public function __construct( $message = '', $class = '', $isHTML = true )
    {
        $this->message = $message;
        $this->setClass( $class );
        $this->isHTML  = $isHTML;
    }


    /**
     * Get the message as plaintext - essentially strips the tags from the
     * message if it is an HTML message
     *
     * @return string
     */
    public function getPlaintext()
    {
        if( $this->isHTML )
            return( strip_tags( $this->message ) );
        else
            return( $this->message );
    }

    /**
     * Get the message
     *
     * @return string 
     */
    public function getMessage()
    {
        return( $this->message );
    }

    /**
     * Get the message type
     *
     * @return int
     */
    public function getType()
    {
        return( $this->type );
    }
    
    /**
     * Set the message type
     *
     * @param int $type Message type
     * @return void
     */
    public function setType( $type )
    {
        $this->type = $type;
    }
    
    /**
     * Get the class
     *
     * @return string the class
     */
    public function getClass()
    {
        return( $this->class );
    }

    /**
     * Set the class
     *
     * @param string $class the class
     */
    public function setClass( $class )
    {
        if( $class == self::ALERT )
            $class = self::WARNING;
            
        $this->class = $class;
    }
}


