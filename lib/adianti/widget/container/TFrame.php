<?php
Namespace Adianti\Widget\Container;

use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Form\TLabel;

/**
 * Frame Widget: creates a kind of bordered area with a title located at its top-left corner
 *
 * @version    2.0
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TFrame extends TElement
{
    private $legend;
    private $width;
    private $height;
    
    /**
     * Class Constructor
     * @param  $value text label
     */
    public function __construct($width = NULL, $height = NULL)
    {
        parent::__construct('fieldset');
        $this->{'id'}    = 'tfieldset_' . uniqid();
        $this->{'class'} = 'tframe';
        
        $this->width = $width;
        $this->height = $height;
        
        if ($width)
        {
            $this->{'style'} .= ";width:{$width}px";
        }
        
        if ($height)
        {
            $this->{'style'} .= ";height:{$height}px";
        }
    }
    
    /**
     * Returns the frame size
     * @return array(width, height)
     */
    public function getSize()
    {
        return array($this->width, $this->height);
    }
    
    /**
     * Set Legend
     * @param  $legend frame legend
     */
    public function setLegend($legend)
    {
        $obj = new TElement('legend');
        $obj->add(new TLabel($legend));
        parent::add($obj);
        $this->legend = $legend;
    }
    
    /**
     * Returns the inner legend
     */
    public function getLegend()
    {
        return $this->legend;
    }
    
    /**
     * returns js code to show frame contents recursivelly
     * used just along with TUIBuilder
     * @ignore-autocomplete on
     */
    public function _getShowCode()
    {
        $panel_id = $this->getId();
        $code = "document.getElementById('{$panel_id}').style.visibility='visible';";
        $children = $this->getChildren();
        $uibuilder = (isset($children[1]) AND !$children[1] instanceof TLabel) ? $children[1] : $children[0];
        if ($uibuilder)
        {
            if ($uibuilder instanceof TNotebook OR $uibuilder instanceof TFrame)
            {
                $code .= $uibuilder->_getHideCode();
            }
            else if (method_exists($uibuilder, 'getChildren'))
            {
                if ($uibuilder->getChildren())
                {
                    foreach ($uibuilder->getChildren() as $object) // run through telement conteiners (position)
                    {
                        if (method_exists($object, 'getChildren'))
                        {
                            if ($object->getChildren())
                            {
                                foreach ($object->getChildren() as $child)
                                {
                                    if (($child instanceof TFrame) or ($child instanceof TNotebook))
                                    {
                                        $code.=$child->_getShowCode();
                                    }
                                }
                             }
                        }
                    }
                }
            }
        }
        return $code;
    }
    
    /**
     * returns js code to hide frame contents recursivelly
     * used just along with TUIBuilder
     * @ignore-autocomplete on
     */
    public function _getHideCode()
    {
        $panel_id = $this->getId();
        $code = "document.getElementById('{$panel_id}').style.visibility='hidden';";
        $children = $this->getChildren();
        $uibuilder = (isset($children[1]) AND !$children[1] instanceof TLabel) ? $children[1] : $children[0];
        if ($uibuilder)
        {
            if ($uibuilder instanceof TNotebook OR $uibuilder instanceof TFrame)
            {
                $code .= $uibuilder->_getHideCode();
            }
            else if (method_exists($uibuilder, 'getChildren'))
            {
                if ($uibuilder->getChildren())
                {
                    foreach ($uibuilder->getChildren() as $object) // run through telement conteiners (position)
                    {
                        if (method_exists($object, 'getChildren'))
                        {
                            if ($object->getChildren())
                            {
                                foreach ($object->getChildren() as $child)
                                {
                                    if (($child instanceof TFrame) or ($child instanceof TNotebook))
                                    {
                                        $code.=$child->_getHideCode();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $code;
    }
    
    /**
     * return the ID's of every child notebook
     * @ignore-autocomplete on
     */
    public function _getSubNotes()
    {
        $panel_id = $this->getId();
        $children = $this->getChildren();
        $uibuilder = (isset($children[1]) AND !$children[1] instanceof TLabel) ? $children[1] : $children[0];
        $returnValue = array();
        if ($uibuilder)
        {
            if ($uibuilder instanceof TNotebook OR $uibuilder instanceof TFrame)
            {
                $returnValue = array_merge($returnValue, $uibuilder->_getSubNotes());
            }
            else if (method_exists($uibuilder, 'getChildren'))
            {
                if ($uibuilder->getChildren())
                {
                    foreach ($uibuilder->getChildren() as $object) // run through telement conteiners (position)
                    {
                        if (method_exists($object, 'getChildren'))
                        {
                            if ($object->getChildren())
                            {
                                foreach ($object->getChildren() as $child)
                                {
                                    if ($child instanceof TNotebook)
                                    {
                                        $returnValue = array_merge($returnValue, array($child->getId()), (array)$child->_getSubNotes());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $returnValue;
    }
    
    /**
     * Return the Frame ID
     * @ignore-autocomplete on
     */
    public function getId()
    {
        return $this->{'id'};
    }
}
