<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
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
 * @package    OSS_Form
 * @subpackage OSS_Form_Element
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * Form element text field with database dropdown
 *
 * NOTICE: It requires chosen library
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Form
 * @subpackage OSS_Form_Element
 * @copyright  Copyright (c) 2007 - 2013, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */

class OSS_Form_Element_DatabaseDropdown extends Zend_Form_Element_Xhtml
{

    public $helper = 'databaseDropdown';

    private $_chznOptions = false;

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     *
     * Chosen options can be set in array two ways first by DQL second simple array.
     *
     * To set options by DQL $options array structure should be like:
     *  [
     *      'dql' => "select u.username from \\Entities\\User where u.username IS NOT NULL", //mandatory
     *      'db'  => "default" //Optional if db is not default. Some project may have more then one.
     *  ]
     * 
     * To set options by array $options array structure should be like:
     *  [
     *      'options' => [ 'option1' => 'option1', 'option2' => 'option2', 'option3' => 'option3' ], //mandatory
     *  ]
     *
     *
     * @param  string|array|Zend_Config $spec
     * @return void
     *
     * @see setChosenOptions()
     * @see setChosenOptionsByDql()
     */
    public function __construct( $spec, $options = null )
    {
        
        if( isset( $options['options'] ) )
        {
            $this->setChosenOptions( $options['options'] );
                unset( $options['options'] );
        }
        else if( isset( $options['dql'] ) )
        {
            if( isset( $options['db'] ) )
            {
                $this->setChosenOptionsByDql( $options['dql'], $options['db'] );
                unset( $options['db'] );
            }
            else
                $this->setChosenOptionsByDql( $options['dql'] );

            unset( $options['dql'] );
        }
        parent::__construct( $spec, $options );
        
    }

    /**
     * Sets chosen list options from DQL
     *
     * Queries database with given DQL query then makes a key value array
     * where key equals to value. And then calls setChosesOptions.
     *
     * NOTE: DQL query must request data from only one field. e.g.:
     *  select u.username from \Entities\User u WHERE u.username IS NOT NULL
     *
     * @param  string $dql DQL query to get chosen options. 
     * @param  string $db  Database name if not default, some project may have more then one.
     * @return OSS_Form_Element_DatabaseDropdown
     *
     * @see setChosenOptions()
     */
    public function setChosenOptionsByDql( $dql, $db = 'default' )
    {
        $em = Zend_Registry::get( "d2em" );
        $query = $em[ $db ]->createQuery( $dql );
        $result = $query->getScalarResult();
        if( is_array( $result ) && count( $result ) > 0 )
        {
            $data = array_map( 'current', $result );
            $data = array_combine( $data, $data );
            $this->setChosenOptions( $data );
        }

        return $this;
    }

    /**
     * Sets chosen options array
     *
     * @param  array $options Key value pair options for chosen.
     * @return OSS_Form_Element_DatabaseDropdown
     */
    public function setChosenOptions( $options )
    {
        $options = [ "" => "" ] + $options;

        $this->_chznOptions = $options;
        if( is_array( $options ) && count( $options ) > 1 )
            $this->setAttrib( 'data-osschzn-options', json_encode( $options ) );

        return $this;
    }

    /**
     * Gets chosen options array
     *
     * @return array
     */
    public function getChosenOptions()
    {
        return $this->_chznOptions;
    }
}
