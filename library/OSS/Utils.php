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
 * @package    OSS_Utils
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Utils
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Utils
{

    /**
     * Parses an XML using SimpleXML and returns with the result object, or false on error.
     *
     * @param string $XML
     * @return array|bool
     */
    public static function parseXML( $XML )
    {
        libxml_use_internal_errors( true );

        $parsedXML = simplexml_load_string( $XML );

        if( !$parsedXML )
        {
            //foreach ( libxml_get_errors() as $error) logError( 'SimpleXML error: ' . $error->message, null, false, false );
            return false;
        }

        return $parsedXML;
    }


    /**
     * Converts a HTML file to PDF using htmldoc. Please note that the current stable version of htmldoc (v1.8.27) does not support CSS. Make sure PHP has write permission in the
     * output directory. Also don't forget that relative paths are relative to the currently executing PHP script (include() and require() doesn't count in that).
     * Using command line OpenOffice might be a better solution (a necessary macro and a tiny, one line shell script are available on the internet), as it supports everything in the HTML,
     * CSS, images, etc, while the htmldoc 1.9.x is still in "developer snapshot" state.
     *
     * @param string $html the input HTML file name
     * @param string $pdf the output PDF file name
     * @param bool $embedFonts default false embed the used fonts to the genereated PDF or not - can save 100's of kBytes even in a plain PDF (think of email sending)
     * @return bool
     */
    public static function HtmlToPdf( $html, $pdf, $embedFonts = false )
    {
        $tempFile = OSS_Utils::getTempDir() . '/html_to_pdf_' . md5( mt_rand() ) . '.html';

        $res = file_put_contents( $tempFile, mb_convert_encoding( file_get_contents( $html ), 'HTML-ENTITIES' ) );

        if( !$res )
            return false;

        // a ' &> /dev/null' at the end of the command should work, but it doesn't
        $command = 'htmldoc --webpage --continuous --compression=9' .
                    ($embedFonts == false ? ' --no-embedfonts' : '') .
                    ' --fontsize 10 --size A4 --top 2cm --bottom 2cm --left 2cm --right 2cm -t pdf14 -f '
                    . escapeshellarg( $pdf ) . ' ' . escapeshellarg( $tempFile );

        //print "\n\n" . $vCommand . "\n\n"; die();
        //file_put_contents('../var/tmp/command.txt', $vCommand);

        $res = @exec( $command );

        if( !$res )
            return false;

        return @file_exists( $pdf );
    }

    /**
     * Converts a HTML file to PDF using wkhtmltopdf
     *
     * @param string $html the input HTML file name
     * @param string $pdf the output PDF file name
     * @return bool
     */
    public static function wkhtmltopdf( $html, $pdf )
    {
        // WARNING: You have to use the static version of wkhtmltopdf

        $command = dirname( __FILE__ ) . '/../../bin/wkhtmltopdf -q -n -O Portrait -s A4 ' . escapeshellarg( $html ) . ' ' . escapeshellarg( $pdf );

        @unlink( $pdf );

        $res = @exec( $command, $output, $ret );

        if( $res === false )
            return false;

        return @file_exists( $pdf );
    }


    /**
     * A generally available function to retrieve options from the application.ini, anywhere in the program. Don't need to use this
     * in the controllers, the $this->_options array is available there, this is useful in models, forms and other places.
     *
     * @todo: this method is rarely called, but a way to speed it up is to store the key and value in session, and then look for it when called
     *
     * @param string $option
     * @return mixed
     */
    public static function getIniOption( $option )
    {
        return Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getApplication()->getOption( $option );
    }


    /**
    * Returns with the resource object. Use if Zend_Registry::get() doesn't work. (And in the OSS framework it doesn't.)
    *
    * @param string $resource
    * @return object
    */
    public static function getResource( $resource )
    {
        return Zend_Controller_Front::getInstance()->getParam( 'bootstrap ')->getResource( $resource );
    }


    /**
    * Returns with the temporary directory set in the application.ini, or if it is not set, then with the result of sys_get_temp_dir().
    *
    * @todo: this method is rarely called, but a way to speed it up is to store the path in session, and then look for it when called
    *
    * @return string
    */
    public static function getTempDir()
    {
        $tempDir = OSS_Utils::getIniOption( 'temporary_directory' );

        return ( $tempDir == '' ? sys_get_temp_dir() : $tempDir );
    }


    /**
     * A function to generate a URL with the given parameters.
     *
     * This is a useful function as no knowledge of the application's path is required.
     *
     * It is also configurable (via Zend_Application config and assuming 'options' is 
     * available in Zend_Registry as it would be from OSS_Controller_Action).
     *
     * You can configure the hostname by setting config: utils.genurl.host_mode
     *
     * * Default (no config / invalid config): ''$_SERVER['HTTP_HOST']''
     * * ''HTTP_X_FORWARDED_HOST'': Use ''$_SERVER['HTTP_X_FORWARDED_HOST']''
     * * ''REPLEACE'': set with value from utils.genurl.host_replace
     *
     * @param string|bool $controller default false The controller to call.
     * @param string|bool $action default false The action to call (controller must be set if setting action)
     * @param string|bool $module default false The module to use. Set to false to ignore.
     * @param array $params default array() An array of key value pairs to add to the URL.
     * @param string $host Defaults to null. Hostname (including http[s]://) to override url with
     * @return string
     */
    public static function genUrl( $controller = false, $action = false, $module = false, $params = array(), $host = null )
    {
        $options = Zend_Registry::get( 'options' );
        
        $url = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        if( $host !== null )
        {
            // strip out http[s]://
            if( strpos( $url, 'https://' ) === 0 )
                $url = substr( $url, 8 );
            else if( strpos( $url, 'http://' ) === 0 )
                $url = substr( $url, 7 );

            $pos = strpos( $url, '/' );

            if( $pos !== false )
                $url = substr( $url, $pos );
        }
        else 
        {
            if( isset( $options['utils']['genurl']['host_mode'] ) )
            {
                switch( $options['utils']['genurl']['host_mode'] )
                {
                    case 'HTTP_X_FORWARDED_HOST':
                        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
                        break;
                        
                    case 'REPLACE':
                        $host = $options['utils']['genurl']['host_replace'];
                        break;
                        
                    default:
                        $host = $_SERVER['HTTP_HOST'];
                }
            }
            else
                $host = $_SERVER['HTTP_HOST'];
        }

        $url = $host . $url;
        
        // when the webpage is directly under "xyz.com/", and not in "xyz.com/wherever"
        // an empty href attribute in an anchor tag means "the current URL", which is not always good
        //if( $url == '' )
        
        if( strpos( $url, 'http' ) !== 0 )
        {
            $protocol = 'http';

            if( isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] == 'on' ) )
            {
                $protocol = 'https';
            }
            elseif( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) )
            {
                $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
            }

            $url = "{$protocol}://{$url}";
        }

        if( $module )
            $url .= "/{$module}";

        if( $controller )
            $url .= "/{$controller}";

        if ( $action )
            $url .= "/{$action}";

        if( sizeof( $params ) > 0 )
        {
            foreach( $params as $var => $value )
                $url .= "/{$var}/{$value}";
        }

        return $url;
    }

    /**
     * Returns number with ordinal postfix.
     *
     * @param int $number
     * @return string
     */
    public static function ordinal( $number )
    {
        $ends = array( 'th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th' );

        return ( in_array( $number % 100, array( 11, 12, 13 ) ) ? $number . 'th' : $number . $ends[ $number % 10 ] );
    }

    
    /**
     * Creates a uniformly distributed directory path from given numeric id.
     *
     * First it turn given id to hex. Then it appends new hex with leading
     * zeros to reach given length ( 3 by default ) if necessary. And then
     * new string is reversed. From reversed string function takes as many
     * characters as defined in length.
     * 
     * e.g. `uniformDistHash( 216 )` returns `8/d/0/216/`
     *      `uniformDistHash( 7 )` returns `7/0/0/7/`
     *      `uniformDistHash( 5057 )` returns `1/c/3/5057/`
     *
     * @param int $id Id for making file structure
     * @param int $length How many levels should be created
     * @return string
     */
    public static function uniformDistHash( $id, $length = 3 )
    {
        $tmpstr = strrev( str_pad( dechex( $id ), $length, 0, STR_PAD_LEFT ) );
        $str = "";
        for( $i = 0; $i < $length; $i++ )
            $str .= $tmpstr[ $i ] . "/";
        $str .= $id . "/";

        return $str;
    }

}
