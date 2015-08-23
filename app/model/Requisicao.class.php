<?php
/**
 * Requisicao Active Record
 * @author  <your-name-here>
 */
class Requisicao extends TRecord
{
    const TABLENAME = 'requisicao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('numeroProcesso');
        parent::addAttribute('data');
        parent::addAttribute('aprovado');
    }


}
