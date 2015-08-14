<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class Grupo extends TRecord
{
    const TABLENAME = 'grupo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $funcionalidades = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('sigla');
    }

    /**
     * Method addSystem_program
     * Add a System_program to the System_group
     * @param $object Instance of System_program
     */
    public function addFuncionalidade(Funcionalidade $object)
    {
        $this->funcionalidades[] = $object;
    }
    
    /**
     * Method getSystem_programs
     * Return the System_group' System_program's
     * @return Collection of System_program
     */
    public function getFuncionalidades()
    {
        return $this->funcionalidades;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->funcionalidades = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
        // load the related System_program objects
        $repository = new TRepository('GrupoFuncionalidade');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', $id));
        $grupo_funcionalidades = $repository->load($criteria);
        if ($grupo_funcionalidades)
        {
            foreach ($grupo_funcionalidades as $grupo_funcionalidade)
            {
                $funcionalidade = new Funcionalidade( $grupo_funcionalidade->funcionalidade_id );
                $this->addFuncionalidade($funcionalidade);
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
    
        // delete the related System_groupSystem_program objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', $this->id));
        $repository = new TRepository('GrupoFuncionalidade');
        $repository->delete($criteria);
        // store the related System_groupSystem_program objects
        if ($this->funcionalidades)
        {
            foreach ($this->funcionalidades as $funcionalidade)
            {
                $grupo_funcionalidade = new GrupoFuncionalidade;
                $grupo_funcionalidade->funcionalidade_id = $funcionalidade->id;
                $grupo_funcionalidade->grupo_id = $this->id;
                $grupo_funcionalidade->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_groupSystem_program objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('GrupoFuncionalidade');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', $id));
        $repository->delete($criteria);
        
        // delete the object itself
        parent::delete($id);
    }
}
?>