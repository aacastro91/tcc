<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Log\TLoggerTXT;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Wrapper\TQuickForm;

/**
 * Description of SrpSeek
 *
 * @author Anderson
 */
class ItemSeek extends TWindow{
    private $form; // form de busca
    private $datagrid; //listagem
    private $navegadorPagina;
    private $carregado;
    
    function __construct() {
        parent::__construct();
        parent::setSize(850, 750);
        parent::setTitle('Busca de Itens');
        new TSession;
                
        //cria o formulario
        $this->form = new TQuickForm('form_busca_item');
        
        //cria os campos de busca do formulario
        $numeroItem                     = new TEntry('numeroItem');
        $descricaoSumaria               = new TEntry('descricaoSumaria');
        $fabricante                     = new TEntry('fabricante');
        
        //valors da sessao
        $descricaoSumaria->setValue(TSession::getValue('item_descricaoSumaria'));
        
        //adiciona os campos no formulario
        //if ((TSession::getValue('Item_filter_data')!== NULL) && (TSession::getValue('Item_filter_data'))){
        //    var_dump(TSession::getValue('Item_filter_data'));
        //    $this->form->setData( TSession::getValue('Item_filter_data'));
        //}
        
        $this->form->addQuickField('Nº Item:', $numeroItem,70);
        $this->form->addQuickField('Descrição Suária:', $descricaoSumaria, 250);
        $this->form->addQuickField('Fabricante:', $fabricante, 150);
        
        //adiciona a acao ao formulario
        $this->form->addQuickAction('Buscar', new TAction(array($this, 'onSearch')),'ico_find.png');
        
        //criar a datagrid
        $this->datagrid = new TDataGrid;
        //$this->datagrid->width = '100%';
        $this->datagrid->setHeight(300);
        
        //criar as colunas da datagrid 
        $numeroItem       = new TDataGridColumn('numeroItem', 'Nº Item', 'left', 50);
        $descricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição Sumária', 'left', 500);
        $fabricante       = new TDataGridColumn('fabricante', 'fabricante', 'left', 150);
        $unidadeMedida    = new TDataGridColumn('unidadeMedida', 'Unidade', 'left', 50);
        $valorUnitario    = new TDataGridColumn('valorUnitario', 'Valor Unit.', 'left', 70);
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($numeroItem);
        $this->datagrid->addColumn($descricaoSumaria);
        $this->datagrid->addColumn($fabricante);
        $this->datagrid->addColumn($unidadeMedida);
        $this->datagrid->addColumn($valorUnitario);
        
        //criar acao da coluna
        $action = new TDataGridAction(array($this, 'onSelect'));
        $action->setLabel('Select');
        $action->setImage('ico_apply.png');
        $action->setField('id');
        $this->datagrid->addAction($action);
        
        //cria o modelo
        $this->datagrid->createModel();
        
        
        //criar o navegador de pagina
        $this->navegadorPagina = new TPageNavigation();
        $this->navegadorPagina->setAction(new TAction(array($this, 'onReload')));
        $this->navegadorPagina->setWidth($this->datagrid->getWidth());
        
        // criar a estrutura da pagina usando uma tabela
        $table = new TTable;
        $table->addRow()->addCell($this->form);
        $table->addRow()->addCell($this->datagrid);
        $table->addRow()->addCell($this->navegadorPagina);
        // add the table inside the page
        parent::add($table);
    }
    
    /**
     * Registro de filtros na sessao
     */    
    function onSearch(){
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('ItemList_filter_numeroItem',   NULL);
        TSession::setValue('ItemList_filter_descricaoSumaria',   NULL);
        TSession::setValue('ItemList_filter_fabricante',   NULL);

        if (isset($data->numeroItem) AND ($data->numeroItem)) {
            $filter = new TFilter('numeroItem', '=', "$data->numeroItem"); // create the filter
            TSession::setValue('ItemList_filter_numeroItem',   $filter); // stores the filter in the session
        }


        if (isset($data->descricaoSumaria) AND ($data->descricaoSumaria)) {
            $filter = new TFilter('descricaoSumaria', 'like', "%{$data->descricaoSumaria}%"); // create the filter
            TSession::setValue('ItemList_filter_descricaoSumaria',   $filter); // stores the filter in the session
        }


        if (isset($data->fabricante) AND ($data->fabricante)) {
            $filter = new TFilter('fabricante', 'like', "%{$data->fabricante}%"); // create the filter
            TSession::setValue('ItemList_filter_fabricante',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Item_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    function onReload($param = null){
        try{
            
            //inicia uma transacao no banco
            TTransaction::open('saciq');
            TTransaction::setLogger(new TLoggerTXT('C:\array\log.txt'));
            
            $repository = new TRepository('Item');
            $limit = 1;
            $criteria = new TCriteria();
            
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);

            //filtro do numero srp
            if (TSession::getValue('ItemList_filter_numeroItem')){
                $criteria->add(TSession::getValue('ItemList_filter_numeroItem'));
            }
            
            if (TSession::getValue('ItemList_filter_descricaoSumaria')){
                $criteria->add(TSession::getValue('ItemList_filter_descricaoSumaria'));
            }
            
            if (TSession::getValue('ItemList_filter_fabricante')){
                $criteria->add(TSession::getValue('ItemList_filter_fabricante'));
            }

            $itens = $repository->load($criteria);
            
            $this->datagrid->clear();
            
            if ($itens){
                foreach ($itens as $item){
                    $this->datagrid->addItem($item);
                }
            }
            
            //reseta as propriedadso do objeto criteria para contar os registros
            $criteria->resetProperties();
            $count = $repository->count($criteria);
            
            $this->navegadorPagina->setCount($count);
            $this->navegadorPagina->setProperties($param);
            $this->navegadorPagina->setLimit($limit);
            
            //fecha a transacao
            TTransaction::close();
            $this->carregado = true;
                        
        } catch (Exception $ex) {
            new TMessage('error', '<b>Error</b> ' . $ex->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onSelect($param){
        try{
            
            $key = $param['key'];
            TTransaction::open('saciq');
            $item = new Item($key);                     
            TTransaction::close();
            
            $obj = new stdClass();
            $obj->item_id          = $item->id;
            $obj->descricaoSumaria = $item->descricaoSumaria;
            $obj->valorUnitario    = $item->valorUnitario;                  
            TForm::sendData('requisicao_form', $obj);
            parent::closeWindow();
            
        } catch (Exception $ex) {
            $obj = new stdClass();
            $obj->item_id = '';
            $obj->descricaoSumaria = '';
            $obj->valorUnitario = '';
            TForm::sendData('requisicao_form', $obj);
            TTransaction::rollback();
        }
    }
    
    function show(){
        if (!$this->carregado){
            $this->onReload();
        }
        parent::show();
    }

}
