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
    


}
