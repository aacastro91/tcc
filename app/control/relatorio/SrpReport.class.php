<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;

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
 * Description of SrpReport
 *
 * @author Anderson
 */
class SrpReport extends TPage {

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
        $this->form = new TForm('form_Srp_report');
        $this->form->class = 'tform'; // CSS class
        $this->form->style = 'width: 500px';

        // creates the table container
        $table = new TTable;
        $table->width = '100%';

        // add the table inside the form
        $this->form->add($table);

        // define the form title
        $row = $table->addRow(); //Set( new TLabel('Relatório de Srp'), '', '','', '' )->class = 'tformtitle';
        $row->class = 'tformtitle';
        $row->addCell(new TLabel('Relatório de Srp'))->colspan = 5;

        // create the form fields
        $numeroSRPI                      = new TEntry('numeroSRPI');
        $numeroSRPF                      = new TEntry('numeroSRPF');
        $numeroIRPI                      = new TEntry('numeroIRPI');
        $numeroIRPF                      = new TEntry('numeroIRPF');
        $numeroProcessoI                 = new TEntry('numeroProcessoI');
        $numeroProcessoF                 = new TEntry('numeroProcessoF');
        $validadeI                       = new TDate('validadeI');
        $validadeF                       = new TDate('validadeF');
        $nomeI                           = new TEntry('nomeI');
        $nomeF                           = new TEntry('nomeF');


        // define the sizes
        $numeroSRPI->setSize(150);
        $numeroSRPF->setSize(150);
        $numeroIRPI->setSize(150);
        $numeroIRPF->setSize(150);
        $numeroProcessoI->setSize(150);
        $numeroProcessoF->setSize(150);
        $validadeI->setSize(85);
        $validadeF->setSize(85);
        //$aprovado->setSize(90);
        //mask
        $validadeI->setMask('dd/mm/yyyy');
        $validadeF->setMask('dd/mm/yyyy');


        $validadeF->setValue(date('d/m/Y'));
        //$validadeI->setNumericMask(0, '', '');
        // validations
        //$aprovado->addValidation('Aprovado', new TRequiredValidator);
        $validadeF->addValidation('Validade - Até', new TDateValidator, array('dd/mm/yyyy'));


        // add one row for each form field
        $table->addRowSet(new TLabel('Nº SRP'), new TLabel('De:'), $numeroSRPI, new TLabel('Até'), $numeroSRPF);
        $table->addRowSet(new TLabel('Nº IRP'), new TLabel('De:'), $numeroIRPI, new TLabel('Até'), $numeroIRPF);
        
        $table->addRowSet(new TLabel('Nº Processo'), new TLabel('De:'), $numeroProcessoI, new TLabel('Até'), $numeroProcessoF);
        $table->addRowSet(new TLabel('Emissão'), new TLabel('De:'), $validadeI, new TLabel('Até'), $validadeF);
        //$row = $table->addRow(); //Set( new TLabel('Aprovado:'), $aprovado );
        //$row->addCell(new TLabel('Aprovado:'));
        //$row->addCell($aprovado)->colspan = 4;

        $this->form->setFields(array($numeroProcessoI, $numeroProcessoF, $numeroIRPI, $numeroIRPF, $nomeI, $nomeF, $validadeI, $validadeF, $numeroSRPI, $numeroSRPF));


        //$aprovado->addItems(array('1' => 'Sim', '0' => 'Não', '%' => 'Todos'));
        //$aprovado->setValue('%');
        //$aprovado->setLayout('horizontal');

        $generate_button = TButton::create('generate', array($this, 'onGenerate'), _t('Generate'), 'ico_apply.png');
        $this->form->addField($generate_button);

        // add a row for the form action
        $table->addRowSet($generate_button, '', '', '', '')->class = 'tformaction';

