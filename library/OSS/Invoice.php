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
 * @package    OSS_Invoice
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * Invoice class.
 *
 * NOTICE: Supports only Doctrine2 database engine.
 *
 * @category   OSS
 * @package    OSS_Invoice
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Invoice
{

    /**
     * Getting Doctrine2 entity manager
     *
     * @return EntityManager
     */
    private static function getStaticD2EM()
    {
        return Zend_Registry::get( "d2em" )[ 'default' ];
    }

    /**
    * Returns with a simple statistics about non- and partially paid invoices as an associative array in which the key
    * 'sum_total' contains the sum of the total to be paid, 'sum_received' the sum of the already received money and 'howmany'
    * the number of invoices.
    *
    * @param \Entities\Customer $customer
    * @return array
    */
    public static function getOutstandingStat( $customer = null )
    {
        $qb = self::getStaticD2EM()->createQueryBuilder()
            ->select( 'sum( i.total ) as sum_total, sum( i.received ) as sum_received, count( i.id ) as howmany' )
            ->from( '\\Entities\\Invoice', "i" )
            ->where( 'i.total > i.received' )
            ->orWhere( 'i.received IS NULL' );

        if( $customer )
            $qb->andWhere( 'i.Customer = ?1' )
                ->setParameter( 1, $customer );

        return $qb->getQuery()->getResult()[0];
    }


    /**
    * Returns with the non- and partially paid invoices as a Doctrine collection object. The Item objects in the collection
    * are sorted by the due date, ascending.
    *
    * @param \Entities\Customer $customer default null if not null then will limit the serach to invoices for a given user
    * @param string $maxDate default null if not null then will limit the serach to invoices having tax_date <= $pMaxDate, must be in "YYYY-MM-DD" format
    * @param string $orderBy Order by field
    * @param string $orderByDir Order by direction e.g. ASC, DESC
    * @return object
    */
    public static function getOutstanding( $customer = null, $maxDate = null, $orderBy = 'due_date', $orderByDir = "ASC" )
    {
        $qb = self::getStaticD2EM()->createQueryBuilder()
            ->select( 'i.*' )
            ->from( '\\Entities\\Invoice', "i" )
            ->where( 'i.received < i.total' )
            ->orWhere( 'i.received IS NULL' );

        if( $customer )
            $qb->andWhere( 'i.Customer = ?1' )
                ->setParameter( 1, $customer );

        if( $maxDate )
            $qb->andWhere( 'i.tax_date <= ?2' )
                ->setParameter( 2, $maxDate );

        if( $orderBy )
            $qb->orderBy( $pOrderBy, $orderByDir );

        return $qb->getQuery()->getResult();
    }


    /**
    * Returns with the sum of received amount from all invoices.
    *
    * @param \Entities\Customer|null $customer Customer for filtering invoice
    * @return float
    */
    public static function getReceivedSumValue( $customer = null )
    {
        $qb = self::getStaticD2EM()->createQueryBuilder()
            ->select( 'sum( i.received) as i.sum_received' )
            ->from( '\\Entities\\Invoice', 'i' );

        if( $customer )
            $qb->andWhere( 'i.Customer = ?', $customer );

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
    * Returns with a Doctrine_Collection object of all the invoices of which the
    * start_date and end_date falls between $minDate and $maxDate respectively, ordered by start_date asc.
    *
    * @param string $minDate
    * @param string $maxDate
    * @param \Entities\Customer|null $customer Customer for filtering invoices.
    * @return object Doctrine_Collection
    */
    public static function getInvoicesBetweenStartEnd( $minDate, $maxDate, $customer = null )
    {
        $qb = self::getStaticD2EM()->createQueryBuilder()
            ->select( 'i.*' )
            ->from( '\\Entities\\Invoice', 'i' )
            ->where( 'i.start_date >= ?1' )
            ->andWhere( 'i.end_date <= ?2' )
            ->setParameter( 1, $minDate )
            ->setParameter( 2, $maxDate );

        if( $customer )
            $qb->andWhere( 'i.Customer = ?3' )
                ->setParameter( 3, $customer );

        $qb->orderBy( 'i.start_date', 'ASC' );

        return $qb->getQuery()->getResult();
    }


    /**
    * Returns with a Doctrine_Collection object of all the invoices for which the start_date and end_date are in this month,
    * ordered by start_date asc. Calls getInvoicesBetweenStartEnd() .
    *
    * @param \Entities\Customer|null $customer Customer for filtering invoices.
    * @return object Doctrine_Collection
    * @see self::getInvoicesBetweenStartEnd
    */
    public static function getInvoicesFromThisMonth( $customer = null )
    {
        return self::getInvoicesBetweenStartEnd( date( "Y-m-01" ), $date( "Y-m-t" ), $customer );
    }


    /**
     * Returns with this month's invoice to add new invoice items to. If it does not exist then creates one.
     * Returns with an Invoice model object.
     *
     * @param \Entities\Customer $customer Invoice for customer
     * @return object Invoice model
     * @see self::getInvoicesFromThisMonth
     */
    public static function getCurrentInvoice( $customer )
    {
        $invoices = self::getInvoicesFromThisMonth( $customer );

        if( $invoice->id == 0 )
        {
            $today = new \DateTime();
            $taxDate = clone $today;
            $taxDate->add( new DateInterval( 'P1M' ) );

            $invoice = $customer->createInvoice( $taxDate )
                ->setPeriod( $today, $taxDate )
                ->setDueDate( $today );
            $invoice->addEvent( \Entities\INVOCE_EVENT::EVENT_CREATED );
        }
        else
        {
            $invoice = $invoices[0];
        }

        return $invoice;
    }

    /**
     * Returns unpaid invoices.
     * If $customer not null it will filter unpaid invoices for customer.
     *
     * @param \Entites\Customer|null $customer Customer for invoice filtering.
     * @return array
     */
    public static function getUnpaidInvoices( $customer = null )
    {
        $qb = self::getStaticD2EM()->createQueryBuilder()
            ->select( 'i.*' )
            ->from( '\\Entities\\Invoice', 'i' )
            ->where( "i.paid_date IS NULL" )
            ->orderBy( 'i.start_date', 'ASC' );

        if( $customer )
            $qb->andWhere( 'i.Customer = ?1' )
                ->setParam( 1, $customer );

        return $qb->getQuery()->getResult();

    }
}

