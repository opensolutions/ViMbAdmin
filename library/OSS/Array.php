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
 * @package    OSS_Array
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Array
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Array
{
    /**
     * Reindex an array of objects by a member of that object. 
     * 
     * Typically used for Doctrine2 collections.
     * 
     * @param array $objects Array of objects to reindex
     * @param string $indexFn The method of the object that will return the new index. Must be a unique key.
     * @return array
     */
    public static function reindexObjects( $objects, $indexFn )
    {
        $new = [];
        
        foreach( $objects as $o )
            $new[ $o->$indexFn() ] = $o;
        
        return $new;
    }

    /**
     * Reorder an array of objects by a member of that object. 
     * 
     * Typically used for Doctrine2 collections.
     * 
     * @param array $objects Array of objects to reindex
     * @param string $indexFn The method of the object that will return the new ordering index (should be unique!).
     * @return array
     */
    public static function reorderObjects( $objects, $orderFn, $orderParam = SORT_REGULAR )
    {
        $new = [];
        
        foreach( $objects as $o )
            $new[ $o->$orderFn() ] = $o;
        
        ksort( $new, $orderParam );
        
        return $new;
    }

    /**
     * Removes array elements where the value is empty().
     *
     * @param array $array The array to clean
     * @param boolean $keepKeys default true keep the original keys or assign new (numeric) ones
     * @return array
     */
    public static function removeEmptyElements( $array, $keepKeys = true )
    {
        if( ( is_array( $array ) == false ) || ( sizeof( $array ) == 0 ) )
            return array();

        $retVal = array();

        // longer code, but faster
        if( $keepKeys == true )
        {
            foreach( $array as $arrayKey => $arrayValue )
            {
                if( empty( $arrayValue ) == false )
                    $retVal[$arrayKey] = $arrayValue;
            }
        }
        else
        {
            foreach( $array as $arrayKey => $arrayValue )
            {
                if( empty( $arrayValue ) == false )
                    $retVal[] = $arrayValue;
            }
        }

        return $retVal;
    }


    /**
     * Works exactly like shuffle(), but this works on associative arrays, too.
     *
     * @param array $array the array to shuffle, reference-type parameter, it contains the result, too
     * @return boolean
     */
    function shuffleAssoc( &$array )
    {
        $newArray = array();
        $keys = array_keys( $array );

        shuffle( $keys );

        foreach( $keys as $key )
            $newArray[$key] = $array[$key];

        $array = $newArray;

        return true;
    }


    /**
    * Returns with an one-dimensional array of values from any kind of nested array.
    *
    * Try to avoid recursive arrays to not to go into an infinite loop.
    *
    * Returns with the values in an associative array by keeping the original key => value pairs.
    * As it keeps the original keys, if a key exists more than once in the structure, it will be
    * overwritten and will appear in the result only once, having the value of the last key-value pair.
    *
    * @param array $array The array to flatten
    * @return array
    */
    public static function flatten( $array )
    {
        $arrayValues = array();

        foreach( $array as $key => $value )
        {
            if( is_scalar( $value ) || is_resource( $value ) || is_null( $value ) )
            {
                $arrayValues[$key] = $value;
            }
            elseif( is_array( $value ) )
            {
                $arrayValues = array_merge( $arrayValues, self::flatten( $value ) );
            }
        }

        return $arrayValues;
    }


    /**
    * Takes a nested array - typically a result set from a query - and filters out a
    * specific field from it. It does not preserve the array keys.
    *
    * @param array $array the input array
    * @param string $fieldName the field to filter out
    * @param boolean $unique default true it can filter out multiple occurences of the same value
    * @param boolean $removeNull default false if true then removes null values from the result set
    * @return array
    */
    public static function filterField( $array, $fieldName, $unique = true, $removeNull = false )
    {
        $retVal = array();

        if( ( is_array( $array ) == false ) || ( sizeof( $array ) == 0 ) )
            return array();

        foreach( $array as $result )
            $retVal[] = $result[$pFieldName];

        if( $unique == true )
            $retVal = array_unique( $retVal );

        if( $removeNull == true)
        {
            foreach( $retVal as $retValKey => $retValValue )
            {
                if( $retValValue === null )
                    unset( $retVal[$retValKey] );
            }
        }

        return $retVal;
    }


    /**
    * Filters out key-value pairs from nested arrays, typically from Doctrine result sets.
    * Both the $pKey and $pValue parameters are array keys in the $pArray.
    *
    * @param array $array the input array
    * @param string $key key filtering
    * @param string $value value filtering
    * @param boolean $unique default true return with unique values only or not
    * @return array
    */
    public static function filterKeyValuePairs( $array, $key, $value, $unique=true)
    {
        $retVal = array();

        if( ( is_array( $array) == false ) || ( sizeof( $array ) == 0 ) )
            return array();

        foreach( $array as $result )
            $retVal[ $result[$key] ] = $result[$value];

        return( $unique == true ? array_unique( $retVal ) : $retVal);
    }


    /**
    * Removes entries from an array based on the matches of the passed regular
    * expression. It handles multi-dimensional arrays.
    *
    * @param array $array the input array
    * @param string $pattern pattern for filter
    * @return array
    */
    public static function removeFields( $array, $pattern)
    {
        if(  ( is_array( $array ) == false ) || ( sizeof( $array ) == 0 ) )
            return $array;

        foreach( $array as $arrKey => $arrValue)
        {
            if( preg_match( "/{$pattern}/ui", $arrKey ) != 0 ) 
            {
                unset( $array[$arrKey] );
                continue;
            }

            if( is_array( $array[$arrKey] ) == true )
                $array[$arrKey] = self::removeFields( $array[$arrKey], $pattern );
        }

        return $array;
    }


    /**
    * Callback function for changeValues()
    *
    * @param mixed &item
    * @param mixed $key
    * &param array $params
    * @return void
    */
    public static function cavCallback( &$item, $key, array $params )
    {
        foreach( $params as $paramKey => $paramValue )
            if( $item === $paramKey )
                $item = $paramValue;
    }


    /**
    * Changes values in an array. Handles nested arrays.
    *
    * @param array &$array
    * @param array $fromToArray array('from1' => 'to1', 'from2' => 'to2', 'from3' => 'to3', ...)
    * @return void
    */
    public static function changeValues( array &$array, array $fromToArray)
    {
        if( ( is_array( $array ) == true ) && ( sizeof( $array ) >��0 ) )
            array_walk_recursive( $array, array( 'OSS_Array', 'cavCallback' ), $fromToArray );
    }


    /**
    * Recursively converts an object into an array. Empty objects become empty arrays.
    *
    * @param object $object
    * @return array
    */
    public static function objectToArray( $object )
    {
        if( ( is_object( $object ) == false ) && ( is_array( $object ) == false ) )
            return $pObject;

        if( is_object( $object ) == true )
            $object = get_object_vars( $object );

        if( sizeof( $object ) == 0 )
            return array();

        return array_map( array( 'OSS_Array', 'objectToArray' ), $object );
    }

    /**
     * Represents an array as a HTML table and returns with it as a string. It handles multi-dimensional arrays,
     * those are represented as nested tables. If $pArray is not an array or an empty array, then it returns with
     * an empty string.
     *
     * @param array $pArray
     * @param string $pTitle default '' the name of the variable or any other string, it will appear as a heading
     * @param string $pWidth default '' a valid CSS width, like '100px', '10%', '13em', etc.
     * @param string $pHeaderBgColour default '#404040'
     * @param string $pBorderColour default '#404040'
     * @return string
     */
    public static function toHtmlTable( $pArray, $pTitle='', $pWidth='', $pHeaderBgColour='#404040', $pBorderColour='#404040' )
    {
        if( !is_array( $pArray ) || !sizeof( $pArray) )
            return '';

        $vRetVal = '';

        $vRetVal .= "
<table style=\"border: 1px solid {$pBorderColour};" . ( $pWidth == '' ? '' : "width: {$pWidth};" ) . "\">";

        if( $pTitle != '' )
        {
            $vRetVal .= "
    <tr>
        <th colspan=\"2\" style=\"background: {$pHeaderBgColour}; font-weight: bold;\">{$pTitle}</th>
    </tr>";
        }

        foreach( $pArray as $vArrKey => $vArrValue )
        {
            $vRetVal .= "
    <tr>";

            if( is_array( $vArrValue ) )
            {
                $vRetVal .= "
        <td valign=\"top\">&nbsp; &nbsp;</td>
        <td>
        <br>";

                $vRetVal .= self::toHtmlTable( $vArrValue, $vArrKey, '100%', $pHeaderBgColour, $pBorderColour );

                $vRetVal .= "
        <br>
        </td>";
            }
            else
            {
                $vRetVal .= "
        <td valign=\"top\">{$vArrKey}</td>
        <td style=\"padding-left: 10px;\">{$vArrValue}</td>";
            }

            $vRetVal .= "
    </tr>";
        }

        $vRetVal .= "
</table>
";

        return $vRetVal;
    }

}
