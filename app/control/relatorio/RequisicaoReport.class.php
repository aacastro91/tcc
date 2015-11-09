<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Log\TLoggerTXT;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;

/*
 * Copyright (C) 2015 Anderson
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Description of RequisicaoReport
 *
 * @author Anderson
 */
class RequisicaoReport extends TPage {

    protected $form; // form
    private $pdf;
    private $data;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct() {
        parent::__construct();

        // creates the form
        $this->form = new TForm('form_Requisicao_report');
        $this->form->class = 'tform'; // CSS class
        $this->form->style = 'width: 500px';

        // creates the table container
        $table = new TTable;
        $table->width = '100%';

        // add the table inside the form
        $this->form->add($table);

        // define the form title
        $row = $table->addRow(); //Set( new TLabel('Relatório de Requisição'), '', '','', '' )->class = 'tformtitle';
        $row->class = 'tformtitle';
        $row->addCell(new TLabel('Relatório de Requisição'))->colspan = 2;

        // create the form fields
        $numeroProcessoI = new TEntry('numeroProcessoI');
        $numeroProcessoF = new TEntry('numeroProcessoF');
        $emissaoI = new TDate('emissaoI');
        $emissaoF = new TDate('emissaoF');
        $aprovado = new TRadioGroup('aprovado');


        // define the sizes
        $numeroProcessoI->setSize(100);
        $numeroProcessoF->setSize(100);
        $emissaoI->setSize(85);
        $emissaoI->setProperty('style', 'margin-right : 0px');
        $emissaoF->setSize(85);
        $emissaoF->setProperty('style', 'margin-right : 0px');
        //$aprovado->setSize(90);
        //mask
        $emissaoI->setMask('dd/mm/yyyy');
        $emissaoF->setMask('dd/mm/yyyy');


        $emissaoF->setValue(date('d/m/Y'));
        //$emissaoI->setNumericMask(0, '', '');
        // validations
        $aprovado->addValidation('Aprovado', new TRequiredValidator);
        $emissaoF->addValidation('Emissão - Até', new TDateValidator, array('dd/mm/yyyy'));


        // add one row for each form field
        $table->addRowSet(new TLabel('Nº Processo'), array($numeroProcessoI, new TLabel('Até'), $numeroProcessoF));
        $table->addRowSet(new TLabel('Emissão'), array($emissaoI, new TLabel('Até'), $emissaoF));
        $row = $table->addRow(); //Set( new TLabel('Aprovado:'), $aprovado );
        $row->addCell(new TLabel('Aprovado:'));
        $row->addCell($aprovado);

        $this->form->setFields(array($numeroProcessoI, $numeroProcessoF, $emissaoI, $emissaoF, $aprovado));


        $aprovado->addItems(array('1' => 'Sim', '0' => 'Não', '%' => 'Todos'));
        $aprovado->setValue('%');
        $aprovado->setLayout('horizontal');

        $generate_button = TButton::create('generate', array($this, 'onGenerate'), _t('Generate'), 'ico_apply.png');
        $this->form->addField($generate_button);

        // add a row for the form action
        $table->addRowSet($generate_button, '')->class = 'tformaction';

        $container = new TTable;       
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        //$container->addRow()->addCell($this->datagrid);
        //$container->addRow()->addCell($this->pageNavigation);
        
        parent::add($container);
    }

    function header() {
        $this->pdf->SetFont('Times', 'B', 18);
        $this->pdf->Cell(0, 5, utf8_decode('Relatório de Requisição'), 0, 1, 'L');
        $this->pdf->ln(2);
        $this->pdf->Cell(0, 0, '', 'T', 1);
    }

    function footer() {
        $this->pdf->SetY(-15);
        $this->pdf->Cell(0, 5, utf8_decode(date('d/m/Y h:i:s')), 'T');
    }

