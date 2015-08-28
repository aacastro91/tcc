<?php

/**
 * Srp Active Record
 * @author  <your-name-here>
 */
class Srp extends TRecord {

    const TABLENAME = 'srp';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max'; // {max, serial}

    private $natureza;
    private $items;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE) {
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
    public function set_natureza(Natureza $object) {
        $this->natureza = $object;
        $this->natureza_id = $object->id;
    }

    /**
     * Method get_natureza
     * Sample of usage: $srp->natureza->attribute;
     * @returns Natureza instance
     */
    public function get_natureza() {
        // loads the associated object
        if (empty($this->natureza))
            $this->natureza = new Natureza($this->natureza_id);

        // returns the associated object
        return $this->natureza;
    }

    /**
     * Method addItem
     * Add a Item to the Srp
     * @param $object Instance of Item
     */
    public function addItem(Item $object) {
        $this->items[] = $object;
    }

    /**
     * Method getItems
     * Return the Srp' Item's
     * @return Collection of Item
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Reset aggregates
     */
    public function clearParts() {
        $this->items = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id) {

        // load the related Item objects
        $repository = new TRepository('Item');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('srp_id', '=', $id));
        $this->items = $repository->load($criteria);

        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store() {
        // store the object itself
        parent::store();

        // delete the related Item objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('srp_id', '=', $this->id));
        $repository = new TRepository('Item');
        $repository->delete($criteria);
        // store the related Item objects
        if ($this->items) {
            foreach ($this->items as $item) {
                unset($item->id);
                $item->srp_id = $this->id;
                $item->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL) {
        $id = isset($id) ? $id : $this->id;
        // delete the related Item objects
        $repository = new TRepository('Item');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('srp_id', '=', $id));
        $repository->delete($criteria);


        // delete the object itself
        parent::delete($id);
    }

}
