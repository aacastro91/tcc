<?php
/**
 * ItemRequisicao Active Record
 * @author  <your-name-here>
 */
class ItemRequisicao extends TRecord
{
    const TABLENAME = 'item_requisicao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('item_id');
        parent::addAttribute('requisicao_id');
        parent::addAttribute('justificativa');
        parent::addAttribute('quantidade');
        parent::addAttribute('prazoEntrega');
    }


}
