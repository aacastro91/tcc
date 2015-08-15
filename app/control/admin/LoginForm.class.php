<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TStyle;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TPassword;


/**
 * Classe de formulario do login
 *
 * @author Anderson
 */
class LoginForm extends TPage {

  protected $form;

  function __construct() {

    parent::__construct();

    //Cria o formulario 'form_User'
    $this->form = new TForm('form_User');
    $this->form->class = 'navbar-form navbar-right';
    
    //Cria uma div de contenção
    $div = new TElement('div');
    $this->form->add($div);

    // cria uma nova div, atribuindo a classe do bootstrap
    // chamada form-group
    $g1 = new TElement('div');
    $g1->class = 'form-group';
    $div->add($g1);
    
    //cria o campo login
    $login = new TEntry('prontuario');
    $login->type = 'text';
    $login->placeholder = 'Prontuário';
    $login->class = 'form-control';
    $login->setProperty('style', 'margin-bottom:0px',false);
    $login->addValidation('Login', new TRequiredValidator);
    //adiciona do primeiro grupo
    $g1->add($login);

    //cria uma nova div
    $g2 = new TElement('div');
    $g2->class = 'form-group';
    $div->add($g2);
    
    $style = new TStyle('x');
    
    //cria o campo da senha
    $password = new TPassword('senha');   
    //$password->type = 'text';
    $password->placeholder = 'Senha';
    $password->class = 'form-control';
    $password->setProperty('style', 'margin-bottom:0px',false);
    $password->addValidation('Senha',new TRequiredValidator);
    //atribui ao grupo 2
    $g2->add($password);
    

    //cria o botao de login
    $login_button = new TButton('login');
    
    // define a acao do botao
    $login_button->setAction(new TAction(array($this, 'onLogin')),'Entrar');
    $login_button->class = 'btn btn-success btn-defualt';

    $div->add($login_button);
    $this->form->setFields(array($login, $password, $login_button));
    parent::add($this->form);
  }

  /**
   * Autenticação do usuario
   */
  function onLogin() {
    try {
      TTransaction::open('saciq');
      $data = $this->form->getData('StdClass');
      $this->form->validate();
      $user = Usuario::autenticar($data->prontuario, $data->senha);
      if ($user) {
        $funcionalidades = $user->getFuncionalidades();
        $funcionalidades['LoginForm'] = TRUE;
        
        TSession::setValue('logged', TRUE);
        TSession::setValue('id', $user->id);
        TSession::setValue('nome', $user->nome);
        TSession::setValue('prontuario', $user->prontuario);
        TSession::setValue('funcionalidades',$funcionalidades);
        
        TApplication::gotoPage('EmptyPage'); // reload
      }
      TTransaction::close();
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
      TSession::setValue('logged', FALSE);
      TTransaction::rollback();
    }
  }
  
  /**
     * Logout
     */
    function onLogout()
    {
        TSession::freeSession();
        TApplication::gotoPage('LoginForm', '');
    }
}
