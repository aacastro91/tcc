<?php
/**
 * UsuarioFormList Registration
 * @author  <your name here>
 */
class UsuarioFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_Usuario');
        $this->form->class = 'tform'; // CSS class
        $this->form->setFormTitle('Cadastro de Usuarios'); // define the form title
        


        // create the form fields
        $id                             = new TEntry('id');
        $nome                           = new TEntry('nome');
        $login                          = new TEntry('login');
        $senha                          = new TPassword('senha');
        $permissao                      = new TCombo('permissao');
        
        $combo_items = array();
        $combo_items['Administrador'] ='Administrador';
        $combo_items['Usuario'] ='Usuário';
        
        $permissao->addItems($combo_items);


        // add the fields
        $this->form->addQuickField('ID', $id,  50);
        $this->form->addQuickField('Nome', $nome,  300, new TRequiredValidator );
        $this->form->addQuickField('Usuário', $login,  200, new TRequiredValidator );
        $this->form->addQuickField('Senha', $senha,  200, new TRequiredValidator );
        $this->form->addQuickField('Permissão', $permissao,  150, new TRequiredValidator );





        // create the form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TQuickGrid;
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $id = $this->datagrid->addQuickColumn('id', 'id', 'left', 50);
        $nome = $this->datagrid->addQuickColumn('Nome', 'nome', 'left', 300);
        $login = $this->datagrid->addQuickColumn('Usuário', 'login', 'left', 200);
       // $senha = $this->datagrid->addQuickColumn('Senha', 'senha', 'left', 200);
        $permissao = $this->datagrid->addQuickColumn('Permissão', 'permissao', 'left', 100);

        
        // create the datagrid actions
        $edit_action   = new TDataGridAction(array($this, 'onEdit'));
        $delete_action = new TDataGridAction(array($this, 'onDelete'));
        
        // add the actions to the datagrid
        $this->datagrid->addQuickAction(_t('Edit'), $edit_action, 'id', 'ico_edit.png');
        $this->datagrid->addQuickAction(_t('Delete'), $delete_action, 'id', 'ico_delete.png');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // create the page container
        $container = TVBox::pack( $this->form, $this->datagrid, $this->pageNavigation);
        parent::add($container);
    }

    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'mysql'
            TTransaction::open('mysql');
            
            // creates a repository for Usuario
            $repository = new TRepository('Usuario');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Usuario_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Usuario_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param)
    {
        try
        {
            // get the parameter $key
            $key=$param['key'];
            
            TTransaction::open('mysql'); // open the transaction
            $object = new Usuario($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object
            TTransaction::close(); // close the transaction
            
            $this->onReload( $param ); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        try
        {
            TTransaction::open('mysql'); // open a transaction with database
            
            // get the form data into an active record Usuario
            $object = $this->form->getData('Usuario');
            $this->form->validate(); // form validation
            $object->senha = md5($object->senha); 
            $object->store(); // stores the object
            $object->senha = '';
            $this->form->setData($object); // fill the form with the active record data
            TTransaction::close(); // close the transaction
                        
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved')); // success message
            $this->onReload(); // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                
                $key=$param['key']; // get the parameter $key
                TTransaction::open('mysql'); // open a transaction with the database
                $object = new Usuario($key); // instantiates the Active Record
                $object->senha = '';
                $this->form->setData($object); // fill the form with the active record data
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page e seu conteÃºdo
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
