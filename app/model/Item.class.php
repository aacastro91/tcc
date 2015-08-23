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
    private $subelemento;
    private $fornecedor;
    private $requisicaos;
    private $cessaos;

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
     * Method addRequisicao
     * Add a Requisicao to the Item
     * @param $object Instance of Requisicao
     */
    public function addRequisicao(Requisicao $object)
    {
        $this->requisicaos[] = $object;
    }
    
    /**
     * Method getRequisicaos
     * Return the Item' Requisicao's
     * @return Collection of Requisicao
     */
    public function getRequisicaos()
    {
        return $this->requisicaos;
    }
    
    /**
     * Method addCessao
     * Add a Cessao to the Item
     * @param $object Instance of Cessao
     */
    public function addCessao(Cessao $object)
    {
        $this->cessaos[] = $object;
    }
    
    /**
     * Method getCessaos
     * Return the Item' Cessao's
     * @return Collection of Cessao
     */
    public function getCessaos()
    {
        return $this->cessaos;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->requisicaos = array();
        $this->cessaos = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Requisicao objects
        $repository = new TRepository('ItemRequisicao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $id));
        $item_requisicaos = $repository->load($criteria);
        if ($item_requisicaos)
        {
            foreach ($item_requisicaos as $item_requisicao)
            {
                $requisicao = new Requisicao( $item_requisicao->requisicao_id );
                $this->addRequisicao($requisicao);
            }
        }
    
        // load the related Cessao objects
        $repository = new TRepository('ItemCessao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $id));
        $item_cessaos = $repository->load($criteria);
        if ($item_cessaos)
        {
            foreach ($item_cessaos as $item_cessao)
            {
                $cessao = new Cessao( $item_cessao->cessao_id );
                $this->addCessao($cessao);
            }
        }
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related ItemRequisicao objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $this->id));
        $repository = new TRepository('ItemRequisicao');
        $repository->delete($criteria);
        // store the related ItemRequisicao objects
        if ($this->requisicaos)
        {
            foreach ($this->requisicaos as $requisicao)
            {
                $item_requisicao = new ItemRequisicao;
                $item_requisicao->requisicao_id = $requisicao->id;
                $item_requisicao->item_id = $this->id;
                $item_requisicao->store();
            }
        }
        // delete the related ItemCessao objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $this->id));
        $repository = new TRepository('ItemCessao');
        $repository->delete($criteria);
        // store the related ItemCessao objects
        if ($this->cessaos)
        {
            foreach ($this->cessaos as $cessao)
            {
                $item_cessao = new ItemCessao;
                $item_cessao->cessao_id = $cessao->id;
                $item_cessao->item_id = $this->id;
                $item_cessao->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related ItemRequisicao objects
        $repository = new TRepository('ItemRequisicao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $id));
        $repository->delete($criteria);
        
        // delete the related ItemCessao objects
        $repository = new TRepository('ItemCessao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('item_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
