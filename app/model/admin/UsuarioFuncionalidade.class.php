<?php
/**
 * System_user_program Active Record
 * @author  <your-name-here>
 */
class UsuarioFuncionalidade extends TRecord
{
    const TABLENAME = 'usuario_funcionalidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('usuario_id');
        parent::addAttribute('funcionalidade_id');
    }
}
?>