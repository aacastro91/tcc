<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TExpression;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TBreadCrumb;

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
 * Description of ImportForm
 *
 * @author Anderson
 */
class ImportForm extends TPage {

    protected $form;
    private $notebook;
    private $step;
    private $frmSelecao;
    private $frmImportacao;
    private $gridSRP;
    private $importacao;

    public function __construct() {
        parent::__construct();

        // create the notebook
        $this->notebook = new TNotebook(650, 200);
        $this->notebook->setTabsVisibility(FALSE);


        $this->step = new TBreadCrumb;
        $this->step->addItem('Seleção', FALSE);
        $this->step->addItem('Importação', TRUE);
        $this->step->select('Seleção');

        // create the form
        $this->form = new TForm;

        // creates the notebook page
        $this->frmSelecao = new TTable;
        $this->frmImportacao = new TTable;
        $this->frmSelecao->style = 'width :640px';
        $this->frmImportacao->style = 'width :640px';

        // add the notebook inside the form
        $this->form->add($this->notebook);

        // adds the notebook page
        $this->notebook->appendPage('Seleção', $this->frmSelecao);
        $this->notebook->appendPage('Importação', $this->frmImportacao);

        //criar campo da aba selecao
        $file = new TFile('file');
        $file->addValidation('Arquivo', new TRequiredValidator);
        $file->setProperty("accept", ".xlsx,.xls");
        $file->setSize('90%');

        // itens pagina 1
        $row = $this->frmSelecao->addRow();
        $label = new TLabel('Importação da tabela');
        $row->class = 'tformtitle';
        $cell = $row->addCell($label);
        $cell->colspan = 2;

        $row = $this->frmSelecao->addRow();
        $row->addCell(new TLabel('Local do arquivo:'));
        $row->addCell($file);


        // itens pagina 2
        $this->gridSRP = new TDataGrid();
        $numeroSRP = new TDataGridColumn('numeroSRP', 'SRP', 'left');
        $nroProcesso = new TDataGridColumn('numeroProcesso', 'Nº Processo', 'left');
        $uasg = new TDataGridColumn('uasg', 'UASG', 'left');
        $validade = new TDataGridColumn('validade', 'Validade', 'left');
        $natureza = new TDataGridColumn('natureza', 'Natureza de Despesa', 'left');
        $nomeProcesso = new TDataGridColumn('nomeProcesso', 'Nome do Processo', 'left', 200);

        $this->gridSRP->addColumn($numeroSRP);
        $this->gridSRP->addColumn($nroProcesso);
        $this->gridSRP->addColumn($uasg);
        $this->gridSRP->addColumn($validade);
        $this->gridSRP->addColumn($natureza);
        $this->gridSRP->addColumn($nomeProcesso);

        $this->gridSRP->createModel();

        $row = $this->frmImportacao->addRow();
        $row->class = 'tformtitle';
        $cell = $row->addCell(new TLabel('Confirmação da SRP para Importação'));
        $cell->colspan = 2;

        $row = $this->frmImportacao->addRow();
        $cell = $row->addCell($this->gridSRP);
        $cell->colspan = 2;

        $btnLoadFile = new TButton('btnLoadFile');
        $btnLoadFile->setAction(new TAction(array($this, 'onLoadFile')), 'Continuar');
        $btnLoadFile->setImage('ico_next.png');

        $btnImportFile = new TButton('btnImportFile');
        $btnImportFile->setAction(new TAction(array($this, 'onImportFile')), 'Importar');
        $btnImportFile->setImage('ico_next.png');

        $this->frmSelecao->addRowSet($btnLoadFile);
        $this->frmImportacao->addRowSet($btnImportFile);

        // define wich are the form fields
        $this->form->setFields(array($file, $btnLoadFile, $btnImportFile));

        // wrap the page content using vertical box
        $vbox = new TVBox;
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->step);
        $vbox->add($this->form);

