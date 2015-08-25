<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
/**
 * FuncionalidadeList Listing
 * @author  <your nome here>
 */
class FuncionalidadeList extends TPage
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
        
        // Cria o form
        $this->form = new TForm('form_search_Funcionalidade');
        $this->form->class = 'tform';
        
        // cria a tabela
        $table = new TTable;
        $table->style = 'width:100%';
        
        $table->addRowSet( new TLabel('Funcionalidades'), '' )->class = 'tformtitle';

        // adiciona a tabela no form
        $this->form->add($table);
        
        // cria os campos de pesquisa do form
        $nome = new TEntry('nome');
        $nome->setValue(TSession::getValue('Funcionalidade_nome'));
        $nome->setSize(500);
        
        $control = new TEntry('classe');
        $control->setValue(TSession::getValue('Funcionalidade_control'));
        $control->setSize(500);
        
        // adiciona linha para os campos de filtro
        $row=$table->addRowSet(new TLabel('Nome: '), $nome);
        $row=$table->addRowSet(new TLabel('Classe de Controle: '), $control);
        
        // cria dois botoes de acao para o form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        
        // define as acoes dos botoes
        $find_button->setAction(new TAction(array($this, 'onSearch')), 'Buscar');
        $find_button->setImage('ico_find.png');
        
        $new_button->setAction(new TAction(array('FuncionalidadeForm', 'onEdit')), 'Novo');
        $new_button->setImage('ico_new.png');
        
        // define quais sao os campos do form
        $this->form->setFields(array($nome, $control, $find_button, $new_button));

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        parent::include_css('app/resources/custom-table.css');

        // cria o datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->class = 'tdatagrid_table customized-table';
        
        $this->datagrid->setHeight(320);
        
        // cria as colunas do datagrid
        $id         = new TDataGridColumn('id', 'ID', 'right');
        $nome       = new TDataGridColumn('nome', 'Nome', 'left');
        $classe     = new TDataGridColumn('classe', 'Classe de Controle', 'left');

        // adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($classe);

        // cria as acoes das colunas do datagrid
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_nome= new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $nome->setAction($order_nome);

        $order_classe= new TAction(array($this, 'onReload'));
        $order_classe->setParameter('order', 'classe');
        $classe->setAction($order_classe);

        // edicao inline
        $nome_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $nome_edit->setField('id');
        $nome->setEditAction($nome_edit);

        $classe_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $classe_edit->setField('id');
        $classe->setEditAction($classe_edit);

        // cria 2 acoes do datagrid
        $action1 = new TDataGridAction(array('FuncionalidadeForm', 'onEdit'));
        $action1->setLabel('Editar');
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel('Excluir');
        $action2->setImage('ico_delete.png');
        $action2->setField('id');
        
        // adiciona as acoes ao datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // cria o modelo do datagrid
        $this->datagrid->createModel();
        
        // cria o navegador de paginas
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // cria a estrutura da pagina usando tabela
        $table = new TTable;
        $table->style = 'width: 80%';
        $table->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $table->addRow()->addCell($this->form);
        $table->addRow()->addCell($this->datagrid);
        $table->addRow()->addCell($this->pageNavigation);
        
        // adiciona a tabela $table dentro da pagina
        parent::add($table);
    }
    
    /**
     * method onInlineEdit()
     * edicao de registro inline
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
            
            // instantiates object Funcionalidade
            $object = new Funcionalidade($key);
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
        
        TSession::setValue('Funcionalidade_nome_filter',   NULL);
        TSession::setValue('Funcionalidade_nome', '');
        
        TSession::setValue('Funcionalidade_control_filter',   NULL);
        TSession::setValue('Funcionalidade_control', '');
        
        // check if the user has filled the form
        if ( $data->nome )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            
            // stores the filter in the session
            TSession::setValue('Funcionalidade_nome_filter',   $filter);
            TSession::setValue('Funcionalidade_nome', $data->nome);            
        }
        
        if ( $data->classe )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('classe', 'like', "%{$data->classe}%");
            
            // stores the filter in the session
            TSession::setValue('Funcionalidade_control_filter',   $filter);
            TSession::setValue('Funcionalidade_control', $data->classe);            
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
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            
            // creates a repository for Funcionalidade
            $repository = new TRepository('Funcionalidade');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            if (!isset($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Funcionalidade_nome_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Funcionalidade_nome_filter'));
            }
            if (TSession::getValue('Funcionalidade_control_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Funcionalidade_control_filter'));
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
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            
            // instantiates object Funcionalidade
            $object = new Funcionalidade($key);
            
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