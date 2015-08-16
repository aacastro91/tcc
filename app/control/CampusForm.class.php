<?php

use Adianti\Base\TStandardForm;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TQuickForm;
/**
 * CampusForm Registration
 * @author  <your name here>
 */
class CampusForm extends TStandardForm
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
        $this->form = new TQuickForm('form_Campus');
        $this->form->setFormTitle('Cadastro de Campus');
        $this->form->class = 'tform'; // CSS class
        
        parent::setDatabase('saciq');
        
        parent::setActiveRecord('Campus');

        // create the form fields
        $id                             = new TEntry('id');
        $nome                           = new TEntry('nome');
        $uasg                           = new TEntry('uasg');
        $sigla                          = new TEntry('sigla');


        // define the sizes
        $id->setSize(50);
        $nome->setSize(500);
        $uasg->setSize(150);
        $sigla->setSize(100);
        
        $id->setEditable(FALSE);

        // validations
        $nome->addValidation('nome', new TRequiredValidator);
        $uasg->addValidation('UASG', new TRequiredValidator);


        // add one row for each form field
        $this->form->addQuickField( new TLabel('ID:'), $id, 50 );
        $this->form->addQuickField( $label_nome = new TLabel('Nome:'), $nome, 400 );
        $this->form->addQuickField( $label_uasg = new TLabel('UASG:'), $uasg, 150 );
        $this->form->addQuickField( new TLabel('Sigla:'), $sigla, 100 );

        
        $this->form->setFields(array($id,$nome,$uasg,$sigla));


        // Adiciona as ações do formulário
        $this->form->addQuickAction('Salvar', new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction('Novo', new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar para a listagem',new TAction(array('CampusList','onReload')),'ico_datagrid.png');

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
     
    function onSave()
    {
        try
        {
            TTransaction::open('saciq'); // open a transaction
            
            // get the form data into an active record Campus
            $object = $this->form->getData('Campus');
            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $this->form->setData($object); // keep form data
            TTransaction::close(); // close the transaction
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key=$param['key'];  // get the parameter $key
                TTransaction::open('saciq'); // open a transaction
                $object = new Campus($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
     * 
     */
}
