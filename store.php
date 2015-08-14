<?php

use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

include_once 'init.php';


try {
    TTransaction::open('sample'); // open transaction
/*
    $aluno = new Aluno();
    $usuario = new Usuario();
    $aluno->setUsuario($usuario);

    $usuario->nome = 'Andrade Castro';
    $usuario->idade = '10';


    //$aluno->Usuario_prontuario = '6';
    $aluno->cidade = 'Capivari s';

    $aluno->store();
*/

    $professor = new Aluno();
    $professor->setUsuario(new Usuario());

    $professor->getUsuario()->nome = 'Prof AndersonsSSSS';
    $professor->getUsuario()->idade = 19;
    $professor->cidade = 'capivari';
    $professor->store();



    new TMessage('info', 'Objeto stored successfully');
    TTransaction::close(); // Closes the transaction
} catch (Exception $e) {
    new TMessage('error', $e->getMessage());
}
