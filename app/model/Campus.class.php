<?php
/**
 * Campus Active Record
 * @author  <your-name-here>
 */
class Campus extends TRecord
{
    const TABLENAME = 'campus';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('uasg');
        parent::addAttribute('nome');
        parent::addAttribute('sigla');
    }


}
