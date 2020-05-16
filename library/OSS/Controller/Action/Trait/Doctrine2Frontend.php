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
 * Controller: Action - Trait for Doctine2Frontend
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller_Action_Traits
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
trait OSS_Controller_Action_Trait_Doctrine2Frontend
{
    /**
     * Parameters used by the frontend controller
     * @var array Parameters used by the frontend controller
     */
    protected $_feParams = null;

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
    public function OSS_Controller_Action_Trait_Doctrine2Frontend_Init( $request, $response, $invokeArgs )
    {
        // is this controller disabled?
        if( isset( $this->_options['frontend']['disabled'][ $this->getRequest()->getControllerName() ] )
            && $this->_options['frontend']['disabled'][ $this->getRequest()->getControllerName() ] )
        {
            $this->addMessage( _( 'This controller has been disabled.' ), OSS_Message::ERROR );
            $this->redirectAndEnsureDie( '' );
        }
         
        $this->_feInit();
        $this->view->FE_COL_TYPES = self::$FE_COL_TYPES;
        
        // check is this action is allowed
        if( isset( $this->_feParams->allowedActions ) && !in_array( $request->getActionName(), $this->_feParams->allowedActions ) )
            $this->redirectAndEnsureDie( 'error/insufficient-permissions' );
        
        $this->traitSetInitialised( 'OSS_Controller_Action_Trait_Doctrine2Frontend' );
    }
    
    static public $FE_COL_TYPES = [
        'HAS_ONE'  => 'hasOne',
        'DATETIME' => 'datetime',
        'DATE'     => 'date',
        'TIME'     => 'time',
        'SCRIPT'   => 'script',
        'XLATE'    => 'xlate',
        'YES_NO'   => 'yes_no'
    ];
    
    
    /**
     * Standard initialisation tasks for all Frontend controllers
     */
    public function init()
    {
    }
    
    /**
     * This is meant to be overridden.
     *
     * @throws OSS_Exception
     */
    protected function _feInit()
    {
        throw new OSS_Exception( 'FrontEnd controllers require an _feInit() function' );
    }
    
    /**
     * Displays the standard Frontend template or the controllers overridden version.
     *
     * @see _resolveTemplate()
     * @param string $tpl The template to display
     * @return void
     */
    protected function _display( $tpl )
    {
        $this->view->display( $this->_resolveTemplate( $tpl, true ) );
        
        // we don't want the template selected automatically here - we'll choose it outselves
        Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
    }
    
    /**
     * Resolves the standard Frontend template or the controllers overridden version.
     *
     * All frontend actions have their own template: `frontend/{$action}.phtml` which is
     * displayed by default. You can however override these by creating a template named:
     * `{$controller}/{$action}.phtml`. This function looks for an overriding template
     * and displays that if it exists, otherwise it displays the default.
     *
     * This will also work for subdirectories: e.g. `$tpl = forms/add.phtml` is also valid.
     *
     * @param string $tpl The template to display
     * @param bool $throw If true, throws an exception is no template is found
     * @return string|bool The template to use of false if none found
     * @throws OSS_Exception
     */
    protected function _resolveTemplate( $tpl, $throw = false )
    {
        if( $this->view->templateExists( $this->getRequest()->getControllerName() . "/{$tpl}" ) )
            return $this->getRequest()->getControllerName() . "/{$tpl}";
        else if( $this->view->templateExists( "frontend/{$tpl}" ) )
            return "frontend/{$tpl}";
        
        if( $throw )
            throw new OSS_Exception( sprintf( _( "No template exists in frontend or controller's view directory for %s "), $tpl ) );
        
        return false;
    }
    
    
    /**
     * The index / default action _forwards to the default action as configured
     * or else the list action by default.
     *
     * Looks for a specific action in `$_feParams['defaultAction']`
     */
    public function indexAction()
    {
        if( $this->feGetParam( 'defaultAction' ) )
            $this->_forward( $this->feGetParam( 'defaultAction' ) );
        else
            $this->_forward( 'list' );
    }

