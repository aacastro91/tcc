<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
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
 * Description of SrpList
 *
 * @author Anderson
 */
class SrpList extends TPage{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    function __construct() {
        parent::__construct();
        parent::include_css('app/resources/custom-table.css');
        
        //criar o form
        $this->form = new TForm('form_consulta_srp');
        $this->form->class = 'tform';
        
        //cria a tabela
        $table = new TTable;
        $table->width = '100%';
        $this->form->add($table);
        
        //adiciona uma linha na tabela para o titulo
        $row = $table->addRow();
        $row->class = 'tformtitle';
        $row->addCell(new TLabel('Consulta SRP'))->colspan = 2;
        
        // cria os campos do formulario
        $numeroSRP        = new TEntry('numeroSRP');
        $numeroIRP        = new TEntry('numeroIRP');
        $numeroProcesso   = new TEntry('numeroProcesso');
        $uasg             = new TEntry('uasg');
        $validade         = new TDate('validade');
        $nome             = new TEntry('nome');
        
        // define os tamanhos
        $numeroSRP->setSize(70);
        $numeroIRP->setSize(70);
        $numeroProcesso->setSize(150);
        $uasg->setSize(70);
        $validade->setSize(100);
        $validade->setProperty('style', 'margin-right : 0px');
        $nome->setSize(400);        
        $validade->setMask('dd/mm/yyyy');
        
        // adiciona uma linha na tabela para cada campo
        $table->addRowSet( new TLabel('Nº SRP:'), $numeroSRP );
        $table->addRowSet( new TLabel('Nº IRP:'), $numeroIRP );
        $table->addRowSet( new TLabel('Proc. Orig.:'), $numeroProcesso );
        $table->addRowSet( new TLabel('Nome:'), $nome );
        $table->addRowSet( new TLabel('UASG:'), $uasg );
        $table->addRowSet( new TLabel('Validade:'), $validade );
        
        $this->form->setFields(array($numeroSRP,$numeroIRP,$numeroProcesso,$uasg,$validade,$nome));
        
        // manter o formulario preenchido durante navegação com os dados da sessao
        $this->form->setData( TSession::getValue('Srp_filter_data') );
        
        //cria o botão de ação
        $find_button = TButton::create('find', array($this, 'onSearch'), 'Consultar', 'ico_find.png');
        
        $this->form->addField($find_button);
        
        // adiciona uma linha para a acao do formulario
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($find_button)->colspan = 2;
        
        // cria o datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);
        

        // cria as colunas do datagrid
        $numeroSRP   = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 70);
        $numeroIRP   = new TDataGridColumn('numeroIRP', 'Nº IRP', 'left', 70);
        $numeroProcesso   = new TDataGridColumn('numeroProcesso', 'Proc. Orig.', 'left', 150);
        $uasg   = new TDataGridColumn('uasg', 'UASG', 'left', 50);
        $validade   = new TDataGridColumn('validade', 'Validade', 'left', 100);
        $nome   = new TDataGridColumn('nome', 'Nome', 'left', 300);
        
        $validade->setTransformer(array($this, 'rowFormat'));


        // adiciona as colunas ao datagrid
        $this->datagrid->addColumn($numeroSRP);
        $this->datagrid->addColumn($numeroIRP);
        $this->datagrid->addColumn($numeroProcesso);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($uasg);
        $this->datagrid->addColumn($validade);
        
        $srpViewAction = new TDataGridAction(array('SrpFormView','onViewSrp'));
        $srpViewAction->setLabel('Itens');
        $srpViewAction->setImage('fa:th-list');
        $srpViewAction->setField('id');
        
        $this->datagrid->addAction($srpViewAction);
        
        // cria o modelo no datagrid
        $this->datagrid->createModel();
        
        // cria o navegador de pagina
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $container = new TTable;       
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        $container->addRow()->addCell($this->pageNavigation);

