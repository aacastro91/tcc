<?php

require_once 'excel_reader2.php';

class TExcelImport{
    
    var $data;
    
    public function __construct($filename) {
        $data = new Spreadsheet_Excel_Reader($filename);
    }
    
}