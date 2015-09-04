<?php

use Adianti\Control\TPage;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBSeekButton;

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
        $table = new TTable;
        $table->style = 'width:100%';
        $this->form->add($table);       
        
        //criar os edits
        $numeroSRP     = new TDBSeekButton('numeroSRP', 'saciq', 'form_requisicao','Srp', 'nome', 'numeroSRP', 'nome');
        $nome          = new TEntry('nome');
        $nroProcesso   = new TEntry('numeroProcesso');
        $uasg          = new TEntry('uasg');
        $validadeAta   = new TEntry('data');
        $prazoEntrega  = new TEntry('prazoEntrega');
        
        // add a row for the form title
        $row  = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell( new TLabel('Requisição de quantitativo'));
        $cell->colspan = 4;
        
        $table->addRowSet(new TLabel('Nº SRP:'),$numeroSRP, new TLabel('Nome Licitação:'), $nome );
        
        $table->addRowSet(new TLabel('Proc. Orig:'), $nroProcesso, new TLabel('UASG:'), $uasg);
        
        $table->addRowSet(new TLabel('Validade da Ata:'), $validadeAta, new TLabel('Prazo de Entrega:'), $prazoEntrega);
                
       // $table_item->addRowSet(new Label, $product_id,  $lab_des, $product_description);

        
        //cria o titulo
        //$row = $details->addRow();
        //$row->class = 'tformtitle';
        //$cell = $row->addCell(new TLabel('Requisição de quantitativo'));
        //$cell->colspan = 2;  
        
        
        
        
        
        $this->form->setFields(array($numeroSRP, $nome, $nroProcesso, $uasg, $validadeAta, $prazoEntrega));
        
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$vbox->add();
        
        $container = new TTable;
        $container->style = 'width: 80%';
        //$container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);

        parent::add($container);
    }

}