    /**
     * Function which can be over-ridden to perform any pre-list tasks
     *
     * @return void
     */
    protected function listPreamble()
    {}
    
    
    /**
     * List the contents of a database table.
     */
    public function listAction()
    {
        $this->listPreamble();
        
        $this->view->data = $this->listGetData();
        
        $this->view->listPreamble    = $this->_resolveTemplate( 'list-preamble.phtml'  );
        $this->view->listPostamble   = $this->_resolveTemplate( 'list-postamble.phtml' );
        $this->view->listRowMenu     = $this->_resolveTemplate( 'list-row-menu.phtml' );
        $this->view->listToolbar     = $this->_resolveTemplate( 'list-toolbar.phtml' );
        $this->view->listScript      = $this->_resolveTemplate( 'js/list.js' );
        $this->view->listAddonScript = $this->_resolveTemplate( 'js/list-addon.js' );
        $this->_display( 'list.phtml' );
    }
    

    /**
     * Provide single object for view. Uses `listGetData()`
     *
     * @param int $id The `id` of the row to load for `viewAction`.
     */
    protected function viewGetData( $id )
    {
        $data = $this->listGetData( $id );
        
        if( is_array( $data ) && isset( $data[0] ) )
            return $data[0];
                
        $this->addMessage( 'Could not load the requested object - it does not exist.', OSS_Message::ERROR );
        $this->redirectAndEnsureDie( $this->_getBaseUrl() . '/index' );
    }
    
    /**
     * Function which can be over-ridden to perform any pre-view tasks
     *
     * @param object $object The Doctrine2 entity to delete
     */
    protected function preView( $object )
    {}

    /**
     * Prepares data for view and AJAX view
     *
     * @return void
     */
    protected function viewPrepareData()
    {
        $object = $this->viewGetData( $this->_getParam( 'id' ) );
        $this->preView( $object );
        
        $this->view->object = $object;
        
        // some of our stock templates also use $row instead of $object (especially SCRIPT types)
        $this->view->row = $object;
    }
    
    /**
     * View an object
     *
     * @return void
     */
    public function viewAction()
    {
        $this->viewPrepareData();

        $this->view->viewPreamble  = $this->_resolveTemplate( 'view-preamble.phtml'  );
        $this->view->viewPostamble = $this->_resolveTemplate( 'view-postamble.phtml' );
        $this->view->viewToolbar   = $this->_resolveTemplate( 'view-toolbar.phtml' );
        $this->view->viewScript    = $this->_resolveTemplate( 'js/view.js' );
        $this->_display( 'view.phtml' );
    }

    /**
     * Function which can be over-ridden to perform any pre-load tasks
     */
    protected function preLoadObject( $id )
    {
        return $id;
    }
    
    /**
     * Function which can be over-ridden to perform any post-load tasks such
     * as checking ownership
     */
    protected function postLoadObject( $object )
    {
        return $object;
    }
    
    
    /**
     * Load an object from the database with the given id
     *
     * @param int $id The ID of the object to load
     * @param bool $redirect If set to false, returns regardless with null
     * @return object|null The Entity object or null
     */
    protected function loadObject( $id, $redirect = true )
    {
        $object = null;
        
        $id = $this->preLoadObject( $id );
        
        if( !$id || !( $object = $this->getD2EM()->getRepository( $this->feGetParam( 'entity') )->find( $id ) ) )
        {
            if( $redirect )
            {
                $this->addMessage( 'The requested object does not exist.', OSS_Message::ERROR );
                $this->redirectAndEnsureDie( $this->_getBaseUrl() . '/index' );
            }
        }
        
        $this->postLoadObject( $object );
        
        return $object;
    }

    
    /**
     * Function which can be over-ridden to perform any pre-deletion tasks
     *
     * You can stop the deletion by returning false but you should also add a
     * message to explain why.
     *
     * @param object $object The Doctrine2 entity to delete
     * @return bool Return false to stop / cancel the deletion
     */
    protected function preDelete( $object )
    {
        return true;
    }
    
    /**
     * Function which can be over-ridden to perform any post-deletion tasks
     *
     * Database `flush()` has been successfully completed at this stage
     *
     * If you return with true, then the standard log message and OSS_Message
     * will be performed. If you want to override these, return false.
     *
     * NB: also calls `postFlush()`
     *
     * @param object $object The Doctrine2 entity to delete
     * @return bool Return false to stop / cancel standard log and OSS_Message
     */
    protected function postDelete( $object )
    {
        return $this->postFlush( $object );
    }
    

