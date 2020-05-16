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
 * @package    OSS_HTML
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Html
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Html
{
	/**
	 * Object to rendering HTMLs.
	 */
	private $_view = null;

	/**
	 * Path to template for rendering.
	 */
	private $_viewscript = null;

	/**
	 * Constructor
	 *
	 * @param object $view Object for rendering HTMLs.
	 * @param string $viewscript Path to template.
	 */
	public function __construct( $view, $viewscript = null )
	{
		$this->_viewscript = $viewscript;
		$this->_view = $view; 
	}

	/**
	 * Sets view script for rendering
	 *
	 * @param string $viewscript Path to template.
	 */
	public function setViewScript( $viewscript )
	{
		$this->_viewscript = $viewscript;
	}

	/**
	 * Renders the view script
	 *
	 * @returns string
	 */
	public function render()
	{
		return $this->_view->render( $this->_viewscript );
	}

	/**
	 * Get contents it's the same as render().
	 *
	 * @returns string
	 * @see OSS_Html::render()
	 */
	public function getContents()
	{
		return $this->render();
	}

	/**
	 * Gets as file.
	 * Sets headers and prints out content.
	 *
	 * @param string $fileName Name of file.
	 * @returns string
	 */
	public function getAsFile( $fileName = false )
	{
		$name = sprintf( "%s_%s.html", $fileName ? $fileName : 'OSSFile', date( 'YmdHis' ) );

		header( 'Content-type: text/html' );
        header( "Content-Disposition: attachment; filename=" . $name );
        header( 'Cache-Control: no-cache, must-revalidate' ); // HTTP/1.1
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );

        echo $this->render();
	}
}


