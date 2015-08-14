<?php
Namespace Adianti\Widget\Form;

use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Control\TAction;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TField;

use Adianti\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Spinner Widget (also known as spin button)
 *
 * @version    2.0
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TSpinner extends TField implements AdiantiWidgetInterface
{
    private $min;
    private $max;
    private $step;
    private $exitAction;
    protected $id;
    protected $formName;
    
    /**
     * Class Constructor
     * @param $name Name of the widget
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id = 'tspinner_'.uniqid();
    }
    
    /**
     * Define the field's range
     * @param $min Minimal value
     * @param $max Maximal value
     * @param $step Step value
     */
    public function setRange($min, $max, $step)
    {
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
        
        parent::setValue($min);
    }
    
    /**
     * Define the action to be executed when the user leaves the form field
     * @param $action TAction object
     */
    function setExitAction(TAction $action)
    {
        if ($action->isStatic())
        {
            $this->exitAction = $action;
        }
        else
        {
            $string_action = $action->toString();
            throw new Exception(AdiantiCoreTranslator::translate('Action (^1) must be static to be used in ^2', $string_action, __METHOD__));
        }
    }
    
    /**
     * Enable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function enableField($form_name, $field)
    {
        TScript::create( " tspinner_enable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Disable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function disableField($form_name, $field)
    {
        TScript::create( " tspinner_disable_field('{$form_name}', '{$field}'); " );
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        // define the tag properties
        $this->tag-> name  = $this->name;    // TAG name
        $this->tag-> value = $this->value;   // TAG value
        $this->tag-> type  = 'text';         // input type
        $this->tag-> style = "width:{$this->size}px";  // size
        
        if ($this->id)
        {
            $this->tag->{'id'}  = $this->id;
        }
        
        // verify if the widget is non-editable
        if (parent::getEditable())
        {
            $exit_action = 'function() {}';
            if (isset($this->exitAction))
            {
                if (!TForm::getFormByName($this->formName) instanceof TForm)
                {
                    throw new Exception(AdiantiCoreTranslator::translate('You must pass the ^1 (^2) as a parameter to ^3', __CLASS__, $this->name, 'TForm::setFields()') );
                }            
                $string_action = $this->exitAction->serialize(FALSE);
                $exit_action = "function() { serialform=(\$('#{$this->formName}').serialize());
                                             __adianti_ajax_lookup('$string_action&'+serialform, this); }";
            }
            
            TScript::create(" tspinner_start( '#{$this->id}', '{$this->value}', '{$this->min}', '{$this->max}', '{$this->step}', $exit_action); ");
        }
        else
        {
            $this->tag-> readonly = "1";
            $this->tag->{'class'} = 'tfield_disabled'; // CSS
            $this->tag-> style = "width:{$this->size}px;".
                                 "-moz-user-select:none;";
            $this->tag-> onmouseover = "style.cursor='default'";
        }
        // shows the tag
        $this->tag->show();
    }
    
    /**
     * Set the value
     */
    public function setValue($value)
    {
        parent::setValue( (int) $value);
    }
}
