<?php
Namespace Adianti\Widget\Container;

use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TFrame;

/**
 * Notebook
 *
 * @version    2.0
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TNotebook
{
    private $width;
    private $height;
    private $currentPage;
    private $pages;
    private $id;
    private $tabAction;
    private $tabsVisibility;
    static private $noteCounter;
    static private $subNotes;
    
    /**
     * Class Constructor
     * @param $width   Notebook's width
     * @param $height  Notebook's height
     */
    public function __construct($width = 500, $height = 650)
    {
        $this->id = 'tnotebook_' . uniqid();
        $this->counter = ++ self::$noteCounter;
        // define some default values
        $this->width = $width;
        $this->height = $height;
        $this->currentPage = 0;
        $this->tabsVisibility = TRUE;
    }
    
    /**
     * Define if the tabs will be visible or not
     * @param $visible If the tabs will be visible
     */
    public function setTabsVisibility($visible)
    {
        $this->tabsVisibility = $visible;
    }
    
    /**
     * Returns the element ID
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set the notebook size
     * @param $width  Notebook's width
     * @param $height Notebook's height
     */
    public function setSize($width, $height)
    {
        // define the width and height
        $this->width  = $width;
        $this->height = $height;
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
     * Define the current page to be shown
     * @param $i An integer representing the page number (start at 0)
     */
    public function setCurrentPage($i)
    {
        // atribui a página corrente
        $this->currentPage = $i;
    }
    
    /**
     * Returns the current page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    
    /**
     * Add a tab to the notebook
     * @param $title   tab's title
     * @param $object  tab's content
     */
    public function appendPage($title, $object)
    {
        $this->pages[$title] = $object;
    }

    /**
     * Return the Page count
     */
    public function getPageCount()
    {
        return count($this->pages);
    }
    
    /**
     * Define the action for the Notebook tab
     * @param $action Action taken when the user
     * clicks over Notebook tab (A TAction object)
     */
    public function setTabAction(TAction $action)
    {
        $this->tabAction = $action;
    }
    
    /**
     * Show the notebook at the screen
     */
    public function show()
    {
        // count the pages
        $pages = $this->getPageCount();
        
        // creates a table
        $note_table = new TTable;
        $note_table->{'width'} = $this->width;
        $note_table->{'border'} = 0;
        $note_table->{'cellspacing'} = 0;
        $note_table->{'cellpadding'} = 0;
        $note_table->{'class'} = 'notebook-table';
        
        // add a row for the tabs
        $row = $note_table->addRow();
        
        $i = 0;
        $id = $this->id;
        
        // get javascript code to show/hide sub-notebooks
        $subnotes_hide = $this->_getHideCode();
        $subnotes_show = $this->_getShowCode();
        
        if ($this->pages)
        {
            // iterate the tabs, showing them
            foreach ($this->pages as $title => $content)
            {
                // verify if the current page is to be shown
                $classe = ($i == $this->currentPage) ? 'tnotebook_aba_sim' : 'tnotebook_aba_nao';
                
                // add a cell for this tab
                if ($this->tabsVisibility)
                {
                    $cell = $row->addCell("&nbsp;$title&nbsp;");
                    $cell->{'class'} = $classe;
                    $cell-> id = "aba_{$id}_{$i}";
                    $cell-> height='23px';
                    $cell-> onclick     = $subnotes_hide.
                                          "tnotebook_hide('$id', $pages);".
                                          "tnotebook_show_tab('$id',$i);".
                                          $this->getShowPageCode($title); // show only this page sub-contents
                    if ($this->tabAction)
                    {
                        $this->tabAction->setParameter('current_page', $i+1);
                        $string_action = $this->tabAction->serialize(FALSE);
                        $cell-> onclick = $cell-> onclick . "__adianti_ajax_exec('$string_action')";
                    }
                    $cell-> onmouseover = "javascript:tnotebook_prelight(this, '{$id}', $i)";
                    $cell-> onmouseout  = "javascript:tnotebook_unprelight(this, '{$id}', $i)";
                    
                    // creates the cell spacer
                    $cell = $row->addCell('&nbsp;');
                    $cell->{'class'} = 'tnotebook_spacer';
                    $cell-> width='3px';
                    
                    $i ++;
                }
            }
        }
        
        // creates the cell terminator
        $cell = $row->addCell('&nbsp;');
        $cell->{'class'} = 'tnotebook_end';
        
        if ($this->tabsVisibility)
        {
            $row = $note_table->addRow();
            $row-> height= '7px';
            $cell = $row->addCell('<span></span>');
            $cell->{'class'} = 'tnotebook_down';
            $cell-> colspan = 100;
        }
        
        // show the table
        $note_table->show();
        
        // creates a <div> around the content
        $quadro = new TElement('div');
        $quadro->{'class'} = 'tnotebook_quadro';
        $width = $this->width-7;
        $quadro-> style = "height:{$this->height}px;width:{$width}px";
        if ($this->counter == 1)
        {
            self::$subNotes = $this->_getSubNotes();
        }
        $i = 0;
        // iterate the tabs again, now to show the content
        if ($this->pages)
        {
            foreach ($this->pages as $title => $content)
            {
                // verify if the current page is to be shown
                if (($i == $this->currentPage) and ($this->counter == 1 or in_array($this->id, self::$subNotes) ) )
                {
                    $classe = 'tnotebook_painel_sim';
                }
                else
                {
                    $classe = 'tnotebook_painel_nao';
                }
                
                // creates a <div> for the contents
                $painel = new TElement('div');
                $painel->{'class'} = $classe;       // CSS
                $painel-> id    = "painel_{$id}_{$i}"; // ID
                $quadro->add($painel);
                
                // check if the content is an object
                if (is_object($content))
                {
                    $painel->add($content);
                }
                
                $i ++;
            }
        }
        
        $quadro_table = new TTable;
        $quadro_table-> width = $this->width;
        $quadro_table->{'class'} = 'tnotebook_table';
        $quadro_table-> border = 0;
        $quadro_table-> cellspacing = 0;
        $quadro_table-> cellpadding = 0;
        $row = $quadro_table->addRow();
        $row->addCell($quadro);
        $quadro_table->show();
    }
    
    /**
     * Follow recursivelly the object childreen looking
     * for TNotebook or TFrame and executes a method
     */
    private function diggNotebook($object, $method)
    {
        if ($object instanceof TElement)
        {
            $children = $object->getChildren();
            if ($children)
            {
                $returnValues = ($method == '_getSubNotes') ? array() : '';
                foreach ($children as $element)
                {
                    if ($element instanceof TNotebook)
                    {
                        if ($method == '_getSubNotes')
                        {
                            $returnValues = array_merge($returnValues, array($element->getId()), (array)$element->_getSubNotes(), (array)$this->diggNotebook($element, $method));
                        }
                        else //getHideCode, getShowCode
                        {
                            $returnValues .= $element->$method() . $this->diggNotebook($element, $method);
                        }
                    }
                    else if ($element instanceof TFrame)
                    {
                        if ($method == '_getSubNotes')
                        {
                            $returnValues = array_merge($returnValues, (array)$element->_getSubNotes(), (array)$this->diggNotebook($element, $method) );
                        }
                        else //getHideCode, getShowCode
                        {
                            $returnValues .= $element->$method() . $this->diggNotebook($element, $method);
                        }
                    }
                    else if ($element instanceof TElement)
                    {
                        if ($method == '_getSubNotes')
                        {
                            $returnValues = array_merge($returnValues, (array)$this->diggNotebook($element, $method));
                        }
                        else
                        {
                            $returnValues .= $this->diggNotebook($element, $method);
                        }
                    }
                }
                
                return $returnValues;
            }
        }
    }
    
    /**
     * returns js code to show notebook contents recursivelly
     * @ignore-autocomplete on
     */
    public function _getShowCode()
    {
        return $this->getCode('show');
    }
    
    /**
     * returns js code to hide notebook contents recursivelly
     * @ignore-autocomplete on
     */
    public function _getHideCode()
    {
        return $this->getCode('hide');
    }
    
    /**
     * returns js code to hide/show notebook contents recursivelly
     * @ignore-autocomplete on
     */
    public function getCode($mode)
    {
        $subnotes_show = '';
        $subnotes_hide = '';
        $i = 0;
        if ($this->pages)
        {
            foreach ($this->pages as $title => $content)
            {
                $subnotes_hide .= $this->diggNotebook($content, '_getHideCode');
                $subnotes_show .= $this->diggNotebook($content, '_getShowCode');
                // a exibição não é recursiva, mostra a primeira aba (page)
                if ($mode=='show')
                {
                    break;
                }
                $i ++;
            }
        }
        
        if ($mode=='hide')
        {
            return "tnotebook_hide('{$this->id}', {$i});" . $subnotes_hide;
        }
        else
        {
            return "tnotebook_show_tab('{$this->id}', 0);" . $subnotes_show;
        }
    }
    
    
    /**
     * returns js code to hide/show an SPECIFIC NOTEBOOK SHEET recursivelly
     * @param $title sheet title
     * @ignore-autocomplete on
     */
    public function getShowPageCode($title)
    {
        $code='';
        $i = 0;
        $content = $this->pages[$title];
        $code = $this->diggNotebook($content, '_getShowCode');
        return $code;
    }
    
    /**
     * return the ID's of every child notebook of the FIRST SHEET
     * @ignore-autocomplete on
     */
    public function _getSubNotes()
    {
        if ($this->pages)
        {
            foreach ($this->pages as $title => $content)
            {
                return (array) $this->diggNotebook($content, '_getSubNotes');
            }
        }
    }
}
