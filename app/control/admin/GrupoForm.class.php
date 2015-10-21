<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TFrame;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TSortList;
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
 * Description of GrupoForm
 *
 * @author Anderson
 */
class GrupoForm extends TPage {

    protected $form; // form
    private $loaded;
    protected $list1;
    protected $list2;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct() {
        parent::__construct();

        // creates the table container
        $table = new TTable;
        $table->style = 'width:100%';

        $frame_funcionalidades = new TFrame;
        $frame_funcionalidades->setLegend('Funcionalidades');

        // Cria o form
        $this->form = new TForm('form_Grupo');
        $this->form->class = 'tform';


        // add the notebook inside the form
        $this->form->add($table);
        $table->addRowSet(new TLabel('Grupo'), '')->class = 'tformtitle';

        // cria os campos de pesquisa do form
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $sigla = new TEntry('sigla');

        $this->list1 = new TSortList('list1');
        $this->list2 = new TSortList('list2');

        $this->list1->setSize(300, 200);
        $this->list2->setSize(300, 200);

        $this->list1->connectTo($this->list2);
        $this->list2->connectTo($this->list1);

        /* $multifield = new TMultiField('programs');
          $program_id = new TDBSeekButton('program_id', 'saciq', 'form_Grupo', 'Funcionalidade', 'nome', 'programs_id', 'programs_nome');
          $program_nome = new TEntry('program_nome');

          $frame_programs->add($multifield);

          $multifield->setHeight(140);
          $multifield->setClass('Funcionalidade');
          $multifield->addField('id', 'ID', $program_id, 100, true);
          $multifield->addField('nome', 'Funcionalidade', $program_nome, 250);
          $multifield->setOrientation('horizontal'); */

        // define the sizes
        //$program_id->setSize(70);
        $id->setSize(100);
        $nome->setSize(400);
        $sigla->setSize(150);

        // validations
        $nome->addValidation('nome', new TRequiredValidator);

        // outras propriedades
        $id->setEditable(false);
        //$program_nome->setEditable(false);
        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet(new TLabel('Nome: '), $nome);
        $table->addRowSet(new TLabel('Sigla:'), $sigla);

        // add a row for the field nome
        //$row = $table->addRow();
        //$cell = $row->addCell($frame_programs);
        //$cell->colspan = 2;

        $vbox1 = new TVBox();
        $vbox1->add(new TLabel('<b>Disponível</b>'));
        $vbox1->add($this->list1);

        $vbox2 = new TVBox();
        $vbox2->add(new TLabel('<b>Selecionado</b>'));
        $vbox2->add($this->list2);

        $hbox = new THBox();
        $hbox->add($vbox1);
        $hbox->add($vbox2);
        $frame_funcionalidades->add($hbox);
        $row = $table->addRow();
        $cell = $row->addCell($frame_funcionalidades);
        $cell->colspan = 2;

        // create an action button (save)
        $save_button = new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), 'Salvar');
        $save_button->setImage('ico_save.png');

        // create an new button (edit with no parameters)
        $new_button = new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), 'Novo');
        $new_button->setImage('ico_new.png');

        $list_button = new TButton('list');
        $list_button->setAction(new TAction(array('GrupoList', 'onReload')), 'Voltar para a listagem');
        $list_button->setImage('ico_datagrid.png');

        // define the form fields
        $this->form->setFields(array($id, $nome, $sigla, $this->list1, $this->list2, $save_button, $new_button, $list_button));

        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);

        $container = new TTable;
        $container->width = '80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'GrupoList'));
        $container->addRow()->addCell($this->form);

        $row = $table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell($buttons);
        $cell->colspan = 2;

        // add the form to the page
        parent::add($container);
    }

    public function onReload($param = null) {
        try {
            $this->list1->setDefault();
            $this->list2->setDefault();

            TTransaction::open('saciq');
            
            if (isset($param['key'])) {
                $key = $param['key'];

                $grupo = new Grupo($key);

                if ($grupo->getFuncionalidades()) {
                    $list2Items = array();
                    foreach ($grupo->getFuncionalidades() as $funcionalidade) {
                        $list2Items[$funcionalidade->id] = $funcionalidade->nome;
                    }
                    $this->list2->addItems($list2Items);
                }
            }
            $repository = new TRepository('Funcionalidade');
            $funcionalidades = $repository->load();

            foreach ($funcionalidades as $f) {
                $id = $f->id;
                if (!isset($list2Items[$id])) {
                    $list1Items[$id] = $f->nome;
                }
            }

            if (isset($list1Items)) {
                $this->list1->addItems($list1Items);
            }


            $this->loaded = true;

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

        //$this->list1->addItems(array('1' => 'One', '2' => 'Two', '3' => 'Three'));
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave() {
        try {

            $data = $this->form->getData();

            

            // open a transaction with database 'saciq'
            TTransaction::open('saciq');

            // cria um objeto Grupo            
            $object = new Grupo($data->id);
            $object->clearParts();
            $object->nome = $data->nome;
            $object->sigla= $data->sigla;
            if ($data->list2){
                foreach ($data->list2 as $value) {
                    $object->addFuncionalidade(new Funcionalidade($value));
                }
            }
                

            //if ($object->programs) {
            //    foreach ($object->programs as $program) {
            //        $object->addFuncionalidade($program);
            //    }
            //}

            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $this->form->setData($object); // fill the form with the active record data

            $usuario = new Usuario(TSession::getValue('id'));
            $funcionalidades = $usuario->getFuncionalidades();
            $funcionalidades['LoginForm'] = TRUE;
            TSession::setValue('funcionalidades', $funcionalidades);
            TTransaction::close(); // close the transaction
            new TMessage('info', 'Registro salvo'); // shows the success message
        } catch (Exception $e) { // Em caso de erro
            if (strpos($e->getMessage(), 'Integrity constraint violation')) {
                $posi = strpos($e->getMessage(), 'Duplicate entry ') + strlen('Duplicate entry ') + 1;
                $posf = strpos($e->getMessage(), '\' for key');
                $str = substr($e->getMessage(), $posi, 3);

                $idGrupo = substr($str, 0, strpos($str, '-'));
                $grupo = new Grupo($idGrupo);
                $idFuncionalidade = substr($str, strpos($str, '-') + 1);
                $funcionalidade = new Funcionalidade($idFuncionalidade);


                new TMessage('error', '<b>Registro duplicado</b><br>A funcionalidade "' . $funcionalidade->nome . '" já foi registrada no grupo ' . $grupo->nome);
            } else {

                new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param) {
        try {
            if (isset($param['key'])) {
                // get the parameter $key
                $key = $param['key'];

                // abre transacao com banco 'saciq'
                TTransaction::open('saciq');

                // instancia um objeto do tipo Grupo
                $object = new Grupo($key);

                $object->programs = $object->getFuncionalidades();

                // fill the form with the active record data
                $this->form->setData($object);

                // close the transaction
                TTransaction::close();
            } else {
                $this->form->clear();
            }
            $this->onReload($param);
        } catch (Exception $e) { // Em caso de erro
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }

    function show() {
        if (!$this->loaded)
            $this->onReload();
        parent::show();
    }

}   

?>