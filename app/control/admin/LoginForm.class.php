<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FrmLogin
 *
 * @author Anderson
 */
class LoginForm extends TPage {

  protected $form;

  function __construct() {

    parent::__construct();

    $this->form = new TForm('form_User');
    $this->form->class = 'navbar-form navbar-right';
    
    $div = new TElement('div');
    $this->form->add($div);

    $g1 = new TElement('div');
    $g1->class = 'form-group';
    $div->add($g1);
    
    $login = new TEntry('usuario');
    $login->type = 'text';
    $login->placeholder = 'Usuário';
    $login->class = 'form-control';
    $login->addValidation('Login', new TRequiredValidator);
    $g1->add($login);

    $g2 = new TElement('div');
    $g2->class = 'form-group';
    $div->add($g2);
    
    $password = new TPassword('senha');   
    $password->type = 'text';
    $password->placeholder = 'Senha';
    $password->class = 'form-control';
    $password->addValidation('Senha',new TRequiredValidator);
    $g2->add($password);
    

    $save_button = new TButton('save');
// define the button action
    $save_button->setAction(new TAction(array($this, 'onLogin')),'Entrar');
    $save_button->class = 'btn btn-success btn-defualt';

    //$this->form->add($g1);
    //$this->form->add($g2);
    $div->add($save_button);
    $this->form->setFields(array($login, $password, $save_button));
    parent::add($this->form);
  }

  /**
   * Autenticates the User
   */
  function onLogin() {
    try {
      TTransaction::open('saciq');
      $data = $this->form->getData('StdClass');
      $this->form->validate();
      $user = Usuario::autenticar($data->usuario, $data->senha);
      if ($user) {
        $funcionalidades = $user->getFuncionalidades();
        $funcionalidades['LoginForm'] = TRUE;
        
        TSession::setValue('logged', TRUE);
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

  /*
    $this->form->setFields(array($login, $password, $save_button));

    $c_nav = new \Adianti\Widget\Base\TElement('div');
    $c_nav->class = 'navbar navbar-fixed-top navbar-inverse';
    $c_nav->role = 'navigation';

    $c_img = new \Adianti\Widget\Base\TElement('div');
    $c_img->class = 'cover-image';
    $c_img->style = 'background-image : url(\'app/templates/{template}/images/img_fundo_index.jpg\')';

    $c_container = new \Adianti\Widget\Base\TElement('div');
    $c_container->class = 'container';

    $this->form = new TForm('form_User');

    $this->form->add($c_nav);
    $this->form->add($c_img);
    $this->form->add($c_container);

    parent::add($this->form); */
}
