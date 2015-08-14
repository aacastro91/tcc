<?php
Namespace Adianti\Widget\Form;

use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TField;

/**
 * ComboBox Widget
 *
 * @version    2.0
 * @package    widget
 * @subpackage form
 * @author     Matheus Agnes Dias
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TMultiSearch extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $items;
    protected $size;
    protected $height;
    protected $minLength;
    protected $maxSize;
    protected $initialItems;
    
    /**
     * Class Constructor
     * @param  $name Widget's name
     */
    public function __construct($name)
    {
        // executes the parent class constructor
        parent::__construct($name);
        $this->id   = 'tmultisearch'.uniqid();

        $this->height = 100;
        $this->minLength = 5;
        $this->maxSize = 0;
        
        if (LANG !== 'en')
        {
            TPage::include_js('lib/adianti/include/tmultisearch/select2_locale_'.LANG.'.js');
        }
        
        // creates a <select> tag
        $this->tag = new TElement('input');
        $this->tag->{'type'} = 'hidden';
        $this->tag->{'component'} = 'multisearch';
    }
    
    /**
     * Define the widget's size
     * @param  $width   Widget's width
     * @param  $height  Widget's height
     */
    public function setSize($width, $height = NULL)
    {
        $this->size   = $width;
        if ($height)
        {
            $this->height = $height;
        }
    }

    /**
     * Define the minimum length for search
     */
    public function setMinLength($length)
    {
        $this->minLength = $length;
    }

    /**
     * Define the maximum number of items that can be selected
     */
    public function setMaxSize($maxsize)
    {
        $this->maxSize = $maxsize;
    }
    
    /**
     * Add items to the combo box
     * @param $items An indexed array containing the combo options
     */
    public function addItems($items)
    {
        if (is_array($items))
        {
            $this->items = $items;
        }
    }
    
    /**
     * Return the post data
     */
    public function getPostData()
    {
        if (isset($_POST[$this->name]))
        {
            $val = $_POST[$this->name];
            
            if ($val)
            {
                $rows = explode('||', $val);
                $data = array();
    
                if (is_array($rows))
                {
                    foreach ($rows as $row)
                    {
                        $columns = explode('::', $row);
                        
                        if (is_array($columns))
                        {
                            $data[ $columns[0] ] = $columns[1];
                        }
                    }
                }
                return $data;
            }
            return '';
        }
        else
        {
            return '';
        }
    }
    
    /**
     * Enable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function enableField($form_name, $field)
    {
        TScript::create( " tmultisearch_enable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Disable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function disableField($form_name, $field)
    {
        TScript::create( " tmultisearch_disable_field('{$form_name}', '{$field}'); " );
    }

    /**
     * Define the field's value
     * @param $value An array the field's values
     */
    public function setValue($value)
    {
        $this->initialItems = $value;
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

        $preitems_json = '';
        if ($this->items)
        {
            $preitems = array();
            foreach ($this->items as $key => $item)
            {
                $new_item = array('id' => $key, 'text' => $item);
                $preitems[] = $new_item;
            }
            $preitems_json = json_encode($preitems);
        }
        
        
        $search_word = AdiantiCoreTranslator::translate('Search');
        $sp = <<<HTML
            $('#{$this->id}').select2(
            {   
                minimumInputLength: '{$this->minLength}',
                maximumSelectionSize: '{$this->maxSize}',
                separator: '||',
                placeholder: '{$search_word}',
                multiple: $multiple,
                id: function(e) { return e.id+"::"+e.text; },
                query: function (query)
                {
                    var data = {results: []};
                    preload_data = {$preitems_json};
                    $.each(preload_data, function(){
                        if(query.term.length == 0 || this.text.toUpperCase().indexOf(query.term.toUpperCase()) >= 0 ){
                            data.results.push({id: this.id, text: this.text });
                        }
                    });
         
                  query.callback(data);
              }
            });
            $('#s2id_{$this->id} > .select2-choices').height('{$this->height}px').width('{$this->size}px').css('overflow-y','auto');
            $load_items
HTML;

        // shows the component
        $this->tag->show();
        TScript::create( $sp );
    }
}
