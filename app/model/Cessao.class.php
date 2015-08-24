<?php
/**
 * Cessao Active Record
 * @author  <your-name-here>
 */
class Cessao extends TRecord
{
    const TABLENAME = 'cessao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $campus;
    private $items;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('numeroCessao');
        parent::addAttribute('data');
        parent::addAttribute('aprovado');
        parent::addAttribute('campus_id');
    }

    
    /**
     * Method set_campus
     * Sample of usage: $cessao->campus = $object;
     * @param $object Instance of Campus
     */
    public function set_campus(Campus $object)
    {
        $this->campus = $object;
        $this->campus_id = $object->id;
    }
    
    /**
     * Method get_campus
     * Sample of usage: $cessao->campus->attribute;
     * @returns Campus instance
     */
    public function get_campus()
    {
        // loads the associated object
        if (empty($this->campus))
            $this->campus = new Campus($this->campus_id);
    
        // returns the associated object
        return $this->campus;
    }
    
    
    /**
     * Method addItem
     * Add a Item to the Cessao
     * @param $object Instance of Item
     */
    public function addItem(Item $object)
    {
        $this->items[] = $object;
    }
    
    /**
     * Method getItems
     * Return the Cessao' Item's
     * @return Collection of Item
     */
    public function getItems()
    {
        return $this->items;
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
        $repository = new TRepository('ItemCessao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cessao_id', '=', $id));
        $cessao_items = $repository->load($criteria);
        if ($cessao_items)
        {
            foreach ($cessao_items as $cessao_item)
            {
                $item = new Item( $cessao_item->item_id );
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
    
        // delete the related ItemCessao objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cessao_id', '=', $this->id));
        $repository = new TRepository('ItemCessao');
        $repository->delete($criteria);
        // store the related ItemCessao objects
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $cessao_item = new ItemCessao;
                $cessao_item->item_id = $item->id;
                $cessao_item->cessao_id = $this->id;
                $cessao_item->store();
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
        // delete the related ItemCessao objects
        $repository = new TRepository('ItemCessao');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cessao_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
