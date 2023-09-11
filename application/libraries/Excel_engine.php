<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PhpExcelEngine
 *
 * @author dev
 */

require APPPATH . 'third_party/PHPExcel/PHPExcel.php';
class Excel_engine {
    //put your code here
        
    public function __construct() {        
    }
    
    public function read_excel_to_array($file = ""){
        if(empty($file)) return array();
        
        $objPHPExcel = PHPExcel_IOFactory::load($file);
     
        $dataArr = array();

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= $highestRow; ++ $row) {
                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataArr[$row][$col] = $val;
                }
            }
        }

        return $dataArr;
    }
}
