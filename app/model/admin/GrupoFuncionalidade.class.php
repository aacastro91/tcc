<?php
/**
 * System_group_program Active Record
 * @author  Anderson
 */
class GrupoFuncionalidade extends TRecord
{
    const TABLENAME = 'grupo_funcionalidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('grupo_id');
        parent::addAttribute('funcionalidade_id');
    }
}
?>