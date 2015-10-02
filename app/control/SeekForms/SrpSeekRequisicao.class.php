<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Wrapper\TQuickForm;
use Adianti\Widget\Wrapper\TQuickGrid;

/**
 * Description of SrpSeek
 *
 * @author Anderson
 */
class SrpSeekRequisicao extends TWindow {

    private $form; // form de busca
    private $datagrid; //listagem
    private $navegadorPagina;
    private $carregado;

    function __construct() {
        parent::__construct();
        parent::setSize(850, 500);
        parent::setTitle('Busca de SRP');
        new TSession;

        //cria o formulario
        $this->form = new TQuickForm('form_busca_srp');

        //cria os campos de busca do formulario
        $numeroSRP = new TEntry('numeroSRP');
        $numeroProcesso = new TEntry('numeroProcesso');
        $nome = new TEntry('nome');

        //valors da sessao
        $numeroSRP->setValue(TSession::getValue('srp_numeroSRP'));
        $numeroProcesso->setValue(TSession::getValue('srp_numeroProcesso'));
        $nome->setValue(TSession::getValue('srp_nome'));

        //adiciona os campos no formulario
        $this->form->addQuickField('Nº SRP', $numeroSRP);
        $this->form->addQuickField('Nº Processo', $numeroProcesso);
        $this->form->addQuickField('Nome', $nome);

        //adiciona a acao ao formulario
        $this->form->addQuickAction('Buscar', new TAction(array($this, 'onSearch')), 'ico_find.png');

        //criar a datagrid
        $this->datagrid = new TQuickGrid;
        $this->datagrid->setHeight(300);

        //criar as colunas da datagrid
        $this->datagrid->addQuickColumn('Nº SRP', 'numeroSRP', 'left', 50);
        $this->datagrid->addQuickColumn('Nº Processo', 'numeroProcesso', 'left', 80);
        $this->datagrid->addQuickColumn('UASG', 'uasg', 'left', 50);
        $this->datagrid->addQuickColumn('Validade', 'validade', 'left', 70);
        $this->datagrid->addQuickColumn('Nome', 'nome', 'left', 280);

        //criar acao da coluna
        $this->datagrid->addQuickAction('Select', new TDataGridAction(array($this, 'onSelect')), 'numeroSRP', 'ico_apply.png');

        //cria o modelo
        $this->datagrid->createModel();


        //criar o navegador de pagina
        $this->navegadorPagina = new TPageNavigation();
        $this->navegadorPagina->setAction(new TAction(array($this, 'onReload')));
        $this->navegadorPagina->setWidth($this->datagrid->getWidth());

        // criar a estrutura da pagina usando uma tabela
        $table = new TTable;
        $table->addRow()->addCell($this->form);
        $table->addRow()->addCell($this->datagrid);
        $table->addRow()->addCell($this->navegadorPagina);
        // add the table inside the page
        parent::add($table);
    }

