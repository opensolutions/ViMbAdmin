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
 * @package    OSS_Debug
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Debug
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Debug
{

   /**
    * This function will 'var_dump and die' - it will (if HTML) surround the
    * output with <pre> tags.
    *
    * The dump command is Zend_Debug::dump()
    *
    *
    * @param object $object The variable / object to dump
    * @param bool $html If true (default) surround the output with <pre> tags
    * @return void
    */
    public static function dd( $object, $html = true )
    {
        if( $html && php_sapi_name() != 'cli' ) echo '<pre>';
        Zend_Debug::dump( $object );
        if( $html && php_sapi_name() != 'cli' ) echo '</pre>';
        die();
    }

    /**
     * This function will 'print_r() and die' - it will (if HTML) surround the
     * output with <pre> tags.
     *
     * @param array $array The array to dump
     * @param bool $html If true (default) surround the output with <pre> tags
     * @return void
     */
    public static function pd( $array, $html = true )
    {
        if( $html && php_sapi_name() != 'cli' ) echo '<pre>';
        print_r( $array );
        if( $html && php_sapi_name() != 'cli' ) echo '</pre>';
        die();
    }
    
    
    /**
     * A wrapper and extension for print_r(). The output looks the same in the browser as the output of print_r() in the source, as it turns the pure
     * text output of print_r() into HTML (XHTML).
     *
     * @param mixed $data the data to be printed or returned
     * @param mixed $var_name null if we don't want to display the variable name, otherwise the name of the variable
     * @param boolean $return default false; if true it returns with the result, if true then prints it
     * @param boolean $addPre default true adds the '<pre> ... </pre>' tags to the output, useful for HTML output
     * @param boolean $addDollarSign default true adds a $ sign to the $var_name if it is set to true
     * @return void|string
     */
    public static function prr( $data, $var_name = null, $return = false, $addPre = true, $addDollarSign = true )
    {
        $retVal = ( $addPre == true ? "\n<pre>\n" : '' ) .
            ( $var_name == '' ? '' :  ($addDollarSign == true ? "\$" : '') . "{$var_name} = " ) .
            print_r( $data, true ) .
            ( $addPre == true ? "\n</pre>\n" : '' );


        if( !$return )
            print $retVal;
        else
            return $retVal;
    }


    /**
     * Returns with a simplified, easier-to-read version of the result of debug_backtrace() as an associative array.
     *
     * @param void
     * @return array
     */
    public static function compact_debug_backtrace()
    {
        $res = debug_backtrace();
        $ret_val = array();

        foreach( $res as $res_val )
        {
            $xyz = array();
            if( isset( $res_val['file'] ) )
                $xyz['file'] = $res_val['file'];
            
            if( isset( $res_val['line'] ) )
                $xyz['line'] = $res_val['line'];
            
            if( isset( $res_val['function'] ) )
                $xyz['function'] = $res_val['function'];
            
            if( isset( $res_val['class'] ) )
                $xyz['class'] = $res_val['class'];
            
            if( isset( $res_val['object']->name ) )
                $xyz['object'] = $res_val['object']->name;

            $ret_val[] = $xyz;
        }

        return $ret_val;
    }


    /**
     * Returns with the inheritance tree of $pClassOrObject, which can be a class name or an object.
     * It returns with a simple indexed array, where index 0 is the class of $pClassOrObject, and
     * index N is the name of the class at the end of the whole inheritance tree. If $pClassOrObject
     * is not a string or an object, then it returns with NULL.
     *
     * @param string|object $classOrObject a string class name or an object
     * @return array|null
     */
    public static function getInheritanceTree( $classOrObject )
    {
        if( ( is_string( $classOrObject ) == false) && ( is_object( $pClassOrObject ) == false ) )
            return null;

        $classList = array();
        $classList[] = get_class( $classOrObject );
        $parentClass = get_parent_class( $classOrObject );

        while( $parentClass )
        {
            $classList[] = $parentClass;
            $parentClass = get_parent_class( $parentClass );
        }

        return $classList;
    }


    /**
     * Putting message to log file.
     *
     * @param string|object $messsage Debug message
     * @return array|null
     */
    public static function log( $message )
    {
        $message = date( 'Y-m-d H:i:s') . ' : ' . $message . "\n";
        @file_put_contents( '../var/tmp/' . date('Y-m-d') . '.log', $message, FILE_APPEND | LOCK_EX);
    }

}
