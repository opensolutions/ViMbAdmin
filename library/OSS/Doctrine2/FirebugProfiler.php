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
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Doctrine2
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Doctrine2_FirebugProfiler implements \Doctrine\DBAL\Logging\SQLLogger
{

    /**
     * Sum of query times
     *
     * @var float
     */
    protected $_totalMS = 0;

    /**
     * Total number of queries logged
     *
     * @var integer
     */
    protected $_queryCount = 0;

    /**
     * Table of queries and their times
     *
     * @var \Zend_Wildfire_Plugin_FirePhp_TableMessage
     */
    protected $_message;

    /**
     * Currentquery
     *
     * @var stdClass
     */
    protected $_curQuery = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_message = new \Zend_Wildfire_Plugin_FirePhp_TableMessage( 'Doctrine Queries' );
        $this->_message->setBuffered( false );
        $this->_message->setHeader( array( 'Time', 'Event', 'Parameters' ) );
        $this->_message->setOption( 'includeLineNumbers', true );
        \Zend_Wildfire_Plugin_FirePhp::getInstance()->send( $this->_message, 'Doctrine Queries' );
    }

    /**
     * Starts query
     *
     * @param string $sql The SQL statement that was executed
     * @param array $params Arguments for SQL
     * @param float $executionMS Time for query to return
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->_curQuery = new \stdClass();
        $this->_curQuery->sql = $sql;
        $this->_curQuery->params = $params;
        $this->_curQuery->types = $types;
        $this->_curQuery->startTime = \microtime(true);
    }

    /**
     * Stops query
     *
     * @return void
     */
    public function stopQuery()
    {
        $executionMS = \microtime(true) - $this->_curQuery->startTime;
        $this->_totalMS += $executionMS;
        ++$this->_queryCount;
        $this->_message->addRow(array(
            number_format($executionMS, 5),
            $this->_curQuery->sql,
            $this->_curQuery->params
        ));
        $this->updateLabel();
    }

    /**
     * Sets the label for the FireBug entry
     *
     * @return void
     */
    public function updateLabel()
    {
        $this->_message->setLabel(
            sprintf('Doctrine Queries (%d @ %f sec)',
            $this->_queryCount,
            number_format($this->_totalMS, 5))
        );
    }
}

