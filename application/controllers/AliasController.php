<?php

/**
 * Open Solutions' ViMbAdmin Project.
 *
 * This file is part of Open Solutions' ViMbAdmin Project which is a
 * project which provides an easily manageable web based virtual
 * mailbox administration system.
 *
 * Copyright (c) 2011 - 2014 Open Source Solutions Limited
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
 * @copyright Copyright (c) 2011 - 2014 Open Source Solutions Limited
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 * @author Open Source Solutions Limited <info _at_ opensolutions.ie>
 * @author Barry O'Donovan <barry _at_ opensolutions.ie>
 * @author Roland Huszti <roland _at_ opensolutions.ie>
 */

/**
 * The alias controller.
 *
 * @package ViMbAdmin
 * @subpackage Controllers
 */
class AliasController extends ViMbAdmin_Controller_PluginAction
{

    /**
     * Local store for the form
     * @var ViMbAdmin_Form_Alias_AddEdit
     */
    private $aliasForm = null;

    /**
     * Most actions in this object will require a domain object to edit / act on.
     *
     * This method will look for an 'id' parameter and, if set, will
     * try to load the domain model and authorise the user to edit / act on
     * it.
     *
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        $this->authorise();

        if( $this->getRequest()->getActionName() == "list" || $this->getRequest()->getActionName() == "index" )
        {
            if( $this->getParam( 'unset', false ) )
                unset( $this->getSessionNamespace()->domain );
            else
            {
                if( isset( $this->getSessionNamespace()->domain ) && $this->getSessionNamespace()->domain )
                    $this->_domain = $this->getSessionNamespace()->domain;
                else if( $this->getDomain() )
                    $this->getSessionNamespace()->domain = $this->getDomain();
            }
        }
    }


    /**
     * The index action. Just jumps to list action.
     */
    public function indexAction()
    {
        $this->_forward( 'list' );
    }


    /**
     * Lists the aliases available to the admin and/or domain. Superadmin can see all.
     *
     * $this->view->alias_actions allow to append aliases list action buttons. %id% will be replaced by
     * alias id form the list. below is example array which creates edit alias button, and another button
     * with drop down options for edit alias. Only one drop down button can be defined per button group,
     * and it always be appended at the end.
     * $actions = [
     *       [                          //Simple link button
     *           'tagName' => 'a',     //Mandatory parameter for element type.
     *           'href' => OSS_Utils::genUrl( "alias", "edit" ) . "/alid/%id%", //Url for action
     *           'title' => "Edit",
     *           'class' => "btn btn-mini have-tooltip",  //Class for css options.
     *           'id' => "test-%id%",      //If setting id id must have %id% which will be replaced by original mailbox id to avoid same ids.
     *           'child' => [             //Mandatory element if is not array it will be shown as text.
     *               'tagName' => "i",    //Mandatory option if child is array to define element type
     *               'class' => "icon-pencil"  //Icon class
     *           ],
     *       ],
     *       [                              //Drop down button
     *           'tagName' => 'span',       //Mandatory parameter for element type
     *           'title' => "Settings",
     *           'class' => "btn btn-mini have-tooltip dropdown-toggle", //Class dropdown-toggle is mandatory for drop down button
     *           'data-toggle' => "dropdown",          //data-toggle attribute is mandatory for drop down button
     *           'id' => "cog-%id%",
     *           'style' => "max-height: 15px;",
     *           'child' => [
     *               'tagName' => "i",
     *               'class' => "icon-cog"
     *           ],
     *           'menu' => [        //menu array is mandatory then defining drop down button
     *               [
     *                   'id' => "menu-edit-%id%",   //Not mandatory attribute but if is set %id% should be use to avoid same ids.
     *                   'text' => "<i class=\"icon-pencil\"></i> Edit",                 //Mandatory for display action text
     *                   'url' =>  OSS_Utils::genUrl( "alias", "edit" ) . "/alid/%id%"  //Mandatory to redirect the action.
     *               ]
     *           ]
     *       ]
     *   ];
     *
     */
    public function listAction()
    {
        //Include mailbox aliases
        $this->view->ima = $ima = $this->_getParam( 'ima', 0 );
        $this->view->domain = $this->getDomain();
        if( isset( $this->_options['defaults']['server_side']['pagination']['enable'] ) && $this->_options['defaults']['server_side']['pagination']['enable'] )
            $this->view->aliases = [];
        else
            $this->view->aliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForAliasList( $this->getAdmin(), $this->getDomain(), $ima );

    }

