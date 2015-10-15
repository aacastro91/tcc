<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TDate;

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

include_once('app/lib/excel/Classes/PHPExcel.php');

/**
 * Description of Exportar
 *
 * @author Anderson
 */
class Exportar {

    //arquivo do excel, do tipo PHPExcel e é instanciado na criação da classe
    private $objPHPExcel;

    /*     * objeto que vai conter array de objetos com os dados a ser
     * salvo na tabela.
     * 
     * Usando esses objeto do tipo stdClass para ordenar por ordem de fornecedor
     */
    private $array_obj;

    function __construct() {
        $this->objPHPExcel = new PHPExcel();
    }

    static function cmp($a, $b) {
        $au = strtoupper($a->fornecedor);
        $bu = strtoupper($b->fornecedor);
        if ($au == $bu) {
            return 0;
        }
        return ($au > $bu) ? +1 : -1;
        //return strcmp($a->fornecedor, $b->fornecedor);
    }

    public function loadRequisicao($id) {
        try {
            //carrega a requisição
            $requisicao = new Requisicao($id);

            //adiciona os dados em um array de objeto
            foreach ($requisicao->getItems() as $item) {
                $obj = new stdClass();
                $obj->DataRequisicao = TDate::date2br($requisicao->emissao);
                $obj->NroProcAquisicao = $requisicao->numeroProcesso;
                $obj->NroSrp = $requisicao->srp->numeroSRP;
                $obj->ProcOrig = $requisicao->srp->numeroProcesso;
                $obj->UASG = $requisicao->srp->uasg;
                $obj->NomeLicitacao = $requisicao->srp->nome;
                $obj->SubElemento = $item->subelemento->id;
                $obj->ValidadeAta = $requisicao->srp->validade;
                $obj->PrazoEntrega = $item->prazoEntrega;
                $obj->EstimativoCampus = CAMPUS;
                $obj->OrcamentoCampus = CAMPUS;
                $obj->CampusDestino = CAMPUS;
                $obj->LocalEntrega = CAMPUS;
                $obj->Item = str_pad($item->numeroItem, 3, '0', STR_PAD_LEFT);
                $obj->DescricaoSumaria = $item->descricaoSumaria;
                $obj->QtdSolicitada = $item->quantidade;
                $obj->ValorLicitadoUnitario = $item->valorUnitario;
                $obj->fornecedor = $item->fornecedor->nome;
                $obj->cnpj = $item->fornecedor->cnpj;
                $obj->contrato = '';
                $obj->justificativa = $item->justificativa;
                $this->array_obj[] = $obj;
            }

            //ordena o array em ordem de fornecedor
            usort($this->array_obj, array("Exportar", "cmp"));

            return true;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    function mask($val, $mask) {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            }
            else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    public function createfile($filename) {
        //pega o usuário salvo na sessão
        $usuario = TSession::getValue('nome');
        //adiciona o autor no arquivo
        $this->objPHPExcel->getProperties()->setCreator($usuario)
                ->setLastModifiedBy($usuario);

        $sheet = $this->objPHPExcel->setActiveSheetIndex(0);

        $criteria = new TCriteria();
        $criteria->add(new TFilter('sigla', '=', CAMPUS));

        $rep = new TRepository('Campus');
        $campusRep = $rep->load($criteria);
        if (isset($campusRep[0])) {
            $campus = $campusRep[0];
        } else {
            throw new Exception('Erro na seleção do campus');
        }

        $cabecalho = "REQUISIÇÕES DA ATA DE REGISTO DE PREÇOS SRP " . $this->array_obj[0]->NroSrp . " (" . $this->array_obj[0]->NomeLicitacao . ") - " . $campus->nome;

        //style
        $style_titulo = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 16,
                'underline' => true,
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
                /* 'borders' => array(
                  'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  ),
                  ),
                  /* 'fill' => array(
                  'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                  'rotation' => 90,
                  'startcolor' => array(
                  'argb' => 'FFA0A0A0',
                  ),
                  'endcolor' => array(
                  'argb' => 'FFFFFFFF',
                  ),
                  ), */
        );

        $style_headers = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 12,
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ),
        );

