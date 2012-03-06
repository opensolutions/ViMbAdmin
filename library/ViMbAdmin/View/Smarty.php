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
 * The Smarty interface for Zend_View.
 *
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_View_Smarty extends Zend_View_Abstract
{
    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Is being cloned?
     *
     * see the comments in clearVars() for an explanation
     */
    protected $_isBeingCloned = false;

    /**
     * Constructor
     *
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct( $tmplPath = null, $extraParams = array() )
    {
        $this->_smarty = new Smarty();

        if( null !== $tmplPath )
            $this->setScriptPath( $tmplPath );

        foreach( $extraParams as $key => $value )
            $this->_smarty->$key = $value;
    }


    /**
    * This is needed for {@link clearVars()} to work properly. It automatically runs (PHP 5) after the "clone" operator did it's job. More descriptions at {@link clearVars()} .
    *
    * @param void
    * @return void
    */
    public function __clone()
    {
        // see the comments in clearVars() for an explanation
        $this->_isBeingCloned = true;
    }


    /**
     * Return the template engine object
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    /**
     * Set the path to the templates
     *
     * @param string $path The directory to set as the path.
     * @return void
     */
    public function setScriptPath($path)
    {
        if (is_readable($path)) {
            $this->_smarty->template_dir = $path;
            return;
        }

        throw new Exception( _( 'Invalid path provided' ) );
    }

    /**
     * Retrieve the current template directory
     *
     * @return string
     */
    public function getScriptPaths()
    {
        return $this->_smarty->template_dir;
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function setBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Assign a variable to the template
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        $this->_smarty->assignByRef($key, $val);
    }

    /**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return (null !== $this->_smarty->getTemplateVars($key));
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }

    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or
     * array of key => value pairs)
     * @param mixed $value (Optional) If assigning a named variable,
     * use this as the value.
     * @return void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }

        $this->_smarty->assign($spec, $value);
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or property
     * overloading ({@link __get()}/{@link __set()}).
     *
     * Both Zend_View_Helper_Action::cloneView() and Zend_View_Helper_Partial::cloneView()
     * executes a "$view->clearVars();" line after a "$view = clone $this->view;" . Because
     * of how the "clone" operator works internally (object references are also copied, so a
     * clone of this object will point to the same Smarty object instance as this, the
     * "$view->clearVars();" unsets all the Smarty template variables. To solve this,
     * there is the {@link __clone()} method in this class which is called by the "clone"
     * operator just after it did it's cloning job.
     *
     * This sets a flag ($this->_isBeingCloned) for use below to avoid clearing the template
     * variables in the cloned object.
     *
     * If for any reason this doesn't work, neither after amending {@link __clone()}, an
     * other "solution" is in the method, but commented out. That will also work, but it is
     * relatively slow and, not nice at all. That takes a look on it's backtrace, and if
     * finds a function name "cloneView" then does NOT execute Smarty's clearAllAssign().
     *
     * Or just make this an empty function if neither the above works.
     *
     * @param void
     * @return void
     */
    public function clearVars()
    {
        //if (in_array('cloneView', OSS_Utils::filterFieldFromResult(OSS_Debug::compact_debug_backtrace(), 'function', false, false)) == false) $this->_smarty->clear_all_assign();
        if( !$this->_isBeingCloned )
            $this->_smarty->clearAllAssign();
        else
            $this->_isBeingCloned = false;
    }

    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render($name)
    {
        return $this->_smarty->fetch($name);
    }

    /**
     * Processes a template and sends the output.
     *
     * @param string $name The template to process.
     * @return void
     */
    public function display( $name )
    {
        return $this->_smarty->display($name);
    }

    /**
     * Checks to see if the named template exists
     *
     * @param string $name The template to look for
     * @return boolean
     */
    public function templateExists( $name )
    {
        return $this->_smarty->templateExists( $name );
    }


    protected function _run()
    {
        include func_get_arg(0);
    }


    /**
     * Add a new message to the stack
     *
     * @param ViMbAdmin_Message An instance of the ViMbAdmin_Message class
     * @return boolean
     */
    public function vimbadminAddMessage( vimbadmin_Message &$message )
    {
        $ViMbAdmin_Messages = $this->_smarty->getTemplateVars( 'ViMbAdmin_Messages' );

        if ( $ViMbAdmin_Messages == null )
            $ViMbAdmin_Messages = array();

        $ViMbAdmin_Messages[] = $message;
        $this->_smarty->assign( 'ViMbAdmin_Messages', $ViMbAdmin_Messages );

        return true;
    }

    public function getTemplateDir()                                                                                                                                                        
    {                                                                                                                                                                                       
        $a = $this->_smarty->getTemplateDir();                                                                                                                                              
        return $a[0];                                                                                                                                                                       
    }

}
