<?php
/**
 * System_user Active Record
 * @author  <your-name-here>
 */
class Usuario extends TRecord
{
    const TABLENAME = 'usuario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    private $usuario_grupos = array();
    private $usuario_funcionalidades = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('prontuario');
        parent::addAttribute('senha');
        parent::addAttribute('email');
    }
    
    /**
     * Method addSystem_user_group
     * Add a System_user_group to the System_user
     * @param $object Instance of System_group
     */
    public function addUsuarioGrupo(UsuarioGrupo $object)
    {
        $this->usuario_grupos[] = $object;
    }
    
    /**
     * Method getSystem_user_groups
     * Return the System_user' System_user_group's
     * @return Collection of System_user_group
     */
    public function getUsuarioGrupos()
    {
        return $this->usuario_grupos;
    }
    
    /**
     * Method addSystem_user_program
     * Add a System_user_program to the System_user
     * @param $object Instance of System_program
     */
    public function addUsuarioFuncionalidade(Funcionalidade $object)
    {
        $this->usuario_funcionalidades[] = $object;
    }
    
    /**
     * Method getSystem_user_programs
     * Return the System_user' System_user_program's
     * @return Collection of System_user_program
     */
    public function getUsuarioFuncionalidades()
    {
        return $this->usuario_funcionalidades;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->usuario_grupos = array();
        $this->usuario_funcionalidades = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
        // load the related System_user_group objects
        $repository = new TRepository('UsuarioGrupo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $id));
        $usuario_usuario_grupos = $repository->load($criteria);
        if ($usuario_usuario_grupos)
        {
            foreach ($usuario_usuario_grupos as $usuario_usuario_grupo)
            {
                $usuario_grupo = new Grupo( $usuario_usuario_grupo->grupo_id );
                $this->addUsuarioGrupo($usuario_grupo);
            }
        }
    
        // load the related System_user_program objects
        $repository = new TRepository('UsuarioFuncionalidade');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $id));
        $usuario_usuario_funcionalidades = $repository->load($criteria);
        if ($usuario_usuario_funcionalidades)
        {
            foreach ($usuario_usuario_funcionalidades as $usuario_usuario_funcionalidade)
            {
                $usuario_funcionalidade = new Funcionalidade( $usuario_usuario_funcionalidade->funcionalidade_id );
                $this->addUsuarioFuncionalidade($usuario_funcionalidade);
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
    
        // delete the related System_userSystem_user_group objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $this->id));
        $repository = new TRepository('UsuarioGrupo');
        $repository->delete($criteria);
        // store the related System_userSystem_user_group objects
        if ($this->usuario_grupos)
        {
            foreach ($this->usuario_grupos as $usuario_grupo)
            {
                $usuario_usuario_grupo = new UsuarioGrupo;
                $usuario_usuario_grupo->grupo_id = $usuario_grupo->id;
                $usuario_usuario_grupo->usuario_id = $this->id;
                $usuario_usuario_grupo->store();
            }
        }
        // delete the related System_userSystem_user_program objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $this->id));
        $repository = new TRepository('UsuarioFuncionalidade');
        $repository->delete($criteria);
        // store the related System_userSystem_user_program objects
        if ($this->usuario_funcionalidades)
        {
            foreach ($this->usuario_funcionalidades as $usuario_funcionalidade)
            {
                $system_user_usuario_funcionalidade = new UsuarioFuncionalidade;
                $system_user_usuario_funcionalidade->funcionalidade_id = $usuario_funcionalidade->id;
                $system_user_usuario_funcionalidade->usuario_id = $this->id;
                $system_user_usuario_funcionalidade->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('UsuarioGrupo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $id));
        $repository->delete($criteria);
        
        // delete the related System_userSystem_user_program objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('UsuarioFuncionalidade');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }
    
    /**
     * Autenticate the user
     * @param $login String with user login
     * @param $password String with user password
     * @returns TRUE if the password matches, otherwise throw Exception
     */
    public static function autenticar($login, $senha)
    {
        $user = self::newFromLogin($login);
        
        if ($user instanceof Usuario)
        {
            if (isset( $user->senha ) AND ($user->senha == md5($senha)) )
            {
                return $user;
            }
            else
            {
                throw new Exception(_t('Wrong password'));
            }
        }
        else
        {
            throw new Exception(_t('User not found'));
        }
    }
    
    /**
     * Returns a SystemUser object based on its login
     * @param $login String with user login
     */
    static public function newFromLogin($login)
    {
        $repos = new TRepository('Usuario');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('prontuario', '=', $login));
        $objects = $repos->load($criteria);
        if (isset($objects[0]))
        {
            return $objects[0];
        }
    }
    
    /**
     * Return the programs the user has permission to run
     */
    public function getFuncionalidades()
    {
        $funcionalidades = array();
        
        foreach( $this->getUsuarioGrupos() as $groupo )
        {
            foreach( $groupo->getSystemPrograms() as $func )
            {
                $funcionalidades[$func->classe] = true;
            }
        }
                
        foreach( $this->getUsuarioFuncionalidades() as $func )
        {
            $funcionalidades[$func->classe] = true;
        }
        
        return $funcionalidades;
    }
    
    /**
     * Check if the user is within a group
     */
    public function checkInGroup(Grupo $group )
    {
        $usuario_grupos = array();
        foreach( $this->getUsuarioGrupos() as $group )
        {
            $usuario_grupos[] = $group->id;
        }
    
        return in_array($group->id, $usuario_grupos);
    }
}
?>