<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TMinValueValidator;
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
use Adianti\Widget\Form\THidden;
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
    private $loaded;

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
        $numeroItem = new TSeekButton('numeroItem');
        $item_id = new THidden('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario = new TEntry('valorUnitario');
        $quantidade = new TEntry('quantidade');
        $prazoEntrega = new TEntry('prazoEntrega');
        $justificativa = new TEntry('justificativa');

        $addItem = new TButton('addItem');

        //ações dos campos
        $numeroSRP->setAction(new TAction(array(new SrpSeek(), 'onReload')));
        $numeroItem->setAction(new TAction(array(new ItemSeek(), 'onReload')));
        $numeroSRP->setExitAction(new TAction(array($this, 'onExitSRP')));

        $addItem->setAction(new TAction(array($this, 'onAddItem')), 'Adicionar');
        $addItem->setImage('fa:save');

        //$onProductChange = new TAction(array($this, 'onProductChange'));
        //$item_id->setExitAction($onProductChange);
        $quantidade->setExitAction(new TAction(array($this, 'onValidaQuantidade')));


        //validadores
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $numeroItem->addValidation('Item', new TRequiredValidator());
        $valorUnitario->addValidation('Preço', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TMinValueValidator(), array(1));
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
        $numeroItem->setSize(60);
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
        $box->add($numeroItem);
        $box->add($descricaoSumaria)->style = 'width : 75%;display:inline-block;';
        $row->addCell($box); //->style = 'width : 85%';
        $table_itens->addRowSet($item_id);
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


        $GnumeroItem = new TDataGridColumn('numeroItem', 'Item', 'center', 50);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição', 'left', 230);
        $Gquantidade = new TDataGridColumn('quantidade', 'Quantidade', 'left', 110);
        $GvalorUnitario = new TDataGridColumn('valorUnitario', 'Preço', 'right', 110);
        $Gtotal = new TDataGridColumn('total', 'Total', 'right', 160);


        $edit = new TDataGridAction(array($this, 'onEditItem'));
        $edit->setLabel('Editar');
        $edit->setImage('ico_edit.png');
        $edit->setField('numeroItem');

        $delete = new TDataGridAction(array($this, 'onDeleteItem'));
        $delete->setLabel('Deletar');
        $delete->setImage('ico_delete.png');
        $delete->setField('numeroItem');

        $this->datagrid->addAction($edit);
        $this->datagrid->addAction($delete);

        $this->datagrid->addColumn($GnumeroItem);
        $this->datagrid->addColumn($GdescricaoSumaria);
        $this->datagrid->addColumn($Gquantidade);
        $this->datagrid->addColumn($GvalorUnitario);
        $this->datagrid->addColumn($Gtotal);
        $this->datagrid->createModel();


        $this->form_requisicao->setFields(array($numeroSRP, $nome, $numeroProcesso, $uasg, $validadeAta));

        $this->form_itens->setFields(array($item_id, $numeroItem, $descricaoSumaria, $valorUnitario, $quantidade, $prazoEntrega, $justificativa, $addItem));

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
            $key = (int) $itens->numeroItem;
            $itens->total = $itens->quantidade * $itens->valorUnitario;
            $requisicao_itens[$key] = $itens;

            TSession::setValue('requisicao_itens', $requisicao_itens);

            // clear product form fields after add
            $itens = new stdClass();
            $itens->item_id = '';
            $itens->numeroItem = '';
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
            $this->form_requisicao->setData($requisicao);
            new TMessage('error', $e->getMessage());
        }
    }

    static public function onValidaQuantidade($param) {

        $quantidade = $param['quantidade'];
        $item_id = $param['item_id'];
        //$data = new stdClass();

        if ((!$item_id) || (!TSession::getValue('SRP_id'))) {
            return;
        }
        

        if (!is_numeric($quantidade)) {
            new TMessage('error', 'Digite um número inteiro no campo quantidade');
            return;
        }

        if ($quantidade <= 0) {
            //$data->quantidade = 0;
            new TMessage('error', 'Quantidade inválida');
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
        
        if ($item->quantidadeDisponivel < $quantidade){
            new TMessage('error', 'Quantidade Indisponível. <br>Disponível: '.$item->quantidadeDisponivel);
            return;
        }
        
    }

    static public function onExitSRP($param) {
        TSession::delValue('requisicao_itens');
        $obj = new stdClass();
        $obj->item_id = '';
        $obj->descricaoSumaria = '';
        $obj->valorUnitario = '';
        $obj->quantidade = '';
        $obj->prazoEntrega = '60 Dias';
        $obj->justificativa = '';
        TForm::sendData('form_itens', $obj);        
    }

    public function onDeleteItem($param) {
        // get the cart objects from session
        $data = $this->form_itens->getData();
        $this->form_itens->setData( $data );
        $items = TSession::getValue('requisicao_itens');
        unset($items[ $param['key'] ]); // remove the product from the array
        TSession::setValue('requisicao_itens', $items); // put the array back to the session
        
        // reload datagrid
        $this->onReload( func_get_arg(0) );
    }
    
    public function onEdit($param){
        
    }

    public function onEditItem($param) {
        
    }

    public function onReload($param) {

        try
        {
            $requisicao = TSession::getValue('form_requisicao');
            $this->form_requisicao->sendData('form_requisicao', $requisicao);
            $this->datagrid->clear(); // clear datagrid
            $items = TSession::getValue('requisicao_itens');
            var_dump($param);
            if ($items)
            {
                foreach ($items as $object)
                {
                    // add the item inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e);
        }
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
