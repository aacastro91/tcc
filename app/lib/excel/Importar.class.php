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
include_once ('app/lib/excel/Classes/PHPExcel.php');

class Importar {

    private $objPHPExcel = null;
    private $activeRow = 3;

    public function __construct($inputFileName) {
        $objReader = new PHPExcel_Reader_Excel2007();
        $objReader->setReadDataOnly(true);

        $this->objPHPExcel = $objReader->load($inputFileName);
    }
    
    public function setActiveRow($row){
        $this->activeRow = $row;
    }
    
    public function isValidRow(){
        return !($this->getFornecedor() == 'CANCELADO');
    }

        private function getColumnCount(){
        
        for ($i = 0; ; $i++){
            $value = $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, 2)->getValue();
            if (!isset($value))
                break;
        }
        return $i;
    }
    
    public function eof(){
        return false;
    }
    
    public function getRowArray($row){
        $count = $this->getColumnCount();
        $startCoordinate = $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0     , $row)->getCoordinate();
        $endCoordinate   = $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($count -1, $row)->getCoordinate();
        return $this->objPHPExcel->getActiveSheet()->rangeToArray($startCoordinate.':'.$endCoordinate);
        
    }
    
    private function getColumnByName($name){
        $row = $this->getRowArray(2);
        for ($i = 0; $i < count($row[0])-1 ; $i++){
            if ($row[0][$i] == $name){
                
                return $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $this->activeRow);
            }
        }
        return null;
    }
    
    public function getNomeProcesso(){
        return $this->getColumnByName('NOME DO PROCESSO')->getValue();
    }
    
    public function getNumeroSubElemento(){
        return $this->getColumnByName('NÚMERO SUBELEMENTO')->getValue();
    }
    
    public function getDescricaoSubElemento(){
        return $this->getColumnByName('DESCRIÇÃO SUBELEMENTO')->getValue();
    }
    
    public function getNroIRP(){
        return $this->getColumnByName('Nº IRP')->getValue();
    }
    
    public function getNroSRP(){
        return $this->getColumnByName('Nº SRP')->getValue();
    }
    
    public function getNumeroProcesso(){
        return $this->getColumnByName('NÚMERO DO PROCESSO')->getValue();
    }
    
    public function getUasgGerenciadora(){
        return $this->getColumnByName('UASG GERENCIADORA')->getValue();
    }
    
    public function getValidadeAta(){
        return PHPExcel_Style_NumberFormat::toFormattedString($this->getColumnByName('VALIDADE DA ATA')->getValue(),'YYYY-MM-DD');
    }
    
    public function getItem(){
        return $this->getColumnByName('ITEM')->getValue();
    }
    
    public function getDescricaoSumaria(){
        return $this->getColumnByName('DESCRIÇÃO SUMÁRIA')->getValue();
    }
    
    public function getDescricaoCompleta(){
        return $this->getColumnByName('DESCRIÇÃO COMPLETA')->getValue();
    }
    
    public function getDescricaoPosLicitacao(){
        return $this->getColumnByName('DESCRIÇÃO PÓS-LICITAÇÃO')->getValue();
    }
    
    public function getUnidadeDeMedida(){
        return $this->getColumnByName('UNIDADE DE MEDIDA')->getValue();
    }
    
    public function getValorUnitarioLicitado(){
        return $this->getColumnByName('VALOR UNITÁRIO LICITADO')->getValue();
    }
    
    public function getFornecedor(){
        return $this->getColumnByName('FORNECEDOR')->getValue();
    }
    
    public function getCNPJ(){
        return $this->getColumnByName('CNPJ')->getValue();
    }
    
    public function getFabricante(){
        return $this->getColumnByName('FABRICANTE')->getValue();
    }
    
    public function getMarca(){
        return $this->getColumnByName('MARCA')->getValue();
    }
        
    public function getOrgao($orgaoParticipante){
        if (!isset($orgaoParticipante)) {
            return NULL;
        }
        
        $value = $this->getColumnByName($orgaoParticipante)->getValue();
        
        if (is_string($value)){
            return NULL;
        }
        
        return $this->getColumnByName($orgaoParticipante)->getValue();
    }

}
