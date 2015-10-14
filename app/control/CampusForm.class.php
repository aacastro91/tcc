<?php

use Adianti\Base\TStandardForm;
use Adianti\Control\TAction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TQuickForm;

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

class CampusForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_Campus');
        $this->form->setFormTitle('Cadastro de Campus');
        $this->form->class = 'tform'; // CSS class
        
        parent::setDatabase('saciq');
        
        parent::setActiveRecord('Campus');

        // create the form fields
        $id                             = new TEntry('id');
        $nome                           = new TEntry('nome');
        $uasg                           = new TEntry('uasg');
        $sigla                          = new TEntry('sigla');


        // define the sizes
        $id->setSize(50);
        $nome->setSize(500);
        $uasg->setSize(150);
        $sigla->setSize(100);
        
        $id->setEditable(FALSE);

        // validations
        $nome->addValidation('nome', new TRequiredValidator);
        $uasg->addValidation('UASG', new TRequiredValidator);


        // add one row for each form field
        $this->form->addQuickField( new TLabel('ID:'), $id, 50 );
        $this->form->addQuickField( $label_nome = new TLabel('Nome:'), $nome, 400 );
        $this->form->addQuickField( $label_uasg = new TLabel('UASG:'), $uasg, 150 );
        $this->form->addQuickField( new TLabel('Sigla:'), $sigla, 100 );

        
        $this->form->setFields(array($id,$nome,$uasg,$sigla));


        // Adiciona as ações do formulário
        $this->form->addQuickAction('Salvar', new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction('Novo', new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar para a listagem',new TAction(array('CampusList','onReload')),'ico_datagrid.png');

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml','CampusList'));
        $container->addRow()->addCell($this->form);
        
        
        // Adiciona o formulário a pagina
        parent::add($container);
    }
}