    function requisicaoHeader() {
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nº Processo: ')), 5, utf8_decode('Nº Processo:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode($this->data->numeroProcesso), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Emissão: ')), 5, utf8_decode('Emissão:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode(TDate::date2br($this->data->emissao)), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Aprovado: ')), 5, utf8_decode('Aprovado:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode(($this->data->aprovado == 1) ? 'Sim' : 'Não'), 0, 1, 'L');

        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nº SRP: ')), 5, utf8_decode('Nº SRP:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode($this->data->srp->numeroSRP), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nome: ')), 5, utf8_decode('Nome:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode($this->data->srp->nome), 0, 1, 'L');
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell(20, 5, utf8_decode('Nº Item'), 0, 0, 'L');
        $this->pdf->Cell(90, 5, utf8_decode('Descrição Sumária'), 0, 0, 'L');
        $this->pdf->Cell(20, 5, utf8_decode('Qtd. Estimada'), 0, 0, 'R');
        $this->pdf->Cell(30, 5, utf8_decode('Valor'), 0, 0, 'R');
        $this->pdf->Cell(0, 5, utf8_decode('Total'), 0, 1, 'R');
        $this->pdf->Cell(0, 0, '', 'T', 1);
    }

    function requisicaoItens() {
        $itens = $this->data->getItems();

        $totalItem = 0;
        $ValorTotal = 0;

        foreach ($itens as $item) {
            $this->pdf->SetFont('Times', '', 9);
            $descricaoSumaria = $item->descricaoSumaria;

            while ($this->pdf->GetStringWidth(utf8_decode($descricaoSumaria)) > 81) {
                $descricaoSumaria = substr($descricaoSumaria, 0, -1);
            }

            $this->pdf->SetFont('Times', '', 9);
            $this->pdf->Cell(15, 5, utf8_decode($item->numeroItem), 0, 0, 'R');
            $this->pdf->Cell(5, 5, '', 0, 0, 'L');

            $this->pdf->Cell(90, 5, utf8_decode($descricaoSumaria), 0, 0, 'L');
            $this->pdf->Cell(20, 5, utf8_decode($item->quantidade), 0, 0, 'R');
            $this->pdf->Cell(30, 5, utf8_decode(number_format($item->valorUnitario, 2, ',', '.')), 0, 0, 'R');
            $this->pdf->Cell(0, 5, utf8_decode(number_format($item->total, 2, ',', '.')), 0, 1, 'R');
            $totalItem++;
            $ValorTotal += $item->total;
        }


        //imprime o rodapé
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->Cell(15, 5, '', 0, 0);
        $this->pdf->Cell(50, 5, utf8_decode('Quantidade de Itens: ' . $totalItem), 0, 0);
        $this->pdf->Cell(0, 5, utf8_decode('Total da Requisição: ' . utf8_decode(number_format($ValorTotal, 2, ',', '.'))), 0, 1, 'R');
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->ln(15);
    }

    /**
     * method onGenerate()
     * Executed whenever the user clicks at the generate button
     */
    function onGenerate() {
        try {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            //TTransaction::setLogger(new TLoggerTXT("c:\\array\\LOG" . date("Ymd-His") . ".txt"));
            // get the form data into an active record
            $formdata = $this->form->getData();

            $emissaoI = $formdata->emissaoI;
            $emissaoF = TDate::date2us($formdata->emissaoF);

            if (!$emissaoI) {
                $emissaoI = '0';
            } else {
                $emissaoI = TDate::date2us($emissaoI);
            }
            

            $this->form->validate();


            $repository = new TRepository('Requisicao');
            $criteria = new TCriteria;

            if ($formdata->numeroProcessoI != '' && $formdata->numeroProcessoF != '') {
                $criteria->add(new TFilter('numeroProcesso', 'BETWEEN', "{$formdata->numeroProcessoI}", "{$formdata->numeroProcessoF}"));
            }
            if ($formdata->emissaoI != '' && $formdata->emissaoF != '') {
                $criteria->add(new TFilter('emissao', 'between', "{$emissaoI}", "{$emissaoF}"));
            }
            if (isset($formdata->aprovado)) {
                $criteria->add(new TFilter('aprovado', 'like', "{$formdata->aprovado}"));
            }


            $requisicoes = $repository->load($criteria, true);
            if ($requisicoes) {

                $this->pdf = new FPDF();
                $this->pdf->AliasNbPages();
                $this->pdf->SetMargins(10, 10, 10);
                $this->pdf->setHeaderCallback(array($this, 'header'));
                $this->pdf->setFooterCallback(array($this, 'footer'));
                $this->pdf->AddPage();
                foreach ($requisicoes as $requisicao) {
                    $this->data = $requisicao;
                    $this->requisicaoHeader();
                    $this->requisicaoItens();
                }

                if (!file_exists("app/output/RelatorioRequisicao.pdf") OR is_writable("app/output/RelatorioRequisicao.pdf")) {
                    $this->pdf->Output("app/output/RelatorioRequisicao.pdf");
                } else {
                    throw new Exception('Permissão negada' . ': ' . "app/output/RelatorioRequisicao.pdf");
                }

                parent::openFile("app/output/RelatorioRequisicao.pdf");

                //new TMessage('info', 'Relatório gerado. Por favor, habilite o pop-up do seu browser.');
            } else {
                new TMessage('error', 'Nenhum registro encontrado');
            }

            // fill the form with the active record data
            $this->form->setData($formdata);

            // close the transaction
            TTransaction::close();
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

}
