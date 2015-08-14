<?php
class PermissaoSistema
{
    public static function checkPermission($action)
    {
        
        $funcionalidades = TSession::getValue('funcionalidades');
        //var_dump($funcionalidades);
       // return true;
        return (isset($funcionalidades[$action]) AND $funcionalidades[$action]);
    } 
}
?>