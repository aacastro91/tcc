<?php

use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

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
include_once('app/lib/excel/Classes/PHPExcel.php');

class Importar {

    private $activeSheet = null;
    private $activeRow = 3;
    //private $objReader = null;
    public $dataFile;
    private $referencia;

    public function __construct() {        
        //$this->objReader = new PHPExcel_Reader_Excel2007();
        //$this->objReader->setReadDataOnly(true);
        
        TTransaction::open('saciq');
        try {
            $this->referencia = array();
            $rep = new TRepository('Referencia');
            $import_name = $rep->load();
            if ($import_name){
                foreach ($import_name as $value) {
                    $this->referencia[$value->nome] = $value->referencia;
                }
            }
            else
            {
                throw new Exception('Arquivo de configuração inválido');
            }
            
            TTransaction::close();
        } catch (Exception $ex) {
            new TMessage('error', $ex->getMessage());
            TTransaction::rollback();
        }        
        
        //$objReader->setReadDataOnly(true);
    }

    public function loadFile($inputFileName) {
        //$objPHPExcel = 
        $this->activeSheet = PHPExcel_IOFactory::load($inputFileName)->getActiveSheet(); //$this->objReader->load($inputFileName)->getActiveSheet();
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
            return 'Referencia do campo "NATUREZA DE DESPESA" não encontrada no arquivo';
        if ($this->getNomeProcesso() == '')
            return 'Referencia do campo "NOME DO PROCESSO" não encontrada no arquivo';
        if ($this->getNumeroSubElemento() == '')
            return 'Referencia do campo "NÚMERO SUBELEMENTO" não encontrada no arquivo';
        if ($this->getDescricaoSubElemento() == '')
            return 'Referencia do campo "DESCRIÇÃO SUBELEMENTO" não encontrada no arquivo';
        if ($this->getNroIRP() == '')
            return 'Referencia do campo "Nº IRP" não encontrada no arquivo';
        if ($this->getNroSRP() == '')
            return 'Referencia do campo "Nº SRP" não encontrada no arquivo';
        if ($this->getNumeroProcesso() == '')
            return 'Referencia do campo "NÚMERO DO PROCESSO" não encontrada no arquivo';
        if ($this->getUasgGerenciadora() == '')
            return 'Referencia do campo "UASG GERENCIADORA" não encontrada no arquivo';
        if ($this->getValidadeAta() == '')
            return 'Referencia do campo "VALIDADE DA ATA" não encontrada no arquivo';
        if ($this->getItem() == '')
            return 'Referencia do campo "ITEM" não encontrada no arquivo';
        if ($this->getDescricaoSumaria() == '')
            return 'Referencia do campo "DESCRIÇÃO SUMÁRIA" não encontrada no arquivo';
        if ($this->getDescricaoCompleta() == '')
            return 'Referencia do campo "DESCRIÇÃO COMPLETA" não encontrada no arquivo';
        if ($this->getDescricaoPosLicitacao() == '')
            return 'Referencia do campo "DESCRIÇÃO PÓS-LICITAÇÃO" não encontrada no arquivo';
        if ($this->getUnidadeDeMedida() == '')
            return 'Referencia do campo "UNIDADE DE MEDIDA" não encontrada no arquivo';
        if ($this->getValorUnitarioLicitado() == '')
            return 'Referencia do campo "VALOR UNITÁRIO LICITADO" não encontrada no arquivo';
        if ($this->getFornecedor() == '')
            return 'Referencia do campo "FORNECEDOR" não encontrada no arquivo';
        if ($this->getCNPJ() == '')
            return 'Referencia do campo "CNPJ" não encontrada no arquivo';
        if ($this->getFabricante() == '')
            return 'Referencia do campo "FABRICANTE" não encontrada no arquivo';
        if ($this->getMarca() == '')
            return 'Referencia do campo "MARCA" não encontrada no arquivo';        
        return '';
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
        if (isset($this->referencia[$name]))
            $nomeArquivo = $this->referencia[$name];
        else
            $nomeArquivo = $name;
        
        $row = $this->getRowArray(2);
        for ($i = 0; $i < count($row) - 1; $i++) {
            if ($row[$i] == $nomeArquivo) {
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
