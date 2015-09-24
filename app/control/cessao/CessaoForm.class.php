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
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TSeekButton;
use Adianti\Widget\Wrapper\TDBSeekButton;

/*
 * Copyright (C) 2015 Anderson
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Description of CessaoForm
 *
 * @author Anderson
 */
class CessaoForm extends TPage {

    private $form_cessao;
    private $form_itens;
    private $datagrid;
    private $loaded;

    function __construct() {
        parent::__construct();

        //cria os containers
        $this->form_cessao = new TForm('form_cessao');
        $this->form_cessao->class = 'tform';
        $this->form_itens = new TForm('form_itens');
        $this->form_itens->class = 'tform';
        $table_cessao = new TTable;
        $table_cessao->width = '100%';
        $table_itens = new TTable;
        $table_itens->width = '100%';

        //empacota os dados
        $this->form_cessao->add($table_cessao);
        $this->form_itens->add($table_itens);

        //campos da cessao;
        $numeroSRP = new TSeekButton('numeroSRP');
        $nome = new TEntry('nome');
        $numeroProcessoOrigem = new TEntry('numeroProcessoOrigem');
        $uasg = new TEntry('uasg');
        $validadeAta = new TEntry('validade');
        $numeroCessao = new TEntry('numeroCessao');
        $campusID = new TDBSeekButton('campusID', 'SACIQ', 'form_cessao', 'Campus', 'nome', 'campusID', 'campusNome');
        $campusNome = new TEntry('campusNome');

        //campos do itens
        $numeroItem = new TSeekButton('numeroItem');
        $item_id = new THidden('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario = new TEntry('valorUnitario');
        $quantidade = new TEntry('quantidade');
        //$prazoEntrega = new TEntry('prazoEntrega');
        //$justificativa = new TEntry('justificativa');

        $addItem = new TButton('addItem');
        $save = new TButton('save');
        $new = new TButton('new');
        $list = new TButton('list');

        //ações dos campos
        $numeroSRP->setAction(new TAction(array(new SrpSeekCessao(), 'onReload')));
        $numeroCessao->setExitAction(new TAction(array($this, 'onExitNumeroProcesso')));
        $campusID->setExitAction(new TAction(array($this, 'onExitCampus')));
        $numeroItem->setAction(new TAction(array(new ItemSeekCessao(), 'onReload')));


        $addItem->setAction(new TAction(array($this, 'onAddItem')), 'Adicionar');
        $addItem->setImage('fa:plus-square-o');

        $save->setAction(new TAction(array($this, 'onSave')), 'Salvar');
        $save->setImage('ico_save.png');

        $new->setAction(new TAction(array($this, 'onEdit')), 'Novo');
        $new->setImage('ico_new.png');

        $list->setAction(new TAction(array('CessaoList', 'onReload')), 'Voltar para a listagem');
        $list->setImage('ico_datagrid.png');

        $quantidade->setExitAction(new TAction(array($this, 'onValidaQuantidade')));


        //validadores
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $numeroItem->addValidation('Item', new TRequiredValidator());
        $valorUnitario->addValidation('Preço', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TMinValueValidator(), array(1));
        $numeroCessao->addValidation('Nº Cessão', new TRequiredValidator());
        $campusID->addValidation('Campus', new TRequiredValidator());

        //outras propriedades
        $descricaoSumaria->setEditable(false);
        $nome->setEditable(false);
        $numeroProcessoOrigem->setEditable(false);
        $uasg->setEditable(false);
        $validadeAta->setEditable(false);
        $valorUnitario->setEditable(false);
        $numeroSRP->setSize(80);
        $nome->setSize(300);
        $nome->setProperty('style', 'margin-right: 10px', false);
        $numeroProcessoOrigem->setSize(160);
        $uasg->setSize(70);
        $validadeAta->setSize(85);
        $numeroItem->setSize(60);
        $descricaoSumaria->setSize('100%');
        $descricaoSumaria->setProperty('style', 'margin-right: 10px', false);
        $numeroCessao->setSize(230);
        $campusID->setSize(50);
        $campusNome->setSize('100%');
        $campusNome->setEditable(false);
        $validadeAta->setMask('dd/mm/yyyy');
        $quantidade->class = 'frm_number_only';
        //$prazoEntrega->setValue('60 Dias');
        $addItem->setProperty('style', 'margin: 0 0 10px 10px;', false);

        $row = $table_cessao->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell(new TLabel('Cessão de quantitativo'));
        $cell->colspan = 4;
        $row = $table_cessao->addRow();
        $row->addCell(new TLabel('Nº SRP:'))->width = '150px';
        $row->addCell($numeroSRP);
        $row->addCell(new TLabel('Nome Licitação:'))->width = '150px';
        $row->addCell($nome);
        $table_cessao->addRowSet(new TLabel('Proc. Orig:'), $numeroProcessoOrigem, new TLabel('UASG:'), $uasg);
        $table_cessao->addRowSet(new TLabel('Validade da Ata:'), $validadeAta, new TLabel('Nº Cessão:'), $numeroCessao);
        $row = $table_cessao->addRow();
        $row->addCell(new TLabel('Campus:'));
        $box = new THBox();
        $box->add($campusID);
        $box->add($campusNome)->style = 'width: 75%; display : inline-block;';
        $row->addCell($box)->colspan = 3;
        
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
        //$table_itens->addRowSet(new TLabel('Prazo de entrega:'), $prazoEntrega);
        //$table_itens->addRowSet(new TLabel('Justificativa:'), $justificativa);
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

        //transformers
        $GvalorUnitario->setTransformer(array($this, 'formatValor'));
        $Gtotal->setTransformer(array($this, 'formatValor'));


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

        $this->form_cessao->setFields(array($numeroSRP, $nome, $numeroProcessoOrigem, $uasg, $validadeAta, $numeroCessao, $campusID, $campusNome, $new, $save, $list));
        $this->form_itens->setFields(array($item_id, $numeroItem, $descricaoSumaria, $valorUnitario, $quantidade, $addItem));

        $hbox = new THBox();
        $hbox->add($save);
        $hbox->add($new);
        $hbox->add($list);

        $vbox = new TVBox;
        $vbox->add($this->form_cessao);
        //$vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->form_itens);
        //$vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->datagrid);
        $vbox->add(new TLabel('&nbsp;'));
        $vbox->add($hbox);
        parent::add($vbox);
    }    

    public function onAddItem($param) {
        $cessao = TSession::getValue('form_cessao');
        try {
            TTransaction::open('saciq');
            $form_item = $this->form_itens->getData();

            $this->form_itens->validate();

            $item = new Item($form_item->item_id);

            if ($item->quantidadeDisponivel < $form_item->quantidade) {
                new TMessage('error', 'Quantidade Indisponível. <br>Disponível: ' . $item->quantidadeDisponivel);
                TTransaction::rollback();
                return;
            }

            $cessao_itens = TSession::getValue('cessao_itens');
            $key = (int) $form_item->numeroItem;
            $form_item->total = $form_item->quantidade * $form_item->valorUnitario;
            $cessao_itens[$key] = $form_item;

            TSession::setValue('cessao_itens', $cessao_itens);
            //var_dump($cessao_itens);
            // clear product form fields after add
            $form_item = new stdClass();
            $form_item->item_id = '';
            $form_item->numeroItem = '';
            $form_item->descricaoSumaria = '';
            $form_item->quantidade = '';
            $form_item->valorUnitario = '';
            $form_item->total = '';
            TTransaction::close();
            $this->form_itens->setData($form_item);
            $this->form_cessao->setData($cessao);
            $this->onReload($param); // reload the sale items
        } catch (Exception $e) {
            $this->form_itens->setData($this->form_itens->getData());
            $this->form_cessao->setData($cessao);
            new TMessage('error', $e->getMessage());
        }
    }
    
    static public function onExitCampus($param){        
        $campusID = $param['campusID'];
        $campusNome = $param['campusNome'];
        if (TSession::getValue('form_cessao')!== NULL && $campusID) {
            $form_cessao = TSession::getValue('form_cessao');
            $form_cessao->campusID = $campusID;
            $form_cessao->campusNome = $campusNome;            
            TSession::setValue('form_cessao', $form_cessao);
        }
    }

    static public function onExitNumeroProcesso($param) {
        $numeroCessao = $param['numeroCessao'];
        if (TSession::getValue('form_cessao')!== NULL && $numeroCessao) {
            $form_cessao = TSession::getValue('form_cessao');
            $form_cessao->numeroCessao = $numeroCessao;
            TSession::setValue('form_cessao', $form_cessao);
        }
    }

    public function formatValor($value, $object, $row) {
        $number = number_format($value, 2, ',', '.');
        return $number;
    }

    static public function onValidaQuantidade($param) {

        $quantidade = $param['quantidade'];
        $item_id = $param['item_id'];
        $numeroItem = $param['numeroItem'];
        //$data = new stdClass();
        if ((!$numeroItem) || (!$item_id) || (!TSession::getValue('SRP_id'))) {
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

        if ($item->quantidadeDisponivel < $quantidade) {
            new TMessage('error', 'Quantidade Indisponível. <br>Disponível: ' . $item->quantidadeDisponivel);
            return;
        }
    }

    public function onDeleteItem($param) {
        // get the cart objects from session
        $items = TSession::getValue('cessao_itens');
        unset($items[$param['key']]); // remove the product from the array
        TSession::setValue('cessao_itens', $items); // put the array back to the session
        // reload datagrid
        $this->onReload(func_get_arg(0));
    }

    public function onEdit($param) {
        if (isset($param) && isset($param['key']))
            $key = $param['key'];

        if (!isset($key)) {
            $form_cessao = new stdClass();
            $form_cessao->numeroSRP = '';
            $form_cessao->numeroProcessoOrigem = '';
            $form_cessao->numeroCessao = '';
            $form_cessao->validade = '';
            $form_cessao->nome = '';
            $form_cessao->uasg = '';
            $form_cessao->campusID = '';
            $form_cessao->campusNome = '';
            
            TSession::delValue('cessao_itens');
            TSession::delValue('form_cessao');
            TForm::sendData('form_cessao', $form_cessao);
            $this->onReload();
            return;
        }
        try {
            TTransaction::open('saciq');

            $cessao = new Cessao($key);
            $form_cessao = new stdClass();
            $form_cessao->id = $key;
            $form_cessao->numeroSRP = $cessao->srp->numeroSRP;
            $form_cessao->numeroProcessoOrigem = $cessao->srp->numeroProcesso;
            $form_cessao->numeroCessao = $cessao->numeroCessao;
            $form_cessao->validade = TDate::date2br($cessao->srp->validade);
            $form_cessao->nome = $cessao->srp->nome;
            $form_cessao->uasg       = $cessao->srp->uasg;
            $form_cessao->campusID   = $cessao->campus->id;
            $form_cessao->campusNome = $cessao->campus->nome;

            TSession::delValue('cessao_itens');
            TSession::setValue('SRP_id', $cessao->srp->id);

            foreach ($cessao->getItems() as $item_cessao) {
                $item = new stdClass();
                $item->item_id            = $item_cessao->id;
                $item->numeroItem         = $item_cessao->numeroItem;
                $item->descricaoSumaria   = $item_cessao->descricaoSumaria;
                $item->quantidade         = $item_cessao->quantidade;
                $item->valorUnitario      = $item_cessao->valorUnitario;
                $item->total              = $item_cessao->total;
                $itens[$item->numeroItem] = $item;
            }

            TSession::setValue('cessao_itens', $itens);
            TSession::setValue('form_cessao', $form_cessao);

            TForm::sendData('form_cessao', $form_cessao);
            $this->onReload();
            TTransaction::close();
        } catch (Exception $ex) {
            TTransaction::rollback();
            new TMessage('error', 'Erro: ' . $ex->getMessage());
        }
    }

    public function onEditItem($param) {
        $items = TSession::getValue('cessao_itens');
        $data = $items[$param['key']];

        $this->form_itens->setData($data);
        //TSession::setValue('cessao_itens', $items); // put the array back to the session        
        // reload datagrid
        $this->onReload(func_get_arg(0));
    }

    public function onReload() {

        try {
            $form_cessao = TSession::getValue('form_cessao');
            if (!$form_cessao) {
                $form_cessao = new stdClass();
                $form_cessao->numeroSRP = '';
                $form_cessao->numeroProcessoOrigem = '';
                $form_cessao->numeroCessao = '';
                $form_cessao->validade = '';
                $form_cessao->nome = '';
                $form_cessao->uasg = '';
                $form_cessao->campusID = '';
                $form_cessao->campusNome = '';
            }
            $this->form_cessao->sendData('form_cessao', $form_cessao);
            $this->datagrid->clear(); // clear datagrid
            $items = TSession::getValue('cessao_itens');
            //var_dump($items);
            if ($items) {
                foreach ($items as $object) {
                    // add the item inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            $this->loaded = true;
        } catch (Exception $e) { // in case of exception
            new TMessage('error', '<b>Error</b> ' . $e);
        }
    }

    public function onSave($param) {
        try {
            $this->form_cessao->validate(); // validate form data            
            $form_cessao_data = $this->form_cessao->getData();

            $form_cessao = TSession::getValue('form_cessao');
            $form_cessao->numeroCessao = $form_cessao_data->numeroCessao;
            $form_cessao->campusID = $form_cessao_data->campusID;
            $form_cessao->campusNome = $form_cessao_data->campusNome;


            $cessao_itens = TSession::getValue('cessao_itens');

            if (!isset($cessao_itens) || count($cessao_itens) == 0) {
                new TMessage('error', 'Insira ao menos 1 item');
                return;
            }

            TTransaction::open('saciq');
            if ($cessao_itens) {
                $id = isset($form_cessao->id) ? $form_cessao->id : NULL;
                $cessao = new Cessao($id); // create a new Sale
                $cessao->clearParts();
                
                $cessao->numeroCessao = $form_cessao->numeroCessao;
                $cessao->campus = new Campus($form_cessao->campusID);
                if (!$cessao->emissao){
                    $cessao->emissao = date("Y-m-d");
                }
                //$cessao->emissao = //TDate::date2us($form_cessao->emissao);//date("Y-m-d");
                $cessao->aprovado = 0;
                $cessao->srp = new Srp(TSession::getValue('SRP_id'));
                foreach ($cessao_itens as $item) {
                    $item_cessao = new Item($item->item_id);
                    $item_cessao->quantidade = $item->quantidade;
                    $item_cessao->quantidade = str_replace('.', '', $item->quantidade);
                    $item_cessao->quantidade = str_replace(',', '.', $item->quantidade);
                    $cessao->addItem($item_cessao); // add the item to the Sale
                }
                $cessao->store(); // store the Sale

                TSession::delValue('cessao_itens');
                TSession::delValue('form_cessao'); 
                TSession::delValue('SRP_id');                

                new TMessage('info', 'Cessão salva');
            }
            TTransaction::close();
            $this->onReload();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function show() {
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }

}
