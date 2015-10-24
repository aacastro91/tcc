<?php

use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
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
 * Description of DocCessaoForm
 *
 * @author Anderson
 */
class DocCessaoForm extends TPage {

    private $form;
    private $loaded;
    private $pdf;
    var $B;
    var $I;
    var $U;
    var $HREF;

    function __construct() {
        parent::__construct();
        $this->form = new TForm('doc_cessao_form');
        $this->form->style = 'width : 500px;';
        $this->form->class = 'tform';

        $table = new TTable;
        $table->width = '100%';
        $this->form->add($table);

        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell(new TLabel('Gerar Documentos da Cessão'))->colspan = 2;

        // cria os campos do formulário
        $memorando = new TEntry('memorando');
        $cidade = new TEntry('cidade');
        $emissao = new TEntry('emissao');
        $destino = new TEntry('destino');
        $gerente = new TEntry('gerente');
        $diretor = new TEntry('diretor');
        $cessao_id = new THidden('cessao_id');

        // define the sizes
        $memorando->setSize(200);
        $cidade->setSize(200);
        $emissao->setSize(200);
        $destino->setSize(200);
        $gerente->setSize(200);
        $diretor->setSize(200);

        //mascara
        $emissao->setMask('dd/mm/yyyy');

        //valors padrao
        $cidade->setValue('Capivari');
        $emissao->setValue(date('d/m/Y'));
        $value = TSession::getValue('doc_cessao_form_cessao_id');
        if (isset($value)) {
            $cessao_id->setValue($value);
        }

        // add one row for each form field
        $table->addRowSet(new TLabel('Memorando:'), $memorando);
        $table->addRowSet(new TLabel('Cidade:'), $cidade);
        $table->addRowSet(new TLabel('Emissão:'), $emissao);
        $table->addRowSet(new TLabel('Destino:'), $destino);
        $table->addRowSet(new TLabel('Gerente:'), $gerente);
        $table->addRowSet(new TLabel('Diretor:'), $diretor);
        $table->addRowSet($cessao_id);



        $this->form->setFields(array($memorando, $cidade, $emissao, $destino, $gerente, $diretor, $cessao_id));


        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('Cessao_filter_data'));

        // create two action buttons to the form
        $find_button = TButton::create('generate', array($this, 'onGenerate'), 'Gerar', 'fa:file-pdf-o');
        //$new_button  = TButton::create('new',  array('CessaoForm', 'onEdit'), 'Novo', 'ico_new.png');

        $this->form->addField($find_button);
        //$this->form->addField($new_button);

        $buttons_box = new THBox;
        $buttons_box->add($find_button);
        //$buttons_box->add($new_button);
        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;

