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
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
                
        // creates the form
        
        $this->form = new TQuickForm('form_Funcionalidade');
        $this->form->setFormTitle(_t('Program'));
        $this->form->class = 'tform'; // CSS class
        
        // defines the database
        parent::setDatabase('saciq');
        
        // defines the active record
        parent::setActiveRecord('Funcionalidade');
        
        // create the form fields
        $id            = new TEntry('id');
        $nome          = new TEntry('nome');
        $classe        = new TEntry('classe');
        
        $id->setEditable(false);

        // add the fields
        $this->form->addQuickField('ID', $id,  50);
        $this->form->addQuickField(_t('Name') . ': ', $nome,  200);
        $this->form->addQuickField(_t('Controller') . ': ', $classe,  200);

        // validations
        $nome->addValidation(_t('Name'), new TRequiredValidator);
        $classe->addValidation(('Controller'), new TRequiredValidator);

        // add form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction(_t('New'), new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction(_t('Back to the listing'),new TAction(array('FuncionalidadeList','onReload')),'ico_datagrid.png');

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml','FuncionalidadeList'));
        $container->addRow()->addCell($this->form);
        
        
        // add the form to the page
        parent::add($container);
    }
}
?>