    /**
     * This action is used then server side pagination is turned on. It will look
     * for alias data by filter passed and return json_array if aliases was found
     * or ko if it was not successful. Return array max size is also defined in application.ini.
     */
    public function listSearchAction()
    {
        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
        if( !isset( $this->_options['defaults']['server_side']['pagination']['enable'] ) || !$this->_options['defaults']['server_side']['pagination']['enable'] )
            echo "ko";
        else
        {
            $strl_len = isset( $this->_options['defaults']['server_side']['pagination']['min_search_str'] ) ? $this->_options['defaults']['server_side']['pagination']['min_search_str'] : 3;
            $search = $this->_getParam( "search", false );
            $this->view->ima = $ima = $this->_getParam( 'ima', 0 );
            if( $search && strlen( $search ) >= $strl_len )
            {
                $aliases = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->filterForAliasList( $search, $this->getAdmin(), $this->getDomain(), $ima );
                $max_cnt = isset( $this->_options['defaults']['server_side']['pagination']['max_result_cnt'] ) ? $this->_options['defaults']['server_side']['pagination']['max_result_cnt'] : false;
                if( $aliases && ( !$max_cnt || $max_cnt >= count( $aliases ) ) )
                    echo json_encode( $aliases );
                else
                    echo "ko";
            }
            else
                echo "ko";
        }
    }

    /**
     * Instantiate / get the alias add-edit form
     * @return ViMbAdmin_Form_Mailbox_AddEdit
     */
    public function getAliasForm()
    {
        if( $this->aliasForm == null )
        {
            $form = new ViMbAdmin_Form_Alias_AddEdit();

            // populate the domain dropdown with the possible domains for this user
            $form->getElement( "domain" )->setMultiOptions(
                [ "" => "" ] + $this->getD2EM()->getRepository( "\\Entities\\Domain" )->loadForAdminAsArray( $this->getAdmin(), true )
            );

            // if we have a default / preferred domain, set it as selected in the form
            if( $this->getDomain() )
                $form->getElement( 'domain' )->setValue( $this->getDomain()->getId() );

            if( $this->isEdit() )
            {
                $form->assignEntityToForm( $this->getAlias(), $this, $this->isEdit() );
                $form->removeElement( 'local_part' );
                $form->removeElement( 'domain' );
            }

            $this->view->form = $this->aliasForm = $form;

            // call plugins
            $this->notify( 'alias', 'add', 'formPostProcess', $this );
        }

        return $this->aliasForm;
    }

