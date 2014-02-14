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

/**
 * The form for adding and editing mailboxes.
 *
 * @package ViMbAdmin
 * @subpackage Form
 */
class ViMbAdmin_Form_Mailbox_AdditionalInfo extends ViMbAdmin_Form_Plugin
{  
    public function init()
    {
        $this->setDecorators( [ [ 'ViewScript', [ 'viewScript' => 'mailbox/form/additional-info.phtml' ] ] ] );
        $this->setAttrib( 'id', 'additional_info' )
            ->setAttrib( 'title', 'Additional Inforamation' );
   
    }
    
    
    /**
     * create elements from elements array. 
     *
     * Elements array is parsed form application.ini file, the structre is given in example below.
     *
     * e.g. This structure will define element plugin_additionalInfo_ext_no which is required,
     * have Ext No. label, have digits validation and contains exactly for digits. Field id and name
     * will be array index next of elements, also it can be defined in array and it will be overwritten.
     * 
     * Defining elemnts in application.ini structure:
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.type = "Zend_Form_Element_Text"
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.required = true
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.label = "Ext No."
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.digits[] = 'Digits'
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.digits[] = true
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.length[] = 'StringLength'
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.length[] = false
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.length.range[] = 4
     *      vimbadmin_plugins.AdditionalInfo.elements.ext_no.options.validators.length.range[] = 4
     *
     * @param array $elements Elments array for controller configurations array.
     * @param null|\Entitites\Mailbox $mailbox Mailbox to set defualt values to form.
     */    
    public function createElements( $elements, $mailbox = null )
    {
        foreach( $elements as $name => $element )
        {
            $element['options']['id'] = isset( $element['options']['id'] ) ? 'plugin_additionalInfo_' . $element['options']['id'] : 'plugin_additionalInfo_' . $name;
            $element['options']['name'] = isset( $element['options']['name'] ) ? $element['options']['name'] : $element['options']['id'];
            if( $mailbox && $mailbox->getPreference( 'xpiInfo.' . $name ) )
                $element['options']['value'] = $mailbox->getPreference( 'xpiInfo.' . $name );
                
            $elobj = new $element['type']( $element['options']['id'], $element['options'] );
            $this->addElement( $elobj );
        }
    }
    
}
