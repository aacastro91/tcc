<?php
/**
 * System_program Active Record
 * @author  <your-name-here>
 */
class Funcionalidade extends TRecord
{
    const TABLENAME = 'funcionalidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('classe');
    }
}
?>