<?php
/**
 * ItemCessao Active Record
 * @author  <your-name-here>
 */
class ItemCessao extends TRecord
{
    const TABLENAME = 'item_cessao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('item_id');
        parent::addAttribute('cessao_id');
        parent::addAttribute('quantidade');
    }


}
