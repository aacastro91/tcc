<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TPassword;

/**
 * LoginForm Registration
 * @author  Anderson
 */
class LoginForm extends TPage {

  protected $form; // form

  /**
   * Class constructor
   * Creates the page and the registration form
   */

  function __construct() {
    parent::__construct();

    $table = new TTable;
    $table->width = '100%';
    // creates the form
    $this->form = new TForm('form_User');
    $this->form->class = 'tform';
    $this->form->style = 'width: 450px;margin:auto; margin-top:120px;';

    // add the notebook inside the form
    $this->form->add($table);

    // create the form fields
    $login = new TEntry('prontuario');
    $password = new TPassword('senha');

    // define the sizes
    $login->setSize(320, 40);
    $password->setSize(320, 40);

    $login->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
    $password->style = 'height:35px;margin-bottom: 15px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';

    $row = $table->addRow();
    $row->addCell(new TLabel('Login'))->colspan = 2;
    $row->class = 'tformtitle';

    $login->placeholder = 'Prontuário';
    $password->placeholder = 'Senha';

    $user = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>';
    $locker = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>';

    $container1 = new TElement('div');
    $container1->add($user);
    $container1->add($login);

    $container2 = new TElement('div');
    $container2->add($locker);
    $container2->add($password);

    $row = $table->addRow();
    $row->addCell($container1)->colspan = 2;

    // add a row for the field password
    $row = $table->addRow();
    $row->addCell($container2)->colspan = 2;

    // create an action button (save)
    $save_button = new TButton('save');
    // define the button action
    $save_button->setAction(new TAction(array($this, 'onLogin')), _t('Log in'));
    $save_button->class = 'btn btn-success btn-defualt';
    $save_button->style = 'margin-left:32px;width:355px;height:40px;border-radius:6px;font-size:18px';

    $row = $table->addRow();
    $row->class = 'tformaction';
    $cell = $row->addCell($save_button);
    $cell->colspan = 2;

    $this->form->setFields(array($login, $password, $save_button));

    // add the form to the page
    parent::add($this->form);
  }

  /**
   * Autenticates the User
   */
  function onLogin() {
    try {
      TTransaction::open('login');
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
        TSession::setValue('funcionalidades', $funcionalidades);

        TApplication::gotoPage('Home'); // reload
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
  function onLogout() {
    TSession::freeSession();
    TApplication::gotoPage('LoginForm', '');
  }

}

?>