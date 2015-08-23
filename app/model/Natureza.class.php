<?php
/**
 * Natureza Active Record
 * @author  <your-name-here>
 */
class Natureza extends TRecord
{
    const TABLENAME = 'natureza';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
    }


}
