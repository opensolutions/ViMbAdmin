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
 * @package    OSS_Csv
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Csv
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Csv
{
	/**
	 * Generated CSV data.
	 */
	private $_csv = null;


	/**
	 * Constructor
	 *
	 * @param array $csvArray Array to parse as CSV.
	 */
	public function __construct( $csvArray = null )
	{
		if( $csvArray )
			$this->_processCSV( $csvArray );
	}

	/**
	 * Parses given array to CSV.
	 *
	 * @param array $csvArray Array to parse as CSV.
	 * @return void
	 */
	private function _processCSV( $csvArray )
	{
		$buffer = fopen( 'php://temp', 'w+' );

        foreach( $csvArray as $row)
            fputcsv( $buffer, $row );

        rewind ($buffer );
        $csv = stream_get_contents( $buffer );
        fclose( $buffer );

        $this->_csv = $csv;
	}

	/**
	 * Parse given array and stores new data.
	 *
	 * @param array $csvArray Array to parse as CSV.
	 * @return void
	 */
	public function setCsvArray( $csvArray )
	{
		$this->_processCSV( $csvArray );
		return $this;
	}

	/**
	 * Returns CSV data.
	 *
	 * @returns string
	 */
	public function getContents()
	{
		return $this->_csv;
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
		$name = sprintf( "%s_%s.csv", $fileName ? $fileName : 'OSSFile', date( 'YmdHis' ) );

		header( 'Content-type: application/csv' );
        header( "Content-Disposition: attachment; filename=" . $name );
        header( 'Cache-Control: no-cache, must-revalidate' ); // HTTP/1.1
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );

        echo $this->_csv;
	}
}