    /**
     * Add an alias.
     */
    public function addAction()
    {
        if( !$this->getAlias() )
        {
            $this->isEdit = $this->view->isEdit = false;
            $form = $this->getAliasForm();

            $this->_alias = new \Entities\Alias();
            $this->getD2EM()->persist( $this->getAlias() );
            $this->getAlias()->setCreated( new \DateTime() );
        }
        else
        {
            $this->isEdit = $this->view->isEdit = true;
            $this->view->alias = $this->getAlias();

            $form = $this->getAliasForm();
            $form->assignEntityToForm( $this->getAlias(), $this, $this->isEdit() );
        }

        $this->view->domainList = $domainList = $this->getD2EM()->getRepository( "\\Entities\\Domain" )->loadForAdminAsArray( $this->getAdmin(), true );

        if( $this->getDomain() )
        {
            $this->view->domain = $this->getDomain();
            if( $form->getElement( 'domain' ) )
                $form->getElement( 'domain' )->setValue( $this->getDomain()->getId() );
        }

        if( $this->getMailbox() )
            $this->view->defaultGoto = "{$this->getMailbox()->getLocalPart()}@{$this->getDomain()->getDomain()}";

        $this->view->alias = $this->getAlias();
        $this->view->emails = $this->_autocompleteArray();

        $this->notify( 'alias', 'add', 'addPrepare', $this );

        if( $this->getRequest()->isPost() )
        {
            $this->notify( 'alias', 'add', 'addPrevalidate', $this );

            if( $form->isValid( $_POST ) )
            {
                $this->notify( 'alias', 'add', 'addPostvalidate', $this );

                $form->assignFormToEntity( $this->getAlias(), $this, $this->isEdit() );

                if( !$this->_setGotos( $form ) )
                    return;

                if( !$this->isEdit() ) // adding
                {
                    if( !$this->getDomain() || $this->getDomain()->getId() != $form->getValue( 'domain' ) )
                        $this->_domain = $this->loadDomain( $form->getValue( 'domain' ) );

                    // do we have available aliases?
                    if( !$this->getAdmin()->isSuper() && $this->getDomain()->getMaxaliases() != 0
                            && $this->getDomain()->getAliasCount() >= $this->getDomain()->getMaxAliases()
                        )
                    {
                        $this->addMessage( _( 'You have used all of your allocated aliases.' ), OSS_Message::ERROR );
                        $this->redirect( "alias/list" );
                    }

                    $this->getAlias()->setDomain( $this->getDomain() );
                    $this->getAlias()->setActive( 1 );

                    if( !$this->_setAddress( $form ) )
                        return;
                }
                else
                    $this->getAlias()->setModified( new \DateTime() );

                if( !$this->isEdit() && $this->getAlias()->getAddress() != $this->getAlias()->getGoto() )
                    $this->getDomain()->setAliasCount( $this->getDomain()->getAliasCount() + 1 );

                $this->log(
                    $this->isEdit() ? \Entities\Log::ACTION_ALIAS_EDIT : \Entities\Log::ACTION_ALIAS_ADD,
                    "{$this->getAdmin()->getFormattedName()} " . ( $this->isEdit() ? ' edited' : ' added' ) . " alias {$this->getAlias()->getAddress()}"
                );

                $this->notify( 'alias', 'add', 'addPreflush', $this );
                $this->getD2EM()->flush();
                $this->notify( 'alias', 'add', 'addPostflush', $this );

                if( $this->getParam( "did", false ) )
                    $this->getSessionNamespace()->domain = $this->getDomain();

                $this->addMessage( _( "You have successfully added/edited the alias." ), OSS_Message::SUCCESS );
                $this->redirect( 'alias/list' );
            }
        }
    }

    /**
     * Edit an alias.
     */
    public function editAction()
    {
        $this->forward( "add" );
    }


    /**
     * Toggles the active property of an alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function ajaxToggleActiveAction()
    {
        if( !$this->getAlias() )
            print 'ko';

        $this->getAlias()->setActive( !$this->getAlias()->getActive() );
        $this->getAlias()->setModified( new \DateTime() );

        $this->log(
            $this->getAlias()->getActive() ? \Entities\Log::ACTION_ALIAS_ACTIVATE : \Entities\Log::ACTION_ALIAS_DEACTIVATE,
            "{$this->getAdmin()->getFormattedName()} " . ( $this->getAlias()->getActive() ? 'activated' : 'deactivated' ) . " alias {$this->getAlias()->getAddress()}"
        );
        $this->notify( 'alias', 'toggleActive', 'preflush', $this, [ 'active' => $this->getAlias()->getActive() ] );
        $this->getD2EM()->flush();
        $this->notify( 'alias', 'toggleActive', 'postflush', $this, [ 'active' => $this->getAlias()->getActive() ] );
        print 'ok';
    }


    /**
     * Deletes an alias. Prints 'ok' on success or 'ko' otherwise to stdout.
     */
    public function deleteAction()
    {
        if( !$this->getAlias() )
            print 'ko';

        foreach( $this->getAlias()->getPreferences() as $pref )
                $this->getD2EM()->remove( $pref );

        $this->notify( 'alias', 'delete', 'preRemove', $this );
        $this->getD2EM()->remove( $this->getAlias() );
        if( $this->getAlias()->getAddress() != $this->getAlias()->getGoto() )
            $this->getDomain()->setAliasCount( $this->getDomain()->getAliasCount() - 1 );

        $this->log(
            \Entities\Log::ACTION_ALIAS_DELETE,
            "{$this->getAdmin()->getFormattedName()} removed alias {$this->getAlias()->getAddress()}"
        );

        $this->notify( 'alias', 'delete', 'preFlush', $this );
        $this->getD2EM()->flush();
        $this->notify( 'alias', 'delete', 'postFlush', $this );

        $this->addMessage( 'Alias has bean removed successfully', OSS_Message::SUCCESS );
        $this->redirect( 'alias/list' );
    }

