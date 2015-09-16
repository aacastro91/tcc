<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TSeekButton;

/**
 * Description of RequisicaoForm
 *
 * @author Anderson
 */
class RequisicaoForm extends TPage {

    private $form_requisicao;
    private $form_itens;
    private $datagrid;
    private $formFields;

    function __construct() {
        parent::__construct();

        //cria os containers
        $this->form_requisicao = new TForm('form_requisicao');
        $this->form_requisicao->class = 'tform';
        $this->form_itens = new TForm('form_itens');
        $this->form_itens->class = 'tform';
        $table_requisicao = new TTable;
        $table_requisicao->width = '100%';
        $table_itens = new TTable;
        $table_itens->width = '100%';

        //empacota os dados
        $this->form_requisicao->add($table_requisicao);
        $this->form_itens->add($table_itens);

        //campos da requisicao;
        $numeroSRP = new TSeekButton('numeroSRP');
        $nome = new TEntry('nome');
        $numeroProcesso = new TEntry('numeroProcesso');
        $uasg = new TEntry('uasg');
        $validadeAta = new TEntry('validade');

        //campos do itens
        $item_id = new TSeekButton('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario = new TEntry('valorUnitario');
        $quantidade = new TEntry('quantidade');
        $prazoEntrega = new TEntry('prazoEntrega');
        $justificativa = new TEntry('justificativa');

        $addItem = new TButton('addItem');

        //ações dos campos
        $numeroSRP->setAction(new TAction(array(new SrpSeek(), 'onReload')));
        $item_id->setAction(new TAction(array(new ItemSeek(), 'onReload')));
        $numeroSRP->setExitAction(new TAction(array($this, 'onExitSRP')));

        $addItem->setAction(new TAction(array($this, 'onAddItem')), 'Adicionar');
        $addItem->setImage('fa:save');


        //$onProductChange = new TAction(array($this, 'onProductChange'));
        //$item_id->setExitAction($onProductChange);
        $quantidade->setExitAction(new TAction(array($this, 'onValidaQuantidade')));


        //validadores
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $item_id->addValidation('Item', new TRequiredValidator());
        $valorUnitario->addValidation('Preço', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $justificativa->addValidation('Justificativa', new TRequiredValidator());
        $prazoEntrega->addValidation('Prazo de entrega', new TRequiredValidator());

        //outras propriedades
        $descricaoSumaria->setEditable(false);
        $nome->setEditable(false);
        $numeroProcesso->setEditable(false);
        $uasg->setEditable(false);
        $validadeAta->setEditable(false);
        $valorUnitario->setEditable(false);
        $numeroSRP->setSize(80);
        $nome->setSize(300);
        $nome->setProperty('style', 'margin-right: 10px', false);
        $numeroProcesso->setSize(160);
        $uasg->setSize(70);
        $validadeAta->setSize(85);
        $item_id->setSize(60);
        $descricaoSumaria->setSize(490);
        $descricaoSumaria->setProperty('style', 'margin-right: 10px', false);
        $prazoEntrega->setSize(90);
        $justificativa->setSize(400);
        $validadeAta->setMask('dd/mm/yyyy');
        $quantidade->class = 'frm_number_only';
        $prazoEntrega->setValue('60 Dias');
        $addItem->setProperty('style', 'margin: 0 0 10px 10px;', false);

        $row = $table_requisicao->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell(new TLabel('Requisição de quantitativo'));
        $cell->colspan = 4;
        $row = $table_requisicao->addRow();
        $row->addCell(new TLabel('Nº SRP:'))->width = '150px';
        $row->addCell($numeroSRP);
        $row->addCell(new TLabel('Nome Licitação:'))->width = '150px';
        $row->addCell($nome);
        $table_requisicao->addRowSet(new TLabel('Proc. Orig:'), $numeroProcesso, new TLabel('UASG:'), $uasg);
        $table_requisicao->addRowSet(new TLabel('Validade da Ata:'), $validadeAta);


        $row = $table_itens->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell(new TLabel('Itens'));
        $cell->colspan = 4;

        $row = $table_itens->addRow();
        $row->addCell(new TLabel('Item:'));
        $box = new THBox();
        $box->add($item_id);
        $box->add($descricaoSumaria)->style = 'width : 75%;display:inline-block;';
        $row->addCell($box); //->style = 'width : 85%';
        $table_itens->addRowSet(new TLabel('Preço:'), $valorUnitario);
        $table_itens->addRowSet(new TLabel('Quantidade:'), $quantidade);
        $table_itens->addRowSet(new TLabel('Prazo de entrega:'), $prazoEntrega);
        $table_itens->addRowSet(new TLabel('Justificativa:'), $justificativa);
        $table_itens->addRowSet($addItem);

        parent::include_css('app/resources/custom-table.css');
        $this->datagrid = new TDataGrid();
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(175);
        $this->datagrid->makeScrollable();
        $this->datagrid->disableDefaultClick();


        $Gitem_id = new TDataGridColumn('item_id', 'Item', 'center', 50);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição', 'left', 240);
        $Gquantidade = new TDataGridColumn('quantidade', 'Quantidade', 'left', 110);
        $GvalorUnitario = new TDataGridColumn('valorUnitario', 'Preço', 'right', 110);
        $Gtotal = new TDataGridColumn('total', 'Total', 'right', 160);


        $edit = new TDataGridAction(array($this, 'onEdit'));
        $edit->setLabel('Editar');
        $edit->setImage('ico_edit.png');
        $edit->setField('id');

        $delete = new TDataGridAction(array($this, 'onDelete'));
        $delete->setLabel('Deletar');
        $delete->setImage('ico_delete.png');
        $delete->setField('id');

        $this->datagrid->addAction($edit);
        $this->datagrid->addAction($delete);

        $this->datagrid->addColumn($Gitem_id);
        $this->datagrid->addColumn($GdescricaoSumaria);
        $this->datagrid->addColumn($Gquantidade);
        $this->datagrid->addColumn($GvalorUnitario);
        $this->datagrid->addColumn($Gtotal);
        $this->datagrid->createModel();


        $this->form_requisicao->setFields(array($numeroSRP, $nome, $numeroProcesso, $uasg, $validadeAta));

        $this->form_itens->setFields(array($item_id, $descricaoSumaria, $valorUnitario, $quantidade, $prazoEntrega, $justificativa, $addItem));

        $vbox = new TVBox;
        $vbox->add($this->form_requisicao);
        //$vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->form_itens);
        //$vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->datagrid);
        parent::add($vbox);
    }

    public function onAddItem($param) {
        try {
            TTransaction::open('saciq');
            $itens = $this->form_itens->getData();
            $requisicao = TSession::getValue('form_requisicao');

            $this->form_itens->validate();

            $item = new Item($itens->item_id);

            $requisicao_itens = TSession::getValue('requisicao_itens');
            $key = (int) $itens->item_id;
            $requisicao_itens[$key] = array('item_id' => $itens->item_id,
                'descricaoSumaria' => $item->descricaoSumaria,
                'quantidade' => $itens->quantidade,
                'valorUnitario' => $itens->valorUnitario,
                'prazoEntrega' => $itens->prazoEntrega,
                'justificativa' => $itens->justificativa,
                'total' => $itens->quantidade * $itens->valorUnitario);

            TSession::setValue('requisicao_itens', $requisicao_itens);

            // clear product form fields after add
            $itens->item_id = '';
            $itens->descricaoSumaria = '';
            $itens->quantidade = '';
            $itens->valorUnitario = '';
            $itens->justificativa = '';
            $itens->total = '';
            TTransaction::close();
            $this->form_itens->setData($itens);
            $this->form_requisicao->setData($requisicao);
            $this->onReload($param); // reload the sale items
        } catch (Exception $e) {
            $this->form_itens->setData($this->form_itens->getData());
            $this->form_requisicao->setData($this->form_requisicao->getData());
            new TMessage('error', $e->getMessage());
        }
    }

    static public function onValidaQuantidade($param) {
        $quantidade = $param['quantidade'];
        $item_id = $param['item_id'];
        $data = new stdClass();

        if (!$item_id) {
            return;
        }

        try {
            TTransaction::open('saciq');
            $item = new Item($item_id);            
            TTransaction::close();
        } catch (Exception $ex) {
            TTransaction::rollback();
        }

        if (!isset($item) && (!$item)) {
            return;
        }

        if (!is_numeric($quantidade)) {
            new TMessage('error', 'Digite um número inteiro no campo quantidade');
            return;
        }

        if ($quantidade <= 0) {
            $data->quantidade = 0;
            new TMessage('error', 'Quantidade inválida');
            return;
        }
    }

    static public function onExitSRP($param) {
        $obj = new stdClass();
        $obj->item_id = '';
        $obj->descricaoSumaria = '';
        $obj->valorUnitario = '';
        $obj->quantidade = '';
        $obj->prazoEntrega = '60 Dias';
        $obj->justificativa = '';
        TForm::sendData('form_itens', $obj);
    }

    public function onDelete($param) {
        
    }

    public function onEdit($param) {
        
    }

    public function onReload($param) {
        var_dump($param);
        // read session items
        $requisicao_itens = TSession::getValue('requisicao_itens');

        $this->datagrid->clear(); // clear product list
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

    /* static public function onProductChange($params){
      print_r($params);
      $data = TForm::getFormByName('form_requisicao');
      if (isset($params['item_id']) && $params['item_id']) {
      try {
      TTransaction::open('saciq');

      $item = new Item($params['item_id']);
      $fill_data = new StdClass;
      $fill_data->valorUnitario = $item->valorUnitario;
      TForm::sendData('form_itens', $fill_data);
      TTransaction::close();
      } catch (Exception $e) { // in case of exception
      new TMessage('error', '<b>Error</b> ' . $e->getMessage());
      TTransaction::rollback();
      }
      }
      } */
}
