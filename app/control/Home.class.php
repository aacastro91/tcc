<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDate;
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
    
    private $dg_UltImportacao;
    private $dg_SRPAVencer;
    private $dg_UltRequisicao;
    private $dg_UltCesssao;
    
    function __construct() {        
        
         parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_home');
        $this->form->class = 'tform'; // CSS class
        $this->form->width = '100%';
        $this->form->style = "background-color:rgba(0,0,0,0)!important;";
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        $this->form->add($table);
        
        $table->style = 'background-color:rgba(0, 0, 0, 0);box-shadow: none !important;border: none !important;';
        
        
        $row = $table->addRow();
        $lbUI = new TLabel('<B>Últimas importações</B>');
        $lbUR = new TLabel('<B>Últimas requisições</B>');
        $lbUC = new TLabel('<B>Últimas cessões</B>');
        $lbSV = new TLabel('<B>SRP à vencer</B>');
        
        $lbUI->setFontSize(16);
        $lbUR->setFontSize(16);
        $lbUC->setFontSize(16);
        $lbSV->setFontSize(16);
        $lbUI->style = "padding-top : 10px;";
        $lbUR->style = "padding-top : 10px;";
        $lbUC->style = "padding-top : 10px;";
        $lbSV->style = "padding-top : 10px;";
        $row->addCell($lbUI);
        $row->addCell(new TLabel('&nbsp;'));
        $row->addCell($lbUR);        
        
        parent::include_css('app/resources/custom-table.css');
        $this->dg_UltImportacao = new TDataGrid;
        $this->dg_UltRequisicao = new TDataGrid;
        $this->dg_UltCesssao    = new TDataGrid;
        $this->dg_SRPAVencer   = new TDataGrid;
        
        $this->dg_UltImportacao->class = 'tdatagrid_table customized-table';
        $this->dg_UltRequisicao->class = 'tdatagrid_table customized-table';
        $this->dg_UltCesssao->class = 'tdatagrid_table customized-table';
        $this->dg_SRPAVencer->class = 'tdatagrid_table customized-table';
        
        //$this->dg_UltImportacao->style = 'width : 100%';
        //$this->dg_UltRequisicao->style = 'width : 100%';
        //$this->dg_UltCesssao->style = 'width : 100%';
        //$this->dg_AtasAVencer->style = 'width : 100%';
        
        $this->dg_UltImportacao->setHeight(320);
        $this->dg_UltRequisicao->setHeight(320);
        $this->dg_UltCesssao->setHeight(320);
        $this->dg_SRPAVencer->setHeight(320);
        
        $this->dg_UltImportacao->makeScrollable();
        $this->dg_UltRequisicao->makeScrollable();
        $this->dg_UltCesssao->makeScrollable();
        $this->dg_SRPAVencer->makeScrollable();
        
        //$this->dg_UltImportacao->disableDefaultClick();
        //$this->dg_UltRequisicao->disableDefaultClick();
        //$this->dg_UltCesssao->disableDefaultClick();
        //$this->dg_SRPAVencer->disableDefaultClick();
        
        //Ultimas Importações
        $numeroSRP   = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 70);
        $numeroIRP   = new TDataGridColumn('numeroIRP', 'Nº IRP', 'left', 70);
        $numeroProcesso   = new TDataGridColumn('numeroProcesso', 'Proc. Orig.', 'left', 150);
        $uasg   = new TDataGridColumn('uasg', 'UASG', 'left', 50);
        $validade   = new TDataGridColumn('validade', 'Validade', 'left', 100);
        $nome   = new TDataGridColumn('nome', 'Nome', 'left', 300);        
        $actUltImp = new TDataGridAction(array('SrpFormView', 'onViewSrp'));
        $actUltImp->setLabel('Visualizar');
        $actUltImp->setImage('fa:search fa-fw');
        $actUltImp->setField('id');
        $this->dg_UltImportacao->addAction($actUltImp);
        
        $this->dg_UltImportacao->addColumn($numeroSRP);
        $this->dg_UltImportacao->addColumn($numeroIRP);
        $this->dg_UltImportacao->addColumn($numeroProcesso);
        $this->dg_UltImportacao->addColumn($nome);
        $this->dg_UltImportacao->addColumn($uasg);
        $this->dg_UltImportacao->addColumn($validade);
        $this->dg_UltImportacao->createModel();
        
        //Ultimas Requisições
        $srp = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 100);
        $numeroProcesso = new TDataGridColumn('numeroProcesso', 'Nº do processo', 'left', 250);
        $data = new TDataGridColumn('emissao', 'Data', 'left', 100);
        $aprovado = new TDataGridColumn('aprovado', 'Aprovado', 'left',100);
        $actUltReq = new TDataGridAction(array('RequisicaoList', 'onReload'));
        $actUltReq->setLabel('Visualizar');
        $actUltReq->setImage('fa:search fa-fw');
        $actUltReq->setField('id');
        $this->dg_UltRequisicao->addAction($actUltReq);
        
        $this->dg_UltRequisicao->addColumn($srp);
        $this->dg_UltRequisicao->addColumn($numeroProcesso);
        $this->dg_UltRequisicao->addColumn($data);
        $this->dg_UltRequisicao->addColumn($aprovado);
        $this->dg_UltRequisicao->createModel();
        
        //Atas a Vencer
        $numeroSRP   = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 70);
        $numeroIRP   = new TDataGridColumn('numeroIRP', 'Nº IRP', 'left', 70);
        $numeroProcesso   = new TDataGridColumn('numeroProcesso', 'Proc. Orig.', 'left', 150);
        $uasg   = new TDataGridColumn('uasg', 'UASG', 'left', 50);
        $validade   = new TDataGridColumn('validade', 'Validade', 'left', 100);
        $nome   = new TDataGridColumn('nome', 'Nome', 'left', 300);
        $actSrpVen = new TDataGridAction(array('SrpFormView', 'onViewSrp'));
        $actSrpVen->setLabel('Visualizar');
        $actSrpVen->setImage('fa:search fa-fw');
        $actSrpVen->setField('id');
        $this->dg_SRPAVencer->addAction($actSrpVen);
        $this->dg_SRPAVencer->addColumn($numeroSRP);
        $this->dg_SRPAVencer->addColumn($numeroIRP);
        $this->dg_SRPAVencer->addColumn($numeroProcesso);
        $this->dg_SRPAVencer->addColumn($nome);
        $this->dg_SRPAVencer->addColumn($uasg);
        $this->dg_SRPAVencer->addColumn($validade);
        $this->dg_SRPAVencer->createModel();
        
        //Ultimas Cessões
        $srp = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 100);
        $numeroCessao = new TDataGridColumn('numeroCessao', 'Nº da Cessão', 'left', 250);
        $data = new TDataGridColumn('emissao', 'Data', 'left', 100);
        $aprovado = new TDataGridColumn('aprovado', 'Aprovado', 'left',100);
        $actUltCes = new TDataGridAction(array('CessaoList', 'onReload'));
        $actUltCes->setLabel('Visualizar');
        $actUltCes->setImage('fa:search fa-fw');
        $actUltCes->setField('id');
        $this->dg_UltCesssao->addAction($actUltCes);
        
        $this->dg_UltCesssao->addColumn($srp);
        $this->dg_UltCesssao->addColumn($numeroCessao);
        $this->dg_UltCesssao->addColumn($data);
        $this->dg_UltCesssao->addColumn($aprovado);
        $this->dg_UltCesssao->createModel();
        
        
        $table->addRowSet($this->dg_UltImportacao,new TLabel('&nbsp;'),$this->dg_UltRequisicao);
        $row = $table->addRow();
        $row->addCell(new TLabel('&nbsp;'))->colspan = 3;
        
        $row = $table->addRow();
        $row->addCell($lbSV);
        $row->addCell(new TLabel('&nbsp;'));
        $row->addCell($lbUC);
        $table->addRowSet($this->dg_SRPAVencer,new TLabel('&nbsp;'),$this->dg_UltCesssao);
        
        $container = TVBox::pack( $this->form);
        parent::add($container);
        //parent::add($this->form);
        
    }
    
    function onReload($param){
        $this->dg_UltImportacao->clear();
        $this->dg_UltRequisicao->clear();
        $this->dg_UltCesssao->clear();
        $this->dg_SRPAVencer->clear();
        
        try {
            TTransaction::open('saciq');
            //TTransaction::setLogger(new TLoggerTXT("c:\\array\\LOG".date("Ymd-His").".txt"));
            //ultimas importações
            $criteriaUI = new TCriteria();
            $param['order'] = 'id';
            $param['direction'] = 'desc';
            $criteriaUI->setProperties($param);
            $criteriaUI->setProperty('limit', 8);
            $repositoryUI = new TRepository('Srp');
            $srps = $repositoryUI->load($criteriaUI, false);            
            foreach ($srps as $srp)
            {
                $srp->validade = TDate::date2br($srp->validade);
                $this->dg_UltImportacao->addItem($srp);
            }
            
            //ultimas Requisições
            $criteriaUR = new TCriteria();
            $param['order'] = 'emissao';
            $param['direction'] = 'desc';
            $criteriaUR->setProperties($param);
            $criteriaUR->setProperty('limit', 8);
            $repositoryUR = new TRepository('Requisicao');
            $requisicoes = $repositoryUR->load($criteriaUR, false);            
            foreach ($requisicoes as $requisicao)
            {
                $requisicao->numeroSRP = $requisicao->srp->numeroSRP;
                $requisicao->aprovado = ($requisicao->aprovado == 0) ? 'Não' : 'Sim';
                $requisicao->emissao = TDate::date2br($requisicao->emissao);
                $this->dg_UltRequisicao->addItem($requisicao);
            }
            
            
            //Atas a vencer
            $criteriaAV = new TCriteria();
            $param['order'] = 'validade';
            $param['direction'] = 'asc';
            $criteriaAV->setProperties($param);
            $criteriaAV->setProperty('limit', 8);
            $criteriaAV->add(new TFilter('validade', '>=' , date("Y-m-d") ));
            $repositoryAV = new TRepository('Srp');
            $atasAVencer = $repositoryAV->load($criteriaAV, false);            
            foreach ($atasAVencer as $atas)
            {
                $atas->validade = TDate::date2br($atas->validade);
                $this->dg_SRPAVencer->addItem($atas);
            }
            
            //ultimas Cessões
            $criteriaUC = new TCriteria();
            $param['order'] = 'emissao';
            $param['direction'] = 'desc';
            $criteriaUC->setProperties($param);
            $criteriaUC->setProperty('limit', 8);
            $repositoryUC = new TRepository('Cessao');
            $cessoes = $repositoryUC->load($criteriaUC, false);            
            foreach ($cessoes as $cessao)
            {
                $cessao->numeroSRP = $cessao->srp->numeroSRP;
                $cessao->aprovado = ($cessao->aprovado == 0) ? 'Não' : 'Sim';
                $cessao->emissao = TDate::date2br($cessao->emissao);
                $this->dg_UltCesssao->addItem($cessao);
            }
            
            
            $this->loaded = true;
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

    
    public function show() {
         if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }        
        parent::show();
    }


}
