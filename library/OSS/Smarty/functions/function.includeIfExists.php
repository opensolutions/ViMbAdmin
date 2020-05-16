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
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Smarty
 * @subpackage Functions
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */

/**
 * Smarty plugin
 * Purpose: Similar with "include" function, but only include the
 * template file when it exists. Otherwise, a default file passed
 * by parameter "else" will be included.
 * Example:
 *   1 {includeIfExists file="foo.tpl" assign="foo"}
 *   2 {includeIfExists file="foo.tpl" else="default.tpl"}
 * -------------------------------------------------------------
 *
 * @category   OSS
 * @package    OSS_Smarty
 * @subpackage Functions
 *
 * @param int $timestamp
 * @return string
 */

function smarty_function_includeIfExists( $params, $smarty )
{
    if( !isset( $params['file'] ) )
        throw new SmartyCompilerException( "Missing 'file' attribute in tmplinclude tag" );

    $original_values = array();

    foreach( $params as $arg => $value )
    {
        if( is_bool( $value ) )
            $params[ $arg ] = $value ? 'true' : 'false';

        if( !in_array( $arg, array( 'file', 'assign', 'else' ) ) )
        {
            $original_values[ $arg ] = $value;
            $smarty->assign( $arg, $value );
        }
    }

    $params['file'] = str_replace( array( '\'', '"' ), '', $params['file'] );
    $params['else'] = str_replace( array( '\'', '"' ), '', $params['else'] );
    
    if( $smarty->getTemplateVars( '___SKIN' ) )
        $skin = $smarty->getTemplateVars( '___SKIN' );
    else
        $skin = false;

    if( $skin && $smarty->templateExists( '_skins/' . $skin . '/' . $params['file'] ) )
        $params['file'] = '_skins/' . $skin . '/' . $params['file'];
    elseif( $skin && $smarty->templateExists( '_skins/' . $skin . '/' . $params['else'] ) )
        $params['file'] = '_skins/' . $skin . '/' . $params['else'];
    elseif( $smarty->templateExists( $params['file'] ) )
        $params['file'] = $params['file'];
    elseif( $smarty->templateExists( $params['else'] ) )
        $params['file'] = $params['else'];
    else
        throw new SmartyCompilerException( "Template file nor alternative does not exist for all skins - [{$params['file']}]" );
    
    $output = '';

    if( isset( $params['assign'] ) )
        $smarty->assign( $params['assign'], $smarty->fetch( $params['file'] ) );
    else
        $output = $smarty->fetch( $params['file'] );

    foreach( $original_values as $arg => $value )
    {
        $smarty->assign( $arg, $value );
    }

    return $output;
}


