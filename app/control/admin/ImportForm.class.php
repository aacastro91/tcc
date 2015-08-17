<?php

use Adianti\Control\TPage;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;

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
    $file->setProperty("accept", ".xls");
    $file->setSize('70%');

    $botao_import = new TButton('btnImportar');
    $botao_import->setLabel('Importar');
    $botao_import->class = 'btn btn-success btn-defualt';
    $botao_import->style = 'margin-left: 40%;width: 250px; height: 40px'; //'margin-left:32px;width:355px;height:40px;border-radius:6px;font-size:18px';


    $table->addRowSet(new TLabel('Local do arquivo:'), $file);

    $container = new TTable;
    $container->style = 'width: 80%';
    $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->addRow()->addCell($this->form);

    $row = $table->addRow();
    $row->class = 'tformaction';
    $cell = $row->addCell($botao_import);
    $cell->colspan = 2;

    // add the form to the page
    parent::add($container);
  }

}
