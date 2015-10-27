<?php
require_once 'init.php';

class TApplication extends AdiantiCoreApplication
{
    static public function run($debug = FALSE)
    {
        new TSession;
        if ($_REQUEST)
        {
            $class = isset($_REQUEST['class']) ? $_REQUEST['class']   : '';
            
            if (TSession::getValue('logged')) // logged
            {
                $funcionalidades = (array)TSession::getValue('funcionalidades');
                // $programs = (array) TSession::getValue('programs'); // programs with permission
                $funcionalidades = array_merge($funcionalidades, 
                        array('Adianti\Base\TStandardSeek' => TRUE, 
                            'LoginForm' => TRUE, 
                            'AdiantiMultiSearchService' => TRUE, 
                            'AdiantiUploaderService' => TRUE, 
                            'EmptyPage' => TRUE,
                            'ItemSeekRequisicao'=> TRUE,
                            'ItemSeekCessao'=> TRUE,
                            'SrpSeekRequisicao'=> TRUE,
                            'SrpSeekCessao'=> TRUE
                            )); // default programs
                
                if( isset($funcionalidades[$class]) )
                //if (true)
                {
                    parent::run($debug);
                }
                else
                {
                    new TMessage('error', _t('Permission denied') );
                }
            }
            else if ($class == 'LoginForm')
            {
                parent::run($debug);
            }
            else
            {
                new TMessage('error', _t('Permission denied'), new TAction(array('LoginForm','onLogout')) );
            }
        }
    }
}

TApplication::run(TRUE);