    /**
     * Gets the ID of the object for deletion - which, by default, returns the id parameter from the request
     *
     * @return int|false
     */
    protected function deleteResolveId()
    {
        return $this->_getParam( 'id', false );
    }

    /**
     * Function which can be over-ridden to perform any pre-delete tasks
     *
     * @return void
     */
    protected function deletePreamble()
    {}
    
    /**
     * Delete and element from the table
     */
    public function deleteAction()
    {
        $this->deletePreamble();
        
        if( $this->feGetParam( 'readonly' ) === true )
            return $this->_forward( 'index' );
        
        $object = $this->loadObject( $this->deleteResolveId() );
        
        try
        {
            if( $this->preDelete( $object ) )
            {
                $did = $object->getId();
                $this->getD2EM()->remove( $object );
                $this->getD2EM()->flush();
                
                if( $this->postDelete( $object ) )
                {
                    $this->getLogger()->info( sprintf( _( 'User %d deleted %s object with id %d' ),
                        $this->getUser()->getId(), $this->feGetParam( 'nameSingular' ), $did )
                    );
                    
                    if( $this->deleteDestinationOnSuccess() === false )
                        $this->addMessage( _( 'The requested object has been deleted' ), OSS_Message::SUCCESS );
                }
            }
        }
        catch( Exception $e )
        {
            $this->getLogger()->err( sprintf( _( 'User %s could not delete %s object with id %d' ),
                $this->getUser()->getId(), $this->feGetParam( 'nameSingular' ), $object->getId() )
            );
            throw $e;
        }
        
        $this->_redirect( $this->_getBaseUrl() . '/index' );
    }
    
    /**
     * You can add `OSS_Message`s here and redirect to a custom destination after a
     * successful deletion operation.
     *
     * By default it returns `false`.
     *
     * On `false`, the default action (`index`) is called and a standard success message is displayed.
     *
     * @return bool `false` for standard message and redirection, otherwise redirect within this function
     */
    protected function deleteDestinationOnSuccess()
    {
        return false;
    }
    
    
    /**
     * Preparation hook that can be overridden by subclasses for add and edit.
     *
     * This is called just before we process a possible POST / submission and
     * will allow us to change / alter the form or object.
     *
     * @param OSS_Form $form The Send form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True if we are editing, otherwise false
     */
    protected function addPrepare( $form, $object, $isEdit )
    {}
    
    /**
     * Prevalidation hook that can be overridden by subclasses for add and edit.
     *
     * This is called if the user POSTs a form just before the form is validated by Zend
     *
     * @param OSS_Form $form The Send form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True if we are editing, otherwise false
     * @return bool If false, the form is not validated or processed
     */
    protected function addPreValidate( $form, $object, $isEdit )
    {
        return true;
    }
    
    /**
     * Postvalidation hook that can be overridden by subclasses for add and edit.
     *
     * This is called if the user POSTs a form just after the form passes standard
     * Zend_Form validation.
     *
     * This hook can hijack the ensure form processing by returning false.
     *
     * It can also cause validation to fail with a message by adding an
     * `OSS_Message` and returning false.
     *
     * @param OSS_Form $form The Send form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True if we are editing, otherwise false
     * @return bool If false, the form is not processed
     */
    protected function addPostValidate( $form, $object, $isEdit )
    {
        return true;
    }

    /**
     * Pre db flush hook that can be overridden by subclasses for add and edit.
     *
     * This is called if the user POSTs a valid form after the posted
     * data has been assigned to the object and just before it is (persisted
     * if adding) and the database is flushed.
     *
     * This hook can prevent flushing by returning false.
     *
     * **NB: You should not `flush()` here unless you know what you are doing**
     *
     * A call to `flush()` is made after this method returns true ensuring a
     * transactional `flush()` for all.
     *
     * @param OSS_Form $form The Send form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True if we are editing, otherwise false
     * @return bool If false, the form is not persisted
     */
    protected function addPreFlush( $form, $object, $isEdit )
    {
        return true;
    }
    
