<?php
/**
 * Subelemento Active Record
 * @author  <your-name-here>
 */
class Subelemento extends TRecord
{
    const TABLENAME = 'subelemento';
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
