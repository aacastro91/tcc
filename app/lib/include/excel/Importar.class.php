<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of importar
 *
 * @author Anderson
 */
include_once ('app/lib/include/excel/Classes/PHPExcel.php');

class Importar {

    private $activeSheet = null;
    private $activeRow = 3;
    private $objReader = null;
    public $dataFile;

    public function __construct() {
        $this->objReader = new PHPExcel_Reader_Excel2007();
        $this->objReader->setReadDataOnly(true);
        //$objReader->setReadDataOnly(true);
    }

    public function loadFile($inputFileName) {
        $this->activeSheet = $this->objReader->load($inputFileName)->getActiveSheet();
        $linha = 0;
        $coluna = 0;
        //echo '<table>' . "\n"; 
        foreach ($this->activeSheet->getRowIterator() as $row) {
            //echo '<tr>' . "\n";	
            $coluna = 0;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $this->dataFile[$coluna][$linha] = $cell->getCalculatedValue();
                $coluna++;
            }
            $linha++;
        }
    }

    public function setActiveRow($row) {
        $this->activeRow = $row;
    }

    public function nextRow() {
        $this->activeRow++;
    }

    public function isValidRow() {
        if ($this->getFornecedor() == 'CANCELADO')
            return false;
        if ($this->getOrgao(CAMPUS)== '')
            return false;
        
        return true;
    }
    
    public function isValidFile(){
        if ($this->getNaturezaDespesa()== '')
            return false;
        if ($this->getNomeProcesso() == '')
            return false;
        
        return true;
    }

    private function getColumnCount() {

        for ($i = 0; count($this->dataFile)-1; $i++) {
            $value = $this->dataFile[$i][1];
            if (!isset($value) || ($value == '')) {
                break;
            }
        }
        return $i;
    }

    public function eof() {
        if (isset($this->dataFile[0][$this->activeRow])){
            return false;
        }  else {
            return true;
        }
    }

    public function getRowArray($row) {
        $return = array();
        $count = @$this->getColumnCount();
        for ($i = 0; $i < $count; $i++){
            $return[] = $this->dataFile[$i][$row-1];
        }
        return $return;
    }

    private function getColumnByName($name) {
        $row = $this->getRowArray(2);
        for ($i = 0; $i < count($row) - 1; $i++) {
            if ($row[$i] == $name) {
                return $this->dataFile[$i][$this->activeRow-1];
            }
        }
        return null;
    }

    public function getNaturezaDespesa() {
        return $this->getColumnByName('NATUREZA DE DESPESA');
    }

    public function getNomeProcesso() {
        return $this->getColumnByName('NOME DO PROCESSO');
    }

    public function getNumeroSubElemento() {
        return $this->getColumnByName('NÚMERO SUBELEMENTO');
    }

    public function getDescricaoSubElemento() {
        return $this->getColumnByName('DESCRIÇÃO SUBELEMENTO');
    }

    public function getNroIRP() {
        return $this->getColumnByName('Nº IRP');
    }

    public function getNroSRP() {
        return $this->getColumnByName('Nº SRP');
    }

    public function getNumeroProcesso() {
        return $this->getColumnByName('NÚMERO DO PROCESSO');
    }

    public function getUasgGerenciadora() {
        return $this->getColumnByName('UASG GERENCIADORA');
    }

    public function getValidadeAta() {
        return PHPExcel_Style_NumberFormat::toFormattedString($this->getColumnByName('VALIDADE DA ATA'), 'YYYY-MM-DD');
    }

    public function getItem() {
        return $this->getColumnByName('ITEM');
    }

    public function getDescricaoSumaria() {
        return $this->getColumnByName('DESCRIÇÃO SUMÁRIA');
    }

    public function getDescricaoCompleta() {
        return $this->getColumnByName('DESCRIÇÃO COMPLETA');
    }

    public function getDescricaoPosLicitacao() {
        return $this->getColumnByName('DESCRIÇÃO PÓS-LICITAÇÃO');
    }

    public function getUnidadeDeMedida() {
        return $this->getColumnByName('UNIDADE DE MEDIDA');
    }

    public function getValorUnitarioLicitado() {
        return $this->getColumnByName('VALOR UNITÁRIO LICITADO');
    }

    public function getFornecedor() {
        return $this->getColumnByName('FORNECEDOR');
    }

    public function getCNPJ($onlyNumber = TRUE) {
        if ($onlyNumber){
            return preg_replace("/[^0-9]/", "", $this->getColumnByName('CNPJ'));
        }
        else{
            return $this->getColumnByName('CNPJ');
        }
    }

    public function getFabricante() {
        return $this->getColumnByName('FABRICANTE');
    }

    public function getMarca() {
        return $this->getColumnByName('MARCA');
    }

    public function getOrgao($orgaoParticipante) {
        if (!isset($orgaoParticipante)) {
            return NULL;
        }

        $value = $this->getColumnByName($orgaoParticipante);

        if (is_string($value)) {
            return NULL;
        }

        return $this->getColumnByName($orgaoParticipante);
    }

}
