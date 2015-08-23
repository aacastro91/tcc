<?php
/**
 * Fornecedor Active Record
 * @author  <your-name-here>
 */
class Fornecedor extends TRecord
{
    const TABLENAME = 'fornecedor';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('cnpj');
    }


}
