<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TMaxLengthValidator;
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
        $numeroProcessoOrigem = new TEntry('numeroProcessoOrigem');
        $uasg = new TEntry('uasg');
        $validadeAta = new TEntry('validade');
        $numeroProcesso = new TEntry('numeroProcesso');
        $emissao = new TDate('emissao');

        //campos do itens
        $numeroItem = new TSeekButton('numeroItem');
        $item_id = new THidden('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario = new TEntry('valorUnitario');
        $quantidade = new TEntry('quantidade');
        $prazoEntrega = new TEntry('prazoEntrega');
        $justificativa = new TEntry('justificativa');

        $addItem = new TButton('addItem');
        $save = new TButton('save');
        $new = new TButton('new');
        $list = new TButton('list');

        //ações dos campos
        $numeroSRP->setAction(new TAction(array(new SrpSeekRequisicao(), 'onReload')));
        $numeroProcesso->setExitAction(new TAction(array($this, 'onExitNumeroProcesso')));
        $emissao->setExitAction(new TAction(array($this, 'onExitEmissao')));
        $numeroItem->setAction(new TAction(array(new ItemSeekRequisicao(), 'onReload')));


        $addItem->setAction(new TAction(array($this, 'onAddItem')), 'Adicionar');
        $addItem->setImage('fa:plus-square-o');

        $save->setAction(new TAction(array($this, 'onSave')), 'Salvar');
        $save->setImage('ico_save.png');

        $new->setAction(new TAction(array($this, 'onEdit')), 'Novo');
        $new->setImage('ico_new.png');

        $list->setAction(new TAction(array('RequisicaoList', 'onReload')), 'Voltar para listagem');
        $list->setImage('ico_datagrid.png');

        //$onProductChange = new TAction(array($this, 'onProductChange'));
        //$item_id->setExitAction($onProductChange);
        $quantidade->setExitAction(new TAction(array($this, 'onValidaQuantidade')));


        //validadores
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $emissao->addValidation('Emissão', new TRequiredValidator());
        $numeroItem->addValidation('Item', new TRequiredValidator());
        $valorUnitario->addValidation('Preço', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TMinValueValidator(), array(1));
        $quantidade->addValidation('Quantidade', new TMaxLengthValidator(), array(11));
        $justificativa->addValidation('Justificativa', new TRequiredValidator());
        $justificativa->addValidation('Justificativa', new TMaxLengthValidator(), array(100));
        $prazoEntrega->addValidation('Prazo de entrega', new TRequiredValidator());
        $prazoEntrega->addValidation('Prazo de entrega', new TMaxLengthValidator(), array(20));
        $numeroProcesso->addValidation('Nº Processo', new TRequiredValidator());
        $numeroProcesso->addValidation('Nº Processo', new TMaxLengthValidator(), array(30));

        //outras propriedades
        $descricaoSumaria->setEditable(false);
        $nome->setEditable(false);
        $numeroProcessoOrigem->setEditable(false);
        $uasg->setEditable(false);
        $validadeAta->setEditable(false);
        $valorUnitario->setEditable(false);
        $numeroSRP->setSize(80);
        $numeroSRP->setMaxLength(10);
        $emissao->setProperty('style', 'margin-right: 0px;',false);
        $nome->setSize(300);
        $nome->setProperty('style', 'margin-right: 10px', false);
        $numeroProcessoOrigem->setSize(160);
        $uasg->setSize(70);
        $validadeAta->setSize(85);
        $numeroProcesso->setMaxLength(30);
        $numeroProcesso->setTip('Número do processo gerado no SIGA');
        $numeroItem->setSize(60);
        $numeroItem->setMaxLength(11);
        $descricaoSumaria->setSize(490);
        $descricaoSumaria->setProperty('style', 'margin-right: 10px', false);
        $prazoEntrega->setSize(90);
        $justificativa->setSize(400);
        $justificativa->setMaxLength(100);
        $emissao->setSize(90);
        $emissao->setMask('dd/mm/yyyy');
        $emissao->setValue(date('d/m/Y'));
        $validadeAta->setMask('dd/mm/yyyy');
        $quantidade->class = 'frm_number_only';
        $quantidade->setMaxLength(11);
        $prazoEntrega->setValue('60 Dias');
        $prazoEntrega->setMaxLength(20);
        
        //$addItem->setProperty('style', 'margin: 0 0 10px 10px;', false);

        $row = $table_requisicao->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell(new TLabel('Requisição de quantitativo'));
        $cell->colspan = 4;
        $row = $table_requisicao->addRow();
        $row->addCell(new TLabel('Nº SRP:'))->width = '150px';
        $row->addCell($numeroSRP);
        $row->addCell(new TLabel('Nome Licitação:'))->width = '150px';
        $row->addCell($nome);
        $table_requisicao->addRowSet(new TLabel('Proc. Orig:'), $numeroProcessoOrigem, new TLabel('UASG:'), $uasg);
        $table_requisicao->addRowSet(new TLabel('Validade da Ata:'), $validadeAta, new TLabel('Nº Processo:'), $numeroProcesso);
        $table_requisicao->addRowSet(new TLabel('Data Emissão:'), $emissao);

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
        $row = $table_itens->addRow();
        $row->class = 'tformaction';
        $row->addCell($addItem)->colspan = 2;
        
        //$table_itens->addRowSet($addItem);

        parent::include_css('app/resources/custom-table.css');
        $this->datagrid = new TDataGrid();
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(175);
        $this->datagrid->makeScrollable();
        $this->datagrid->disableDefaultClick();


        $GnumeroItem = new TDataGridColumn('numeroItem', 'Item', 'center', 50);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição', 'left', 230);
        $Gquantidade = new TDataGridColumn('quantidade', 'Quantidade', 'right', 110);
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


        $this->form_requisicao->setFields(array($numeroSRP, $nome, $numeroProcessoOrigem, $uasg, $validadeAta, $numeroProcesso, $emissao, $new, $save, $list));

        $this->form_itens->setFields(array($item_id, $numeroItem, $descricaoSumaria, $valorUnitario, $quantidade, $prazoEntrega, $justificativa, $addItem));



        $hbox = new THBox();
        $hbox->add($save);
        $hbox->add($new);
        $hbox->add($list);
        
        $table_grid = new TTable();
        $table_grid->style = 'width: 100%;border-spacing: 0px;';
        $table_grid->addRowSet($this->datagrid);
        $row = $table_grid->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($hbox)->colspan = 2;

        $vbox = new TVBox;
        $vbox->add($this->form_requisicao);
        $vbox->add($this->form_itens);
        $vbox->add($table_grid);
        $vbox->add(new TLabel('&nbsp;'));
        parent::add($vbox);
    }

    public function onAddItem($param) {
        $requisicao = TSession::getValue('form_requisicao');
        try {
            TTransaction::open('saciq');
            $form_item = $this->form_itens->getData();

            $this->form_itens->validate();

            $item = new Item($form_item->item_id);

            $itens_o = TSession::getValue('requisicao_itens_o');
            if (isset($itens_o[$form_item->numeroItem])){
                $object = $itens_o[$form_item->numeroItem];
                $item->estoqueDisponivel = $item->estoqueDisponivel + $object->quantidade;
            }
        
            if ($item->estoqueDisponivel < $form_item->quantidade) {
                new TMessage('error', 'Quantidade Indisponível. <br>Disponível: ' . $item->estoqueDisponivel);
                TTransaction::rollback();
                return;
            }

            $requisicao_itens = TSession::getValue('requisicao_itens');
            $key = (int) $form_item->numeroItem;
            $form_item->total = $form_item->quantidade * $form_item->valorUnitario;
            $requisicao_itens[$key] = $form_item;

            TSession::setValue('requisicao_itens', $requisicao_itens);
            //var_dump($requisicao_itens);
            // clear product form fields after add
            $form_item = new stdClass();
            $form_item->item_id = '';
            $form_item->numeroItem = '';
            $form_item->descricaoSumaria = '';
            $form_item->quantidade = '';
            $form_item->valorUnitario = '';
            $form_item->justificativa = '';
            $form_item->prazoEntrega = '60 Dias';
            $form_item->total = '';
            TTransaction::close();
            $this->form_itens->setData($form_item);
            $this->form_requisicao->setData($requisicao);
            $this->onReload($param); // reload the sale items
        } catch (Exception $e) {
            $this->form_itens->setData($this->form_itens->getData());
            $this->form_requisicao->setData($requisicao);
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
        }
    }
    
    static public function onExitEmissao($param) {
        $emissao = $param['emissao'];
        if (TSession::getValue('form_requisicao')!== NULL && $emissao) {
            $form_requisicao = TSession::getValue('form_requisicao');
            $form_requisicao->emissao = $emissao;
            TSession::setValue('form_requisicao', $form_requisicao);
        }
    }

    static public function onExitNumeroProcesso($param) {
        $numeroProcesso = $param['numeroProcesso'];
        if (TSession::getValue('form_requisicao')!== NULL && $numeroProcesso) {
            $form_requisicao = TSession::getValue('form_requisicao');
            $form_requisicao->numeroProcesso = $numeroProcesso;
            TSession::setValue('form_requisicao', $form_requisicao);
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
        } catch (Exception $e) {
            TTransaction::rollback();
        }

        if (!isset($item) && (!$item)) {
            return;
        }
        
        $itens = TSession::getValue('requisicao_itens_o');
        if (isset($itens[$numeroItem])){
            $object = $itens[$numeroItem];
            $item->estoqueDisponivel = $item->estoqueDisponivel + $object->quantidade;
        }

        if ($item->estoqueDisponivel < $quantidade) {
            new TMessage('error', 'Quantidade Indisponível. <br>Disponível: ' . $item->estoqueDisponivel);
            return;
        }
    }

    public function onDeleteItem($param) {
        // get the cart objects from session
        $items = TSession::getValue('requisicao_itens');
        unset($items[$param['key']]); // remove the product from the array
        TSession::setValue('requisicao_itens', $items); // put the array back to the session
        // reload datagrid
        $this->onReload(func_get_arg(0));
    }

    public function onEdit($param) {
        if (isset($param) && isset($param['key']))
            $key = $param['key'];

        if (!isset($key)) {
            $form_requisicao = new stdClass();
            $form_requisicao->numeroSRP = '';
            $form_requisicao->numeroProcessoOrigem = '';
            $form_requisicao->numeroProcesso = '';
            $form_requisicao->validade = '';
            $form_requisicao->nome = '';
            $form_requisicao->uasg = '';
            $form_requisicao->emissao = date('d/m/Y');
            TSession::delValue('requisicao_itens');
            TSession::delValue('requisicao_itens_o');
            TSession::delValue('form_requisicao');
            TSession::delValue('SRP_id');
            TForm::sendData('form_requisicao', $form_requisicao);
            $this->onReload();
            return;
        }
        try {
            TTransaction::open('saciq');
            
            $requisicao = new Requisicao($key);            
            $form_requisicao = new stdClass();
            $form_requisicao->id = $key;
            $form_requisicao->numeroSRP = $requisicao->srp->numeroSRP;
            $form_requisicao->numeroProcessoOrigem = $requisicao->srp->numeroProcesso;
            $form_requisicao->numeroProcesso = $requisicao->numeroProcesso;
            $form_requisicao->validade = TDate::date2br($requisicao->srp->validade);
            $form_requisicao->nome = $requisicao->srp->nome;
            $form_requisicao->uasg = $requisicao->srp->uasg;
            $form_requisicao->emissao = TDate::date2br($requisicao->emissao);

            TSession::delValue('requisicao_itens');
            TSession::delValue('requisicao_itens_o');
            TSession::setValue('SRP_id', $requisicao->srp->id);
            
            TSeekButton::disableField('form_requisicao', 'numeroSRP');

            foreach ($requisicao->getItems() as $item_requisicao) {
                $item = new stdClass();
                $item->item_id = $item_requisicao->id;
                $item->numeroItem = $item_requisicao->numeroItem;
                $item->descricaoSumaria = $item_requisicao->descricaoSumaria;
                $item->quantidade = $item_requisicao->quantidade;
                $item->valorUnitario = $item_requisicao->valorUnitario;
                $item->justificativa = $item_requisicao->justificativa;
                $item->prazoEntrega = $item_requisicao->prazoEntrega;
                $item->total = $item_requisicao->total;
                $itens[$item->numeroItem] = $item;
            }
            TSession::setValue('requisicao_itens', $itens);
            TSession::setValue('requisicao_itens_o', $itens);
            TSession::setValue('form_requisicao', $form_requisicao);

            TForm::sendData('form_requisicao', $form_requisicao);
            $this->onReload();
            TTransaction::close();
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }

    public function onEditItem($param) {
        $items = TSession::getValue('requisicao_itens');
        $data = $items[$param['key']];

        $this->form_itens->setData($data);
        //TSession::setValue('requisicao_itens', $items); // put the array back to the session        
        // reload datagrid
        $this->onReload(func_get_arg(0));
    }

    public function onReload() {

        try {
            $form_requisicao = TSession::getValue('form_requisicao');
            if (!$form_requisicao) {
                $form_requisicao = new stdClass();
                $form_requisicao->numeroSRP = '';
                $form_requisicao->numeroProcessoOrigem = '';
                $form_requisicao->numeroProcesso = '';
                $form_requisicao->validade = '';
                $form_requisicao->nome = '';
                $form_requisicao->uasg = '';
                $form_requisicao->emissao = date('d/m/Y');
            }
            $this->form_requisicao->sendData('form_requisicao', $form_requisicao);
            $this->datagrid->clear(); // clear datagrid
            $items = TSession::getValue('requisicao_itens');
            //var_dump($items);
            if ($items) {
                foreach ($items as $object) {
                    // add the item inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            $this->loaded = true;
        } catch (Exception $e) { // in case of exception
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
        }
    }

    public function onSave($param) {
        try {
            $this->form_requisicao->validate(); // validate form data            
            $form_requisicao_data = $this->form_requisicao->getData();

            $form_requisicao = TSession::getValue('form_requisicao');
            $form_requisicao->numeroProcesso = $form_requisicao_data->numeroProcesso;
            $form_requisicao->emissao = $form_requisicao_data->emissao;
            


            $requisicao_itens = TSession::getValue('requisicao_itens');

            if (!isset($requisicao_itens) || count($requisicao_itens) == 0) {
                new TMessage('error', 'Insira ao menos 1 item');
                return;
            }

            TTransaction::open('saciq');
            //TTransaction::setLogger(new \Adianti\Log\TLoggerTXT("c:\\array\\LOG".date("Ymd-His").".txt"));
            if ($requisicao_itens) {
                $id = isset($form_requisicao->id) ? $form_requisicao->id : NULL;
                $requisicao = new Requisicao($id); // create a new Sale
                $requisicao->clearParts();
                
                $requisicao->numeroProcesso = $form_requisicao->numeroProcesso;
                $requisicao->emissao = TDate::date2us($form_requisicao->emissao);
                //if (!$requisicao->emissao){
                //    $requisicao->emissao = date("Y-m-d");
                //}
                $requisicao->aprovado = 0;
                $requisicao->srp = new Srp(TSession::getValue("SRP_id"));
                foreach ($requisicao_itens as $item) {
                    $item_requisicao = new Item($item->item_id);
                    $item_requisicao->justificativa = $item->justificativa;
                    $item_requisicao->quantidade = $item->quantidade;
                    $item_requisicao->prazoEntrega = $item->prazoEntrega;
                    $item_requisicao->quantidade = str_replace('.', '', $item->quantidade);
                    $item_requisicao->quantidade = str_replace(',', '.', $item->quantidade);
                    $requisicao->addItem($item_requisicao); // add the item to the Sale
                }
                $requisicao->store(); // store the Sale
                    
                TSession::delValue('requisicao_itens');
                TSession::delValue('form_requisicao');
                TSession::delValue('SRP_id');

                new TMessage('info', 'Requisição salva');
            }
            TTransaction::close();
            $this->onReload();
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
        }
    }

    public function show() {
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }

}