    /**
     * Post database flush hook that can be overridden by subclasses for add and edit.
     *
     * This is called if the user POSTs a valid form after the posted
     * data has been flushed to the database.
     *
     * If you return `false`, the the standard log and OSS_Message will not be
     * created / displayed and a `redirect()` will not be performed.
     *
     * NB: also calls `postFlush()`
     *
     * @param OSS_Form $form The Send form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True if we are editing, otherwise false
     * @return bool If false, supress standard log and OSS_Message and the redirection
     */
    protected function addPostFlush( $form, $object, $isEdit )
    {
        return $this->postFlush( $object );
    }
    
    /**
     * Post database flush hook that can be overridden by subclasses and is called by
     * default for a successful add / edit / delete.
     *
     * Called by `addPostFlush()` and `postDelete()` - if overriding these, ensure to
     * call this if you have overridden it.
     *
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @return bool
     */
    protected function postFlush( $object )
    {
        return true;
    }
    
    
    /**
     * Post process hook that can be overridden by subclasses for add and edit actions.
     *
     * This is called immediately after the initstantiation of the form object and, if
     * editing, includes the Doctrine2 entity `$object`.
     *
     * If you need to have, for example, edit values set in the form, then use the
     * `addPrepare()` hook rather than this one.
     *
     * @see addPrepare()
     * @param OSS_Form $form The form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True of we are editing an object, false otherwise
     * @param array $options Options passed onto Zend_Form
     * @param string $cancelLocation Where to redirect to if 'Cancal' is clicked
     */
    protected function formPostProcess( $form, $object, $isEdit, $options = null, $cancelLocation = null )
    {
    }
    
    /**
     * Get the `Zend_Form` object for adding / editing actions with some processing.
     *
     * You should not override this but rather the `formPostProcess()` function to
     * make changes immediately after the form object has been instantiated.
     *
     * @param bool $isEdit True of we are editing an object, false otherwise
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param array $options Options passed onto Zend_Form
     * @param string $cancelLocation Where to redirect to if 'Cancal' is clicked
     * @return Zend_Form
     */
    protected function getForm( $isEdit, $object, $options = null, $cancelLocation = null )
    {
        $options['cancelLocation'] = $cancelLocation === null ? $this->_getBaseUrl() . '/index' : $cancelLocation;
        $options['isEdit'] = $isEdit;

        $formName = $this->feGetParam( 'form' );
        $form = new $formName( $options );
        
        $form->setAction(
            OSS_Utils::genUrl(
                $this->getRequest()->getControllerName(),
                ( $isEdit ? 'edit' : 'add' ),
                $this->getRequest()->getModuleName() == "index" ? false : $this->getRequest()->getModuleName(),
                [ 'id' => $object->getId() ]
            )
        );
        
        $this->formPostProcess( $form, $object, $isEdit, $options, $cancelLocation );
        return $form;
    }

    /**
     * You can add `OSS_Message`s here and redirect to a custom destination after a
     * successful add / edit operation.
     *
     * By default it returns `false`.
     *
     * On `false`, the default action (`index`) is called and a standard success message is displayed.
     *
     *
     * @param OSS_Form $form The form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True of we are editing an object, false otherwise
     * @return bool `false` for standard message and redirection, otherwise redirect within this function
     */
    protected function addDestinationOnSuccess( $form, $object, $isEdit  )
    {
        return false;
    }
    
    /**
     * This is a part off add action, in this section the form assign to entity
     * and changes are saved.
     *
     * @param OSS_Form $form The form object
     * @param object $object The Doctrine2 entity (being edited or blank for add)
     * @param bool $isEdit True of we are editing an object, false otherwise
     * @return void
     */
    protected function addProcessForm( $form, $object, $isEdit  )
    {
        do{
            try
            {
                if( !$this->addPostValidate( $form, $object, $isEdit ) )
                    break;

                $form->assignFormToEntity( $object, $this, $isEdit );

                if( $this->addPreFlush( $form, $object, $isEdit ) )
                {
                    if( !$isEdit )
                    {
                        // make sure we're not already persist()ed:
                        if( $this->getD2EM()->getUnitOfWork()->getEntityState( $object ) == \Doctrine\ORM\UnitOfWork::STATE_NEW )
                            $this->getD2EM()->persist( $object );
                    }
    
                    $this->getD2EM()->flush();
                    
                    if( $this->addPostFlush(  $form, $object, $isEdit  ) )
                    {
                        $this->getLogger()->info(
                            sprintf( _( 'User %d %s %s object with id %d' ),
                                $this->getUser()->getId(), $isEdit ? _( 'edited' ) : _( 'added' ),
                                $this->feGetParam( 'nameSingular' ), $object->getId()
                            )
                        );
                        
                        return true;
                    }
                }
            }
            catch( Exception $e )
            {
                $this->getLogger()->err(
                    sprintf( _( 'ERROR - FAILED: User %d %s %s object with id %d' ) . "\n" . $e,
                        $this->getUser()->getId(), $isEdit ? _( 'edited' ) : _( 'added' ),
                        $this->feGetParam( 'nameSingular' ), $object->getId()
                    )
                );
                throw( $e );
            }
        }while( false );

        return false;
    }
    