        // create the page container
        //$container = TVBox::pack($this->form);
        parent::add($this->form);
    }

    public function onReload($param) {
        //if (!isset($param['key'])){
        //    AdiantiCoreApplication::gotoPage('DocCessaoList'); 
        // }
        if (!isset($param['key'])) {
            return;
        }

        $value = $param['key'];
        TSession::setValue('doc_cessao_form_cessao_id', $value);
        $this->loaded = true;
    }

    public function onGenerate($param) {
        //setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        //var_dump($param);

        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';

        $data = $this->form->getData();
        $id = $data->cessao_id;
        $this->pdf = new FPDF();
        $this->pdf->AliasNbPages();
        try {
            TTransaction::open('saciq');

            $cessao = new Cessao($id);

            $memorando = utf8_decode($data->memorando);
            $emissao = TDate::date2us($data->emissao);
            $cidade = $data->cidade;
            $destino = $data->destino;

            $emissao = strtotime($emissao);
            $mes = ucfirst(strftime('%B', $emissao));
            $emissao = $cidade . ', ' . strftime(" %d de {$mes} de %Y.", $emissao);
            $srp = $cessao->srp->numeroSRP;
            $natureza = $cessao->srp->natureza->descricao;
            $nomeSrp = $cessao->srp->nome;






            //$this->pdf->Open();
            $this->pdf->SetMargins(25, 20, 25);
            $this->pdf->setHeaderCallback(array($this, 'Header'));
            $this->pdf->AddPage();
            $this->pdf->SetFont('Times', '', 12);
            //$this->pdf->Cell(15);
            $this->pdf->Cell(0, 5, "{$memorando}", 0, 1);
            $this->pdf->Ln(10);
            $this->pdf->Cell(0, 5, $emissao, 0, 0, 'R');
            $this->pdf->Ln(10);
            $this->pdf->Cell(0, 5, utf8_decode('À'), 0, 1);
            $this->pdf->Cell(0, 5, utf8_decode("GERÊNCIA ADMINISTRATIVA DO CAMPUS {$destino}"));
            $this->pdf->Ln(10);
            $this->pdf->SetFont('Times', 'B', 12);
            $this->pdf->MultiCell(0, 5, utf8_decode("ASSUNTO: CESSÃO DE QUANTITATIVO - SRP {$srp} - {$natureza} - {$nomeSrp}"));
            $this->pdf->SetFont('Times', '', 12);
            $this->pdf->Ln(15);
            $this->pdf->SetX(21);
            $this->WriteHTML(utf8_decode("1.Conforme solicitação, autorizamos a utilização do quantitativo abaixo referido," .
                            "referente a estimativa do Campus Capivari para a <B>SRP {$srp} - {$natureza} - {$nomeSrp}</B>"));
            $this->pdf->Ln(10);

            //cabecalho da tabela
            $y = $this->pdf->GetY();
            $x = $this->pdf->GetX();
            $this->pdf->SetFont('Times', 'B', 12);
            $width = array(18, 42, 65, 38);
            $this->pdf->MultiCell($width[0], 10, utf8_decode('ITEM'), 1, 'C');
            $this->pdf->SetXY($x += $width[0], $y);
            $this->pdf->MultiCell($width[1], 5, utf8_decode("DESCRIÇÃO \n SUMÁRIA"), 1, 'C');
            $this->pdf->SetXY($x += $width[1], $y);
            $this->pdf->MultiCell($width[2], 5, utf8_decode("DESCRIÇÃO \nPÓS-LICITAÇÃO"), 1, 'C');
            $this->pdf->SetXY($x += $width[2], $y);
            $this->pdf->MultiCell($width[3], 10, utf8_decode('QUANT.'), 1, 'C');
            $this->pdf->SetFont('Times', '', 12);
            $this->pdf->ln(0);
            $y = $this->pdf->GetY();
            $x = $this->pdf->GetX();
            //preencher a tabela
            foreach ($cessao->getItems() as $item) {
                $numeroItem = $item->numeroItem;
                $descricaoSumaria = substr($item->descricaoSumaria,0 ,80);
                $descricaoPosLicitacao = substr($item->descricaoPosLicitacao, 0, 80);
                $quantidade = $item->quantidade;

                $t1 = $this->pdf->GetStringWidth($descricaoSumaria);
                $t2 = $this->pdf->GetStringWidth($descricaoPosLicitacao);
                $tamanhoTexto = $t1 > $t2 ? $t1 : $t2;
                $tamanhoDesc = $t1 > $t2 ? $width[1] : $width[2];
                $text = $t1 > $t2 ? $descricaoSumaria : $descricaoPosLicitacao;
                $qtdLinha = 1;
                $offset = 0;
                $atualSize = 0;
                while (true) {
                    $pos = strpos($text, ' ', $offset);
                    if ($pos === FALSE) {
                        while ($tamanhoTexto > $tamanhoDesc) {
                            $qtdLinha++;
                            $tamanhoTexto -= $tamanhoDesc;
                        }
                        break;
                    }
                    $textCompare = substr($text, $offset, $pos - $offset);
                    $textSize = $this->pdf->GetStringWidth($textCompare . ' ');

                    if ($textSize + $atualSize > $tamanhoDesc) {
                        $qtdLinha++;
                        $tamanhoTexto -= $atualSize;
                        $atualSize = 0;
                    } else {
                        $atualSize+= $textSize;
                        $offset = $pos + 1;
                    }
                }

                $alturaLinha = 5 * $qtdLinha;

                $this->pdf->MultiCell($width[0], $alturaLinha, utf8_decode($numeroItem), 'LRT', 'C');
                $this->pdf->SetXY($x += $width[0], $y);
                $this->pdf->MultiCell($width[1], 5, utf8_decode($descricaoSumaria), 'LRT', 'J');
                $this->pdf->SetXY($x += $width[1], $y);
                $this->pdf->MultiCell($width[2], 5, utf8_decode($descricaoPosLicitacao), 'LRT', 'J');
                $this->pdf->SetXY($x += $width[2], $y);
                $this->pdf->MultiCell($width[3], $alturaLinha, utf8_decode($quantidade), 'LRT', 'C');
                $this->pdf->Ln(0);
                $y = $this->pdf->GetY();
                $x = $this->pdf->GetX();
            }
            $this->pdf->Cell(array_sum($width), 0, '', 'T');







            $this->pdf->Output('app/output/doc.pdf');
            parent::openFile('app/output/doc.pdf');
            $this->form->sendData('doc_cessao_form', $data);


            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            new \Adianti\Widget\Dialog\TMessage('error', $e->getMessage());
        }
    }

    function Header() {
        $this->pdf->SetFont('Times', 'B', 12);
        //$this->pdf->Cell(15);
        $this->pdf->Cell(0, 5, utf8_decode('SERVIÇO PÚBLICO FEDERAL'), 0, 1, 'L');
        //$this->pdf->Cell(15);
        $this->pdf->Cell(0, 5, utf8_decode('INSTITUTO FEDERAL DE SÃO PAULO'), 0, 1, 'L');
        //$this->pdf->Cell(15);
        $this->pdf->SetFont('Times', 'BI', 12);
        $size = $this->pdf->GetStringWidth(utf8_decode('CAMPUS')) + $this->pdf->GetStringWidth(utf8_decode(' '));
        $this->pdf->Cell($size, 5, utf8_decode('CAMPUS'), 0, 0, 'L');
        $this->pdf->SetFont('Times', 'B', 12);
        $this->pdf->Cell(0, 5, utf8_decode('CAPIVARI'), 0, 0, 'L');
        // Line break
        $this->pdf->Ln(10);
    }

// Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->pdf->SetY(-15);
        // Arial italic 8
        $this->pdf->SetFont('Arial', 'I', 8);
        // Page number
        $this->pdf->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function OpenTag($tag, $attr) {
        // Opening tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = $attr['HREF'];
        if ($tag == 'BR')
            $this->pdf->Ln(5);
    }

    function CloseTag($tag) {
        // Closing tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
    }

    function SetStyle($tag, $enable) {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style .= $s;
        }
        $this->pdf->SetFont('', $style);
    }

    function WriteHTML($html) {
        // HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->pdf->Write(5, $e);
            }
            else {
                // Tag
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    // Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function show() {
        // check if the datagrid is already loaded
        if (!$this->loaded AND ( !isset($_GET['method']) OR $_GET['method'] !== 'onReload')) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }

}