        parent::add($vbox);
    }

    private function checkFile($file) {
        $source_file = 'tmp/' . $file;
        $target_file = 'uploads/' . $file;

        //$finfo = new finfo(FILEINFO_MIME_TYPE);
        if (file_exists($source_file)) { //AND $finfo->file($source_file) == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            rename($source_file, $target_file);
        } else {
            new TMessage('error', 'Arquivo não suportado');
            return false;
        }

        if (!file_exists($target_file)) {
            new TMessage('error', 'Arquivo Inválido');
            return false;
        }
        return $target_file;
    }

    private function checkDatabase($file) {
        try {
            TTransaction::open('saciq');

            $criteria = new TCriteria;
            $criteria->add(new TFilter('numeroSRP', '=', $this->importacao->getNroSRP()));
            $criteria->add(new TFilter('numeroIRP', '=', $this->importacao->getNroIRP()));
            $criteria->add(new TFilter('numeroProcesso', '=', $this->importacao->getNumeroProcesso()));
            $criteria->add(new TFilter('uasg', '=', $this->importacao->getUasgGerenciadora()));
            $criteria->add(new TFilter('validade', '=', $this->importacao->getValidadeAta()));

            $repository = new TRepository('Srp');
            $count = $repository->count($criteria);

            if ($count > 0) {
                $continua = new TAction(array($this, 'onContinua'));

                // define os parâmetros de cada ação
                $continua->setParameter('confirma', 1);
                $continua->setParameter('file', $file);
                new TQuestion('Essa SRP já foi importada, reimportar a planilha <br>irá excluir os dados anteriores, Confirma?', $continua);
                TTransaction::close();
                return false;
            }
            TTransaction::close();
            return true;
        } catch (Exception $e) {
            TTransaction::rollback();
        }
    }

    private function preencherGrid() {
        $item = new StdClass;
        $item->numeroSRP = $this->importacao->getNroSRP();  //'210/2013';
        $item->numeroProcesso = $this->importacao->getNumeroProcesso(); //'2319/2013-73';
        $item->uasg = $this->importacao->getUasgGerenciadora(); //'158154';
        $item->validade = date('d/m/Y', strtotime($this->importacao->getValidadeAta())); //'01/01/2015';
        $item->natureza = $this->importacao->getNaturezaDespesa(); //'PERMANENTE';
        $item->nomeProcesso = $this->importacao->getNomeProcesso(); //'MOBILIÁRIO EM GERAL';
        $this->gridSRP->addItem($item);
    }

    function onContinua($param) {

        if (isset($param['confirma']) && $param['confirma'] == 1) {
            $data = $this->form->getData();
            $data->file = $param['file'];
            $this->gridSRP->clear();
            $this->importacao = new Importar();
            $this->importacao->loadFile($param['file']);
            $this->importacao->setActiveRow(3);
            $this->preencherGrid();
            $this->notebook->setCurrentPage(1);
            $this->step->select('Importação');
            $this->form->setData($data);
        }
    }

    function onLoadFile($param) {
        try {
            $data = $this->form->getData();
            $this->form->validate();
        } catch (Exception $e) {
            new TMessage('error', '<b>Error</b>: <br> ' . $e->getMessage());
            return;
        }

        $file = $this->checkFile($param['file']);

        if ($file == false) {
            return;
        }

        $data->file = $file;

        set_time_limit(0);

        $this->importacao = new Importar();
        $this->importacao->loadFile($file);
        $mes = $this->importacao->isValidFile();
        if ($mes) {
            new TMessage('error', '<b>Error</b>: <br> Arquivo fora do padrão<br>' . $mes);
            return;
        }

        $hoje = date("Y-m-d");

        if ($this->importacao->getValidadeAta() < $hoje) {
            new TMessage('error', 'SRP da planilha Vencida!');
            return;
        }

        $this->importacao->setActiveRow(3);

        $this->form->setData($data);

        if ($this->checkDataBase($file)) {
            $this->preencherGrid();
            $this->notebook->setCurrentPage(1);
            $this->step->select('Importação');
        }
    }

    private function LoadObjectByField($model, $field, $value) {
        $repository = new TRepository($model);
        $criteria = new TCriteria();
        $criteria->add(new TFilter($field, '=', $value));
        $nat = $repository->load($criteria);
        if (count($nat) > 0) {
            return $nat[0];
        } else {
            return NULL;
        }
    }

    function onImportFile() {

        $data = $this->form->getData();
        $this->importacao = new Importar();
        $this->importacao->loadFile($data->file);
        $this->importacao->setActiveRow(3);
        try {
            TTransaction::open('saciq');

            $criteria = new TCriteria;
            $criteria->add(new TFilter('numeroSRP', '=', $this->importacao->getNroSRP()));
            $criteria->add(new TFilter('numeroIRP', '=', $this->importacao->getNroIRP()));
            $criteria->add(new TFilter('numeroProcesso', '=', $this->importacao->getNumeroProcesso()));
            $criteria->add(new TFilter('uasg', '=', $this->importacao->getUasgGerenciadora()));
            $criteria->add(new TFilter('validade', '=', $this->importacao->getValidadeAta()));

            $repositorySrp = new TRepository('Srp');

            $count = $repositorySrp->count($criteria);
            if ($count > 0) {
                $RepSRP = $repositorySrp->load($criteria);
                $RepSRP[0]->delete();
            }

            $srp = null;

            while (!$this->importacao->eof()) {

                if (!$this->importacao->isValidRow()) {
                    $this->importacao->nextRow();
                    continue;
                }

                $natureza = $this->LoadObjectByField('Natureza', 'descricao', $this->importacao->getNaturezaDespesa());
                if (!isset($natureza)) {
                    $natureza = new Natureza();
                    $natureza->descricao = $this->importacao->getNaturezaDespesa();
                    $natureza->store();
                }

                $subelemento = $this->LoadObjectByField('Subelemento', 'descricao', $this->importacao->getDescricaoSubElemento());
                if (!isset($subelemento)) {
                    $subelemento = new Subelemento();
                    $subelemento->id = $this->importacao->getNumeroSubElemento();
                    $subelemento->descricao = $this->importacao->getDescricaoSubElemento();
                    $subelemento->store();
                }

                $fornecedor = $this->LoadObjectByField('Fornecedor', 'cnpj', $this->importacao->getCNPJ());
                if (!isset($fornecedor)) {
                    $fornecedor = new Fornecedor();
                    $fornecedor->nome = $this->importacao->getFornecedor();
                    $fornecedor->cnpj = $this->importacao->getCNPJ();
                    $fornecedor->store();
                }

                if (!isset($srp)) {
                    $srp = new Srp();
                    $srp->numeroSRP = $this->importacao->getNroSRP();
                    $srp->numeroIRP = $this->importacao->getNroIRP();
                    $srp->numeroProcesso = $this->importacao->getNumeroProcesso();
                    $srp->uasg = $this->importacao->getUasgGerenciadora();
                    $srp->validade = $this->importacao->getValidadeAta();
                    $srp->nome = $this->importacao->getNomeProcesso();
                    $srp->natureza = $natureza;
                }

                $item = new Item();
                $item->numeroItem = $this->importacao->getItem();
                $item->descricaoSumaria = $this->importacao->getDescricaoSumaria();
                $item->descricaoCompleta = $this->importacao->getDescricaoCompleta();
                $item->descricaoPosLicitacao = $this->importacao->getDescricaoPosLicitacao();
                $item->unidadeMedida = $this->importacao->getUnidadeDeMedida();
                $item->marca = $this->importacao->getMarca();
                $item->valorUnitario = $this->importacao->getValorUnitarioLicitado();
                $item->quantidadeDisponivel = $this->importacao->getOrgao(CAMPUS);
                $item->estoqueDisponivel = $item->quantidadeDisponivel;
                $item->fabricante = $this->importacao->getFabricante();
                $item->fornecedor = $fornecedor;
                $item->subelemento = $subelemento;
                $srp->addItem($item);

                $this->importacao->nextRow();
            }
            $srp->store();

            new TMessage('info', 'Planilha importada com sucesso');

            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
        }
        $this->notebook->setCurrentPage(0);
        $this->form->setData($data);
        $this->step->select('Seleção');
    }

}
