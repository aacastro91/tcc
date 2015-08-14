<?php
/**
 * UsuarioForm Registration
 * @author  <your nome here>
 */
class UsuarioForm extends TPage
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
        $this->form = new TForm('form_Usuario');
        $this->form->class = 'tform';

        // creates the table container
        $table = new TTable;
        $table->style = 'width: 100%';
        
        $table->addRowSet( new TLabel(_t('User')), '', '','' )->class = 'tformtitle';
        
        // add the table inside the form
        $this->form->add($table);
        
        $frame_grupos = new TFrame(NULL, 280);
        $frame_grupos->setLegend(_t('Groups'));
        $frame_grupos->style .= ';margin: 4px';
        $frame_funcionalidades = new TFrame(NULL, 280);
        $frame_funcionalidades->setLegend(_t('Programs'));
        $frame_funcionalidades->style .= ';margin: 15px';


        // create the form fields
        $id                  = new TEntry('id');
        $nome                = new TEntry('nome');
        $prontuario          = new TEntry('prontuario');
        $password            = new TPassword('senha');
        $repassword          = new TPassword('resenha');
        $email               = new TEntry('email');
        $multifield_funcionalidades = new TMultiField('funcionalidades');
        $funcionalidade_id   = new TDBSeekButton('funcionalidade_id', 'saciq', 'form_Usuario', 'Funcionalidade', 'nome', 'funcionalidades_id', 'funcionalidades_nome');
        $funcionalidade_nome = new TEntry('funcionalidade_nome');
        $grupos              = new TDBCheckGroup('grupos','saciq','Grupo','id','nome');
        //$frontpage_id        = new TDBSeekButton('frontpage_id', 'saciq', 'form_Usuario', 'Funcionalidade', 'nome', 'frontpage_id', 'frontpage_nome');
        //$frontpage_nome      = new TEntry('frontpage_nome');
        
        $scroll = new TScroll;
        $scroll->setSize(290, 230);
        $scroll->add( $grupos );
        $frame_grupos->add( $scroll );
        $frame_funcionalidades->add( $multifield_funcionalidades );

        // define the sizes
        $id->setSize(100);
        $nome->setSize(200);
        $prontuario->setSize(150);
        $password->setSize(150);
        $email->setSize(200);
        //$frontpage_id->setSize(100);
        $multifield_funcionalidades->setHeight(140);
        
        // outros
        $id->setEditable(false);
        $funcionalidade_nome->setEditable(false);
        //$frontpage_name->setEditable(false);
        
        // validations
        $nome->addValidation(_t('Name'), new TRequiredValidator);
        $prontuario->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $funcionalidade_id->setSize(50);
        $funcionalidade_nome->setSize(200);
        
        // configuracoes multifield
        $multifield_funcionalidades->setClass('Funcionalidade');
        $multifield_funcionalidades->addField('id', 'ID',  $funcionalidade_id, 60);
        $multifield_funcionalidades->addField('nome',_t('Name'), $funcionalidade_nome, 250);
        $multifield_funcionalidades->setOrientation('horizontal');
        
        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'),                 $id,           new TLabel(_t('Name').': '), $nome);
        $table->addRowSet(new TLabel(_t('Login').': ' ),     $prontuario,        new TLabel(_t('Email').': '), $email);
        $table->addRowSet(new TLabel(_t('Password').': '),   $password,     new TLabel(_t('Password confirmation').': '), $repassword);
        //$table->addRowSet(new TLabel(_t('Front page').': '), $frontpage_id, new TLabel(_t('Page nome') . ': '), $frontpage_name);
        
        $row=$table->addRow();
        $cell = $row->addCell($frame_grupos);
        $cell->colspan = 2;
        
        $cell = $row->addCell($frame_funcionalidades);
        $cell->colspan = 2;

        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('ico_save.png');
        
        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), _t('New'));
        $new_button->setImage('ico_new.png');
        
        $list_button=new TButton('list');
        $list_button->setAction(new TAction(array('UsuarioList','onReload')), _t('Back to the listing'));
        $list_button->setImage('ico_datagrid.png');
        
        // define the form fields
        $this->form->setFields(array($id,$nome,$prontuario,$password,$repassword,$multifield_funcionalidades,/*$frontpage_id, $frontpage_name,*/ $grupos,$email,$save_button,$new_button,$list_button));
        
        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $buttons );
        $cell->colspan = 4;

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'UsuarioList'));
        $container->addRow()->addCell($this->form);

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
            
            // get the form data into an active record Usuario
            $object = $this->form->getData('Usuario');
            
            // form validation
            $this->form->validate();
            
            $senha = $object->senha;
            
            if( ! $object->id )
            {
                if( ! $object->senha )
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            if( $object->senha )
            {
                if( $object->senha != $object->resenha )
                    throw new Exception(_t('The passwords do not match'));
                
                $object->senha = md5($object->senha);
            }
            else
                unset($object->senha);
            
            
            if( $object->groups )
            {
                foreach( $object->groups as $group )
                {
                    $object->addSystemUserGroup( new SystemGroup($group) );
                }
            }
            
            if( $object->programs )
            {
                foreach( $object->programs as $program )
                {
                    $object->addSystemUserProgram( $program );
                }
            }
            
            $object->store(); // stores the object
            
            $object->password = $senha;
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
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
                
                // instantiates object Usuario
                $object = new Usuario($key);
                
                unset($object->senha);
                
                $grupos = array();
                
                if( $grupos_db = $object->getUsuarioGrupos() )
                {
                    foreach( $grupos_db as $grupo )
                    {
                        $grupos[] = $grupo->id;
                    }
                }
                
                $object->funcionalidades = $object->getUsuarioFuncionalidades();
                
                $object->grupos = $grupos;
                
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
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
?>