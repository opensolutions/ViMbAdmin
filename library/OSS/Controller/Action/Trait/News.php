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
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */


/**
 * Controller: Action - Trait for Freshbooks
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_News
{

    /**
     * @var object An instant of news object
     */
    protected $_news = null;
    
    
    /**
     * The trait's initialisation method.
     *
     * This function is called from the Action's contructor and it passes those
     * same variables used for construction to the traits' init methods.
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function OSS_Controller_Action_Trait_News_Init( $request, $response, $invokeArgs )
    {
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_News' );
    }
    
    /**
     * Gets the News Resource (which is a load ondemand resource)
     *
     * Then sets the local `$this->_news` to the RSS feed of all the configured
     * channels in `application.ini:ondemand_rescoures.news`.
     *
     * Access a specific channel via: `getNews( $channel )`
     *
     * @return array|Zend_Feed_Rss The news object for the given channel or all news channels
     */
    protected function getNews( $type = null )
    {
        if( $this->_news === null )
        {
            // first, see if we have it cached
            if( ( $this->_news = $this->getD2Cache()->fetch( 'ondemand_resources.news' ) ) === false )
            {
                $news = new OSS_Resource_News( $this->getOptions()['ondemand_resources']['news'] );
                $this->getBootstrap()->registerPluginResource( $news );
    
                // $this->_news = $this->getBootstrap()->getPluginResource( 'news' )->getNews();
                $this->_news = $news->getNews();
    
                // store it in the cache also
                $this->getD2Cache()->save( 'ondemand_resources.news', $this->_news, $this->getOptions()['ondemand_resources']['news']['cache_period'] );
            }
        }
    
        if( $type === null )
            return $this->_news;
        else
            return $this->_news[ $type ]['channel'];
    }
    
    
    /**
     * Returns an key field name
     *
     * @return string key field name
     */
    protected function getNewsKey( $type = 'general' )
    {
        return $this->getNews( )[ $type ]['key'];
    }
    
    
    // FIXME Does this have any business being here?
    protected function checkNews( $user )
    {
        $unread = 0;
        $max = intval( $this->_options['news']['max_news_items'] );
        $user_read_items = is_array( $user->getIndexedPreference( 'news.general.read' ) )? $user->getIndexedPreference( 'news.general.read' ) : array( $user->getIndexedPreference( 'news.general.read' ) );
    
        $key = $this->getNewsKey();
        foreach( $this->getNews( 'general' ) as $item )
        {
            if( $item->$key() == $user->getPreference( 'news.general.last_seen' ) )
                break;
    
            if( in_array( $item->$key(), $user_read_items ) )
                break;
    
            if( $unread >= $this->_options['news']['max_news_items'] )
                break;
    
            $unread++;
        }
        $this->getSessionNamespace()->unread_news = $unread;
    
        $key = $this->getNewsKey( 'alerts' );
        $alerts = $this->getNews( 'alerts' );
    
        if( $alerts )
        {
            while($alerts->current()->$key() != $user->getPreference( 'news.alerts.last_seen' ) && $alerts->current()->$key() )
            {
                $this->getSessionNamespace()->unread_alert = $alerts->current()->$key();
                $alerts->next();
            }
        }
    }
    
    
}

