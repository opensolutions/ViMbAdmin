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
 * Function icludes template form skin, if file is not existing in skin folder
 * it displays default one.
 *
 * @category   OSS
 * @package    OSS_Smarty
 * @subpackage Functions
 *
 * @param int $timestamp
 * @return string
 */
function smarty_function_tmplinclude( $params, $smarty )
{
    if( !isset( $params['file'] ) )
        throw new SmartyCompilerException( "Missing 'file' attribute in tmplinclude tag" );

    $original_values = array();
    
    foreach( $params as $arg => $value )
    {
        if( is_bool( $value ) )
            $params[ $arg ] = $value ? 'true' : 'false';
        
        if( !in_array( $arg, array( 'file', 'assign' ) ) )
        {
            $original_values[ $arg ] = $value;
            $smarty->assign( $arg, $value );
        }
    }
    
    if( substr( $params['file'], 0, 24 ) == '$_smarty_tpl->tpl_vars[\'' )
    {
        $params['file'] = substr( $params['file'], 24 );
        $params['file'] = substr( $params['file'], 0, strpos( $params['file'], '\'' ) );
        $params['file'] = $smarty->getTemplateVars( $params['file'] );
    }
    elseif( substr( $params['file'], 0, 24 ) == '($_smarty_tpl->tpl_vars[' )
    {
        $params['file'] = substr( $params['file'], 24 );
        $params['file'] = substr( $params['file'], 0, strpos( $params['file'], ']' ) );
        $params['file'] = $smarty->getTemplateVars( $params['file'] );
    }
    elseif( substr( $params['file'], 0, 23 ) == '$_smarty_tpl->tpl_vars[' )
    {
        $params['file'] = substr( $params['file'], 23 );
        $params['file'] = substr( $params['file'], 0, strpos( $params['file'], ']' ) );
        $params['file'] = $smarty->getTemplateVars( $params['file'] );
    }
    else
        $params['file'] = str_replace( array( '\'', '"' ), '', $params['file'] );

    if( $smarty->getTemplateVars( '___SKIN' ) )
        $skin = $smarty->getTemplateVars( '___SKIN' );
    else
        $skin = false;
    
    if( $skin && $smarty->templateExists( '_skins/' . $skin . '/' . $params['file'] ) )
        $params['file'] = '_skins/' . $skin . '/' . $params['file'];
    elseif( !$smarty->templateExists( $params['file'] ) )
        throw new SmartyCompilerException( "Template file does not exist - [{$params['file']}]" );
        
    
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
