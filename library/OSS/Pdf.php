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
 * @package    OSS_Pdf
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Pdf
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Pdf
{
	/**
	 * OSS_Html object.
	 */
	private $_html = null;

	/**
	 * Constructor
	 *
	 * @param object $view Object for rendering HTMLs.
	 * @param string $viewscript Path to template.
	 */
	public function __construct( $view, $viewscript = null )
	{
		$this->_html = new OSS_Html( $view, $viewscript );
	}

	/**
	 * Sets view script for rendering
	 *
	 * @param string $viewscript Path to template.
	 * @return OSS_Pdf
	 */
	public function setViewScript( $viewscript )
	{
		$this->_html->setViewScript( $viewscript );
		return $this;
	}

	/**
	 * Creates PDF and returns path.
	 *
	 * First it renders and creates HTML file and then it creates pdf file.
	 * It removes new HTML file and returns path to new pdf file.
	 * 
	 * PDF file removing is added in all other methods where _processPDF are called.
	 *
	 * @return string
	 */
	private function _processPDF()
	{
        $ts = date( 'YmdHis' );
        $tn = APPLICATION_PATH . "/../var/tmp/OSS_PDF_" . $ts . '_' . OSS_String::random( 8, true, true, true, '', '' );

        $fhtml = "{$tn}.html";
        $fpdf  = "{$tn}.pdf";

        if( @file_put_contents( $fhtml, $this->_html->render() ) === false )
            return false;
        
        $path = Zend_Registry::get('options')['includePaths']['osslibrary'] . "/bin";
        
        @exec( escapeshellcmd( $path . "/wkhtmltopdf-amd64 -q '{$fhtml}' '{$fpdf}'" ) );

        @unlink( $fhtml );

        if( !file_exists( $fpdf ) || !filesize( $fpdf ) )
            return false;

        return $fpdf;
	}

	/**
	 * Renders the view script
	 *
	 * @returns string
	 */
	public function render()
	{
		$fpdf = $this->_processPDF();
		$pdf = Zend_Pdf::load( $fpdf );
        $ret = $pdf->render();
        @unlink( $fpdf );
        return $ret;
	}

	/**
	 * Get contents it's the same as render().
	 *
	 * @returns string
	 */
	public function getContents()
	{
		$fpdf = $this->_processPDF();
		$ret = file_get_contents( $fpdf );
        @unlink( $fpdf );
        return $ret;
	}

	/**
	 * Gets as file.
	 * Sets headers and prints out contents.
	 * This function is usable then you want to export in browser.
	 *
	 * @param string $fileName Name of file.
	 * @returns string
	 */
	public function getAsFile( $fileName = false )
	{
		$fpdf = $this->_processPDF();

		$name = sprintf( "%s_%s.pdf", $fileName ? $fileName : 'OSSFile', date( 'YmdHis' ) );

		header( 'Content-type: application/pdf' );
        header( "Content-Disposition: attachment; filename=" . $name );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );

        echo file_get_contents( $fpdf );
        @unlink( $fpdf );
    }

}


