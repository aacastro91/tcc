<?php
/**
 * FuncionalidadeForm Registration
 * @author  <your nome here>
 */
class FuncionalidadeForm extends TStandardForm
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
        
        // define o banco de dados
        parent::setDatabase('saciq');
        
        // define a classe modelo (activeRecord)
        parent::setActiveRecord('Funcionalidade');
        
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
}
?>