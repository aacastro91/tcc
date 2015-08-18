<?php

error_reporting(E_ALL ^ E_NOTICE);

class TExcelImport {

    private $data;

    public function __construct($filename) {
        require_once 'excel_reader2.php';
        $this->data = new Spreadsheet_Excel_Reader($filename);
    }

    public function dump() {
        echo $this->data->dump(true, true);
    }

}
