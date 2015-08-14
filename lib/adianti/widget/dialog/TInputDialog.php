<?php
Namespace Adianti\Widget\Dialog;

use Adianti\Widget\Base\TScript;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Wrapper\TQuickForm;

use Exception;

/**
 * Input Dialog
 *
 * @version    2.0
 * @package    widget
 * @subpackage dialog
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TInputDialog
{
    private $id;
    private $action;
    
    /**
     * Class Constructor
     * @param $title_msg  Dialog Title
     * @param $form    Dialog form body
     * @param $action  Action to be processed when closing the dialog
     * @param $caption Button caption
     */
    public function __construct($title_msg, TForm $form, TAction $action = NULL, $caption = '')
    {
        $this->id = uniqid();
        
        $modal_wrapper = new TElement('div');
        $modal_wrapper->{'class'} = 'modal';
        $modal_wrapper->{'id'}    = $this->id;
        $modal_wrapper->{'style'} = 'padding-top: 10%; z-index:4000';
        $modal_wrapper->{'tabindex'} = '-1';
        
        $modal_dialog = new TElement('div');
        $modal_dialog->{'class'} = 'modal-dialog';
        
        $modal_content = new TElement('div');
        $modal_content->{'class'} = 'modal-content';
        
        $modal_header = new TElement('div');
        $modal_header->{'class'} = 'modal-header';
        
        $close = new TElement('button');
        $close->{'type'} = 'button';
        $close->{'class'} = 'close';
        $close->{'data-dismiss'} = 'modal';
        $close->{'aria-hidden'} = 'true';
        $close->add('×');
        
        $title = new TElement('h4');
        $title->{'class'} = 'modal-title';
        $title->{'style'} = 'display:inline';
        $title->add( $title_msg ? $title_msg : AdiantiCoreTranslator::translate('Input') );
        
        $form_name = $form->getName();
        $wait_message = AdiantiCoreTranslator::translate('Loading');
        
        if ($form instanceof TQuickForm)
        {
            $form->delActions();
            $actionButtons = $form->getActionButtons();
            
            if ($actionButtons)
            {
                foreach ($actionButtons as $key => $button)
                {
                    $button->{'data-toggle'} = "modal";
                    $button->{'data-dismiss'} = 'modal';
                    $button->addFunction( "\$( '.modal-backdrop' ).last().remove(); \$('#{$this->id}').modal('hide'); \$('body').removeClass('modal-open');" );
                    $buttons[] = $button;
                }
            }
        }
        else
        {
            $button = new TButton(strtolower(str_replace(' ', '_', $caption)));
            $button->{'data-toggle'} = "modal";
            $button->{'data-dismiss'} = 'modal';
            $button->addFunction( "\$( '.modal-backdrop' ).last().remove(); \$('#{$this->id}').modal('hide'); \$('body').removeClass('modal-open');" );
            $button->setAction( $action );
            $button->setLabel( $caption );
            $buttons[] = $button;
            $form->addField($button);
        }
        
        $footer = new TElement('div');
        $footer->{'class'} = 'modal-footer';
        
        $modal_wrapper->add($modal_dialog);
        $modal_dialog->add($modal_content);
        $modal_content->add($modal_header);
        $modal_header->add($close);
        $modal_header->add($title);
        
        $modal_content->add($form);
        $modal_content->add($footer);
        
        if (isset($buttons) AND $buttons)
        {
            foreach ($buttons as $button)
            {
                $footer->add($button);
            }
        }
        
        $modal_wrapper->show();
        TScript::create( "tdialog_start( '#{$this->id}' );");
    }
}
