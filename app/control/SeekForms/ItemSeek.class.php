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
use Adianti\Widget\Datagrid\TDataGridColumn;
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
class ItemSeek extends TWindow {

    private $form; // form de busca
    private $datagrid; //listagem
    private $navegadorPagina;
    private $carregado;
    private $continue;
    private $message;

    function __construct() {
        parent::__construct();
        parent::setSize(850, 750);
        parent::setTitle('Busca de Itens');
        new TSession;

        if (!TSession::getValue('SRP_id')) {
            $this->continue = false;
        } else {
            $this->continue = true;
        }

        //cria o formulario
        $this->form = new TQuickForm('form_busca_item');

        //cria os campos de busca do formulario
        $numeroItem = new TEntry('numeroItem');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $fabricante = new TEntry('fabricante');



        //valors da sessao
        $descricaoSumaria->setValue(TSession::getValue('item_descricaoSumaria'));

        //adiciona os campos no formulario
        //if ((TSession::getValue('Item_filter_data')!== NULL) && (TSession::getValue('Item_filter_data'))){
        //    var_dump(TSession::getValue('Item_filter_data'));
        //    $this->form->setData( TSession::getValue('Item_filter_data'));
        //}

        $this->form->addQuickField('Nº Item:', $numeroItem, 70);
        $this->form->addQuickField('Descrição Suária:', $descricaoSumaria, 250);
        $this->form->addQuickField('Fabricante:', $fabricante, 150);

        //adiciona a acao ao formulario
        $this->form->addQuickAction('Buscar', new TAction(array($this, 'onSearch')), 'ico_find.png');

        //criar a datagrid
        $this->datagrid = new TDataGrid;
        //$this->datagrid->width = '100%';
        $this->datagrid->setHeight(300);

        //criar as colunas da datagrid 
        $GnumeroItem = new TDataGridColumn('numeroItem', 'Nº Item', 'left', 50);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição Sumária', 'left', 500);
        $Gquantidade = new TDataGridColumn('quantidadeDisponivel', 'Quantidade', 'right', 70);
        $GunidadeMedida = new TDataGridColumn('unidadeMedida', 'Unidade', 'left', 50);
        $GvalorUnitario = new TDataGridColumn('valorUnitario', 'Valor Unit.', 'right', 70);

        // add the columns to the DataGrid
        $this->datagrid->addColumn($GnumeroItem);
        $this->datagrid->addColumn($GdescricaoSumaria);
        $this->datagrid->addColumn($Gquantidade);
        $this->datagrid->addColumn($GunidadeMedida);
        $this->datagrid->addColumn($GvalorUnitario);

        //criar acao da coluna
        $action = new TDataGridAction(array($this, 'onSelect'));
        $action->setLabel('Select');
        $action->setImage('ico_apply.png');
        $action->setField('numeroItem');
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
    function onSearch() {
        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('ItemList_filter_numeroItem', NULL);
        TSession::setValue('ItemList_filter_descricaoSumaria', NULL);
        TSession::setValue('ItemList_filter_fabricante', NULL);

        if (isset($data->numeroItem) AND ( $data->numeroItem)) {
            $filter = new TFilter('numeroItem', '=', "$data->numeroItem"); // create the filter
            TSession::setValue('ItemList_filter_numeroItem', $filter); // stores the filter in the session
        }


        if (isset($data->descricaoSumaria) AND ( $data->descricaoSumaria)) {
            $filter = new TFilter('descricaoSumaria', 'like', "%{$data->descricaoSumaria}%"); // create the filter
            TSession::setValue('ItemList_filter_descricaoSumaria', $filter); // stores the filter in the session
        }


        if (isset($data->fabricante) AND ( $data->fabricante)) {
            $filter = new TFilter('fabricante', 'like', "%{$data->fabricante}%"); // create the filter
            TSession::setValue('ItemList_filter_fabricante', $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Item_filter_data', $data);

        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    function onReload($param = null) {
        if ($this->message === false)
            return;
        try {

            //inicia uma transacao no banco
            TTransaction::open('saciq');
            TTransaction::setLogger(new TLoggerTXT('C:\array\log.txt'));

            $repository = new TRepository('Item');
            $limit = 10;
            $criteria = new TCriteria();

            if ((!TSession::getValue('SRP_id')) && (!$this->continue)) {
                $this->closeWindow();
                new TMessage('error', 'Número SRP Inválido');
                $this->message = false;
                return;
            }

            if (TSession::getValue('SRP_id')) {
                $criteria->add(new TFilter('srp_id', '=', TSession::getValue('SRP_id')));
            }

            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);

            //filtro do numero srp
            if (TSession::getValue('ItemList_filter_numeroItem')) {
                $criteria->add(TSession::getValue('ItemList_filter_numeroItem'));
                TSession::setValue('ItemList_filter_numeroItem', NULL);
            }

            if (TSession::getValue('ItemList_filter_descricaoSumaria')) {
                $criteria->add(TSession::getValue('ItemList_filter_descricaoSumaria'));
                TSession::setValue('ItemList_filter_descricaoSumaria', NULL);
            }

            if (TSession::getValue('ItemList_filter_fabricante')) {
                $criteria->add(TSession::getValue('ItemList_filter_fabricante'));
                TSession::setValue('ItemList_filter_fabricante', NULL);
            }

            $itens = $repository->load($criteria);

            $this->datagrid->clear();

            if ($itens) {
                foreach ($itens as $item) {
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

    public function onSelect($param) {
        try {
            
            if (!$param['key'])
                return;

            if ((!TSession::getValue('SRP_id')) && (!$this->continue)) {
                $this->closeWindow();
                new TMessage('error', 'Número SRP Inválido');
                $this->message = false;
                return;
            }

            $key = $param['key'];
            TTransaction::open('saciq');
            TTransaction::setLogger(new TLoggerTXT('c:\array\file.txt'));

            $repository = new TRepository('Item');
            $criteria = new TCriteria();
            
            $criteria->add(new TFilter('numeroItem', '=', $key));
            if (TSession::getValue('SRP_id')) {
                $criteria->add(new TFilter('srp_id', '=', TSession::getValue('SRP_id')));
            }

            $itens = $repository->load($criteria);
            
            if (count($itens) > 0){
                
                $item = $itens[0];
                $obj = new stdClass();
                                     
                $obj->item_id = $item->id;

                if (strpos($item->descricaoSumaria,'–')){
                    $item->descricaoSumaria = str_replace('–','-',$item->descricaoSumaria);
                    $item->store();
                }
                $obj->numeroItem = $item->numeroItem;
                $obj->descricaoSumaria = $item->descricaoSumaria;
                $obj->valorUnitario = $item->valorUnitario;
                TForm::sendData('form_itens', $obj);
                parent::closeWindow();
            }
            else{
                $obj = new stdClass();
                $obj->item_id = '';
                $obj->numeroItem = '';
                $obj->descricaoSumaria = '';
                $obj->valorUnitario = '';
                //$obj->quantidade = '';
                $obj->prazoEntrega = '60 Dias';
                $obj->justificativa = '';
                TForm::sendData('form_itens', $obj);
                parent::closeWindow();
            }
            TTransaction::close();
        } catch (Exception $ex) {
            $obj = new stdClass();
            $obj->item_id = '';
            $obj->descricaoSumaria = '';
            $obj->valorUnitario = '';
            $obj->quantidade = '';
            $obj->prazoEntrega = '60 Dias';
            $obj->justificativa = '';
            TForm::sendData('form_itens', $obj);
            TTransaction::rollback();
        }
    }

    function show() {
        if (!$this->carregado) {
            $this->onReload();
        }
        parent::show();
    }

}
