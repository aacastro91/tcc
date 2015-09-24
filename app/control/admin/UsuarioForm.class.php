<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Log\TLoggerTXT;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TFrame;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TScroll;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TMultiField;
use Adianti\Widget\Form\TPassword;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBCheckGroup;
use Adianti\Widget\Wrapper\TDBSeekButton;
/**
 * UsuarioForm Registration
 * @author  Anderson
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
        // Cria o form
        $this->form = new TForm('form_Usuario');
        $this->form->class = 'tform';

        // creates the table container
        $table = new TTable;
        $table->style = 'width: 100%';
        
        $table->addRowSet( new TLabel('Usuário'), '', '','' )->class = 'tformtitle';
        
        // adiciona a tabela no form
        $this->form->add($table);
        
        $frame_grupos = new TFrame(NULL, 280);
        $frame_grupos->setLegend('Grupos');
        $frame_grupos->style .= ';margin: 4px';
        $frame_funcionalidades = new TFrame(NULL, 280);
        $frame_funcionalidades->setLegend('Funcionalidades');
        $frame_funcionalidades->style .= ';margin: 15px';


        // cria os campos de pesquisa do form
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
        $multifield_funcionalidades->setHeight(140);
        
        // outros
        $id->setEditable(false);
        $funcionalidade_nome->setEditable(false);
        $email->setProperty('autocomplete', 'off');
        
        // validations
        $nome->addValidation('Nome', new TRequiredValidator);
        $prontuario->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $funcionalidade_id->setSize(50);
        $funcionalidade_nome->setSize(200);
        
        // configuracoes multifield
        $multifield_funcionalidades->setClass('Funcionalidade');
        $multifield_funcionalidades->addField('id', 'ID',  $funcionalidade_id, 60, true);
        $multifield_funcionalidades->addField('nome','Nome', $funcionalidade_nome, 250);
        $multifield_funcionalidades->setOrientation('horizontal');
        
        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'),           $id,         new TLabel('Nome: '), $nome);
        $table->addRowSet(new TLabel('Prontuário: ' ), $prontuario, new TLabel('Email: '), $email);
        $table->addRowSet(new TLabel('Senha: '),       $password,   new TLabel('Confirmação da senha: '), $repassword);
        
        $row=$table->addRow();
        $cell = $row->addCell($frame_grupos);
        $cell->colspan = 2;
        
        $cell = $row->addCell($frame_funcionalidades);
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
        $list_button->setAction(new TAction(array('UsuarioList','onReload')), 'Voltar para a listagem');
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
                    throw new Exception('O campo Senha é obrigatório');
            }
            
            if( $object->senha )
            {
                if( $object->senha != $object->resenha )
                    throw new Exception('As senhas não conferem');
                
                $object->senha = md5($object->senha);
            }
            else
                unset($object->senha);
            
            
            if( $object->grupos )
            {
                foreach( $object->grupos as $group )
                {
                    $object->addUsuarioGrupo( new Grupo($group) );
                }
            }
            
            if( $object->funcionalidades )
            {
                foreach( $object->funcionalidades as $funcionalidade )
                {
                    $object->addUsuarioFuncionalidade( $funcionalidade );
                }
            }
            
            $object->store(); // stores the object
            
            $object->senha = '';
            $object->resenha = '';
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            $usuario = new Usuario(TSession::getValue('id'));
            $funcionalidades = $usuario->getFuncionalidades();
            $funcionalidades['LoginForm'] = TRUE;
            TSession::setValue('funcionalidades',$funcionalidades);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // Em caso de erro
        {
            if (strpos($e->getMessage(), 'Integrity constraint violation')) {
                $posi = strpos($e->getMessage(), 'Duplicate entry ') + strlen('Duplicate entry ') + 1;
                $posf = strpos($e->getMessage(), '\' for key');
                $str = substr($e->getMessage(), $posi, 3);

                $idUsuario = substr($str, 0, strpos($str, '-'));
                $usuario = new Usuario($idUsuario);
                $idFuncionalidade = substr($str, strpos($str, '-') + 1);
                $funcionalidade = new Funcionalidade($idFuncionalidade);


                new TMessage('error', '<b>Registro duplicado</b><br>A funcionalidade "' . $funcionalidade->nome . '"<br>já foi registrada para o usuário ' . $usuario->nome);
            } else {

                new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            }
            
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