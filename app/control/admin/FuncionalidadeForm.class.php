<?php
/**
 * FuncionalidadeForm Registration
 * @author  Anderson
 */
class FuncionalidadeForm extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Cria da página e o formulário de registro
     */
    function __construct()
    {
        parent::__construct();
                
        // cria o formulário
        
        $this->form = new TQuickForm('form_Funcionalidade');
        $this->form->setFormTitle('Cadastro de Funcionalidades');
        $this->form->class = 'tform'; // CSS class
        
        // Cria os campos do formulário
        $id            = new TEntry('id');
        $nome          = new TEntry('nome');
        $classe        = new TEntry('classe');
        
        $id->setEditable(false);

        // Adiciona os campos ao formulário
        $this->form->addQuickField('Código:', $id,  50);
        $this->form->addQuickField('Nome: ', $nome,  500);
        $this->form->addQuickField('Classe de controle: ', $classe,  500);

        // Validadores
        $nome->addValidation('Nome', new TRequiredValidator);
        $classe->addValidation('Classe de controle', new TRequiredValidator);

        // Adiciona as ações do formulário
        $this->form->addQuickAction('Salvar', new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction('Novo', new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar para a listagem',new TAction(array('FuncionalidadeList','onReload')),'ico_datagrid.png');

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml','FuncionalidadeList'));
        $container->addRow()->addCell($this->form);
        
        
        // Adiciona o formulário a pagina
        parent::add($container);
    }
    
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave() {
        try {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');

            // get the form data into an active record Grupo
            $object = $this->form->getData('Funcionalidade');
            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $this->form->setData($object); // fill the form with the active record data
            
            TTransaction::close(); // close the transaction
            new TMessage('info', 'Registro salvo'); // shows the success message
        } catch (Exception $e) { // Em caso de erro
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>A Classe de controle "' . $object->classe . '" já foi registrada');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
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

                // open a transaction with database 'saciq'
                TTransaction::open('saciq');

                // instantiates object Grupo
                $object = new Funcionalidade($key);

                // fill the form with the active record data
                $this->form->setData($object);

                // close the transaction
                TTransaction::close();
            } else {
                $this->form->clear();
            }
        } catch (Exception $e) { // Em caso de erro
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

}
?>