    /**
     * Sets goto to alias
     *
     * Parse goto value form field. If its empty return false and set error message.
     * Function goes trough all goto address and if one of got addresses is not valid
     * set error to form and return false. If everything is ok it return true. Gotos
     * Is set to alias even the false is return to return goto addresses to end user.
     *
     *
     * @param ViMbAdmin_Form_Alias_AddEdit $form Alias form
     * @return bool
     */
    private function _setGotos( $form )
    {
        if( !$form->getValue( 'goto' ) )
        {
            $form->getElement( 'goto' )->addError( _( 'You must have at least one goto address.' ) );
            $this->getAlias()->setGoto( "" );
            return false;
        }
        else
        {
            $gotos = $form->getValue( 'goto' );
            $this->getAlias()->setGoto( implode( ',', array_unique( $gotos ) ) );
            foreach( $gotos as $key => $goto )
            {
                $goto = trim( $goto );

                if( $goto == '')
                    unset( $gotos[ $key ] );
                else
                {
                    if( substr( $goto, 0, 1 ) != '@' && !Zend_Validate::is( $goto, 'EmailAddress', array( 1, null ) ) )
                    {
                        $form->getElement( 'goto' )->addError( 'Invalid email address(es).' );
                        return false;
                    }
                }
            }

            if( count( $gotos ) == 0 )
            {
                $form->getElement( 'goto' )->addError( 'You must have at least one goto address.' );
                return false;
            }
            $this->getAlias()->setGoto( implode( ',', array_unique( $gotos ) ) );

        }
        return true;
    }


    /**
     * Sets address to alias
     *
     * Checks if local part and domain makes a valid address, or it is only domain address.
     * If yes then it checks if its unique address, if yes address set to alias and return true.
     * All other cases message is set to form and return false.
     *
     * @param ViMbAdmin_Form_Alias_AddEdit $form Alias form
     * @return bool
     */
    private function _setAddress( $form )
    {
        $address = sprintf( "%s@%s", $form->getValue( 'local_part'), $this->getDomain()->getDomain() );

        // is the alias valid (allowing for wildcard domains (i.e. with no local part)
        if( $form->getValue( "local_part" ) &&  !Zend_Validate::is( "{$address}", 'EmailAddress', array( 1, null ) ) )
        {
            $form->getElement( 'local_part' )->addError( _( 'Invalid email address.' ) );
            return false;
        }

        $alias = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->findOneBy( ["address" => $address ] );
        if( $alias )
        {
            if( $this->_options['mailboxAliases'] )
            {
                if( $alias->getAddress() == $alias->getGoto() )
                    $msg = _( 'A mailbox alias exists for' ) . " {$address}";
                else
                    $msg =  _( 'Alias already exists for' ) . " {$address}";
            }
            else
                $msg = _( 'Alias already exists for' ) . " {$address}";

            $this->addMessage( $msg, OSS_Message::ERROR );

            //check if it works correctly.
            return false;
        }
        $this->getAlias()->setAddress( $address );
        return true;
    }

    /**
     * The Ajax function providing JSON data for the jQuery UI Autocomplete on adding/editing aliases.
     */
    private function _autocompleteArray()
    {
        $aliases   = $this->getD2EM()->getRepository( "\\Entities\\Alias" )->loadForAliasList( $this->getAdmin(), null, true );
        $addresses = [];

        foreach( $aliases as $alias )
            $addresses[] = $alias['address'];

        return json_encode( $addresses );
    }

}
