<?php
Namespace Adianti\Widget\Menu;

use Adianti\Widget\Menu\TMenu;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;

use SimpleXMLElement;

/**
 * Menubar Widget
 *
 * @version    2.0
 * @package    widget
 * @subpackage menu
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TMenuBar extends TElement
{
    public function __construct()
    {
        parent::__construct('div');
        $this->{'style'} = 'margin: 0;';
        $this->{'class'} = 'nav navbar-nav';
    }
    
    /**
     * Build a MenuBar from a XML file
     * @param $xml_file path for the file
     * @param $permission_callback check permission callback
     */
    public static function newFromXML($xml_file, $permission_callback = NULL, $bar_class = 'nav navbar-nav', $menu_class = 'dropdown-menu', $item_class = '')
    {
        if (file_exists($xml_file))
        {
            $menu_string = file_get_contents($xml_file);
            if (utf8_encode(utf8_decode($menu_string)) == $menu_string ) // SE UTF8
            {
                $xml = new SimpleXMLElement($menu_string);
            }
            else
            {
                $xml = new SimpleXMLElement(utf8_encode($menu_string));
            }
            
            $menubar = new TMenuBar;
            $menubar->{'class'} = $bar_class;
            foreach ($xml as $xmlElement)
            {
                $atts   = $xmlElement->attributes();
                $label  = (string) $atts['label'];
                $action = (string) $xmlElement-> action;
                $icon   = (string) $xmlElement-> icon;
                
                $button_div = new TElement('div');
                $button_div->{'class'} = 'btn-group';
                
                $button = new TElement('button');
                $button->{'data-toggle'} = 'dropdown';
                $button->{'class'} = 'btn btn-default dropdown-toggle';
                $button->add($label);
                
                $span = new TElement('span');
                $span->{'class'} = 'caret';
                $span->add('');
                $button->add($span);
                $menu = new TMenu($xmlElement-> menu-> menuitem, $permission_callback, 1, $menu_class, $item_class);
                
                // check children count (permissions)
                if (count($menu->getMenuItems()) >0)
                {
                    $button_div->add($button);
                    $button_div->add($menu);
                    $menubar->add($button_div);
                }
            }
            
            return $menubar;
        }
    }
    
    /**
     * Show
     */
    public function show()
    {
        TScript::create( 'tmenubar_start()' );
        parent::show();
    }
}
