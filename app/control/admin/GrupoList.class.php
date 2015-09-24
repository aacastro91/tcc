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
/**
 * GrupoList Listing
 * @author  Anderson
 */
class GrupoList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Construtor
     * 
     * Criação da listagem de grupos
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/custom-table.css');
        
        // Cria o form
        $this->form = new TForm('form_search_Grupo');
        $this->form->class = 'tform';
        
        // cria a tabela
        $table = new TTable;
        $table->style = 'width:100%';
        
        //cria o titulo
        $table->addRowSet( new TLabel('Grupos'), '' )->class = 'tformtitle';
        
        // Adiciona a tabela no form
        $this->form->add($table);
        
        // Cria os campos para filtro      
        $nome = new TEntry('nome');
        $nome->setValue(TSession::getValue('s_nome'));
        $nome->setSize(300);
        
        $sigla = new TEntry('sigla');
        $sigla->setValue(TSession::getValue('s_sigla'));
        $sigla->setSize(100);
        
        // Adiciona linha na tabela para inserir o campos
        $row=$table->addRow();
        $row->addCell(new TLabel('Nome: '));
        $row->addCell($nome);
        
        $row = $table->addRow();
        $row->addCell(new TLabel('Sigla: '));
        $row->addCell($sigla);
        
        // cria os dois botoes de ações do form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        // define o botao de acao
        $find_button->setAction(new TAction(array($this, 'onSearch')),'Buscar');
        $find_button->setImage('ico_find.png');
        
        $new_button->setAction(new TAction(array('GrupoForm', 'onEdit')), 'Novo');
        $new_button->setImage('ico_new.png');
        
        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;
        
        // define qual é os campos do form
        $this->form->setFields(array($nome, $sigla, $find_button, $new_button));
        
        // cria o datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);
        
        // cria as colunas do datagrid
        $id    = new TDataGridColumn('id', 'ID', 'center');
        $nome  = new TDataGridColumn('nome', 'Nome', 'center');
        $sigla = new TDataGridColumn('sigla', 'Sigla', 'center');
        

        // adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($sigla);

        // cria as acoes das colunas do datagrid (quando clica no titulo do grid)
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_nome= new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $nome->setAction($order_nome);
        
        $order_sigla= new TAction(array($this, 'onReload'));
        $order_sigla->setParameter('order', 'sigla');
        $sigla->setAction($order_sigla);
        

        // edição em linha
        $nome_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $nome_edit->setField('id');
        $nome->setEditAction($nome_edit);
        
        $sigla_edit = new TDataGridAction(array($this,'onInlineEdit'));
        $sigla_edit->setField('id');
        $sigla->setEditAction($sigla_edit);
        

        // cria duas acoes do datagrid
        $action1 = new TDataGridAction(array('GrupoForm', 'onEdit'));
        $action1->setLabel('Editar');
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel('Excluir');
        $action2->setImage('ico_delete.png');
        $action2->setField('id');
        
        // adiciona as acoes ao datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // cria o modelo do datagrid
        $this->datagrid->createModel();
        
        // cria o navegador de paginas
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // cria a estrutura da tela usando tabelas
        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        $container->addRow()->addCell($this->pageNavigation);
        
        // add the container inside the page
        parent::add($container);
    }
    
    /**
     * method onInlineEdit()
     * Metodo para edicao em linha na Datagrid
     * @param $param Array containing:
     *              key: id do objeto
     *              field nome: nome do campo que vai ser atualizado
     *              value: Novo valor do atributo
     */
    function onInlineEdit($param)
    {
        try
        {
            // pega os parametros
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            // abre uma transacao com o banco 'saciq'
            TTransaction::open('saciq');
            
            // instancia um objeto de Grupo
            $object = new Grupo($key);
            // atualiza o campo do objeto
            $object->{$field} = $value;
            //salva o objeto modificado
            $object->store();
            
            // fecha a transacao
            TTransaction::close();
            
            // recarrega a lista
            $this->onReload($param);
            // mostra a menssagem de sucesso
            new TMessage('info', "Registro atualziado");
        }
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSearch()
     * registrar o filtro na sessão quando o usuário realiza uma pesquisa
     */
    function onSearch()
    {
        // pegar os dados do form de busca
        $data = $this->form->getData();
        
        TSession::setValue('s_nome_filter',   NULL);
        TSession::setValue('s_sigla_filter', NULL);
        
        TSession::setValue('s_nome', '');
        TSession::setValue('s_sigla', '');
        
        // checa se o valor foi preenchido pelo usuario
        if ( $data->nome )
        {
            // cria o filtro usando o que o usuario digitou
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            
            TSession::setValue('s_nome_filter', $filter);
            TSession::setValue('s_nome', $data->nome);            
        }
        if ( $data->sigla )
        {
            // cria o filtro usando o que o usuario digitou
            $filter = new TFilter('sigla', 'like', "%{$data->sigla}%");
            
            // stores the filter in the session
            TSession::setValue('s_sigla_filter',   $filter);
            TSession::setValue('s_sigla', $data->sigla);
        }
        // preenche o form com os dados novamente
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * carregar o datagrid com objetos do banco
     */
    function onReload($param = NULL)
    {
        try
        {
            // abre uma transacao com o banco 'saciq'
            TTransaction::open('saciq');
            
            if( ! isset($param['order']) )
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            // cria um repository para Grupo
            $repository = new TRepository('Grupo');
            $limit = 10;
            // cria um criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('s_nome_filter'))
            {
                // adiciona o filtro gravado na sessao para o obj criteria.
                $criteria->add(TSession::getValue('s_nome_filter'));
            }
            if (TSession::getValue('s_sigla_filter'))
            {
                // adiciona o filtro gravado na sessao para o obj criteria.
                $criteria->add(TSession::getValue('s_sigla_filter'));
            }
            
            // carrega os objetos de acordo o filtro criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterar a coleção de active records
                foreach ($objects as $object)
                {
                    // adiciona o objeto dentro do datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset o criteria para o record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // quantidade de registros
            $this->pageNavigation->setProperties($param); // ordem, pagina
            $this->pageNavigation->setLimit($limit); // limite
            
            // fecha a transacao
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // em caso de erro
        {
            // mostra a mensagem de excessao
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executada quando o usuario clica no botao delete
     * pergunta se o usuario realmente deseja excluir
     */
    function onDelete($param)
    {
        // define a acao de deletar
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // mostra o dialogo para o usuario
        new TQuestion('Deseja realmente excluir ?', $action);
    }
    
    /**
     * method Delete()
     * Deleta o registro
     */
    function Delete($param)
    {
        try
        {
            // pega o parametro $key
            $key=$param['key'];
            // abre uma transacao com o banco 'saciq'
            TTransaction::open('saciq');
            
            // instancia o objeto Grupo
            $object = new Grupo($key);
            
            // deleta os objetos do banco de dados
            $object->delete();
            
            // fecha a transacao
            TTransaction::close();
            
            // recarrega a listagem
            $this->onReload( $param );
            // mostra menssagem de sucesso
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'));
        }
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
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
        // checa se o datagrid ja está carregado
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
?>