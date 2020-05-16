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
class OSS_Pdf_PdfLatex 
{
    /**
     *Options required for the class.
     *
     * @var array
     */
    private $_options;
    
    
    /**
     * The Smarty view object (may also work with Zend_View but needs to be tested)
     *
     * @var Zend_View
     */
    private $_view;


    /**
     * Take and set configuration parameters.
     *
     * @param array $config default null An associative array of configuration options
     * @param object $view default null a Smarty view object
     * @return void
     */
    function __construct( $config = null, $view = null ) 
    {
        $this->_options   = $config;
        $this->_view      = $view;
    }
    
    
    /**
     * Generate the PDF file using the given template
     *
     * All required style files, includes, etc are either provided in the
     * template as absolute paths or as relative paths to the template file.
     *
     * @param string $pTemplate
     * @param $pViewVariables default null
     * @param &$pPdfFileName this will contain the full path to the PDF 
     * @throws OSS_Pdf_PdfLatex_Exception
     * @return string a binary string, the content of the PDF file
     */
    public function generatePdf( $pTemplate, $pViewVariables = null, &$pPdfFileName )
    {
        $this->_changeDelimiters();

        foreach( $pViewVariables as $var => $val ) $this->_view->$var = $val;

        // try and create a temporary file for the generated LaTeX

        // tempnam() doesn't like '..' in the filename 
        $filename = "{$this->_options['executables']['pdflatex']['output_directory']}/auto-gen-" . mt_rand();
        $pPdfFileName = "{$filename}.pdf";

        if (@touch($filename) === false) throw new OSS_Pdf_PdfLatex_Exception( 'Could not create temporary file for the PDF document. Check permissions.' );

        @file_put_contents( $filename, $this->_view->render( "../../data/pdflatex/templates/{$pTemplate}.tpl" ) );

        // generate the PDF
        $cmd = 'cd '
                . escapeshellarg( $this->_options['executables']['pdflatex']['output_directory'] ) 
                . ' && '
                . escapeshellcmd( $this->_options['executables']['pdflatex']['cmd'] )
                . ' -output-directory ' . escapeshellarg( $this->_options['executables']['pdflatex']['output_directory'] ) . ' ' 
                . escapeshellarg( $filename );

        $lastline = exec( $cmd, $output, $retval );
        $lastline = exec( $cmd, $output, $retval );

        if ( $retval != 0 ) throw new OSS_Pdf_PdfLatex_Exception( 'Could not compile LaTeX file ' . $filename );

        $pdf = @file_get_contents( $filename . '.pdf' );

        @unlink( $filename );
        @unlink( $filename . '.aux' );
        @unlink( $filename . '.log' );
        //@unlink( $filename . '.pdf' ); // the file might be needed later

        $this->_resetDelimiters();

        return $pdf;
    }


    /**
     * LaTeX syntax will conflict badly with Smarty. As such, this function changes
     * the Smarty delimiters to something unlikely to appear in a LaTeX file.
     * 
     * @param string $ld Left delimiter
     * @param string $rd Right delimiter
     * @return void
     */
    private function _changeDelimiters( $ld = '<!--{', $rd = '}--!>' )
    {
        $this->_view->getEngine()->left_delimiter   = $ld;
        $this->_view->getEngine()->right_delimiter  = $rd;
    }

    /**
     * LaTeX syntax will conflict badly with Smarty. This function resets
     * the Smarty delimiters to their originals after _changeDelimiters() 
     * was used.
     * 
     * @see _changeDelimiters()
     *
     * @param string $ld Left delimiter
     * @param string $rd Right delimiter
     * @retur void
     */
    private function _resetDelimiters( $ld = '{', $rd = '}' )
    {
        $this->_view->getEngine()->left_delimiter   = $ld;
        $this->_view->getEngine()->right_delimiter  = $rd;
    }
}

