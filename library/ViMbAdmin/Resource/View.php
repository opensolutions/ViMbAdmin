<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 Open Source Solutions Limited
 *
 * ViMbAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ViMbAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ViMbAdmin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Open Source Solutions Limited T/A Open Solutions
 *   147 Stepaside Park, Stepaside, Dublin 18, Ireland.
 *   Barry O'Donovan <barry _at_ opensolutions.ie>
 *
 * @copyright Copyright (c) 2011 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/*
 * The Zend view resource.
 *
 * @package ViMbAdmin
 * @subpackage Resource
 */
class ViMbAdmin_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the View instance
     *
     * @var
     */
    protected $_view;


    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }


    public function getView()
    {
        // Get session configuration options from the application.ini file
        $options = $this->getOptions();

        if( $options['enabled'] )
        {
            if( null === $this->_view ) // this cannot be &&'d with the above!
            {
                // Initialize view
                $view = new Zend_View();
                $view->doctype( $options['doctype'] );
                $view->headTitle( $options['title'] );

                // Add it to the ViewRenderer
                $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper( 'ViewRenderer' );
                $viewRenderer->setView( $view );

                Zend_Layout::startMvc(
                    array(
                        'layout'     => 'layout',
                        'layoutPath' => APPLICATION_PATH . '/layouts/scripts',
                    )
                );

                $this->_view = $view;
            }

            return $this->_view;
        }
    }
}