    /**
     * Registro de filtros na sessao
     */
    function onSearch() {
        $data = $this->form->getData();

        TSession::setValue('srp_numeroSRP_filter', NULL);
        TSession::setValue('srp_numeroProcesso_filter', NULL);
        TSession::setValue('srp_nome_filter', NULL);

        TSession::setValue('srp_numeroSRP', '');
        TSession::setValue('srp_numeroProcesso', '');
        TSession::setValue('srp_nome', '');

        if (isset($data->numeroSRP) && ($data->numeroSRP)) {
            $filter = new TFilter('numeroSRP', '=', "{$data->numeroSRP}");
            // armazenar o filtro na sessao
            TSession::setValue('srp_numeroSRP_filter', $filter);
            TSession::setValue('srp_numeroSRP', $data->numeroSRP);
        }

        if (isset($data->numeroProcesso) && ($data->numeroProcesso)) {
            $filter = new TFilter('numeroProcesso', '=', "{$data->numeroProcesso}");
            TSession::setValue('srp_numeroProcesso_filter', $filter);
            TSession::setValue('srp_numeroProcesso', $data->numeroProcesso);
        }

        if (isset($data->nome) && ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            TSession::setValue('srp_nome_filter', $filter);
            TSession::setValue('srp_nome', $data->nome);
        }

        $this->form->setData($data);

        // redefinir os parametros para o metodo reload
        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    function onReload($param = null) {
        try {
            //inicia uma transacao no banco
            TTransaction::open('saciq');

            $repository = new TRepository('Srp');
            $limit = 10;
            $criteria = new TCriteria();
            $criteria->setProperties($param);


            //filtro do numero srp
            if (TSession::getValue('srp_numeroSRP_filter')) {
                $criteria->add(TSession::getValue('srp_numeroSRP_filter'));
            }

            if (TSession::getValue('srp_numeroProcesso_filter')) {
                $criteria->add(TSession::getValue('srp_numeroProcesso_filter'));
            }

            if (TSession::getValue('srp_nome_filter')) {
                $criteria->add(TSession::getValue('srp_nome_filter'));
            }
            $srps = $repository->load($criteria);

            $this->datagrid->clear();

            $hoje = date("Y-m-d");
            
            if ($srps) {
                foreach ($srps as $srp) {
                    if ($srp->validade < $hoje)
                        continue;
                    $srp->validade = TDate::date2br($srp->validade);
                    $this->datagrid->addItem($srp);
                }
            }

            //reseta as propriedadso do objeto criteria para contar os registros
            $criteria->resetProperties();
            $count = $repository->count($criteria);

            $this->navegadorPagina->setCount($count);
            $this->navegadorPagina->setProperties($param);
            $this->navegadorPagina->setLimit($limit);

            //fecha a transacao
            TTransaction::close();
            $this->carregado = true;
        } catch (Exception $ex) {
            new TMessage('error', '<b>Error</b> ' . $ex->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSelect($param) {
        try {
            //var_dump($param);
            $key = $param['key'];

            if (!$key)
                return;


            TTransaction::open('saciq');
            //$srp = new Srp($key);

            $repository = new TRepository('Srp');
            $criteria = new TCriteria();
            $criteria->add(new TFilter('numeroSRP', '=', $key));

            $srps = $repository->load($criteria);
            if (count($srps) > 0) {
                $srp = $srps[0];
            }
            
            $hoje = date("Y-m-d");
            if ($srp->validade < $hoje){
                new TMessage('error', 'SRP Vencida!');
                return;
            }

            //TTransaction::close();

            $oldSRP = NULL;
            if ($oldSRP != TSession::getValue('SRP_id'))
                $oldSRP = TSession::getValue('SRP_id');

            if ($oldSRP != $srp->id) {
                if (TSession::getValue('requisicao_itens'))
                    $reloadForm = true;
                TSession::delValue('requisicao_itens');
                $obj = new stdClass();
                $obj->numeroItem = '';
                $obj->descricaoSumaria = '';
                $obj->valorUnitario = '';
                $obj->quantidade = '';
                $obj->prazoEntrega = '60 Dias';
                $obj->justificativa = '';
                TForm::sendData('form_itens', $obj);
            }

            TSession::setValue('SRP_id', $srp->id);

            $obj = new stdClass();
            $obj->numeroSRP = $srp->numeroSRP;
            $obj->nome = $srp->nome;
            $obj->numeroProcessoOrigem = $srp->numeroProcesso;
            $obj->uasg = $srp->uasg;
            $obj->validade = TDate::date2br($srp->validade);
            TForm::sendData('form_requisicao', $obj);
            TSession::setValue('form_requisicao', $obj);
            if (isset($reloadForm) && ($reloadForm)) {
                TScript::create("__adianti_load_page2('engine.php?class=RequisicaoForm');");
                //new TScript("__adianti_load_page('engine?class=RequisicaoForm');");
            }
            //AdiantiCoreApplication::executeMethod('RequisicaoForm','onReload');
            TTransaction::close();
            parent::closeWindow();
        } catch (Exception $ex) {
            $obj = new stdClass();
            $obj->numeroSRP = '';
            $obj->nome = '';
            $obj->numeroProcesso = '';
            $obj->uasg = '';
            $obj->validade = '';
            TForm::sendData('requisicao_form', $obj);
            TTransaction::rollback();
        }
    }

    function show() {
        if (!$this->carregado) {
            $this->onReload();
        }
        parent::show();
    }

}
