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
 * @package    OSS_View
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_View
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_View_Smarty extends Zend_View_Abstract
{

    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Should we use a custom skin?
     * @var string
     */
    protected $_skin = false;


    /**
     * Is being cloned?
     *
     * @see OSS_View_Smarty::clearVars()
     * @var bool
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
    * @return void
    */
    public function __clone()
    {
        // see the comments in clearVars() for an explanation
        $this->_isBeingCloned = true;

        $tpl_vars = $this->_smarty->tpl_vars;
        $template_dir = $this->_smarty->template_dir;
        $compile_dir = $this->_smarty->compile_dir;
        $config_dir = $this->_smarty->config_dir;
        $plugins_dir = $this->_smarty->plugins_dir;

        $this->__construct();

        $this->_smarty->tpl_vars = $tpl_vars;
        $this->_smarty->template_dir = $template_dir;
        $this->_smarty->compile_dir = $compile_dir;
        $this->_smarty->config_dir = $config_dir;
        $this->_smarty->plugins_dir = $plugins_dir;
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
     * @throw Exception If path is not readable
     * @return void
     */
    public function setScriptPath( $path )
    {
        if( is_readable( $path ) )
        {
            $this->_smarty->template_dir = $path;
            return;
        }

        throw new Exception( 'Invalid path provided' );
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
    public function setBasePath( $path, $prefix = 'Zend_View' )
    {
        return $this->setScriptPath( $path );
    }


    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function addBasePath( $path, $prefix = 'Zend_View' )
    {
        return $this->setScriptPath( $path );
    }


    /**
     * Assign a variable to the template
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set( $key, $val )
    {
        $this->_smarty->assignByRef( $key, $val );
    }


    /**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get( $key )
    {
        return $this->_smarty->getTemplateVars( $key );
    }


    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return bool
     */
    public function __isset( $key )
    {
        return ( null !== $this->_smarty->getTemplateVars( $key ) );
    }


    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset( $key )
    {
        $this->_smarty->clearAssign( $key );
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
    public function assign( $spec, $value = null )
    {
        if( is_array( $spec ) )
        {
            $this->_smarty->assign( $spec );
            return;
        }

        $this->_smarty->assign( $spec, $value );
    }


    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string
     */
    public function render( $name )
    {
        return $this->_smarty->fetch( $this->resolveTemplate( $name ) );
    }

    
    /**
     * Processes a template and sends the output.
     *
     * @param string $name The template to process.
     * @return void
     */
    public function display( $name )
    {
        return $this->_smarty->display( $this->resolveTemplate( $name ) );
    }


    /**
     * Checks to see if the named template exists
     *
     * @param string $name The template to look for
     * @return bool
     */
    public function templateExists( $name )
    {
        return $this->_smarty->templateExists( $this->resolveTemplate( $name ) );
    }


    /**
     * Register a class for access to static methods / constants in a template. Especially
     * useful for classes outside the standard namespace.
     *
     * From the Smarty developers:
     *
     * > We have decided not to integrate namespace support into the template syntax.
     * > The goal of Smarty is to speparate the design as much as possible from the
     * > business logic. With namespace syntax we would more and more of business
     * > logic into the templates.
     * >
     * > Instead ou can register a class with optional namespace for the use in the template like:
     * >
     * > `$smarty->registerClass("FOO","\Fully\Qualified\Name\Foo");`
     *
     * @see http://www.smarty.net/forums/viewtopic.php?p=65279
     *
     * @param string $n The variable name for the class within the template
     * @param string $c The fully qualified class name
     */
    public function registerClass( $n, $c )
    {
        $this->_smarty->registerClass( $n, $c );
    }
    
    /**
     * Checks to see if the named template exists in the current skin
     *
     * @param string $name The template to look for
     * @return boolean
     */
    public function skinTemplateExists( $name )
    {
        if( $this->_skin && is_readable( $this->_smarty->template_dir[0] . '/_skins/' . $this->_skin . '/' . $name ) )
            return true;

        return $this->templateExists( $name );
    }


    /**
     * This function "resolves" a given template name into an appropriate
     * template file depending on whether we're using skins or not.
     *
     * If we're using skins and if a template exists in the skin, then
     * it'll be used. Otherwise we'll use the default templates.
     *
     *
     * @param string $name The name of the template to use
     * @return string The resolved template name
     */
    public function resolveTemplate( $name )
    {
        // if we're using a skin see if a skin file exists.
        // if so, use it, otherwise use the default skin files
        if( $this->_skin && is_readable( $this->_smarty->template_dir[0] . '/_skins/' . $this->_skin . '/' . $name ) )
            return '_skins/' . $this->_skin . '/' . $name;

        return $name;
    }

    /**
     * Run
     *
     * @return void
     */
    protected function _run()
    {
        include func_get_arg(0);
    }


    /**
     * Add a new message to the stack
     *
     * @param OSS_Message An instance of the OSS_Message class
     * @return bool
     */
    public function ossAddMessage( OSS_Message &$message )
    {
        $OSS_Messages = $this->_smarty->getTemplateVars( 'OSS_Messages' );

        if ( $OSS_Messages == null )
            $OSS_Messages = array();

        $OSS_Messages[] = $message;
        $this->_smarty->assign( 'OSS_Messages', $OSS_Messages );

        return true;
    }

    /**
     *
     * Set the skin to use
     * @param string $s The name of the skin
     * @throws Exception
     */
    public function setSkin( $s )
    {

        // does the skin exist?
        if( is_readable( $this->_smarty->template_dir[0] . "/_skins/$s" ) )
        {
            $this->_skin = $s;
            return true;
        }

        throw new Exception( "Specified skin directory does not exist or is not readable ("
            . $this->_smarty->template_dir[0] . "/_skins/$s" . ")"
        );
    }

    /**
     *
     * Return the name of the skin in use or false if default.
     * @return string The name of the skin in use or false if default.
     */
    public function getSkin()
    {
        return $this->_skin;
    }
    
    /**
     * Load a config file, optionally load just selected sections
     *
     * @param string $cfile       filename
     * @param mixed  $sections    array of section names, single section or null
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     */
    public function configLoad( $cfile, $sections = null )
    {
        return $this->_smarty->configLoad( $cfile, $sections );
    }

}
