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
 * @package    OSS_Log
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Log
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Log extends Zend_Log
{

     /**
     * Inserts an element into the form after an already existing element.
     *
     * @param string|object $afterElement an element name or an instance of Zend_Form_Element, the new element will be placed after that one
     * @param string|object $element an element name or an instance of Zend_Form_Element, the new element which will be inserted
     * @param string $name default null the name for the new element
     * @param array $options default null the options for the new element
     * @param int $order default null if there are ordered subforms in the form, then passing an order number might be necessary to position the element correctly
     * @return void
     */
    public function alert( $message )
    {
        if ( php_sapi_name() == 'cli' )
        {
            $return = $message;
        }
        else
        {
            $return = $message . "

           host : {$_SERVER['HTTP_HOST']}
     user agent : {$_SERVER['HTTP_USER_AGENT']}
    remote addr : {$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}
script filename : {$_SERVER['SCRIPT_FILENAME']}
 request method : {$_SERVER['REQUEST_METHOD']}
   query string : {$_SERVER['QUERY_STRING']}
    request uri : {$_SERVER['REQUEST_URI']}
";
        }

        try
        {
            $this->log( $message, Zend_Log::ALERT );
        }
        catch( Exception $e )
        {
            $this->debug( $e->getMessage() );
        }
    }

}
