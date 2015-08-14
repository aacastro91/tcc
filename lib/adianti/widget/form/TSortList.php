<?php
Namespace Adianti\Widget\Form;

use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TField;
use Adianti\Widget\Form\TImage;

/**
 * A Sortable list
 *
 * @version    2.0
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TSortList extends TField implements AdiantiWidgetInterface
{
    private $initialItems;
    private $items;
    private $valueSet;
    private $connectedTo;
    private $itemIcon;
    protected $id;
    
    /**
     * Class Constructor
     * @param  $name widget's name
     */
    public function __construct($name)
    {
        // executes the parent class constructor
        parent::__construct($name);
        $this->id   = 'tsortlist_'.uniqid();
        
        $this->initialItems = array();
        $this->items = array();
        
        // creates a <ul> tag
        $this->tag = new TElement('ul');
        $this->tag->{'class'} = 'tsortlist';
        $this->tag->{'itemname'} = $name;
    }
    
    /**
     * Define the item icon
     * @param $image Item icon
     */
    public function setItemIcon(TImage $icon)
    {
        $this->itemIcon = $icon;
    }
    
    /**
     * Define the list size
     */
    public function setSize($width, $height = NULL)
    {
        $this->tag->{'style'} = "width:{$width}px;height:{$height}px";
    }
    
    /**
     * Define the field's value
     * @param $value An array the field's values
     */
    public function setValue($value)
    {
        $items = $this->initialItems;
        if (is_array($value))
        {
            $this->items = array();
            foreach ($value as $index)
            {
                if (isset($items[$index]))
                {
                    $this->items[$index] = $items[$index];
                }
                else if (isset($this->connectedTo) AND is_array($this->connectedTo))
                {
                    foreach ($this->connectedTo as $connectedList)
                    {
                        if (isset($connectedList->initialItems[$index] ) )
                        {
                            $this->items[$index] = $connectedList->initialItems[$index];
                        }
                    }
                }
            }
        	$this->valueSet = TRUE;
        }
    }
    
    /**
     * Connect to another list
     * @param $list Another TSortList
     */
    public function connectTo(TSortList $list)
    {
        $this->connectedTo[] = $list;
    }
    
    /**
     * Add items to the sort list
     * @param $items An indexed array containing the options
     */
    public function addItems($items)
    {
        if (is_array($items))
        {
            $this->initialItems += $items;
            $this->items += $items;
        }
    }
    
    /**
     * Return the sort items
     */
    public function getItems()
    {
        return $this->initialItems;
    }
    
    /**
     * Return the post data
     */
    public function getPostData()
    {
        if (isset($_POST[$this->name]))
        {
            return $_POST[$this->name];
        }
        else
        {
            return array();
        }
    }
    
    /**
     * Enable the field
     */
    public static function enableField($form_name, $field)
    {
        TScript::create( " tsortlist_enable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Disable the field
     */
    public static function disableField($form_name, $field)
    {
        TScript::create( " tsortlist_disable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Clear the field
     */
    public static function clearField($form_name, $field)
    {
        TScript::create( " tsortlist_clear_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        $this->tag->{'id'} = $this->id;
        
        if ($this->items)
        {
            $i = 1;
            // iterate the checkgroup options
            foreach ($this->items as $index => $label)
            {
                // control to reduce available options when they are present
                // in another connected list as a post value
	            if ($this->connectedTo AND is_array($this->connectedTo))
	            {
	                foreach ($this->connectedTo as $connectedList)
	                {
                        if (isset($connectedList->items[$index]) AND $connectedList->valueSet )
                        {
                            continue 2;
                        }
	                }
	            }

                // instantiates a new Item
                $item = new TElement('li');
                
                if ($this->itemIcon)
                {
                    $item->add($this->itemIcon);
                }
                
                $item->add(new TLabel($label));
                $item->{'class'} = 'tsortlist_item btn btn-default';
                $item->{'style'} = 'display:block;';
                $item->{'id'} = "tsortlist_{$this->name}_item_{$i}_li";
                $item->{'title'} = $this->tag->title;
                
                $input = new TElement('input');
                $input->{'id'}   = "tsortlist_{$this->name}_item_{$i}_li_input";
                $input->{'type'} = 'hidden';
                $input->{'name'} = $this->name . '[]';
                $input->{'value'} = $index;
                $item->add($input);
                
                $this->tag->add($item);
                $i ++;
            }
        }
        
        if (parent::getEditable())
        {
            $connect = 'false';
            if ($this->connectedTo AND is_array($this->connectedTo))
            {
                foreach ($this->connectedTo as $connectedList)
                {
                    $connectIds[] =  '#'.$connectedList->getId();
                }
                $connect = implode(', ', $connectIds);
            }
            TScript::create(" tsortlist_start( '#{$this->id}', '{$connect}' ) ");
        }
        $this->tag->show();
    }
}
