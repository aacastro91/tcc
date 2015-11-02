<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBSeekButton;

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
    private $cidade;
    private $gerente;
    private $diretor;
    private $ImprimiNoRodape = false;
    var $B;
    var $I;
    var $U;
    var $HREF;

    static function cmp($a, $b) {
        $au = strtoupper($a->numeroItem);
        $bu = strtoupper($b->numeroItem);
        if ($au == $bu) {
            return 0;
        }
        return ($au > $bu) ? +1 : -1;
        //return strcmp($a->fornecedor, $b->fornecedor);
    }

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
        //$cidade = new TEntry('cidade');
        $emissao = new TEntry('emissao');
        $campusID = new TEntry('campusID'); //TDBSeekButton('campusID', 'saciq', 'doc_cessao_form', 'Campus', 'nome', 'campusID', 'campusNome');
        $campusNome = new TEntry('campusNome');
        $gerente = new TEntry('gerente');
        $diretor = new TEntry('diretor');
        $cessao_id = new THidden('cessao_id');

        // define the sizes
        $memorando->setSize(300);
        //$cidade->setSize(200);
        $emissao->setSize(90);
        $campusID->setSize(50);
        $campusID->setEditable(false);
        $campusNome->setSize(226);
        $campusNome->setEditable(false);
        $gerente->setSize(300);
        $diretor->setSize(300);

        //mascara
        $emissao->setMask('dd/mm/yyyy');
        $emissao->setValue(date('d/m/Y'));

        //validadores
        $memorando->addValidation('Memorando', new TRequiredValidator());
        $emissao->addValidation('Emissão', new TRequiredValidator());
        $campusID->addValidation('Destino', new TRequiredValidator());
        $gerente->addValidation('Gerente Administrativo(a)', new TRequiredValidator());
        $diretor->addValidation('Diretor(a) Geral', new TRequiredValidator());

        $value = TSession::getValue('doc_cessao_form_cessao_id');
        if (isset($value)) {
            $cessao_id->setValue($value);
        }

        // add one row for each form field
        $table->addRowSet(new TLabel('Memorando:'), $memorando);
        //$table->addRowSet(new TLabel('Cidade:'), $cidade);
        $table->addRowSet(new TLabel('Emissão:'), $emissao);
        //$table->addRowSet(new TLabel('Destino:'), $destino);
        //$row = $table->addRow();
        $box = new THBox();
        $box->add($campusID);
        $box->add($campusNome)->style = 'width: 75%; display : inline-block;';
        //$row->addCell($box)->colspan = 2;
        $table->addRowSet(new TLabel('Destino:'), $box);
        $table->addRowSet(new TLabel('Gerente Administrativo(a):'), $gerente);
        $table->addRowSet(new TLabel('Diretor(a):'), $diretor);
        $table->addRowSet($cessao_id);

        $this->form->setFields(array($memorando, /* $cidade, */ $emissao, $campusID, $campusNome, $gerente, $diretor, $cessao_id));


        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('Cessao_filter_data'));

        // create two action buttons to the form
        $generate_button = TButton::create('generate', array($this, 'onGenerate'), 'Gerar', 'fa:file-pdf-o');
        $back_button = TButton::create('back', array('DocCessaoList', 'onReload'), 'Voltar', 'ico_back.png');
        //$new_button  = TButton::create('new',  array('CessaoForm', 'onEdit'), 'Novo', 'ico_new.png');

        $this->form->addField($generate_button);
        $this->form->addField($back_button);

        $buttons_box = new THBox;
        $buttons_box->add($generate_button);
        $buttons_box->add($back_button);
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
        $data = new stdClass();
        if (!isset($param['key'])) {
            return;
        }

        $value = $param['key'];
        TSession::setValue('doc_cessao_form_cessao_id', $value);
        $data->cessao_id = $value;
        $this->loaded = true;

        if (isset($value)) {
            try {
                TTransaction::open('saciq');

                $cessao = new Cessao($value);
                $data->campusID = $cessao->campus_id;
                $data->campusNome = $cessao->campus->nome;
                TTransaction::close();
            } catch (Exception $e) {
                TTransaction::rollback();
            }
        }
        $this->form->setData($data);
    }

    public function onGenerate($param) {
        //var_dump($param);

        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';

        $data = $this->form->getData();

        $id = $data->cessao_id;
        $this->pdf = new FPDF();
        $this->pdf->AliasNbPages();
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        try {
            TTransaction::open('saciq');
            $this->form->validate();

            $cessao = new Cessao($id);
            $itens_list = $cessao->getItems();

            if (count($itens_list) == 0) {
                new TMessage('error', 'Nenhum item encontrado na cessão');
                $this->form->sendData('doc_cessao_form', $data);
                return;
            }

            $memorando = utf8_decode($data->memorando);
            $emissao = TDate::date2us($data->emissao);

            $repository = new TRepository('Campus');
            $criteria = new TCriteria();
            $criteria->add(new TFilter('sigla', '=', CAMPUS));

            $campus_list = $repository->load($criteria);
            $campus = $campus_list[0];

            $this->cidade = $campus->nome;
            $destino = strtoupper($data->campusNome);
            $this->gerente = $data->gerente;
            $this->diretor = $data->diretor;



            $emissao = strtotime($emissao);
            $mes = ucfirst(strftime('%B', $emissao));
            $emissao = $this->cidade . ', ' . strftime(" %d de {$mes} de %Y.", $emissao);
            $srp = $cessao->srp->numeroSRP;
            $natureza = $cessao->srp->natureza->descricao;
            $nomeSrp = $cessao->srp->nome;

            //$this->pdf->Open();
            $this->pdf->SetMargins(25, 20, 25);
            $this->pdf->setHeaderCallback(array($this, 'Header'));
            //$this->pdf->setFooterCallback(array($this, 'Footer'));

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
            $width = array(18, 107, 38);
            $this->pdf->MultiCell($width[0], 10, utf8_decode('ITEM'), 1, 'C');
            $this->pdf->SetXY($x += $width[0], $y);
            $this->pdf->MultiCell($width[1], 10, utf8_decode("DESCRIÇÃO"), 1, 'C');
            $this->pdf->SetXY($x += $width[1], $y);
            $this->pdf->MultiCell($width[2], 10, utf8_decode('QUANT.'), 1, 'C');
            $this->pdf->SetFont('Times', '', 12);
            $this->pdf->ln(0);
            $y = $this->pdf->GetY();
            $x = $this->pdf->GetX();


            usort($itens_list, array("DocCessaoForm", "cmp"));

            //preencher a tabela
            foreach ($itens_list as $item) {
                $numeroItem = $item->numeroItem;
                $descricaoSumaria = substr($item->descricaoSumaria, 0, 80);
                $quantidade = $item->quantidade;

                $t1 = $this->pdf->GetStringWidth($descricaoSumaria);
                $tamanhoTexto = $t1;
                $tamanhoDesc = $width[1];
                $qtdLinha = 1;
                $offset = 0;
                $atualSize = 0;
                while (true) {
                    $pos = strpos($descricaoSumaria, ' ', $offset);
                    if ($pos === FALSE) {
                        while ($tamanhoTexto > $tamanhoDesc) {
                            $qtdLinha++;
                            $tamanhoTexto -= $tamanhoDesc;
                        }
                        break;
                    }
                    $textCompare = substr($descricaoSumaria, $offset, $pos - $offset);
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

                if ($qtdLinha == 1) {
                    $qtdLinha = 2;
                }

                $alturaLinha = 5 * $qtdLinha;

                if ($this->pdf->GetY() + $alturaLinha > 280) {
                    $this->pdf->Cell(array_sum($width), 0, '', 'T');
                    $this->pdf->AddPage();
                    $y = $this->pdf->GetY();
                    $x = $this->pdf->GetX();
                }

                $this->pdf->MultiCell($width[0], $alturaLinha, utf8_decode($numeroItem), 'LRT', 'C');
                $this->pdf->SetXY($x += $width[0], $y);
                $this->pdf->MultiCell($width[1], 5, utf8_decode($descricaoSumaria), 'LRT', 'J');
                $this->pdf->SetXY($x += $width[1], $y);
                $this->pdf->MultiCell($width[2], $alturaLinha, utf8_decode($quantidade), 'LRT', 'C');
                $this->pdf->Ln(0);
                $y = $this->pdf->GetY();
                $x = $this->pdf->GetX();
            }
            $this->pdf->Cell(array_sum($width), 0, '', 'T');

            if ($this->pdf->GetY() + $alturaLinha > 210) {
                $this->pdf->AddPage();
                $this->ImprimiNoRodape = false;
            } else {
                $this->ImprimiNoRodape = true;
            }


            $this->Footer();


            if (!file_exists("app/output/doc.pdf") OR is_writable("app/output/doc.pdf")) {
                $this->pdf->Output("app/output/doc.pdf");
            } else {
                throw new Exception('Permissão negada' . ': ' . "app/output/doc.pdf");
            }
            
            parent::openFile('app/output/doc.pdf');
            $this->form->sendData('doc_cessao_form', $data);

            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
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
        $this->pdf->Cell(0, 5, utf8_decode($this->cidade), 0, 0, 'L');
        // Line break
        $this->pdf->Ln(10);
    }

// Page footer
    function Footer() {

        if ($this->ImprimiNoRodape) {
            $this->pdf->SetAutoPageBreak(false, 0);
            $this->pdf->SetXY(43, -95);
        } else {
            $this->pdf->ln(15);
            $this->pdf->SetX(43);
        }
        $this->pdf->Cell(0, 5, 'Atenciosamente,', 0, 0);
        $this->pdf->ln(20);
        $this->pdf->SetX(43);
        $this->pdf->Cell(0, 5, utf8_decode("{$this->gerente}"), 0, 1);
        $this->pdf->SetX(43);
        $this->pdf->Cell(0, 5, 'Gerente Administrativo(a)', 0, 1);
        $this->pdf->ln(10);
        $this->pdf->SetX(43);
        $this->pdf->Cell(0, 5, 'De Acordo, ____/_____/_______', 0, 1);
        $this->pdf->ln(25);
        $this->pdf->SetX(43);
        $this->pdf->Cell(0, 5, utf8_decode("{$this->diretor}"), 0, 1);
        $this->pdf->SetX(43);
        $this->pdf->Cell(0, 5, 'Diretor(a) Geral', 0, 1);
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
