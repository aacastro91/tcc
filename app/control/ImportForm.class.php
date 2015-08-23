<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
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

    function onImportar($param) {

        $source_file = 'tmp/' . $param['file'];
        $target_file = 'uploads/' . $param['file'];


        $finfo = new finfo(FILEINFO_MIME_TYPE);
        
        //echo $finfo->file($source_file);
        //return;
        
        // if the user uploaded a source file
        if (file_exists($source_file)/* AND $finfo->file($source_file) == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'*/) {
            // move to the target directory
            rename($source_file, $target_file);
            //unlink($source_file);
        }
        else
        {
            new TMessage('error', 'Arquivo não suportado');
            return;
        }
        
        if (!file_exists($target_file))
        {
            new TMessage('error', 'Arquivo Inválido');
            return;
            
        }   
        
        $importacao = new Importar($target_file);

        $importacao->setActiveRow(3);
        echo $importacao->getDescricaoCompleta();
    }

}
