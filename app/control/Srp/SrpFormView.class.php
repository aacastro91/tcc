<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TTableRow;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;

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
 * Description of SrpFormView
 *
 * @author Anderson
 */
class SrpFormView extends TPage {

    protected $form; //formulario
    protected $datagrid;

    function __construct() {
        parent::__construct();
        parent::include_css('app/resources/custom-table.css');

        //cria o formulario
        $this->form = new TForm('SrpFormView');
        $this->form->class = 'tform';
        //$this->form->style = 'max-width: 500px';

        $table = new TTable;
        $table->width = '100%';
        $this->form->add($table);

        $row = $table->addRow();
        $row->class = 'tformtitle';
        $row->addCell(new TLabel('Listagem de Itens da SRP'))
                ->colspan = 2;

        //cria os campos do formulário
        $id = new TEntry('id');
        $numeroSRP = new TEntry('numeroSRP');
        $numeroIRP = new TEntry('numeroIRP');
        $numeroProcesso = new TEntry('numeroProcesso');
        $nome = new TEntry('nome');
        $uasg = new TEntry('uasg');
        $validade = new TDate('validade');
        $natureza = new TEntry('natureza');

        // define os tamanhos
        $id->setSize(70);
        $numeroSRP->setSize(90);
        $numeroIRP->setSize(90);
        $numeroProcesso->setSize(120);
        $nome->setSize(350);
        $uasg->setSize(70);
        $validade->setSize(90);
        $natureza->setSize(200);

        //desabilitando os campos
        $id->setEditable(false);
        $numeroSRP->setEditable(false);
        $numeroIRP->setEditable(false);
        $numeroProcesso->setEditable(false);
        $nome->setEditable(false);
        $uasg->setEditable(false);
        $validade->setEditable(false);
        $natureza->setEditable(false);

        // adiciona uma linha para cada campo no formulario
        $table->addRowSet(new TLabel('id:'), $id);
        $table->addRowSet(new TLabel('Nº SRP:'), $numeroSRP);
        $table->addRowSet(new TLabel('Nº IRP:'), $numeroIRP);
        $table->addRowSet(new TLabel('Proc. Orig.:'), $numeroProcesso);
        $table->addRowSet(new TLabel('Nome:'), $nome);
        $table->addRowSet(new TLabel('UASG:'), $uasg);
        $table->addRowSet(new TLabel('Validade:'), $validade);
        $table->addRowSet(new TLabel('Natureza:'), $natureza);

        $this->form->setFields(array($id, $numeroSRP, $numeroIRP, $numeroProcesso,
            $nome, $uasg, $validade, $natureza));


        //criar a datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->makeScrollable();
        $this->datagrid->disableDefaultClick();
        $this->datagrid->setHeight(180);

        //criar as colunas da datagrid 
        $GnumeroItem = new TDataGridColumn('numeroItem', 'Nº Item', 'left', 50);
        $GdescricaoSumaria = new TDataGridColumn('descricaoSumaria', 'Descrição Sumária', 'left', 400);
        $GquantidadeEstimada = new TDataGridColumn('quantidadeDisponivel', 'Qtd. Estimada', 'right', 100);
        $GquantidadeDisponivel = new TDataGridColumn('estoqueDisponivel', 'Qtd. Disponível', 'right', 100);
        $GunidadeMedida = new TDataGridColumn('unidadeMedida', 'Unidade', 'left', 50);
        $GvalorUnitario = new TDataGridColumn('valorUnitario', 'Valor Unit.', 'right', 70);

        // add the columns to the DataGrid
        $this->datagrid->addColumn($GnumeroItem);
        $this->datagrid->addColumn($GdescricaoSumaria);
        $this->datagrid->addColumn($GquantidadeEstimada);
        $this->datagrid->addColumn($GquantidadeDisponivel);
        $this->datagrid->addColumn($GunidadeMedida);
        $this->datagrid->addColumn($GvalorUnitario);


        /*
        $viewDetalhe = new TDataGridAction(array($this, 'onShowDetail'));
        $viewDetalhe->setLabel('Detalhes');
        $viewDetalhe->setImage('ico_view.png');
        $viewDetalhe->setField('id');

        $this->datagrid->addAction($viewDetalhe);
         */

        // cria o modelo no datagrid
        $this->datagrid->createModel();

        $back_button = new TButton('back');
        $back_button->setAction(new TAction(array('SrpList', 'onReload')), 'Voltar');
        $back_button->setImage('ico_back.png');
        $this->form->addField($back_button);

        $buttons_box = new THBox;
        $buttons_box->add($back_button);

        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;

        // cria o container da pagina
        $container = TVBox::pack($this->form, $this->datagrid);
        parent::add($container);
    }

    /*
    public function onShowDetail($param = NULL) {
        $pos = $this->datagrid->getRowIndex('id', $param['key']);
        
        // get row by position
        $current_row = $this->datagrid->getRow($pos);
        $current_row->style = "background-color: #8D8BC8; color:white; text-shadow:none";
        
        // create a new row
        $row = new TTableRow;
        $row->style = "background-color: #E0DEF8";
        //$row->addCell('123');

        
        //$this->datagrid
        $cell = $row->addCell('teste' . $current_row->nome);
        $cell->colspan = 6;
        $cell->style='padding:10px;';
        
        // insert the new row
        $this->datagrid->insert($pos +1, $row);
    }*/
    
    function onReload($param = null)
    {
        $this->datagrid->clear();
                
        $key = $param['key'];
        if (!isset($key)){
            
            $value = TSession::getValue('srp_form_view_key');
            if (!isset($value))
                return;
            
            $key = $value;
        }
        
        try {
            TTransaction::open('saciq');
            $srp = new Srp($key);

            if (!isset($srp)) {
                TTransaction::close();
                return;
            }
            $object = new stdClass();
            $object->id = $srp->id;
            $object->numeroSRP = $srp->numeroSRP;
            $object->numeroIRP = $srp->numeroIRP;
            $object->numeroProcesso = $srp->numeroProcesso;
            $object->uasg = $srp->uasg;
            $object->validade = TDate::date2br($srp->validade);
            $object->nome = $srp->nome;
            $object->natureza = $srp->natureza->descricao;
            
            foreach ($srp->getItems() as $item) {
                $row = $this->datagrid->addItem($item);
            }           
            
            TForm::sendData('SrpFormView', $object);

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

    public function onViewSrp($param = NULL) {
        $key = $param['key'];
        if (!isset($key))
            return;
        TSession::setValue('srp_form_view_key', $key);
        
        $this->onReload($param);
    }
    
    function show(){
        $this->onReload();
        parent::show();
    }

}
