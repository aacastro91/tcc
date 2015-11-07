<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;

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
 * Description of RequisicaoList
 *
 * @author Anderson
 */
class RequisicaoList extends TPage {

    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct() {
        parent::__construct();

        // creates the form
        $this->form = new TForm('form_search_Requisicao');
        $this->form->class = 'tform'; // CSS class
        // creates a table
        $table = new TTable;
        $table->width = '100%';
        $this->form->add($table);

        // add a row for the form title
        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell(new TLabel('Consulta Requisicao'))->colspan = 2;


        // create the form fields
        $numeroProcesso = new TEntry('numeroProcesso');


        // define the sizes
        $numeroProcesso->setSize(200);

        $numeroProcesso->setMaxLength(30);
        
        $numeroProcesso->setTip('Número do processo gerado no SIGA');


        // add one row for each form field
        $table->addRowSet(new TLabel('Nº do Processo:'), $numeroProcesso);


        $this->form->setFields(array($numeroProcesso));


        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('Requisicao_filter_data'));

        // create two action buttons to the form
        $find_button = TButton::create('find', array($this, 'onSearch'), 'Buscar', 'ico_find.png');
        $new_button = TButton::create('new', array('RequisicaoForm', 'onEdit'), 'Novo', 'ico_new.png');

        $this->form->addField($find_button);
        $this->form->addField($new_button);

        $buttons_box = new THBox;
        $buttons_box->add($find_button);
        $buttons_box->add($new_button);

        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;

        // creates a Datagrid
        parent::include_css('app/resources/custom-table.css');
        $this->datagrid = new TDataGrid;
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);


        // creates the datagrid columns
        $id = new TDataGridColumn('id', 'ID', 'right', 80);
        $srp = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 100);
        $numeroProcesso = new TDataGridColumn('numeroProcesso', 'Nº do processo', 'left', 250);
        $data = new TDataGridColumn('emissao', 'Data', 'left', 100);

        $id->setTransformer(array($this, 'rowFormat'));


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($srp);
        $this->datagrid->addColumn($numeroProcesso);
        $this->datagrid->addColumn($data);


        // creates two datagrid actions
        $action1 = new TDataGridAction(array('RequisicaoForm', 'onEdit'));
        //$action1 = new TDataGridAction(array($this, 'onCheckValidadeSRP'));
        $action1->setLabel('Editar');
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        $action1->setDisplayCondition(array($this, 'onDisplayConditionEdit'));

        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel('Excluir');
        $action2->setImage('ico_delete.png');
        $action2->setField('id');

        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        //limpar a sessao com detalhes de itens e requisicao
        TSession::delValue('requisicao_itens');
        TSession::delValue('SRP_id');
        TSession::delValue('form_requisicao');

        // create the page container
        $container = TVBox::pack($this->form, $this->datagrid, $this->pageNavigation);
        parent::add($container);
    }

    public function rowFormat($id, $object, $row) {
        if ($object->srp->estaVencida()) {
            $row->style = "background: #FFDADE";
        }
        return $id;
    }

    public function onDisplayConditionEdit($object) {
        if ($object->srp->estaVencida()) {
            return false;
        }
        return true;
    }
/*
    function onCheckValidadeSRP($param) {

        if (isset($param) && isset($param['key']))
            $key = $param['key'];

        if (!isset($key)) {
            return;
        }

        try {
            TTransaction::open('saciq');

            $requisicao = new Requisicao($key);
            if ($requisicao->srp->estaVencida()) {
                new TMessage('error', 'SRP Vencida!');
                return;
            }
            AdiantiCoreApplication::loadPage('RequisicaoForm', 'onEdit', array('key' => $key));
        } catch (Exception $ex) {
            TTransaction::rollback();
            new TMessage('error', 'Erro: ' . $ex->getMessage());
        }
    }*/

    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch() {
        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('RequisicaoList_filter_numeroProcesso', NULL);

        if (isset($data->numeroProcesso) AND ( $data->numeroProcesso)) {
            $filter = new TFilter('numeroProcesso', 'like', "%{$data->numeroProcesso}%"); // create the filter
            TSession::setValue('RequisicaoList_filter_numeroProcesso', $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Requisicao_filter_data', $data);

        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL) {
        try {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            //TTransaction::setLogger(new TLoggerTXT('c:\array\file.txt'));
            // creates a repository for Requisicao
            $repository = new TRepository('Requisicao');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;

            // default order
            if (empty($param['order'])) {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->add(new TFilter('aprovado', '=', '0'));


            if (TSession::getValue('RequisicaoList_filter_numeroProcesso')) {
                $criteria->add(TSession::getValue('RequisicaoList_filter_numeroProcesso')); // add the session filter
            }


            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            $this->datagrid->clear();
            if ($objects) {
                // iterate the collection of active records
                foreach ($objects as $object) {
                    $object->emissao = TDate::date2br($object->emissao);
                    $object->numeroSRP = $object->srp->numeroSRP;


                    $this->datagrid->addItem($object);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count = $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param) {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param) {
        try {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('saciq'); // open a transaction with database
            $object = new Requisicao($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload($param); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
        } catch (Exception $e) { // in case of exception
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * method show()
     * Shows the page
     */
    function show() {
        // check if the datagrid is already loaded
        if (!$this->loaded AND ( !isset($_GET['method']) OR $_GET['method'] !== 'onReload')) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }

}
