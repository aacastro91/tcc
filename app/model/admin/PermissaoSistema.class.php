<?php
class PermissaoSistema
{
    public static function checkPermission($action)
    {
        //pega as funcionalidades que o usuario logado pode acessar
        $funcionalidades = TSession::getValue('funcionalidades');
        
        //retorna true ou falso, indicando se o usuario pode ou nao acessar a tela
        return (isset($funcionalidades[$action]) AND $funcionalidades[$action]);
    } 
}
?>