<?php

use Adianti\Control\TPage;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;

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
    
    private $form;
        
    function __construct() {
        
        parent::__construct();
        // Cria o form
        $this->form = new TForm('form_requisicao');
        $this->form->class = 'tform';
        
        // cria a tabela
        $table = new TTable;
        $table->style = 'width:100%';
        
        //cria o titulo
        $row = $table->addRow();
        $row->class = 'tformtitle';
        $cell = $row->addCell(new TLabel('Requisição de quantitativo'));
        $cell->colspan = 2;  
        
        
        //criar os edits
        $nroSRP        = new TEntry('numeroSRP');
        $nome          = new TEntry('name');
        $nroProcesso   = new TEntry('numeroProcesso');
        $uasg          = new TEntry('uasg');
        $validadeAta   = new TEntry('data');
        $prazoEntrega  = new TEntry('prazoEntrega');
        
        
        $row = $table->addRow();
        $row->addMultiCell(new TLabel('Nº SRP:'), $nroSRP);
        $row->addMultiCell(new TLabel('Nome Licitação:'), $nome);
        
        $row = $table->addRow();
        $row->addMultiCell(new TLabel('Proc. Orig:'), $nroProcesso);
        $row->addMultiCell(new TLabel('UASG:'), $uasg);
        
        $row = $table->addRow();
        $row->addMultiCell(new TLabel('Validade da Ata:'), $validadeAta);
        $row->addMultiCell(new TLabel('Prazo de Entrega:'), $prazoEntrega);
        
        
        
        $this->form->add($table);
        
        //$this->form->setFields(array($nome, $control, $find_button, $new_button));
        
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$vbox->add();
        
        $container = new TTable;
        $container->style = 'width: 80%';
        //$container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);

        parent::add($container);
    }

}
