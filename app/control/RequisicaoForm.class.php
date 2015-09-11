<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TFrame;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TSeekButton;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RequisicaoForm
 *
 * @author Anderson
 */
class RequisicaoForm extends TPage{

    protected $form; // form
    protected $formFields;
    protected $dt_venda;
    protected $product_list;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct() {
        parent::__construct();

        // creates the form
        $this->form = new TForm('requisicao_form');
        $this->form->class = 'tform'; // CSS class
        //parent::include_css('app/resources/custom-frame.css');

        $table_master = new TTable;
        $table_master->width = '100%';

        $table_master->addRowSet(new TLabel('Requisição de quantitativo'), '', '')->class = 'tformtitle';

        // add a table inside form
        $table_general = new TTable;
        $table_general->width = '100%';
        $tableProduct = new TTable;
        $tableProduct->width = '100%';

        $frame_general = new TFrame;
        $frame_general->syle = 'width : 100%';
        $frame_general->setLegend('Requisição');
        $frame_general->style = 'background:whiteSmoke';
        $frame_general->add($table_general);

        $table_master->addRow()->addCell($frame_general)->colspan = 2;
        $row = $table_master->addRow();
        $row->addCell($tableProduct);

        $this->form->add($table_master);

        // master fields
        $numeroSRP     = new TSeekButton('numeroSRP');
        $nome          = new TEntry('nome');
        $nroProcesso   = new TEntry('numeroProcesso');
        $uasg          = new TEntry('uasg');
        $validadeAta   = new TDate('validade');
        $aprovado      = new TCombo('aprovado');

        // detail fields
        $item_id          = new TSeekButton('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario    = new TEntry('valorUnitario');
        $quantidade       = new TEntry('quantidade');
        $prazoEntrega     = new TEntry('prazoEntrega');
        $justificativa    = new TEntry('justificativa');
        $total            = new TEntry('total');

        //ações
        $numeroSRP->setAction(new TAction(array(new SrpSeek(), 'onReload')));
        $item_id->setAction(new TAction(array(new ItemSeek(), 'onReload')));
        $item_id->setExitAction(new TAction(array($this, 'onProductChange')));

        //tamanho
        $numeroSRP->setSize(80);
        $nroProcesso->setSize(100);
        $uasg->setSize(70);
        $validadeAta->setSize(78);
        $nome->setSize('95%');
        
        $item_id->setSize(60);
        $descricaoSumaria->setSize('100%');
        $justificativa->setSize('70%');
        $prazoEntrega->setSize(90);

        //não editaveis        
        $descricaoSumaria->setEditable(false);
        $nome->setEditable(false);
        $nroProcesso->setEditable(false);
        $uasg->setEditable(false);
        $valorUnitario->setEditable(false);                
        
        //mask
        $validadeAta->setMask('dd/mm/yyyy'); 
        $quantidade->class = 'frm_number_only';

        //validadores
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $item_id->addValidation('Item', new TRequiredValidator());
        $valorUnitario->addValidation('Preço', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $justificativa->addValidation('Justificativa', new TRequiredValidator());
        $prazoEntrega->addValidation('Prazo de validade', new TRequiredValidator());
        
        //outras propriedades
        $nome->setProperty("style", "min-width : 200px");
        $prazoEntrega->setValue('60 Dias');
        //
        $itens = array();
        $itens['1'] = 'Sim';
        $itens['0'] = 'Não';
        $aprovado->addItems($itens);
        $aprovado->setValue('1');
        $aprovado->setDefaultOption(false);

        // pedido
        $row = $table_general->addRow();
        $row->addCell(new TLabel('Nº SRP:'))->width = '150px';
        $row->addCell($numeroSRP);
        $row->addCell(new TLabel('Nome Licitação:'))->width = '150px';
        $row->addCell($nome);
        $table_general->addRowSet(new TLabel('Proc. Orig:'), $nroProcesso, new TLabel('UASG:'), $uasg);
        $table_general->addRowSet(new TLabel('Validade da Ata:'), $validadeAta, new TLabel('Pendente:'), $aprovado);
        
        // products
        $frame_product = new TFrame();
        $frame_product->style = 'width : 100%';
        $frame_product->setLegend('Itens');
        $row = $tableProduct->addRow();
        $row->addCell($frame_product);

        $add_product = new TButton('add_product');
        $action_product = new TAction(array($this, 'onProductAdd'));
        $add_product->setAction($action_product, 'Adicionar');
        $add_product->setImage('fa:save');

        $subtable_product = new TTable;
        $subtable_product->width = '100%';
        $frame_product->add($subtable_product);
        $row = $subtable_product->addRow();
        $row->addCell(new TLabel('Item:'));
        $box = new THBox();
        $box->add($item_id);
        $box->add($descricaoSumaria)->style = 'width : 75%;display:inline-block;';
        $row->addCell($box)->style = 'width : 85%';
        //$subtable_product->addRowSet(new TLabel('Item'), array($item_id, $descricaoSumaria));
        $subtable_product->addRowSet(new TLabel('Preço:'), $valorUnitario);
        $subtable_product->addRowSet(new TLabel('Quantidade:'), $quantidade);
        $subtable_product->addRowSet(new TLabel('Prazo de entrega:'), $prazoEntrega);
        $subtable_product->addRowSet(new TLabel('Justificativa:'), $justificativa);
        $subtable_product->addRowSet($add_product);

        //$label_product->setFontColor('#FF0000');
       // $label_amount->setFontColor('#FF0000');
        //$label_sale_price->setFontColor('#FF0000');
        
        parent::include_css('app/resources/custom-table.css');
        $this->product_list = new TDataGrid();
        $this->product_list->style = 'width: 100%';
        $this->product_list->class = 'tdatagrid_table customized-table';
        $this->product_list->setHeight(175);
        $this->product_list->makeScrollable();
        $this->product_list->disableDefaultClick();
        
        $Gedit    = new TDataGridColumn('edit', '', 'left', 30);
        $Gdelete  = new TDataGridColumn('delete', '', 'left', 30);
        $Gid      = new TDataGridColumn('id', 'ID', 'center', 120);
        $Gitem_id = new TDataGridColumn('item_id', 'Item', 'center', 120);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição', 'left', 500);
        $Gquantidade = new TDataGridColumn('quantidade', 'Quantidade', 'left', 150);
        $GvalorUnitario = new TDataGridColumn('valorUnitario', 'Preço', 'right', 150);
        $Gtotal = new TDataGridColumn('total', 'Total', 'right', 200);
        
        //$Gedit->style = 'min-width : 28px';
        
        $this->product_list->addColumn($Gedit);
        $this->product_list->addColumn($Gdelete);
        $this->product_list->addColumn($Gid);
        $this->product_list->addColumn($Gitem_id);
        $this->product_list->addColumn($GdescricaoSumaria);
        $this->product_list->addColumn($Gquantidade);
        $this->product_list->addColumn($GvalorUnitario);
        $this->product_list->addColumn($Gtotal);
        $this->product_list->createModel();

        $row = $tableProduct->addRow();
        
        $row->addCell($this->product_list);

        // create an action button (save)
        $save_button = new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), 'Salvar');
        $save_button->setImage('ico_save.png');

        // create an new button (edit with no parameters)
        $new_button = new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onClear')), 'Novo');
        $new_button->setImage('ico_new.png');

        // define form fields
        $this->formFields = array( $numeroSRP, $nome, $nroProcesso, $uasg, $validadeAta, $aprovado, $item_id,$descricaoSumaria,$prazoEntrega, $valorUnitario,$quantidade,$total, $add_product, $save_button, $new_button);
        $this->form->setFields($this->formFields);

        $table_master->addRowSet(array($save_button, $new_button), '', '')->class = 'tformaction'; // CSS class
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 80%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }

