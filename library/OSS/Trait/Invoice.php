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
 * @package    OSS_Trait
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * Invoice trait.
 *
 * NOTICE: Supports only Doctrine2 database engine.
 *
 * @category   OSS
 * @package    OSS_trait
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Trait_Invoice
{
    /**
     * The Doctrine2 entity manager
     * @var EntityManager The Doctrine2 entity manager
     */
    private $_d2em = null;

    /**
     * Getting Doctrine2 entity manager
     *
     * @return EntityManager
     */
    protected function getD2EM()
    {
        if( !$this->_d2em )
            $this->_d2em = Zend_Registry::get( "d2em" )[ 'default' ];

        return $this->_d2em;
    }

    /**
     * Persisting invoice
     *
     * @return void
     */
    public function persist()
    {
        $this->getD2EM()->persist( $this );
    }
    

    /**
     * Cycles through all the invoice entries for the current invoice, calculates the subtotal,
     * vat and total values and sets them to invoice. Returns with the updated OSS_Invoice object.
     *
     * @param void
     * @return \Entities\Invoice
     */
    public function updateInvoice()
    {
        $subTotals = 0;
        $tax = 0;
        $totals = 0;

        foreach( $this->getEntries() as $entry )
        {
            $subTotals += $entry->getSubtotal();
            $tax += $entry->getTax();
            $totals += $entry->getTotal();
        }

        $this->setSubtotal( $subTotals );
        $this->setTax( $tax );
        $this->setTotal( $totals );

        return $this;
    }

    /**
     * Finalises an invoice (sets the finalised date and the subtotal, vat and total fields) and returns with the updated OSS_Invoice object.
     * Calls updateInvoice() for the calculation.
     * WARNING: IT IS REALLY DRIVER-BASED, IT WILL FAIL FOR CUSTOMERS.
     *
     * @param void
     * @return \Entities\Invoice
     */
    public function finalise()
    {
        $this->setFinalised( new \DateTime() );
        $this->updateInvoice();

        $this->addEvent( \Entities\InvoiceEvent::EVENT_FINALISED );

        return $this;
    }

    /**
     * Updates the received field by $amount, and if received >= total then sets the paid_date to
     * the current date and time. Returns with the updated Invoice model ($this).
     *
     * @param int|float $amount Received amount
     * @return \Entities\Invoice
     */
    public function updateReceivedBy( $amount )
    {
        $this->setReceived( $this->getReceived() + $amount );
        return $this;
    }

    /**
     * Set start date and end date for invoice
     *
     * @param DateTime $start Start date
     * @param DateTime $end End date
     * @return \Entities\Invoice
     */
    public function setPeriod( $start, $end )
    {
        $this->setStartDate( $start );
        $this->setEndDate( $end );
        return $this;
    }


    /**
     * Returns with the number of days the invoice is overdue.
     *
     * @return int
     */
    public function overdueDays()
    {
        return OSS_DateTime::dateDiffDays( $this->getDueDate()->format( "Y-m-d" ), null, false );
    }


    /**
     * Adds a new event to the invoice and returns with the OSS_Invoice object.
     *
     * @param string $type Type of event
     * @param string $data default null
     * @return \Entities\Invoice
     */
    public function addEvent( $type, $data = null )
    {
        $event = new \Entities\InvoiceEvent();
        $event->setEvent( $type );
        $event->setCreated( new \DateTime() );
        $event->setInvoice( $this );

        if( $data )
            $event->setData( $data );

        $this->getD2EM()->persist( $event );
        $this->addInvoiceEvent( $event );

        return $this;
    }


    /**
     * Sets the paid_date of the invoice, saves to the database and returns with the updated Invoice 
     * model.
     *
     * If $paidDate is null then the current date and time will be used, otherwise expects a "yyyy-mm-dd"
     * or "mm/dd/yyyy" (ISO or USA) date (with optional "hh:mm:ss" time part)
     *
     * @param datetime|null $paidDate Paid Date
     * @return \Entities\Invoice
     */
    public function setPaidDate( $paidDate = null )
    {
        $this->setPaidDate( !$paidDate ? new \DateTime() : $paidDate );
        return $this;
    }


    /**
     * Adds an invoice item to the invoice and returns with the new InvoiceItem model object. Calls InvoiceItem::addItem() .
     *
     * If vat code is not passed it will take first latest VAT Rate from item's category.
     *
     * @param string $itemCode Item code to add in invoice.
     * @param int $quantity Quantity of items.
     * @param int|float|null $cost If not null then it will overwrite the items default price
     * @param string $vatCode VAT code to set vat for item.
     * @return \Entities\Invoice
     */
    public function addItem( $itemCode, $quantity = 1, $cost = null, $vatCode = null )
    {
        if( $this->getFinalised() )
            throw new Exception( 'Invoice is finalised, you cannot add new items to it.' );

        $query = $this->getD2EM()->createQuery( sprintf( "SELECT it FROM \\Entities\\InvoiceItem it  WHERE it.code = '%s' ORDER BY it.created DESC", $itemCode) )
                ->setMaxResults( 1 );
        $item = $query->getResult();


        if( !$item )
            throw new Exception( sprintf( "Item with code '%s' is not existent.", $itemCode ) );
        else
            $item = $item[0];

        if( $vatCode )
        {
            $query = $this->getD2EM()->createQuery( sprintf( "SELECT tx FROM \\Entities\\InvoiceTaxRate tx WHERE tx.category = '%s' AND tx.code = %s ORDER BY tx.effective_from DESC", $item->getTaxCategory(), $vatCode  ) )
                ->setMaxResults( 1 );
        }
        else
        {
            $query = $this->getD2EM()->createQuery( sprintf( "SELECT tx FROM \\Entities\\InvoiceTaxRate tx WHERE tx.category = '%s' ORDER BY tx.effective_from DESC", $item->getTaxCategory() ) )
                ->setMaxResults( 1 );
        }
        $taxRate = $query->getResult();

        if( !$taxRate )
            throw new Exception( sprintf( "Tax rate with code '%s' is not existent.", $vatCode ) );
        else
            $taxRate = $taxRate[0];

        $cost = !$cost ? $item->getCost() : $cost;
        $subtotal = round( $cost * $quantity, 2 );
        $tax = round( $subtotal * $taxRate->getRate(), 2 );

        $entry = new \Entities\InvoiceEntry();
        $entry->setInvoice( $this );
        $entry->setQuantity( $quantity );
        $entry->setUnitCost( $cost );
        $entry->setItem( $item );
        $entry->setTaxRate( $taxRate );
        $entry->setSubtotal( $subtotal );
        $entry->setTax( $tax );
        $entry->setTotal( $subtotal + $tax );
        $entry->setCreated( new \DateTime() );

        $this->getD2EM()->persist( $entry );
        $this->addInvoiceEntry( $entry );

        $this->updateInvoice();
        
        return $this;
    }
    
    /**
     * Gets latest event for current invoice.
     *
     * @return \Entities\InvoiceEvent|bool
     */
    public function getLastEvent()
    {
        $query = $this->getD2EM()->createQuery( "SELECT e FROM \\Entities\\InvoiceEvent e  WHERE e.Invoice = ?1 ORDER BY e.created DESC, e.id DESC" )
                ->setParameter( 1, $this )
                ->setMaxResults( 1 );
        $event = $query->getResult()[0];

        return isset( \Entities\InvoiceEvent::$EVENTS[ $event->getEvent() ] ) ? \Entities\InvoiceEvent::$EVENTS[ $event->getEvent() ] : false;
    }

    /**
     * Gets invoice VAT amounts grouped by categories.
     *
     * Returned array structure:
     * $vats = [
     *      "S" => [ "rate" => "21.0", "15.14" ],
     *      "E" => [ "rate" => "15.0", "0" ]
     *  ]
     *
     * @return array
     */
    public function getTaxDetailed()
    {
        $vats = [];
        foreach( $this->getEntries() as $entry )
        {
            $rate = $entry->getTaxRate();
            if( !isset( $vats[ $rate->getCategory() ] ) )
                $vats[ $rate->getCategory() ] = [ "rate" => sprintf( "%.1f", $rate->getRate() * 100 ), "amount" => 0 ];

            $vats[ $rate->getCategory() ][ "amount" ] += $entry->getTax();
        }

        return $vats;
    }
}

?>