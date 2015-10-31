<?php

use Adianti\Control\TPage;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
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
 * Description of Home
 *
 * @author Anderson
 */
class Home extends TPage{
    
    private $form;
    private $loaded;
    
    private $dg_ultimasImportações;
    private $dg_AtasAVencer;
    private $dg_UltPedCompra;
    private $dg_UltCessQuantitativo;
    
    function __construct() {        
        
         parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_home');
        $this->form->class = 'tform'; // CSS class
        $this->form->style = "background-color: rgba(0,0,0,0) !important;";
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        $this->form->add($table);
        
        $table->style = 'background-color:rgba(0, 0, 0, 0);';
        
        
        $row = $table->addRow();
        $lbUI = new TLabel('<B>Últimas Importações</B>');
        $lbUI->setFontSize(16);
        $lbUI->style = "padding-top : 10px;";
        $row->addCell($lbUI);
        $row->addCell(new TLabel('&nbsp;'));
        $row->addCell(new TLabel('Últimas Pedidoss'));
        
        
        parent::include_css('app/resources/custom-table.css');
        $this->dg_ultimasImportações = new TDataGrid;
        $this->dg_ultimasImportações->class = 'tdatagrid_table customized-table';
        $this->dg_ultimasImportações->style = 'width : 100%';
        $this->dg_ultimasImportações->setHeight(320);
        $this->dg_ultimasImportações->makeScrollable();
        $this->dg_ultimasImportações->disableDefaultClick();
        
        $numeroSRP   = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 70);
        $numeroIRP   = new TDataGridColumn('numeroIRP', 'Nº IRP', 'left', 70);
        $numeroProcesso   = new TDataGridColumn('numeroProcesso', 'Proc. Orig.', 'left', 150);
        $uasg   = new TDataGridColumn('uasg', 'UASG', 'left', 50);
        $validade   = new TDataGridColumn('validade', 'Validade', 'left', 100);
        $nome   = new TDataGridColumn('nome', 'Nome', 'left', 300);
        
        $this->dg_ultimasImportações->addColumn($numeroSRP);
        $this->dg_ultimasImportações->addColumn($numeroIRP);
        $this->dg_ultimasImportações->addColumn($numeroProcesso);
        $this->dg_ultimasImportações->addColumn($nome);
        $this->dg_ultimasImportações->addColumn($uasg);
        $this->dg_ultimasImportações->addColumn($validade);
        $this->dg_ultimasImportações->createModel();
        
        
        $table->addRowSet($this->dg_ultimasImportações,new TLabel('&nbsp;'),$this->dg_ultimasImportações);
        
        $container = TVBox::pack( $this->form);
        parent::add($container);
        //parent::add($this->form);
        
    }
    
    function onReload($param){
        $obj = new stdClass();
        $obj->numeroSRP = '22/1234';
        $obj->numeroIRP = '123456';
        $obj->numeroProcesso = '9876543210123';
        $obj->nome= 'nome teste';
        $obj->uasg = '150213';
        $obj->validade = '01/01/2016';
        
        $this->dg_ultimasImportações->clear();
        $this->dg_ultimasImportações->addItem($obj);
    }

    
    public function show() {
         if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }        
        parent::show();
    }


}
