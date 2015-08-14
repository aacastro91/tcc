<?php
Namespace Adianti\Widget\Datagrid;

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Widget\Base\TElement;
use Adianti\Control\TAction;
use Adianti\Widget\Container\TTable;

use Exception;

/**
 * Page Navigation provides navigation for a datagrid
 *
 * @version    2.0
 * @package    widget
 * @subpackage datagrid
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TPageNavigation
{
    private $limit;
    private $count;
    private $order;
    private $page;
    private $first_page;
    private $action;
    private $width;
    
    /**
     * Set the Amount of displayed records
     * @param $limit An integer
     */
    public function setLimit($limit)
    {
        $this->limit  = (int) $limit;
    }
    
    /**
     * Define the PageNavigation's width
     * @param $width PageNavigation's width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }
    
    /**
     * Define the total count of records
     * @param $count An integer (the total count of records)
     */
    public function setCount($count)
    {
        $this->count = (int) $count;
    }
    
    /**
     * Define the current page
     * @param $page An integer (the current page)
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
    }
    
    /**
     * Define the first page
     * @param $page An integer (the first page)
     */
    public function setFirstPage($first_page)
    {
        $this->first_page = (int) $first_page;
    }
    
    /**
     * Define the ordering
     * @param $order A string containint the column name
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    /**
     * Set the page navigation properties
     * @param $properties array of properties
     */
    public function setProperties($properties)
    {
        $order      = isset($properties['order'])  ? addslashes($properties['order'])  : '';
        $page       = isset($properties['page'])   ? $properties['page']   : 1;
        $first_page = isset($properties['first_page']) ? $properties['first_page']: 1;
        
        $this->setOrder($order);
        $this->setPage($page);
        $this->setFirstPage($first_page);
    }
    
    /**
     * Define the PageNavigation action
     * @param $action TAction object (fired when the user navigates)
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    /**
     * Show the PageNavigation widget
     */
    public function show()
    {
        if (!$this->action instanceof TAction)
        {
            throw new Exception(AdiantiCoreTranslator::translate('You must call ^1 before add this component', __CLASS__ . '::' . 'setAction()'));
        }
        
        $first_page  = isset($this->first_page) ? $this->first_page : 1;
        $direction   = 'asc';
        $page_size = isset($this->limit) ? $this->limit : 10;
        $max = 10;
        $registros = $this->count;
        
        if (!$registros)
        {
            $registros = 0;
        }
        
        if ($page_size > 0)
        {
            $pages = (int) ($registros / $page_size) - $first_page +1;
        }
        else
        {
            $pages = 1;
        }
        
        if ($page_size>0)
        {
            $resto = $registros % $page_size;
        }
        
        $pages += $resto>0 ? 1 : 0;
        $last_page = min($pages, $max);
        
        $div = new TElement('div');
        $div->{'class'} = 'tpagenavigation';
        $div-> align = 'center';
        
        $table = new TTable;
        $table-> cellpadding=0;
        $table-> cellspacing=0;
        $row = $table->addRow();
        
        //previous
        $link = new TElement('a');
        $span = new TElement('span');
        $cell = $row->addCell($link);
        $link->add($span);
        $cell->{'class'} = 'prev';
        
        if ($first_page > 1)
        {
            $this->action->setParameter('offset', ($first_page - $max) * $page_size);
            $this->action->setParameter('limit',  $page_size);
            $this->action->setParameter('page',   $first_page - $max);
            $this->action->setParameter('first_page', $first_page - $max);
            $this->action->setParameter('order', $this->order);
            
            $link-> href      = $this->action->serialize();
            $link-> generator = 'adianti';
            $span->add('&lt;&lt;');
        }
        else
        {
            $span->add('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }
        
        for ($n = $first_page; $n <= $last_page + $first_page -1; $n++)
        {
            $offset = ($n -1) * $page_size;
            $link = new TElement('a');
            
            $this->action->setParameter('offset', $offset);
            $this->action->setParameter('limit',  $page_size);
            $this->action->setParameter('page',   $n);
            $this->action->setParameter('first_page', $first_page);
            $this->action->setParameter('order', $this->order);
            
            $link-> href      = $this->action->serialize();
            $link-> generator = 'adianti';
            
            $span = new TElement('span');
            $span->add($n);
            $cell = $row->addCell($link);
            $link->add($span);
            
            if($this->page == $n)
            {
                $cell->{'class'}='on';
            }
        }
        
        for ($z=$n; $z<=10; $z++)
        {
            $link = new TElement('a');
            $span = new TElement('span');
            $span->add($z);
            $cell = $row->addCell($link);
            $link->add($span);
            $cell->{'class'}='off';
        }
        
        $link = new TElement('a');
        $span = new TElement('span');
        $cell = $row->addCell($link);
        $link->add($span);
        $cell->{'class'} = 'next';
        
        if ($pages > $max)
        {
            $offset = ($n -1) * $page_size;
            $first_page = $n;
            
            $this->action->setParameter('offset',  $offset);
            $this->action->setParameter('limit',   $page_size);
            $this->action->setParameter('page',    $n);
            $this->action->setParameter('first_page', $first_page);
            $this->action->setParameter('order', $this->order);
            $link-> href      = $this->action->serialize();
            $link-> generator = 'adianti';
            
            $span->add('&nbsp; &gt;&gt; &nbsp; ');
        }
        else
        {
            $span->add('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }
        
        $div->add($table);
        $div->show();
    }
}
