<?php
/**
 * Srp Active Record
 * @author  <your-name-here>
 */
class Srp extends TRecord
{
    const TABLENAME = 'srp';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $natureza;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('numeroSRP');
        parent::addAttribute('numeroIRP');
        parent::addAttribute('numeroProcesso');
        parent::addAttribute('uasg');
        parent::addAttribute('validade');
        parent::addAttribute('nome');
        parent::addAttribute('natureza_id');
    }

    
    /**
     * Method set_natureza
     * Sample of usage: $srp->natureza = $object;
     * @param $object Instance of Natureza
     */
    public function set_natureza(Natureza $object)
    {
        $this->natureza = $object;
        $this->natureza_id = $object->id;
    }
    
    /**
     * Method get_natureza
     * Sample of usage: $srp->natureza->attribute;
     * @returns Natureza instance
     */
    public function get_natureza()
    {
        // loads the associated object
        if (empty($this->natureza))
            $this->natureza = new Natureza($this->natureza_id);
    
        // returns the associated object
        return $this->natureza;
    }
    


}
