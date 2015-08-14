<?php
Namespace Adianti\Widget\Form;

use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TField;
use Adianti\Widget\Form\THidden;

/**
 * FileChooser widget
 *
 * @version    2.0
 * @package    widget
 * @subpackage form
 * @author     Nataniel Rabaioli
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TFile extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $height;
    
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id = $this->name . '_' . uniqid();
        $this->height = 25;
    }
    
    public function setSize($width, $height = NULL)
    {
        $this->size   = $width;
    }
    
    public function setHeight($height)
    {
        $this->height = $height;
    }
    
    /**
     * Show the widget at the screen
     */
    public function show()
    {
        // define the tag properties
        $this->tag-> id    = $this->id;
        $this->tag-> name  = 'file_' . $this->name;  // tag name
        $this->tag-> value = $this->value; // tag value
        $this->tag-> type  = 'file';       // input type
        $this->tag-> style = "width:{$this->size}px;height:{$this->height}px";  // size
        
        $hdFileName = new THidden($this->name);
        $hdFileName->setValue( $this->value );
        
        // verify if the widget is editable
        if (!parent::getEditable())
        {
            // make the field read-only
            $this->tag-> readonly = "1";
            $this->tag-> type = 'text';
            $this->tag->{'class'} = 'tfield_disabled'; // CSS
        }
        
        $div = new TElement('div');
        $div-> style="display:inline;width:100%;";
        $div-> id = 'div_file_'.uniqid();
        
        $div->add( $hdFileName );
        $div->add( $this->tag );
        $div->show();
        
        $uploaderClass = 'AdiantiUploaderService';
        $action = "engine.php?class={$uploaderClass}";
        TScript::create("
            $(document).ready( function()
            {
                $('#{$this->tag-> id}').change( function()
                {
                    var tfile = new  TFileAjaxUpload('{$this->tag-> id}','{$action}','{$div-> id}');
                    
                    tfile.initFileAjaxUpload();
                });
            });");
    }
    
    /**
     * Enable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function enableField($form_name, $field)
    {
        TScript::create( " tfile_enable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Disable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function disableField($form_name, $field)
    {
        TScript::create( " tfile_disable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Clear the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function clearField($form_name, $field)
    {
        TScript::create( " tfile_clear_field('{$form_name}', '{$field}'); " );
    }
}