        parent::add($this->form);
    }

    function header() {
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->SetFont('Times', 'B', 18);
        $this->pdf->Cell(0, 5, utf8_decode('Relatório de Srp'), 0, 1, 'L');
        $this->pdf->ln(2);
        $this->pdf->Cell(0, 0, '', 'T', 1);
    }

    function footer() {
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(0, 5, utf8_decode(date('d/m/Y h:i:s')), 'T');
    }

    function srpHeader() {
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nº SRP: ')), 5, utf8_decode('Nº SRP:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(26, 5, utf8_decode($this->data->numeroSRP), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nº IRP: ')), 5, utf8_decode('Nº IRP:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(26, 5, utf8_decode($this->data->numeroIRP), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nº Processo: ')), 5, utf8_decode('Nº Processo:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(45, 5, utf8_decode($this->data->numeroProcesso), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Emissão: ')), 5, utf8_decode('Emissão:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(35, 5, utf8_decode(TDate::date2br($this->data->validade)), 0, 1, 'L');
        
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Nome: ')), 5, utf8_decode('Nome:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(100, 5, utf8_decode($this->data->nome), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell($this->pdf->GetStringWidth(utf8_decode('Natureza: ')), 5, utf8_decode('Natureza:'), 0, 0, 'L');
        $this->pdf->SetFont('Times', '', 12);
        $this->pdf->Cell(50, 5, utf8_decode($this->data->natureza->descricao), 0, 1, 'L');
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell(20, 5, utf8_decode('Nº Item'), 0, 0, 'L');
        $this->pdf->Cell(90, 5, utf8_decode('Descrição Sumária'), 0, 0, 'L');
        $this->pdf->Cell(15, 5, utf8_decode('Qtd. Inicial'   ), 0, 0, 'R');
        $this->pdf->Cell(35, 5, utf8_decode('Qtd. Disponível'), 0, 0, 'R');
        $this->pdf->Cell(0 , 5, utf8_decode('Valor Unitário' ), 0, 1, 'R');
        $this->pdf->Cell(0 , 0, '', 'T', 1);
    }

    function srpItens() {
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
            $this->pdf->Cell(15, 5, utf8_decode($item->quantidadeDisponivel), 0, 0, 'R');
            $this->pdf->Cell(35, 5, utf8_decode($item->estoqueDisponivel), 0, 0, 'R');
            $this->pdf->Cell(0, 5, utf8_decode(number_format($item->valorUnitario, 2, ',', '.')), 0, 1, 'R');
            $totalItem++;
        }


        //imprime o rodapé
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell(0, 0, '', 'T', 1);
        $this->pdf->Cell(15, 5, '', 0, 0);
        $this->pdf->Cell(50, 5, utf8_decode('Quantidade de Itens: ' . $totalItem), 0, 1);
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

            $validadeI = $formdata->validadeI;
            $validadeF = TDate::date2us($formdata->validadeF);

            if (!$validadeI===0) {
                $validadeI = '0';
            } else {
                $validadeI = TDate::date2us($validadeI);
            }
            

            $this->form->validate();


            $repository = new TRepository('Srp');
            $criteria = new TCriteria;

            if ($formdata->numeroProcessoI && $formdata->numeroProcessoF) {
                $criteria->add(new TFilter('numeroProcesso', 'BETWEEN', "{$formdata->numeroProcessoI}", "{$formdata->numeroProcessoF}"));
            }
            if ($formdata->validadeI && $formdata->validadeF) {
                $criteria->add(new TFilter('validade', 'between', "{$validadeI}", "{$validadeF}"));
            }
            if ($formdata->numeroSRPI && $formdata->numeroSRPF) {
                $criteria->add(new TFilter('numeroSRP', 'between', "{$formdata->numeroSRPI}", "{$formdata->numeroSRPF}"));
            }
            if ($formdata->numeroIRPI && $formdata->numeroIRPF) {
                $criteria->add(new TFilter('numeroIRP', 'between', "{$formdata->numeroIRPI}", "{$formdata->numeroIRPF}"));
            }


            $srps = $repository->load($criteria, true);
            if ($srps) {

                $this->pdf = new FPDF();
                $this->pdf->AliasNbPages();
                $this->pdf->SetMargins(10, 10, 10);
                $this->pdf->setHeaderCallback(array($this, 'header'));
                $this->pdf->setFooterCallback(array($this, 'footer'));
                $this->pdf->AddPage();
                foreach ($srps as $srp) {
                    $this->data = $srp;
                    $this->srpHeader();
                    $this->srpItens();
                }

                if (!file_exists("app/output/RelatorioSrp.pdf") OR is_writable("app/output/RelatorioSrp.pdf")) {
                    $this->pdf->Output("app/output/RelatorioSrp.pdf");
                } else {
                    throw new Exception('Permissão negada' . ': ' . "app/output/RelatorioSrp.pdf");
                }

                parent::openFile("app/output/RelatorioSrp.pdf");

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