        $style_data = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 12,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ),
        );

        $sheet->getStyle('A1:W1')->applyFromArray($style_titulo);
        $sheet->getStyle('A2:W2')->applyFromArray($style_headers);

        $sheet->getDefaultRowDimension()->setRowHeight(72.75);


        //define a largura das colunas
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(33);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(33);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(56);
        $sheet->getColumnDimension('G')->setWidth(19);
        $sheet->getColumnDimension('H')->setWidth(22);
        $sheet->getColumnDimension('I')->setWidth(24);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(12);
        $sheet->getColumnDimension('P')->setWidth(49);
        $sheet->getColumnDimension('Q')->setWidth(18);
        $sheet->getColumnDimension('R')->setWidth(20);
        $sheet->getColumnDimension('S')->setWidth(25);
        $sheet->getColumnDimension('T')->setWidth(84);
        $sheet->getColumnDimension('U')->setWidth(27);
        $sheet->getColumnDimension('V')->setWidth(25);
        $sheet->getColumnDimension('W')->setWidth(49);

        //adiciona o cabeçalho
        $sheet->setCellValue('A1', $cabecalho);
        $sheet->mergeCells('A1:W1');

        $sheet->setCellValue('A2', 'DATA REQUISIÇÃO');
        $sheet->setCellValue('B2', 'Nº PROC AQUISIÇÃO');
        $sheet->setCellValue('C2', 'Nº SRP');
        $sheet->setCellValue('D2', 'PROC ORIG');
        $sheet->setCellValue('E2', 'UASG');
        $sheet->setCellValue('F2', 'NOME DA LICITAÇÃO');
        $sheet->setCellValue('G2', 'SUBELEMENTO');
        $sheet->setCellValue('H2', 'VALIDADE DA ATA');
        $sheet->setCellValue('I2', 'PRAZO DE ENTREGA');
        $sheet->setCellValue('J2', 'ESTIMATIVO CAMPUS');
        $sheet->setCellValue('K2', 'ORÇAMENTO CAMPUS');
        $sheet->setCellValue('L2', 'CAMPUS DE DESTINO');
        $sheet->setCellValue('M2', 'LOCAL DE ENTREGA');
        $sheet->setCellValue('N2', "MARQUE COM UM 'X' NOS ITENS NÃO EMPENHADOS");
        $sheet->setCellValue('O2', 'ITEM');
        $sheet->setCellValue('P2', 'DESCRIÇÃO SUMÁRIA');
        $sheet->setCellValue('Q2', 'QTD SOLICITADA');
        $sheet->setCellValue('R2', 'VALOR LICITADO UNITÁRIO');
        $sheet->setCellValue('S2', 'VALOR LICITADO TOTAL');
        $sheet->setCellValue('T2', 'FORNECEDOR');
        $sheet->setCellValue('U2', 'CNPJ');
        $sheet->setCellValue('V2', 'CONTRATO');
        $sheet->setCellValue('W2', 'JUSTIFICATIVA DA AQUISIÇÃO POR ITEM');

        $row = 3;
        $fornecedor = $this->array_obj[0]->fornecedor;
        $fornecedor_startCel = "S{$row}";
        $fornecedor_endCel = "S{$row}";
        foreach ($this->array_obj as $obj) {
            if ($fornecedor != $obj->fornecedor){
                $sheet->setCellValueByColumnAndRow(18, $row, "=SUBTOTAL(9,{$fornecedor_startCel}:{$fornecedor_endCel})");
                $sheet->getStyleByColumnAndRow(18, $row)->getNumberFormat()->setFormatCode('_("R$ "* #.##0,00_);_("R$ "* (#.##0,00);_("R$ "* "-"??_);_(@_)');
                $sheet->setCellValueByColumnAndRow(19, $row, $fornecedor . ' Total');
                $sheet->getStyle("A{$row}:W{$row}")->applyFromArray($style_data);
                $fornecedor = $obj->fornecedor;                
                $row++;
                $fornecedor_startCel = "S{$row}";
            }
            $sheet->setCellValueByColumnAndRow( 0, $row, $obj->DataRequisicao);
            $sheet->setCellValueByColumnAndRow( 1, $row, $obj->NroProcAquisicao);
            $sheet->setCellValueByColumnAndRow( 2, $row, $obj->NroSrp);
            $sheet->setCellValueByColumnAndRow( 3, $row, $obj->ProcOrig);
            $sheet->setCellValueByColumnAndRow( 4, $row, $obj->UASG);
            $sheet->setCellValueByColumnAndRow( 5, $row, $obj->NomeLicitacao);
            $sheet->setCellValueByColumnAndRow( 6, $row, $obj->SubElemento);
            $sheet->setCellValueByColumnAndRow( 7, $row, $obj->ValidadeAta);
            $sheet->setCellValueByColumnAndRow( 8, $row, $obj->PrazoEntrega);
            $sheet->setCellValueByColumnAndRow( 9, $row, $obj->EstimativoCampus);
            $sheet->setCellValueByColumnAndRow(10, $row, $obj->OrcamentoCampus);
            $sheet->setCellValueByColumnAndRow(11, $row, $obj->CampusDestino);
            $sheet->setCellValueByColumnAndRow(12, $row, $obj->LocalEntrega);
            $sheet->setCellValueByColumnAndRow(13, $row, '');
            $sheet->setCellValueByColumnAndRow(14, $row, $obj->Item);
            $sheet->setCellValueByColumnAndRow(15, $row, $obj->DescricaoSumaria);
            $sheet->setCellValueByColumnAndRow(16, $row, $obj->QtdSolicitada);
            $sheet->setCellValueByColumnAndRow(17, $row, $obj->ValorLicitadoUnitario);
            $sheet->setCellValueByColumnAndRow(18, $row, "=Q{$row}*R{$row}");
            $sheet->getStyleByColumnAndRow(18, $row)->getNumberFormat()->setFormatCode('_("R$ "* #.##0,00_);_("R$ "* (#.##0,00);_("R$ "* "-"??_);_(@_)');
            $sheet->setCellValueByColumnAndRow(19, $row, $obj->fornecedor);
            $sheet->setCellValueByColumnAndRow(20, $row, $this->mask($obj->cnpj, '##.###.###/####-##'));
            $sheet->setCellValueByColumnAndRow(21, $row, '');
            $sheet->setCellValueByColumnAndRow(22, $row, $obj->justificativa);
            $sheet->getStyle("A{$row}:W{$row}")->applyFromArray($style_data);
            
            $fornecedor_endCel = "S{$row}";
            
            $row++;
        }
        //$sheet->setCellValueByColumnAndRow(18, $row, "=SUBTOTAL(9;{$fornecedor_startCel}:{$fornecedor_endCel})");
        //$sheet->setCellValueExplicitByColumnAndRow(18, $row, '=SUM(S3:S4)');
        $sheet->setCellValueByColumnAndRow(18, $row, "=SUBTOTAL(9,{$fornecedor_startCel}:{$fornecedor_endCel})");
        $sheet->getStyleByColumnAndRow(18, $row)->getNumberFormat()->setFormatCode('_("R$ "* #.##0,00_);_("R$ "* (#.##0,00);_("R$ "* "-"??_);_(@_)');
        
        $sheet->setCellValueByColumnAndRow(19, $row, $fornecedor . ' Total');
        $sheet->getStyle("A{$row}:W{$row}")->applyFromArray($style_data);
        $row++;
        
        $sheet->setCellValueByColumnAndRow(18, $row, "=SUBTOTAL(9,S3:{$fornecedor_endCel})");
        $sheet->getStyleByColumnAndRow(18, $row)->getNumberFormat()->setFormatCode('_("R$ "* #.##0,00_);_("R$ "* (#.##0,00);_("R$ "* "-"??_);_(@_)');
        $sheet->setCellValueByColumnAndRow(19, $row, 'Total Geral');
        $sheet->getStyle("A{$row}:W{$row}")->applyFromArray($style_data);
        $row++;

        $this->objPHPExcel->setActiveSheetIndex(0);


        /*
          // Redirect output to a client’s web browser (Excel2007)
          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
          header('Cache-Control: max-age=0');
          // If you're serving to IE 9, then the following may be needed
          header('Cache-Control: max-age=1');

          // If you're serving to IE over SSL, then the following may be needed
          header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
          header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
          header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
          header('Pragma: public'); // HTTP/1.0
         */
        $file = "app/output/" . $filename . ".xlsx";
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save($file);

        /*
          header("Pragma: public");
          header("Expires: 0"); // set expiration time
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
          header("Content-Length: " . filesize($file));
          header("Content-disposition: inline; filename=\"{$file}\"");
          header("Content-Transfer-Encoding: binary");

          //echo file_get_contents($file);
          readfile($file); */
    }

}