    /**
     * Gets the ID of the object for editing - which, by default, returns the id parameter from the request
     *
     * @return int|false
     */
    protected function editResolveId()
    {
        return $this->_getParam( 'id', false );
    }
    

    /**
     * Function which can be over-ridden to perform any pre-add/edit tasks
     *
     * @return void
     */
    protected function addPreamble()
    {}
    
    /**
     * Add (or edit) an object
     */
    public function addAction()
    {
        $this->addPreamble();
        
        if( $this->feGetParam( 'readonly' ) === true )
            return $this->_forward( 'index' );
        
        $this->view->isEdit = $isEdit = false;
        
        $eid = $this->editResolveId();
        
        if( $eid && is_numeric( $eid ) )
        {
            $this->view->isEdit = $isEdit = true;
    
            $this->view->object = $object = $this->loadObject( $eid );
    
            $this->view->form = $form = $this->getForm( $isEdit, $object );
            $form->assignEntityToForm( $object, $this );
            if( $form->getElement( 'submit' ) )
                $form->getElement( 'submit' )->setLabel( 'Save Changes' );
        }
        else
        {
            $this->view->object = $object = new $this->_feParams->entity();
            $this->view->form = $form = $this->getForm( $isEdit, $object );
        }
    
        $this->addPrepare( $form, $object, $isEdit );
        
        if( $this->getRequest()->isPost() && $this->addPreValidate( $form, $object, $isEdit ) && $form->isValid( $_POST ) )
        {
            if( $this->addProcessForm( $form, $object, $isEdit ) )
            {
                if( $this->addDestinationOnSuccess( $form, $object, $isEdit ) === false )
                {
                    $this->addMessage( $this->feGetParam( 'titleSingular' ) . ( $isEdit ? ' edited.' : ' added.' ), OSS_Message::SUCCESS );
                    $this->redirectAndEnsureDie( $this->_getBaseUrl() . "/index" );
                }
            }
        }
    
        $this->view->addPreamble  = $this->_resolveTemplate( 'add-preamble.phtml'  );
        $this->view->addPostamble = $this->_resolveTemplate( 'add-postamble.phtml' );
        $this->view->addToolbar   = $this->_resolveTemplate( 'add-toolbar.phtml' );
        $this->view->addScript    = $this->_resolveTemplate( 'js/add.js' );
        
        $this->_display( 'add.phtml' );
    }
    
    
    
    /**
     * Edit an object - just forwards to addAction()
     */
    public function editAction()
    {
        $this->_forward( 'add' );
    }
    
    
    
    
    /**
     * Set a frontend parameter
     *
     * @param string $p The parameter name
     * @param mixed $v The value
     */
    protected function feSetParam( $p, $v )
    {
        $this->_feParams->$p = $v;
    }

    /**
     * Get a frontend parameter
     *
     * @return mixed The frontend parameter (or null if null or not set)
     */
    protected function feGetParam( $p )
    {
        if( isset( $this->_feParams->$p ) )
            return $this->_feParams->$p;
        
        return null;
    }
    
    /**
     * Gets controller or module/controller string
     *
     * If module is index that means that its default, function will return only controller.
     * Otherwise it will return module/controller. It will allow front end usage in modules.
     *
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
         if( $this->getRequest()->getModuleName() == "index" )
            return $this->getRequest()->getControllerName();
        else
            return $this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName();
    }


}

