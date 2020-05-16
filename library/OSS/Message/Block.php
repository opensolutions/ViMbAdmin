<?php

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
 * @package    OSS_Message
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * OSS_Message block type class for displaying messages for users.
 *
 * @category   OSS
 * @package    OSS_Message
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Message_Block extends OSS_Message
{

    /**
     * Elements for the action area
     * 
     * @var null|array
     */
    private $actions = null;
    
    
    /**
     * Constructor
     *
     * @param string $message Message to display
     * @param string $class  Message class
     * @param bool $isHTML Htmk flag
     * @return void
     */
    public function __construct( $message = '', $class = '', $isHTML = true )
    {
        parent::__construct( $message, $class, $isHTML );
        $this->setType( self::TYPE_BLOCK );
    }
    
    /**
     * Adding message box
     *
     * @param string $str Action description
     * @return void
     */
    public function addAction( $str )
    {
        if( $this->actions === null )
            $this->actions = array();
            
        $this->actions[] = $str;
    }
    
    /**
     * Getting messages
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
}


