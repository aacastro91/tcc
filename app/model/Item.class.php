<?php

/*
 * Copyright (C) 2015 Anderson
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Item Active Record
 * @author  Anderson
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
        parent::addAttribute('estoqueDisponivel');
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
