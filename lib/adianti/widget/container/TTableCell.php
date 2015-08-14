<?php
Namespace Adianti\Widget\Container;

use Adianti\Widget\Base\TElement;

/**
 * TableCell: Represents a cell inside a table
 *
 * @version    2.0
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TTableCell extends TElement
{
    /**
     * Class Constructor
     * @param $value  TableCell content
     */
    public function __construct($value)
    {
        parent::__construct('td');
        parent::add($value);
    }
}
