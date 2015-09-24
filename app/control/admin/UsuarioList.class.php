<?php
/**
 * UsuarioList Listing
 * @author  Anderson
 */
class UsuarioList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/custom-table.css');
        // Cria o form
        $this->form = new TForm('form_search_Usuario');
        $this->form->class = 'tform';
        
        // cria a tabela
        $table = new TTable;
        $table->style = 'width:100%';
        
        $table->addRowSet( new TLabel('Consulta Usuários'), '' )->class = 'tformtitle';
        
        // adiciona a tabela no form
        $this->form->add($table);
        
        // cria os campos de pesquisa do form
        $id = new TEntry('id');
        $id->setValue(TSession::getValue('Usuario_id'));
        
        $nome = new TEntry('nome');
        $nome->setValue(TSession::getValue('Usuario_nome'));
        
        // add a row for the filter field
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet(new TLabel('Nome: '), $nome);
        
        // cria dois botoes de acao para o form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        // define as acoes dos botoes
        $find_button->setAction(new TAction(array($this, 'onSearch')), 'Buscar');
        $find_button->setImage('ico_find.png');
        
        $new_button->setAction(new TAction(array('UsuarioForm', 'onEdit')), 'Novo');
        $new_button->setImage('ico_new.png');
        
        // add a row for the form actions
        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;
        
        // define wich are the form fields
        $this->form->setFields(array($id, $nome, $find_button, $new_button));
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->setHeight(320);
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->style = 'width: 100%';
        
        // creates the datagrid columns
        $id     = new TDataGridColumn('id',    'ID', 'right');
        $nome   = new TDataGridColumn('nome',  'Nome', 'left');
        $prontuario  = new TDataGridColumn('prontuario', 'Prontuario', 'left');
        $email  = new TDataGridColumn('email', 'Email', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($prontuario);
        $this->datagrid->addColumn($email);


        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_nome= new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $nome->setAction($order_nome);



        // inline editing
        $nome_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $nome_edit->setField('id');
        $nome->setEditAction($nome_edit);


        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array('UsuarioForm', 'onEdit'));
        $action1->setLabel('Editar');
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel('Excluir');
        $action2->setImage('ico_delete.png');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // creates the page structure using a table
        $table = new TTable;
        $table->style = 'width: 80%';
        $table->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $table->addRow()->addCell($this->form);
        $table->addRow()->addCell($this->datagrid);
        $table->addRow()->addCell($this->pageNavigation);
        
        // add the table inside the page
        parent::add($table);
    }
    
    /**
     * method onInlineEdit()
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field nome: object attribute to be updated
     *              value: new attribute content 
     */
    function onInlineEdit($param)
    {
        try
        {
            
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            
            // instantiates object Usuario
            
            $object = new Usuario($key);
            
            // deletes the object from the database
            $object->{$field} = $value;
            $object->store();
            
            // close the transaction
            TTransaction::close();
            
            // reload the listing
            $this->onReload($param);
            // shows the success message
            new TMessage('info', 'Registro atualizado');
        }
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // pegar os dados do form de busca
        $data = $this->form->getData();
        
        TSession::setValue('Usuario_id_filter',   NULL);
        TSession::setValue('Usuario_nome_filter',   NULL);
        
        TSession::setValue('Usuario_id', '');
        TSession::setValue('Usuario_nome', '');
        
        // check if the user has filled the form
        if ( $data->id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('id', '=', "{$data->id}");
            
            // stores the filter in the session
            TSession::setValue('Usuario_id_filter',   $filter);
            TSession::setValue('Usuario_id', $data->id);
        }
        if ( $data->nome )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            
            // stores the filter in the session
            TSession::setValue('Usuario_nome_filter',   $filter);
            TSession::setValue('Usuario_nome', $data->nome);
        }
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * carregar o datagrid com objetos do banco
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('saciq');
            
            if( ! isset($param['order']) )
                $param['order'] = 'id';
            
            // creates a repository for Usuario
            $repository = new TRepository('Usuario');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Usuario_id_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Usuario_id_filter'));
            }
            if (TSession::getValue('Usuario_nome_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Usuario_nome_filter'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
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
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executada quando o usuario clica no botao delete
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // mostra o dialogo para o usuario
        new TQuestion('Deseja realmente excluir ?', $action);
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
            // open a transaction with database 'permission'
            TTransaction::open('saciq');
            
            // instantiates object Usuario
            $object = new Usuario($key);
            
            // deletes the object from the database
            $object->delete();
            
            // close the transaction
            TTransaction::close();
            
            // reload the listing
            $this->onReload( $param );
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'));
        }
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
?>