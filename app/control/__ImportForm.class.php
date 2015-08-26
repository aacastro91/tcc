<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TBreadCrumb;

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

    private $notebook;
    private $form;
    private $step;
    private $page1;
    private $page2;
    private $page3;

    public function __construct() {
        parent::__construct();
        
        // create the notebook
        $this->notebook = new TNotebook(500, 125);
        $this->notebook->setTabsVisibility(FALSE);
        
        $this->step = new TBreadCrumb;
        $this->step->addItem('Seleção', FALSE);
        $this->step->addItem('Verificação', FALSE);
        $this->step->addItem('Importação', TRUE);
        $this->step->select('Seleção');
        
        // create the form
        $this->form = new TForm;
        
        // creates the notebook page
        $page1 = new TTable;
        $page2 = new TTable;
        $page3 = new TTable;
        
        // add the notebook inside the form
        $this->form->add($this->notebook);
        
        // adds the notebook page
        $this->notebook->appendPage('Seleção'    , $page1);
        $this->notebook->appendPage('Verificação', $page2);
        $this->notebook->appendPage('Importação' , $page3);
        
        //criar campo da aba selecao
        $file = new TFile('file');
        $file->setProperty("accept", ".xlsx");
        $file->setSize('250px');
        
        //campos aba verificacao
        $field2 = new TEntry('field2');   
        $field3 = new TEntry('field3');   

        // add the fields into the tables
        $page1->addRowSet(new TLabel('Local do arquivo:'),   $file );
        $page2->addRowSet(new TLabel('The name you:'), $field2 );
        $page3->addRowSet(new TLabel('The name you typed:'), $field3 );
        
        // creates the action buttons
        $button1=new TButton('action1');
        $button1->setAction(new TAction(array($this, 'onStep2')), 'Next');
        $button1->setImage('ico_next.png');
        
        $button2=new TButton('action2');
        $button2->setAction(new TAction(array($this, 'onStep1')), 'Previous');
        $button2->setImage('ico_previous.png');
        
        $button3=new TButton('action3');
        $button3->setAction(new TAction(array($this, 'onStep3')), 'Next');
        $button3->setImage('ico_next.png');
        
        $button4=new TButton('action4');
        $button4->setAction(new TAction(array($this, 'onStep2')), 'Previous');
        $button4->setImage('ico_previous.png');
        
        $button5=new TButton('save');
        $button5->setAction(new TAction(array($this, 'onSave')), 'Save');
        $button5->setImage('ico_save.png');
        
        $page1->addRowSet( $button1 );
        $page2->addRowSet( $button2, $button3 );
        $page3->addRowSet( $button4, $button5 );
        
        // define wich are the form fields
        $this->form->setFields(array($file, $field2, $field3, $button1, $button2, $button3, $button4, $button5));
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->step);
        $vbox->add($this->form);

        parent::add($vbox);
    }
    
    function onStep1()
    {
        $this->notebook->setCurrentPage(0);
        $this->form->setData($this->form->getData());
        
        $this->step->select('Seleção');
    }
    
    function onStep2()
    {
        $data = $this->form->getData();
        $data->field2 = 'Hi xx';
        $this->notebook->setCurrentPage(1);
        $this->form->setData($data);
        
        $this->step->select('Verificação');
    }
    
    function onStep3()
    {
        $data = $this->form->getData();
        $data->field5 = 'Hi asdf ';
        $this->notebook->setCurrentPage(2);
        $this->form->setData($data);
        
        $this->step->select('Importação');
    }
    
    function onSave()
    {
        $this->notebook->setCurrentPage(2);
        $this->form->setData($this->form->getData());
        new TMessage('info', str_replace('"field', '<br>"field ', json_encode($this->form->getData())));
        
        $this->step->select('Importação');
    }

}
