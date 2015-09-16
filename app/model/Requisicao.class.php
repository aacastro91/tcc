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
    
    
    private $items;
    private $srp;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('numeroProcesso');
        parent::addAttribute('emissao');
        parent::addAttribute('aprovado');
        parent::addAttribute('srp_id');
    }

    
    /**
     * Method addItem
     * Add a Item to the Requisicao
     * @param $object Instance of Item
     */
    public function addItem(Item $object)
    {
        $this->items[] = $object;
    }
    
    /**
     * Method getItems
     * Return the Requisicao' Item's
     * @return Collection of Item
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Method set_srp
     * Sample of usage: $requisicao->srp = $object;
     * @param $object Instance of Srp
     */
    public function set_srp(Srp $object)
    {
        $this->srp = $object;
        $this->srp_id = $object->id;
    }
    
    /**
     * Method get_srp
     * Sample of usage: $requisicao->srp->attribute;
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
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->items = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Item objects
        $repository = new TRepository('ItemRequisicao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('requisicao_id', '=', $id));
        $requisicao_items = $repository->load($criteria);
        if ($requisicao_items)
        {
            foreach ($requisicao_items as $requisicao_item)
            {
                $item = new Item( $requisicao_item->item_id );
                $this->addItem($item);
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
        $criteria->add(new TFilter('requisicao_id', '=', $this->id));
        $repository = new TRepository('ItemRequisicao');
        $repository->delete($criteria);
        // store the related ItemRequisicao objects
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $requisicao_item = new ItemRequisicao;
                $requisicao_item->item_id = $item->id;
                $requisicao_item->requisicao_id = $this->id;
                $requisicao_item->store();
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
        $criteria->add(new TFilter('requisicao_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