    /**
     * On product change
     */
    static function onProductChange($params) {
        if (isset($params['product_id']) && $params['product_id']) {
            try {
                TTransaction::open('samples');

                $product = new Product($params['product_id']);
                $fill_data = new StdClass;
                $fill_data->product_price = $product->sale_price;
                TForm::sendData('form_Sale', $fill_data);
                TTransaction::close();
            } catch (Exception $e) { // in case of exception
                new TMessage('error', '<b>Error</b> ' . $e->getMessage());
                TTransaction::rollback();
            }
        }
    }

    /**
     * Clear form
     * @param $param URL parameters
     */
    function onClear($param) {
        $this->form->clear();
        TSession::setValue('requisicao_itens', array());
        $this->onReload($param);
    }

    /**
     * Add a product into item list
     * @param $param URL parameters
     */
    public function onProductAdd($param) {
        try {
            TTransaction::open('saciq');
            $data = $this->form->getData();
            
            
            //if ((!$data->item_id) || (!$data->valorUnitario) || (!$data->quantidade) || (!$data->prazoEntrega) || (!$data->justificativa)) {
            //    throw new Exception('Ver');
           // }          

            $item = new Item($data->item_id);

            $requisicao_itens = TSession::getValue('requisicao_itens');
            $key = (int) $data->item_id;
            $requisicao_itens[$key] = array('item_id' => $data->item_id,
                'descricaoSumaria' => $item->descricaoSumaria,
                'quantidade' => $data->quantidade,
                'valorUnitario' => $data->valorUnitario,
                'total' => $data->quantidade * $data->valorUnitario);

            TSession::setValue('requisicao_itens', $requisicao_itens);

            // clear product form fields after add
            $data->item_id = '';
            $data->descricaoSumaria = '';
            $data->quantidade = '';
            $data->valorUnitario = '';
            $data->total = '';
            TTransaction::close();
            $this->form->setData($data);

            $this->onReload($param); // reload the sale items
        } catch (Exception $e) {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Edit a product from item list
     * @param $param URL parameters
     */
    public function onEditItemProduto($param) {
        $data = $this->form->getData();

        // read session items
        $requisicao_itens = TSession::getValue('requisicao_itens');

        // get the session item
        $sale_item = $requisicao_itens[(int) $param['items_item_id']];

        $data->product_id = $param['items_item_id'];
        $data->product_name = $sale_item['product_name'];
        $data->product_amount = $sale_item['product_amount'];
        $data->product_price = $sale_item['product_price'];
        $data->product_discount = $sale_item['product_discount'];

        // fill product fields
        $this->form->setData($data);

        $this->onReload($param);
    }

    /**
     * Delete a product from item list
     * @param $param URL parameters
     */
    public function onDeleteItem($param) {
        $data = $this->form->getData();

        $data->product_id = '';
        $data->product_name = '';
        $data->product_amount = '';
        $data->product_price = '';
        $data->product_discount = '';

        // clear form data
        $this->form->setData($data);

        // read session items
        $requisicao_itens = TSession::getValue('requisicao_itens');

        // delete the item from session
        unset($requisicao_itens[(int) $param['items_item_id']]);
        TSession::setValue('requisicao_itens', $requisicao_itens);

        // reload sale items
        $this->onReload($param);
    }

    /**
     * Reload the item list
     * @param $param URL parameters
     */
    public function onReload($param) {
        // read session items
        $requisicao_itens = TSession::getValue('requisicao_itens');

        $this->product_list->clear(); // clear product list
        $data = $this->form->getData();

        if ($requisicao_itens) {
            $cont = 1;
            foreach ($requisicao_itens as $requisicao_itens_id => $itens) {
                $item_name = 'prod_' . $cont++;
                $item = new StdClass;

                // create action buttons
                $action_del = new TAction(array($this, 'onDeleteItem'));
                $action_del->setParameter('items_item_id', $requisicao_itens_id);

                $action_edi = new TAction(array($this, 'onEditItemProduto'));
                $action_edi->setParameter('items_item_id', $requisicao_itens_id);

                $button_del = new TButton('delete_product' . $cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction($action_del, '');
                $button_del->setImage('fa:trash-o');

                $button_edi = new TButton('edit_product' . $cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction($action_edi, '');
                $button_edi->setImage('fa:edit');

                $item->edit = $button_edi;
                $item->delete = $button_del;

                $this->formFields[$item_name . '_edit'] = $item->edit;
                $this->formFields[$item_name . '_delete'] = $item->delete;
                $item->id = -1;
                $item->item_id = $itens['item_id'];
                $item->descricaoSumaria = $itens['descricaoSumaria'];
                $item->quantidade = $itens['quantidade'];
                $item->valorUnitario = $itens['valorUnitario'];
                $item->total = $itens['total'];

                $row = $this->product_list->addItem($item);
                $row->onmouseover = '';
                $row->onmouseout = '';
            }

            $this->form->setFields($this->formFields);
        }

        $this->loaded = TRUE;
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param) {
        try {
            TTransaction::open('samples');

            if (isset($param['key'])) {
                $key = $param['key'];
                $object = new Sale($key);
                $requisicao_itens = $object->getSaleItems();

                $session_items = array();
                foreach ($requisicao_itens as $item) {
                    $session_items[$item->product_id] = $item->toArray();
                    $session_items[$item->product_id]['product_id'] = $item->product_id;
                    $session_items[$item->product_id]['product_name'] = $item->product->description;
                    $session_items[$item->product_id]['product_amount'] = $item->amount;
                    $session_items[$item->product_id]['product_price'] = $item->sale_price;
                    $session_items[$item->product_id]['product_discount'] = $item->discount;
                }
                TSession::setValue('requisicao_itens', $session_items);

                $this->form->setData($object); // fill the form with the active record data
                $this->onReload($param); // reload sale items list
                TTransaction::close(); // close transaction
            } else {
                $this->form->clear();
                TSession::setValue('requisicao_itens', null);
                $this->onReload($param);
            }
        } catch (Exception $e) { // in case of exception
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Save the sale and the sale items
     */
    function onSave() {
        try {
            // open a transaction with database 'samples'
            TTransaction::open('samples');

            $sale = $this->form->getData('Sale');
            $this->form->validate(); // form validation
            // get session items
            $requisicao_itens = TSession::getValue('requisicao_itens');

            if ($requisicao_itens) {
                $total = 0;
                foreach ($requisicao_itens as $sale_item) {
                    $item = new SaleItem;
                    $item->product_id = $sale_item['product_id'];
                    $item->sale_price = $sale_item['product_price'];
                    $item->amount = $sale_item['product_amount'];
                    $item->discount = $sale_item['product_discount'];
                    $item->total = ($sale_item['product_price'] * $sale_item['product_amount']) - $sale_item['product_amount'];

                    $sale->addSaleItem($item);
                    $total += ($item->sale_price * $item->amount) - $item->discount;
                }
            }
            $sale->total = $total;
            $sale->store(); // stores the object
            $this->form->setData($sale); // keep form data
            TTransaction::close(); // close the transaction
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        } catch (Exception $e) { // in case of exception
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback();
        }
    }

    /**
     * Show the page
     */
    public function show() {
        // check if the datagrid is already loaded
        if (!$this->loaded AND ( !isset($_GET['method']) OR $_GET['method'] !== 'onReload')) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }

}

?>