<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TCombo;
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
 * @author Aluno
 */
class RequisicaoForm  extends TPage{
    
    private $form_master;
    private $form_detail;
    private $datagrid;
        
    function __construct() {
        
        parent::__construct();
        // Cria o form
        $this->form_master = new TForm('form_requisicao_master');
        $this->form_master->class = 'tform';
        $table_master = new TTable;
        $table_master->style = 'width:100%';
        $this->form_master->add($table_master);       
        
        //criar os campos do master
        $numeroSRP     = new TSeekButton('numeroSRP');
        $nome          = new TEntry('nome');
        $nroProcesso   = new TEntry('numeroProcesso');
        $uasg          = new TEntry('uasg');
        $validadeAta   = new TEntry('validade');
        $aprovado      = new TCombo('aprovado');
        
        //criar os campos do detail
        $item_id          = new TSeekButton('item_id');
        $descricaoSumaria = new TEntry('descricaoSumaria');
        $valorUnitario    = new TEntry('valorUnitario');
        $quantidade       = new TEntry('quantidade');
        $prazoEntrega     = new TEntry('prazoEntrega');
        $justificativa    = new TEntry('justificativa');

        $numeroSRP->setSize(80);
        $numeroSRP->addValidation('Nº SRP', new TRequiredValidator());
        $nome->setSize('95%');
        $nome->setProperty("style", "min-width : 200px");
        $nome->setEditable(false);
        $nroProcesso->setSize(100);
        $nroProcesso->setEditable(false);
        $uasg->setSize(70);
        $uasg->setEditable(false);
        $validadeAta->setSize(90);
        $validadeAta->setMask('dd/mm/yyyy');  
        $validadeAta->setEditable(false);
        
        $itens = array();
        $itens['1'] = 'Sim';
        $itens['0'] = 'Não';
        $aprovado->addItems($itens);
        $aprovado->setValue('1');
        $aprovado->setDefaultOption(false);

        $obj_srp = new SrpSeek();
        $numeroSRP->setAction(new TAction(array($obj_srp, 'onReload')));
        
        $obj_item = new SrpSeek();
        $item_id->setAction(new TAction(array($obj_item, 'onReload')));

        // adicionando o titulo
        $row  = $table_master->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell( new TLabel('Requisição de quantitativo'));
        $cell->colspan = 4;

        //adicionando os campos no formulario
        $row = $table_master->addRow();
        $row->addCell(new TLabel('Nº SRP:'))->width = '150px';
        $row->addCell($numeroSRP);
        $row->addCell(new TLabel('Nome Licitação:'))->width = '150px';
        $row->addCell($nome);
        $table_master->addRowSet(new TLabel('Proc. Orig:'), $nroProcesso, new TLabel('UASG:'), $uasg);
        $table_master->addRowSet(new TLabel('Validade da Ata:'), $validadeAta, new TLabel('Pendente:'), $aprovado);

        $this->form_master->setFields(array($numeroSRP, $nome, $nroProcesso, $uasg, $validadeAta, $aprovado));
        
        
        
        
        
        $this->form_detail = new TForm('form_requisicao_detail');
        $this->form_detail->class = 'tform';
        $table_itens = new TTable;
        $table_itens->style = 'width:100%';
        $this->form_detail->add($table_itens);
        
        //$item_id     = new TDBSeekButton( 'item_id', 'saciq', 'form_requisicao_detail', 'Item', 'city_id2', 'city_name2'  );
        //$nome          = new TEntry('nome');
        //$nroProcesso   = new TEntry('numeroProcesso');
        //$uasg          = new TEntry('uasg');
        
        
        $obj_srp = new ItemSeek();
        $item_id->setAction(new TAction(array($obj_srp, 'onReload')));
                
        $item_id->setExitAction(new TAction(array($this,'onProductChange')));
        $descricaoSumaria->setEditable(false);
        $valorUnitario->setEditable(false);
        
        $item_id->addValidation('Item', new TRequiredValidator());
        $quantidade->addValidation('Quantidade', new TRequiredValidator());
        $prazoEntrega->addValidation('Prazo Entrega', new TRequiredValidator());
        $justificativa->addValidation('Justificativa', new TRequiredValidator());
        
        // adicionando o titulo
        $row  = $table_itens->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell( new TLabel('Itens'));
        $cell->colspan = 2;
        
        $table_itens->addRowSet(new TLabel('Item'), array($item_id, $descricaoSumaria));
        $table_itens->addRowSet(new TLabel('Preço'), $valorUnitario);
        $table_itens->addRowSet(new TLabel('Quantidade'), $quantidade);
        $table_itens->addRowSet(new TLabel('Justificativa'), $justificativa);        
        
        $this->form_master->setFields(array($item_id, $descricaoSumaria, $valorUnitario, $quantidade, $prazoEntrega, $justificativa));
        
        
        
        //datagrid
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
        
        // cria 2 acoes do datagrid
        $action1 = new TDataGridAction(array($this, 'onEdit'));
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
        
        

        //$container = new TTable;
        //$container->style = 'width: 80%';
        ///////$container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->addRow()->addCell($this->form_header);
        
        $vbox = new TVBox;
        $vbox->style = 'width: 80%';
        $vbox->add($this->form_master);
        $vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->form_detail   );
        $vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->datagrid);
        //$vbox->add(new TLabel('&nbsp;'));
        //$vbox->add($this->form_customer);
        parent::add($vbox);

        //parent::add($container);
    }
    
    public function onEdit(){
        
    }
    
    public function onDelete(){
        
    }
    
    public function onReload(){
        
    }
    
    static function onProductChange( $params ){
        
    }

}
