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
 * @package    OSS_Asterisk
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Asterisk
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Asterisk_AMI_Parser
{

    /**
     * Parse the response to a `sip show peer $peer` command
     *
     * This function takes the response array of the form:
     *
     *     Array
     *     (
     *         [data] => "Privilege: Command
     *
     *	           * Name       : pcustomer
     *	           Secret       : <Set>
     *    	       MD5Secret    : <Not set>
     *	           ...
     *	           ToHost       : customer
     *	           Addr->IP     : 192.168.40.43:5060
     *	           ...
     *	           Encryption   : No"
     *
     *	       [Response] => Follows
     *     )
     *
     * and converts it to an associate array:
     *
     *     Array
     *     (
     *         [Privilege] => Command
     *         [* Name] => pcustomer
     *         [Secret] => <Set>
     *         ...
     *         [ToHost] => customer
     *         [Addr->IP] => 192.168.40.43:5060
     *         [Addr] = Array
     *                  (
     *                      [IP] => 192.168.40.43
     *                      [Port] => 5060
     *                  )
     *         ...
     *         [Encryption] => No
     *     )
     *
     *
     * Example usage:
     *
     *     $resp = $ami->command( "sip show peer PEER_NAME" );
     *     $result = OSS_Asterisk_AMI_Parser::parsePeerResponse( $resp );
     *
     *
     * @param array $response Response of AMI command
     * @return array
     */
    static function parseSipShowPeerResponse( $response )
    {
        if( !strpos( $response['data'], ':' ) )
            return $response['data'];

        $data = [];
        foreach( explode( "\n", $response['data'] ) as $line )
        {
            $a = strpos( 'z'.$line, ':' ) - 1;
            if( $a >= 0 )
                $data[ trim( substr( $line, 0, $a ) ) ] = trim( substr( $line, $a + 1 ) );
        }
        
        $addr = explode( ":", $data[ "Addr->IP" ] );
        $data["Addr"]["IP"] = $addr[0];
        $data["Addr"]["Port"] = $addr[1];
        
        return( $data );
    }

    /**
	 * Parse channel response.
	 *
     * It takes response array it checks if response have data. If it has it tries to parse form
     * this type of array:
     * Array
     * (
     *   [data] => Privilege: Command
     *    -- General --
     *       Name: SIP/pcustomer-0000010a
     *       Type: SIP
     *       UniqueID: 1350570966.266
     *       LinkedID: 1350570966.266
     *       ...
     *       Indirect Bridge: SIP/ptelco-0000010b
     *    --   PBX   --
     *       Context: fwd-call
     *         Extension: 1201
     *         ...
     *        Variables:
     *            BRIDGEPVTCALLID=4093270c7dd45f224ffff58f129f3275@192.168.40.42:5060
     *            BRIDGEPEER=SIP/ptelco-0000010b
     *            ...
     *             SIPURI=sip:1101@192.168.40.43:5060
     *
     *      CDR Variables:
     *            level 1: dnid=1201
     *            level 1: calldate=2012-10-18 15:36:07
     *            ...
     *            level 1: sequence=337
     *
     *   [Response] => Follows
     *  )
     * To this type of array:
     *  Array
     *    (
     *        [] => Array
     *            (
     *                [Privilege] => Command
     *            )
     *        [general] => Array
     *            (
     *                [Name] => SIP/pcustomer-0000010a
     *                [Type] => SIP
     *                [UniqueID] => 1350570967.266
     *                [LinkedID] => 1350570966.266
     *                ...
     *                [Indirect Bridge] => SIP/ptelco-0000010b
     *            )
     *        [pbx] => Array
     *            (
     *                [Context] => fwd-call
     *                [Extension] => 1201
     *                ...
     *                [Variables] => Array
     *                    (
     *                        [BRIDGEPVTCALLID] => 4093270c7dd45f224ffff58f129f3275@192.168.40.42:5060
     *                        [BRIDGEPEER] => SIP/ptelco-0000010b
     *                        ...
     *                         [SIPURI] => sip:1101@192.168.40.43:5060
     *                      )
     *            )
     *        [cdr] => Array
     *            (
     *                [dnid] => 1201
     *                [calldate] => 2012-10-18 15:36:07
     *                ...
     *                [sequence] => 337
     *            )
     *    )
     *
     * Usage: 
     *     $presp = $ami->command( "core show channel CHANNEL_NAME" );
     *     $result = OSS_Asterisk_AMI_Parser::parseChannelResponse( $presp );
     *
     *
     * @var array $response Response of AMI command
     * @return array
     */
    static function parseChannelResponse( $response )
    {
        if( !strpos( $response['data'], ':' ) )
            return $response['data'];

        $data = [];
        foreach( explode( "\n", $response['data'] ) as $line )
        {
            if( strpos( $line, "General") !== false )
                $name = "general";
            elseif( strpos( $line, "PBX") !== false )
                $name = "pbx";
            elseif( strpos( $line, "CDR Variables") !== false )
                $name = "cdr";
            else
            {
                if( $name == "variables" )
                {
                    $a = strpos( 'z'.$line, '=' ) - 1;
                    if( $a >= 0 )
                    {
                        $data['pbx']['variables'][ trim( substr( $line, 0, $a ) ) ] = trim( substr( $line, $a + 1 ) );
                        continue;
                    }
                }
                
                $a = strpos( 'z'.$line, ':' ) - 1;
                if( $a >= 0 )
                {

                    if( $name == "cdr" )
                    {
                        $cdrVar = explode( "=", trim( substr( $line, $a + 1 ) ) );
                        $data['cdr'][ trim( $cdrVar[0] ) ] = trim( $cdrVar[1] );
                    }
                    else
                    {

                    	if( trim( substr( $line, 0, $a ) ) != "Variables" )
                        	$data[$name][ trim( substr( $line, 0, $a ) ) ] = trim( substr( $line, $a + 1 ) );
                        else
                        	$name = "variables";
                    }
                }
            }
        }
        return( $data );
    }

    /**
	 * Parse channels response when channels call had attribute concise.
	 *
     * It takes response array it checks if response have data. If it has it tries to parse form
     * this type of array:
     *    Array
     *    (
     *        [data] => Privilege: Command
     *            SIP/ptelco-00000113!ttelco!!1!Up!AppDial!(Outgoing Line)!1201!cust1!cust1!3!15!SIP/pcustomer-00000112!1350573839.275
     *            SIP/pcustomer-00000112!fwd-call!1201!6!Up!Dial!SIP/ptelco/1201!1101!cust1!cust1!3!15!SIP/ptelco-00000113!1350573837.274
     *
     *        [Response] => Follows
     *    )
     * To this type of array:
     *    Array
     *    (
     *        [0] => Array
     *            (
     *                [0] => SIP/ptelco-00000113
     *                [1] => ttelco
     *                [2] => 
     *                 [3] => 1
     *                [4] => Up
     *                [5] => AppDial
     *                [6] => (Outgoing Line)
     *                [7] => 1201
     *                [8] => cust1
     *                [9] => cust1
     *                [10] => 3
     *                [11] => 15
     *                [12] => SIP/pcustomer-00000112
     *                [13] => 1350573839.275
     *            )
     *        [1] => Array
     *            (
     *                [0] => SIP/pcustomer-00000112
     *                  ...
     *                [13] => 1350573837.274
     *            )
     *    )
     *
     * Usage: 
     *     $resp = $ami->command( "core show channels concise" );
     *     $result = OSS_Asterisk_AMI_Parser::parseChannelsConciseResponse( $resp );
     *
     *
     * @var array $response Response of AMI command
     * @return array
     */
    static function parseChannelsConciseResponse( $response )
    {
        if( !strpos( $response['data'], ':' ) )
            return $response['data'];

        $data = [];
        foreach( explode( "\n", $response['data'] ) as $line )
        {
            $line = preg_replace('!\s+!', ' ', $line);
            $a = explode( "!", $line );
            
            if( count( $a ) > 3 && $a[0] != "Channel" )
                $data[] = $a;
        }
        return( $data );
    }
}


