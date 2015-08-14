<?php
Namespace Adianti\Widget\Wrapper;

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TMultiSearch;
use Adianti\Database\TCriteria;

use Exception;

/**
 * Database Multisearch Widget
 *
 * @version    2.0
 * @package    widget
 * @subpackage wrapper
 * @author     Pablo Dall'Oglio
 * @author     Matheus Agnes Dias
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TDBMultiSearch extends TMultiSearch
{
    protected $id;
    protected $initialItems;
    protected $items;
    protected $size;
    protected $height;
    protected $minLength;
    protected $maxSize;
    private $database;
    private $model;
    private $key;
    private $column;
    private $operator;
    private $orderColumn;
    private $criteria;
    
    /**
     * Class Constructor
     * @param  $name     widget's name
     * @param  $database database name
     * @param  $model    model class name
     * @param  $key      table field to be used as key in the combo
     * @param  $value    table field to be listed in the combo
     * @param  $ordercolumn column to order the fields (optional)
     * @param  $criteria criteria (TCriteria object) to filter the model (optional)
     */
    public function __construct($name, $database, $model, $key, $value, $orderColumn = NULL, TCriteria $criteria = NULL)
    {
        // executes the parent class constructor
        parent::__construct($name);
        
        if (empty($database))
        {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'database', __CLASS__));
        }
        
        if (empty($model))
        {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'model', __CLASS__));
        }
        
        if (empty($key))
        {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'key', __CLASS__));
        }
        
        if (empty($value))
        {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'value', __CLASS__));
        }
        
        $this->database = $database;
        $this->model = $model;
        $this->key = $key;
        $this->column = $value;
        $this->operator = 'like';
        $this->orderColumn = isset($orderColumn) ? $orderColumn : NULL;
        $this->criteria = $criteria;
    }
    
    /**
     * Define the search operator
     * @param $operator Search operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
    
    /**
     * Shows the widget
     */
    public function show()
    {
        // define the tag properties
        $this->tag-> name  = $this->name;    // tag name
        $this->tag-> id  = $this->id;    // tag name
        $this->setProperty('style', "width:{$this->size}px", FALSE); //aggregate style info
        $multiple = $this->maxSize == 1 ? 'false' : 'true';
        
        $load_items = '';
        if ($this->initialItems)
        {
            $new_items = array();
            foreach ($this->initialItems as $key => $item)
            {
                $new_item = array('id' => $key, 'text' => $item);
                $new_items[] = $new_item;
            }
            
            if ($multiple == 'true')
            {
                $load_items = '$("#'.$this->id.'").select2("data", '.json_encode($new_items).');';
            }
            else
            {
                $load_items = '$("#'.$this->id.'").select2("data", '.json_encode($new_item).');';
            }
        }
        
        $orderColumn = isset($this->orderColumn) ? $this->orderColumn : $this->column;
        $criteria = '';
        if ($this->criteria)
        {
            $criteria = base64_encode(serialize($this->criteria));
        }
        
        $seed = APPLICATION_NAME.'s8dkld83kf73kf094';
        $hash = md5("{$seed}{$this->database}{$this->key}{$this->column}{$this->model}");
        $length = $this->minLength;

        $class = 'AdiantiMultiSearchService';
        $callback = array($class, 'onSearch');
        $method = $callback[1];
        
        $search_word = AdiantiCoreTranslator::translate('Search');
        $sp = <<<HTML
            $('#{$this->id}').select2(
            {   
                minimumInputLength: '{$length}',
                separator: '||',
                placeholder: '{$search_word}',
                multiple: $multiple,
                id: function(e) { return e.id+"::"+e.text; },
                ajax: {
                    url: "engine.php?class={$class}&method={$method}&static=1&database={$this->database}&key={$this->key}&column={$this->column}&model={$this->model}&orderColumn={$orderColumn}&criteria={$criteria}&operator={$this->operator}",
                    dataType: 'json',
                    quietMillis: 100,
                    data: function(value, page) {
                        return {
                            value: value,
                            hash: '{$hash}'
                        };
                    },
                    results: function(data, page ) 
                    {
                        var aa = [];
                        $(data.result).each(function(i) {
                            var item = this.split('::');
                            aa.push({
                                id: item[0],
                                text: item[1]
                            });
                        });               

                        return {                             
                            results: aa 
                        }
                    }
                },             
                              
            });
            $('#s2id_{$this->id} > .select2-choices').height('{$this->height}px').width('{$this->size}px').css('overflow-y','auto');
            $load_items
HTML;

        // shows the component
        $this->tag->show();
        TScript::create($sp);
    }
}
