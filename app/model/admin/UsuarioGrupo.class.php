<?php
/**
 * System_user_group Active Record
 * @author  <your-name-here>
 */
class UsuarioGrupo extends TRecord
{
    const TABLENAME = 'usuario_grupo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('usuario_id');
        parent::addAttribute('grupo_id');
    }
}
?>