<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2012 Open Source Solutions Limited
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
 * @package ViMbAdmin
 * @subpackage Library
 */
class ViMbAdmin_Form extends Twitter_Form
{
    use OSS_Form_Trait;
    use OSS_Form_Trait_CancelLocation;
    use OSS_Form_Trait_IsEdit;
    use OSS_Form_Trait_GenericElements;
    use OSS_Form_Trait_InsertElementFns;
    use OSS_Form_Trait_Doctrine2;
                        
                        
    /**
     * Constructor
     *
     * @param  null|array $options
     */
    public function __construct( $options = null )
    {
        $this->setAttrib( 'accept-charset', 'UTF-8' );
        $this->setMethod( 'post' );
        $this->setAttrib( 'horizontal', true );
        $this->setAttrib( 'id', 'vimbadmin_form' );  
        $this->setAttrib( 'name', 'vimbadmin_form' );
        
        $this->addElementPrefixPath( 'OSS_Filter',   'OSS/Filter/',   'filter' );
        $this->addElementPrefixPath( 'OSS_Validate', 'OSS/Validate/', 'validate' );
                                                                                                                                
        if( method_exists( $this, 'initialiseTraits' ) )
            $this->initialiseTraits( $options );
                                                                                                                                                            
        parent::__construct( $options );
    }

    /**
     * Adds element to actions display group.
     *
     * If view script is false than it proceed element like Twitter_Form::_addActionsDisplayGroupElement.
     * if view script existent, that mean element in action group is placed by viewscript, function just adds
     * element to form and removes it decorators, to avoid bad spacing.S
     *
     * @param Zend_Form_Element $element Form element to place on action display group.
     * @param bool $viewscript Defines if element will be placed in form by viewscript or not.
     * @return PBX_Form
     */
    protected function _addActionsDisplayGroupElement( $element, $viewscript = false )
    {
        if( !$element instanceof Zend_Form_Element && $name )
        {
            $element = $this->getElement( $name );
        }
        else
        {
            $element->clearDecorators();
            $element->setDecorators( $this->_getElementDecorators() );
        }

        if( !$viewscript )
        {
            $element->removeDecorator( "Label" );
            $element->removeDecorator( "outerwrapper" );
            $element->removeDecorator( "innerwrapper" );

            $displayGroup = $this->getDisplayGroup( "zfBootstrapFormActions" );

            if( $displayGroup === null )
            {
                $displayGroup = $this->addDisplayGroup(
                    [ $element ],
                    "zfBootstrapFormActions",
                    [
                        "decorators" => [
                            "FormElements",
                            [ "HtmlTag", [ "tag" => "div", "class" => "form-actions" ] ]
                        ]
                    ]
                );
            }
            else
                $displayGroup->addElement($element);
        }
        else
        {
            $this->addElement( $element );
            $element->removeDecorator( "Label" );
            $element->removeDecorator( "outerwrapper" );
            $element->removeDecorator( "innerwrapper" );

        }

         return $this;
    }
}

