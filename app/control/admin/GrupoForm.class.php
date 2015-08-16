<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TFrame;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TMultiField;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBSeekButton;
/**
 * GrupoForm Registration
 * @author  <your nome here>
 */

class GrupoForm extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the table container
        $table = new TTable;
        $table->style = 'width:100%';
        
        $frame_programs = new TFrame;
        $frame_programs->setLegend('Funcionalidades');
        
        // Cria o form
        $this->form = new TForm('form_Grupo');
        $this->form->class = 'tform';
        
        
        // add the notebook inside the form
        $this->form->add($table);
        $table->addRowSet( new TLabel('Grupo'), '' )->class = 'tformtitle';

        // cria os campos de pesquisa do form
        $id              = new TEntry('id');
        $nome            = new TEntry('nome');
        $sigla           = new TEntry('sigla');
        $multifield      = new TMultiField('programs');
        $program_id      = new TDBSeekButton('program_id', 'saciq', 'form_Grupo', 'Funcionalidade', 'nome', 'programs_id', 'programs_nome');
        $program_nome    = new TEntry('program_nome');
        
        $frame_programs->add($multifield);    
        
        $multifield->setHeight(140);
        $multifield->setClass('Funcionalidade');
        $multifield->addField('id', 'Funcionalidade' . ' ID',  $program_id, 100, true);
        $multifield->addField('nome','Nome', $program_nome, 250);
        $multifield->setOrientation('horizontal');
        
        // define the sizes
        $program_id->setSize(70);
        $id->setSize(100);
        $nome->setSize(200);
        $sigla->setSize(150);

        // validations
        $nome->addValidation('nome', new TRequiredValidator);
        
        // outras propriedades
        $id->setEditable(false);
        $program_nome->setEditable(false);

        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet(new TLabel('Nome: '), $nome);
        $table->addRowSet(new TLabel('Sigla:'), $sigla);
        
        // add a row for the field nome
        $row = $table->addRow();
        $cell = $row->addCell($frame_programs);
        $cell->colspan = 2;

        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), 'Salvar');
        $save_button->setImage('ico_save.png');
        
        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), 'Novo');
        $new_button->setImage('ico_new.png');
        
        $list_button=new TButton('list');
        $list_button->setAction(new TAction(array('GrupoList','onReload')), 'Voltar para a listagem');
        $list_button->setImage('ico_datagrid.png');

        // define the form fields
        $this->form->setFields(array($id,$nome,$sigla,$multifield,$save_button,$new_button,$list_button));
        
        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);
        
        $container = new TTable;
        $container->width = '80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'GrupoList'));
        $container->addRow()->addCell($this->form);
        
        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $buttons );
        $cell->colspan = 2;

        // add the form to the page
        parent::add($container);
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        try
        {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            
            // get the form data into an active record Grupo
            $object = $this->form->getData('Grupo');
            
            if( $object->programs )
            {
                foreach( $object->programs as $program )
                {
                    $object->addFuncionalidade( $program );
                }
            }
            
            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $this->form->setData($object); // fill the form with the active record data
            
            $usuario = new Usuario(TSession::getValue('id'));
            $funcionalidades = $usuario->getFuncionalidades();
            $funcionalidades['LoginForm'] = TRUE;
            TSession::setValue('funcionalidades',$funcionalidades);
            TTransaction::close(); // close the transaction
            new TMessage('info', 'Registro salvo'); // shows the success message
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
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'saciq'
                TTransaction::open('saciq');
                
                // instantiates object Grupo
                $object = new Grupo($key);
                
                $object->programs = $object->getFuncionalidades();
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // Em caso de erro
        {
            // mostrar mensagem de erro
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }
}
?>