        parent::add($container);
    }
    
    function onSearch(){
        // pega os dados do formulario
        $data = $this->form->getData();
        
        // limpa os filtros da sessao
        TSession::setValue('SrpList_filter_numeroSRP',   NULL);
        TSession::setValue('SrpList_filter_numeroIRP',   NULL);
        TSession::setValue('SrpList_filter_numeroProcesso',   NULL);
        TSession::setValue('SrpList_filter_uasg',   NULL);
        TSession::setValue('SrpList_filter_validade',   NULL);
        TSession::setValue('SrpList_filter_nome',   NULL);

        if (isset($data->numeroSRP) AND ($data->numeroSRP)) {
            $filter = new TFilter('numeroSRP', 'like', "%{$data->numeroSRP}%"); // cria o filtro
            TSession::setValue('SrpList_filter_numeroSRP',   $filter); // armazena o filtro na sessao
        }


        if (isset($data->numeroIRP) AND ($data->numeroIRP)) {
            $filter = new TFilter('numeroIRP', 'like', "%{$data->numeroIRP}%"); // cria o filtro
            TSession::setValue('SrpList_filter_numeroIRP',   $filter); // armazena o filtro na sessao
        }


        if (isset($data->numeroProcesso) AND ($data->numeroProcesso)) {
            $filter = new TFilter('numeroProcesso', 'like', "%{$data->numeroProcesso}%"); // cria o filtro
            TSession::setValue('SrpList_filter_numeroProcesso',   $filter); // armazena o filtro na sessao
        }


        if (isset($data->uasg) AND ($data->uasg)) {
            $filter = new TFilter('uasg', 'like', "%{$data->uasg}%"); // cria o filtro
            TSession::setValue('SrpList_filter_uasg',   $filter); // armazena o filtro na sessao
        }


        if (isset($data->validade) AND ($data->validade)) {
            $filter = new TFilter('validade', '=',TDate::date2us($data->validade)); // cria o filtro
            TSession::setValue('SrpList_filter_validade',   $filter); // armazena o filtro na sessao
        }


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // cria o filtro
            TSession::setValue('SrpList_filter_nome',   $filter); // armazena o filtro na sessao
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Srp_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    function onReload($param = NULL){
        try
        {
            // abre uma transação com o banco 'saciq'
            TTransaction::open('saciq');
            //TTransaction::setLogger(new \Adianti\Log\TLoggerTXT("c:\\array\\LOG".date("Ymd-His").".txt"));
            // cria um repository para Srp
            $repository = new TRepository('Srp');
            $limit = 10;
            // cria um criteria
            $criteria = new TCriteria;
            
            // ordem default
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // ordem, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('SrpList_filter_numeroSRP')) {
                $criteria->add(TSession::getValue('SrpList_filter_numeroSRP')); // add the session filter
            }


            if (TSession::getValue('SrpList_filter_numeroIRP')) {
                $criteria->add(TSession::getValue('SrpList_filter_numeroIRP')); // add the session filter
            }


            if (TSession::getValue('SrpList_filter_numeroProcesso')) {
                $criteria->add(TSession::getValue('SrpList_filter_numeroProcesso')); // add the session filter
            }


            if (TSession::getValue('SrpList_filter_uasg')) {
                $criteria->add(TSession::getValue('SrpList_filter_uasg')); // add the session filter
            }


            if (TSession::getValue('SrpList_filter_validade')) {
                $criteria->add(TSession::getValue('SrpList_filter_validade')); // add the session filter
            }


            if (TSession::getValue('SrpList_filter_nome')) {
                $criteria->add(TSession::getValue('SrpList_filter_nome')); // add the session filter
            }

            
            // carrega os objetos de acordo com os filtros
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterar a coleção de active records
                foreach ($objects as $object)
                {
                    //muda a data para o formato brasileiro (DD/MM/YYYY)
                    $object->validade = TDate::date2br($object->validade);
                    //adiciona o objeto no datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reseta o criteria (filtro) para contagem de registros
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // contagem de registro
            $this->pageNavigation->setProperties($param); // ordem, pagina
            $this->pageNavigation->setLimit($limit); // limite
            
            // fecha a transação
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // Em caso de erro, gera uma exceção
        {
            // mostra mensagem de erro da exceção
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // rolback no na transação
            TTransaction::rollback();
        }
    }
    
    public function rowFormat($date, $object, $row) {
        
        if ($object->estaVencida(TDate::date2us($date))) {
            $row->style = "background: #FFDADE";
        }
        return $date;
    }
    
    /**
     * method show()
     * Exibe a pagina
     */
    function show()
    {
        // checa se o datagrid ja foi preenchido
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }

}
