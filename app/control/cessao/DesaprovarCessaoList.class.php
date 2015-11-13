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
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
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
 * Description of DesaprovarCessaoList
 *
 * @author Anderson
 */
class DesaprovarCessaoList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_Desaprovar_Cessao');
        $this->form->class = 'tform'; // CSS class
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        $this->form->add($table);
        
        // add a row for the form title
        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell( new TLabel('Desaprovar Cessao') )->colspan = 2;
        

        // create the form fields
        $numeroCessao                 = new TEntry('numeroCessao');
        
        $numeroCessao->setTip('Número do processo gerado no SIGA');


        // define the sizes
        $numeroCessao->setSize(200);


        // add one row for each form field
        $table->addRowSet( new TLabel('Nº da Cessão:'), $numeroCessao );


        $this->form->setFields(array($numeroCessao));


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Cessao_filter_data') );
        
        // create two action buttons to the form
        $find_button = TButton::create('find', array($this, 'onSearch'), 'Buscar', 'ico_find.png');
        //$new_button  = TButton::create('new',  array('CessaoForm', 'onEdit'), 'Novo', 'ico_new.png');
        
        $this->form->addField($find_button);
        //$this->form->addField($new_button);
        
        $buttons_box = new THBox;
        $buttons_box->add($find_button);
        //$buttons_box->add($new_button);
        
        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;
        
        // creates a Datagrid
        parent::include_css('app/resources/custom-table.css');
        $this->datagrid = new TDataGrid;
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);
        $this->datagrid->makeScrollable();
        $this->datagrid->disableDefaultClick();
        

        // creates the datagrid columns
        $id             = new TDataGridColumn('id', 'ID', 'right', 80);
        $srp            = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 100);
        $numeroCessao   = new TDataGridColumn('numeroCessao','Nº da Cessão', 'left', 250);
        $data           = new TDataGridColumn('emissao', 'Data', 'left', 100);


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($srp);
        $this->datagrid->addColumn($numeroCessao);
        $this->datagrid->addColumn($data);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array($this, 'onQuestionAprovarCessao'));
        $action1->setLabel('Desaprovar Cessao');
        $action1->setImage('fa:history fa-fw');
        $action1->setField('id');
       
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        //limpar a sessao com detalhes de itens e cessao
        TSession::delValue('cessao_itens');
        TSession::delValue('SRP_id');
        TSession::delValue('form_cessao');
        
        // create the page container
        //$container = TVBox::pack( $this->form, $this->datagrid);
        $container = new TTable;       
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        //$container->addRow()->addCell($this->pageNavigation);
        parent::add($container);
    }
    
    function onAprovar($param){
        if (!isset($param)){
            return;
        }
        
        $key = $param['cessao'];
        if (!isset($key) || !$key){
            return;
        }
        
        try{
            TTransaction::open('saciq');
            $Cessao = new Cessao($key);
            if (!$Cessao->aprovado){
                new TMessage('error', 'Cessão já Desaprovada');
                $this->onReload();
                return;
            }
            $Cessao->aprovado = FALSE;
            $Cessao->store();            
            TTransaction::close();
            new TMessage('info', 'Cessão Desaprovada com sucesso!');
            $this->onReload();
        } catch (Exception $e) {
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
            return;
        }
    }
    
    function onQuestionAprovarCessao($param){
        
        if (!isset($param)){
            return;
        }
        
        $key = $param['key'];
        if (!isset($key) || !$key){
            return;
        }
        
        try{
            TTransaction::open('saciq');
            $Cessao = new Cessao($key);
            if ($Cessao->srp->estaVencida()){
                new TMessage('error', 'SRP já está vencida!');
                return;
            }  
            $pergunta = 'Voce realmente quer Desaprovar a seguinte Cessão?<br>'.
                    'SRP: ' . $Cessao->srp->numeroSRP .'<br>'.
                    'Nº Cessão: '. $Cessao->numeroCessao .'<br>'.
                    'Emissão: ' . TDate::date2br($Cessao->emissao);
            TTransaction::close();            
        } catch (Exception $e) {
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
            return;
        }
        
        if (!isset($pergunta) || !$pergunta){
            return;
        }
        
        
        $sim = new TAction(array($this, 'onAprovar'));
        //$nao = new TAction(array($this, 'onAction2'));

        // define os parâmetros de cada ação
        $sim->setParameter('cessao', $key);
        
        // shows the question dialog
        new TQuestion($pergunta, $sim);
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
        TSession::setValue('CessaoList_filter_numeroCessao',   NULL);

        if (isset($data->numeroCessao) AND ($data->numeroCessao)) {
            $filter = new TFilter('numeroCessao', 'like', "%{$data->numeroCessao}%"); // create the filter
            TSession::setValue('CessaoList_filter_numeroCessao',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Cessao_filter_data', $data);
        
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
            //TTransaction::setLogger(new TLoggerTXT('c:\array\file.txt'));
            
            // creates a repository for Cessao
            $repository = new TRepository('Cessao');
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
            $criteria->add(new TFilter('aprovado', '=', '1'));
            

            if (TSession::getValue('CessaoList_filter_numeroCessao')) {
                $criteria->add(TSession::getValue('CessaoList_filter_numeroCessao')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    if ($object->srp->estaVencida()){
                        continue;
                    }
                    $object->emissao = TDate::date2br($object->emissao);
                    $object->numeroSRP = $object->srp->numeroSRP;
                    
                    
                    $this->datagrid->addItem($object);
                }
            }
                        
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
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
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
            $object = new Cessao($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
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
