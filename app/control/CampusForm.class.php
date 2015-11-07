<?php

use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Validator\TMaxLengthValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;

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
 * CampusForm Registration
 * @author  Anderson
 */
class CampusForm extends TPage {

    protected $form; // form

    /**
     * Class constructor
     * Creates the page and the registration form
     */

    function __construct() {
        parent::__construct();

        // creates the form
        $this->form = new TForm('form_Campus');
        $this->form->class = 'tform'; // CSS class
        //$this->form->style = 'width: 500px';

        // add a table inside form
        $table = new TTable;
        $table->width = '100%';
        $this->form->add($table);

        // add a row for the form title
        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell(new TLabel('Cadastro de Câmpus'))->colspan = 2;



        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $uasg = new TEntry('uasg');
        $sigla = new TEntry('sigla');

        $id->setEditable(FALSE);


        // define the sizes
        $id->setSize(50);
        $nome->setSize(400);
        $uasg->setSize(150);
        $sigla->setSize(100);


        // validations
        $nome->addValidation('Nome', new TRequiredValidator);
        $uasg->addValidation('UASG', new TRequiredValidator);
        $sigla->addValidation('Sigla', new TRequiredValidator);


        $nome->addValidation('Nome', new TMaxLengthValidator, array(50));
        $uasg->addValidation('UASG', new TMaxLengthValidator, array(10));
        $sigla->addValidation('Sigla', new TMaxLengthValidator, array(3));


        // add one row for each form field
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet($label_nome = new TLabel('Nome:'), $nome);
        //$label_nome->setFontColor('#FF0000');
        $table->addRowSet($label_uasg = new TLabel('UASG:'), $uasg);
        //$label_uasg->setFontColor('#FF0000');
        $table->addRowSet($label_sigla = new TLabel('Sigla:'), $sigla);
        //$label_sigla->setFontColor('#FF0000');


        $this->form->setFields(array($id, $nome, $uasg, $sigla));


        // create the form actions
        $save_button = TButton::create('save', array($this, 'onSave'), 'Salvar', 'ico_save.png');
        $new_button = TButton::create('new', array($this, 'onEdit'), 'Novo', 'ico_new.png');
        $back_button = TButton::create('back', array('CampusList', 'onReload'), 'Voltar para a listagem', 'ico_datagrid.png');

        $this->form->addField($save_button);
        $this->form->addField($new_button);
        $this->form->addField($back_button);

        $buttons_box = new THBox;
        $buttons_box->add($save_button);
        $buttons_box->add($new_button);
        $buttons_box->add($back_button);

        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;

        $container = new TTable;
        //$container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'CampusList'));
        $container->addRow()->addCell($this->form);

        parent::add($container);
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave() {
        try {
            TTransaction::open('saciq'); // open a transaction
            // get the form data into an active record Campus
            $object = $this->form->getData('Campus');
            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $this->form->setData($object); // keep form data
            TTransaction::close(); // close the transaction
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        } catch (Exception $e) { // in case of exception
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique se a sigla já não foi registrada em outro câmpus');
            } else if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error</b> ' . $e->getCode()); // shows the exception error message
            }
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param) {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('saciq'); // open a transaction
                $object = new Campus($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            } else {
                $this->form->clear();
            }
        } catch (Exception $e) { // in case of exception
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

}
