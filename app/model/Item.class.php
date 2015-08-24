<?php
/**
 * Item Active Record
 * @author  <your-name-here>
 */
class Item extends TRecord
{
    const TABLENAME = 'item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $srp;
    private $fornecedor;
    private $subelemento;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('numeroItem');
        parent::addAttribute('descricaoSumaria');
        parent::addAttribute('descricaoCompleta');
        parent::addAttribute('descricaoPosLicitacao');
        parent::addAttribute('unidadeMedida');
        parent::addAttribute('marca');
        parent::addAttribute('valorUnitario');
        parent::addAttribute('quantidadeDisponivel');
        parent::addAttribute('fabricante');
        parent::addAttribute('fornecedor_id');
        parent::addAttribute('subelemento_id');
        parent::addAttribute('srp_id');
    }

    
    /**
     * Method set_srp
     * Sample of usage: $item->srp = $object;
     * @param $object Instance of Srp
     */
    public function set_srp(Srp $object)
    {
        $this->srp = $object;
        $this->srp_id = $object->id;
    }
    
    /**
     * Method get_srp
     * Sample of usage: $item->srp->attribute;
     * @returns Srp instance
     */
    public function get_srp()
    {
        // loads the associated object
        if (empty($this->srp))
            $this->srp = new Srp($this->srp_id);
    
        // returns the associated object
        return $this->srp;
    }
    
    
    /**
     * Method set_fornecedor
     * Sample of usage: $item->fornecedor = $object;
     * @param $object Instance of Fornecedor
     */
    public function set_fornecedor(Fornecedor $object)
    {
        $this->fornecedor = $object;
        $this->fornecedor_id = $object->id;
    }
    
    /**
     * Method get_fornecedor
     * Sample of usage: $item->fornecedor->attribute;
     * @returns Fornecedor instance
     */
    public function get_fornecedor()
    {
        // loads the associated object
        if (empty($this->fornecedor))
            $this->fornecedor = new Fornecedor($this->fornecedor_id);
    
        // returns the associated object
        return $this->fornecedor;
    }
    
    
    /**
     * Method set_subelemento
     * Sample of usage: $item->subelemento = $object;
     * @param $object Instance of Subelemento
     */
    public function set_subelemento(Subelemento $object)
    {
        $this->subelemento = $object;
        $this->subelemento_id = $object->id;
    }
    
    /**
     * Method get_subelemento
     * Sample of usage: $item->subelemento->attribute;
     * @returns Subelemento instance
     */
    public function get_subelemento()
    {
        // loads the associated object
        if (empty($this->subelemento))
            $this->subelemento = new Subelemento($this->subelemento_id);
    
        // returns the associated object
        return $this->subelemento;
    }
    


}
