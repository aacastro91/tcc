<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportForm
 *
 * @author Anderson
 */
class ImportForm extends TPage {

    protected $form;

    public function __construct() {
        parent::__construct();
        // Cria o form
        $this->form = new TForm('form_importar');
        $this->form->class = 'tform';

        // creates the table container
        $table = new TTable;
        $table->style = 'width: 100%';

        $row = $table->addRow();
        $row->class = 'tformtitle';
        $cell = $row->addCell(new TLabel('Importar Planilha XLS'));
        $cell->colspan = 2;
        //$table->addRowSet(new TLabel('Importar Planilha XLS'),'')->class = 'tformtitle';
        // adiciona a tabela no form
        $this->form->add($table);

        $file = new TFile('file');
        $file->setProperty("accept", ".xlsx");
        $file->setSize('70%');

        $botao_import = new TButton('btnImportar');
        $botao_import->setLabel('Importar');
        $botao_import->class = 'btn btn-success btn-defualt';
        $botao_import->style = 'margin-left: 40%;width: 250px; height: 40px'; //'margin-left:32px;width:355px;height:40px;border-radius:6px;font-size:18px';
        $botao_import->setAction(new TAction(array($this, 'onImportar')), 'Import');

        $table->addRowSet(new TLabel('Local do arquivo:'), $file);

        $container = new TTable;

        $container->style = 'width: 80%';
        //$container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', ''));
        $container->addRow()->addCell($this->form);

        $row = $table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell($botao_import);
        $cell->colspan = 2;

        $this->form->setFields(array($file, $botao_import));

        // add the form to the page
        parent::add($container);
    }

    /*
    private function LoadNaturezaByDescricao($descricao) {
        $repository = new TRepository('Natureza');
        $criteria = new TCriteria();
        $criteria->add(new TFilter('descricao', '=', $descricao));
        $nat = $repository->load($criteria);
        if (count($nat) > 0) {
            return  $nat[0];
        } else {
            return NULL;
        }
    }
    
    private function LoadSubElementoByDescricao($descricao) {
        $repository = new TRepository('Subelemento');
        $criteria = new TCriteria();
        $criteria->add(new TFilter('descricao', '=', $descricao));
        $nat = $repository->load($criteria);
        if (count($nat) > 0) {
            return  $nat[0];
        } else {
            return NULL;
        }
    }*/
    
    private function LoadObjectByField($model, $field, $value ){
        $repository = new TRepository($model);
        $criteria = new TCriteria();
        $criteria->add(new TFilter($field, '=', $value));
        $nat = $repository->load($criteria);
        if (count($nat) > 0) {
            return  $nat[0];
        } else {
            return NULL;
        }
    }

    function onImportar($param) {

        $source_file = 'tmp/' . $param['file'];
        $target_file = 'uploads/' . $param['file'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        if (file_exists($source_file) AND $finfo->file($source_file) == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            rename($source_file, $target_file);
        } else {
            new TMessage('error', 'Arquivo não suportado');
            return;
        }

        if (!file_exists($target_file)) {
            new TMessage('error', 'Arquivo Inválido');
            return;
        }
        
        set_time_limit(0);
        $importacao = new Importar();
        $importacao->loadFile($target_file);
        $importacao->setActiveRow(3);
        
        try {
            TTransaction::open('saciq');
            
            $srp = null;

            while (!$importacao->eof()) {

                if (!$importacao->isValidRow()) {
                    $importacao->nextRow();
                    continue;
                }
                
                $natureza = $this->LoadObjectByField('Natureza','descricao',$importacao->getNaturezaDespesa());
                if (!isset($natureza)){
                    $natureza = new Natureza();
                    $natureza->descricao = $importacao->getNaturezaDespesa();
                    $natureza->store();                    
                }
                
                $subelemento = $this->LoadObjectByField('Subelemento','descricao',$importacao->getDescricaoSubElemento());
                if (!isset($subelemento)){
                    $subelemento = new Subelemento();
                    $subelemento->id = $importacao->getNumeroSubElemento();
                    $subelemento->descricao = $importacao->getDescricaoSubElemento();
                    $subelemento->store();                            
                }
                
                $fornecedor = $this->LoadObjectByField('fornecedor', 'nome', $importacao->getFornecedor());
                if (!isset($fornecedor)){
                    $fornecedor = new Fornecedor();
                    $fornecedor->nome = $importacao->getFornecedor();
                    $fornecedor->cnpj = $importacao->getCNPJ();
                    $fornecedor->store();                            
                }
                
                if (!isset($srp)){
                    $srp = new Srp();
                    $srp->numeroSRP      = $importacao->getNroSRP();
                    $srp->numeroIRP      = $importacao->getNroIRP();
                    $srp->numeroProcesso = $importacao->getNumeroProcesso();
                    $srp->uasg           = $importacao->getUasgGerenciadora();
                    $srp->validade       = $importacao->getValidadeAta();
                    $srp->nome           = $importacao->getNomeProcesso();
                    $srp->natureza       = $natureza;
                    $srp->store();
                }
                
                $item = new Item();
                $item->numeroItem = $importacao->getItem();
                $item->descricaoSumaria = $importacao->getDescricaoSumaria();
                $item->descricaoCompleta = $importacao->getDescricaoCompleta();
                $item->descricaoPosLicitacao = $importacao->getDescricaoPosLicitacao();
                $item->unidadeMedida = $importacao->getUnidadeDeMedida();
                $item->marca = $importacao->getMarca();
                $item->valorUnitario = $importacao->getValorUnitarioLicitado();
                $item->quantidadeDisponivel = $importacao->getOrgao(CAMPUS);
                $item->fabricante = $importacao->getFabricante();
                $item->fornecedor = $fornecedor;
                $item->subelemento = $subelemento;
                $item->srp = $srp;
                $item->store();
                
                $importacao->nextRow();
            }
            
            new TMessage('info', 'Planilha importada com sucesso');

            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
        }
    }

}
