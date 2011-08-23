<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 Open Source Solutions Limited
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
 * @copyright Copyright (c) 2011 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/*
 * @package ViMbAdmin
 * @subpackage Library
 */

/**
 * A class to encapsulate messages to be displayed on the webpages.
 *
 * These are the main required elements for this:
 *
 * 1. The ViMbAdmin/Message.php class (this file)
 * 2. The ViMbAdmin/Smarty/functions/function.ViMbAdmin_Message.php (to display the message on the view)
 *
 * To use this, add a message to the view as follows:
 *
 * public function exampleAction() {
 *     $this->view->vimbadminAddMessage( new ViMbAdmin_Message( 'This is a info message!', ViMbAdmin_Message::INFO ) );
 * }
 *
 * Multiple messages can be added of different kinds (INFO, ALERT, etc).
 *
 * Then to display these messages in your view (Smarty template) just include the following
 * text (i.e. Smarty function):
 *
 * {ViMbAdmin_Message}
 *
 */
class ViMbAdmin_Message
{

    const INFO    = 'info';
    const ALERT   = 'alert';
    const SUCCESS = 'success';
    const ERROR   = 'error';

    /**
     * A variable to hold the message (either scalar string or array of strings
     *
     * @var mixed The message
     */
    protected $message;

    /**
     * A variable to hold the appropriate HTML class (e.g. error, success, info)
     *
     * @var string The appropriate HTML class (e.g. error, success, info)
     */
    protected $class = '';

    /**
     * A variable to indicate whether the message is HTML or not
     *
     * @var boolean Is the message in HTML?
     */
    protected $isHTML = true;


    /**
     * The constructor
     *
     * @param string $request The message
     * @param string $response The HTML div class
     * @param boolean $invokeArgs Is the message HTML? (default: true)
     */
    public function __construct( $message = '', $class = '', $isHTML = true )
    {
        $this->message = $message;
        $this->class   = $class;
        $this->isHTML  = $isHTML;
    }


    /**
     * Get the message as plaintext - essentially strips the tags from the
     * message if it is an HTML message
     *
     * @return string The message after strip_tags()
     */
    public function getPlaintext()
    {
        if( $this->isHTML )
            return strip_tags( $this->message );
        else
            return $this->message;
    }


    /**
     * Get the message
     *
     * @return string The message
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Get the class
     *
     * @return string the class
     */
    public function getClass()
    {
        return $this->class;
    }

}
