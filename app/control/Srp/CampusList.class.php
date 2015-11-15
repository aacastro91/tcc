<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
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
 * CampusList Listing
 * @author  Anderson
 */
class CampusList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/custom-table.css');
        
        // creates the form
        $this->form = new TForm('form_search_Campus');
        $this->form->class = 'tform'; // CSS class
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        
        // add a row for the form title
        $table->addRowSet( new TLabel('Consulta Câmpus') ,'')->class = 'tformtitle'; // CSS class        

        $this->form->add($table);
                
        // create the form fields
        $nome  = new TEntry('nome');
        $nome->setValue(TSession::getValue('s_nome'));
        $uasg  = new TEntry('uasg');
        $uasg->setValue(TSession::getValue('s_uasg'));
        $sigla = new TEntry('sigla');
        $sigla->setValue(TSession::getValue('s_sigla'));


        // define the sizes
        $nome->setSize(500);
        $uasg->setSize(100);
        $sigla->setSize(150);


        // add one row for each form field
        $table->addRowSet( new TLabel('Nome:'), $nome );
        $table->addRowSet( new TLabel('UASG:'), $uasg );
        $table->addRowSet( new TLabel('Sigla:'), $sigla );


        $this->form->setFields(array($nome,$uasg,$sigla));


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Campus_filter_data') );
        
        // create two action buttons to the form
        $find_button = TButton::create('find', array($this, 'onSearch'), 'Buscar', 'ico_find.png');
        $new_button  = TButton::create('new',  array('CampusForm', 'onEdit'), 'Novo', 'ico_new.png');
        
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
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $id      = new TDataGridColumn('id', 'ID', 'right');
        $nome    = new TDataGridColumn('nome', 'Nome', 'left');
        $uasg    = new TDataGridColumn('uasg', 'UASG', 'left');
        $sigla   = new TDataGridColumn('sigla', 'Sigla', 'left');

        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($uasg);
        $this->datagrid->addColumn($sigla);


        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_nome= new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $nome->setAction($order_nome);

        $order_uasg= new TAction(array($this, 'onReload'));
        $order_uasg->setParameter('order', 'uasg');
        $uasg->setAction($order_uasg);

        $order_sigla= new TAction(array($this, 'onReload'));
        $order_sigla->setParameter('order', 'sigla');
        $sigla->setAction($order_sigla);

        // inline editing
        $nome_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $nome_edit->setField('id');
        $nome->setEditAction($nome_edit);
/*
        $uasg_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $uasg_edit->setField('id');
        $uasg->setEditAction($uasg_edit);

        $sigla_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $sigla_edit->setField('id');
        $sigla->setEditAction($sigla_edit);
*/
        // creates two datagrid actions
        $action1 = new TDataGridAction(array('CampusForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel(_t('Delete'));
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
        
        $container = new TTable;
        //$container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        $container->addRow()->addCell($this->pageNavigation);
        
        // add the container inside the page
        parent::add($container);
    }
    
    /**
     * method onInlineEdit()
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('saciq'); // open a transaction with database
            $object = new Campus($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Registro atualizado");
        }
        catch (Exception $e) // in case of exception
        {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('CampusList_filter_nome',   NULL);
        TSession::setValue('CampusList_filter_uasg',   NULL);
        TSession::setValue('CampusList_filter_sigla',   NULL);

        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('CampusList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->uasg) AND ($data->uasg)) {
            $filter = new TFilter('uasg', 'like', "%{$data->uasg}%"); // create the filter
            TSession::setValue('CampusList_filter_uasg',   $filter); // stores the filter in the session
        }


        if (isset($data->sigla) AND ($data->sigla)) {
            $filter = new TFilter('sigla', 'like', "%{$data->sigla}%"); // create the filter
            TSession::setValue('CampusList_filter_sigla',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Campus_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            
            // creates a repository for Campus
            $repository = new TRepository('Campus');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('CampusList_filter_nome')) {
                $criteria->add(TSession::getValue('CampusList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('CampusList_filter_uasg')) {
                $criteria->add(TSession::getValue('CampusList_filter_uasg')); // add the session filter
            }


            if (TSession::getValue('CampusList_filter_sigla')) {
                $criteria->add(TSession::getValue('CampusList_filter_sigla')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Deseja realmente excluir ?', $action);
    }
    
    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('saciq'); // open a transaction with database
            $object = new Campus($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', 'Registro excluído'); // success message
        }
        catch (Exception $e) // in case of exception
        {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
