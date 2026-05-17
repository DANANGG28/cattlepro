<?php
/**
 * SimpleXLSX - Lightweight XLSX parser
 * Compatible with PHP 5.6+
 * Based on SimpleXLSX by Sergey Shuchkin
 */

class SimpleXLSX {
    private $sheets = [];
    private $sheetNames = [];
    private $sharedStrings = [];
    private static $error = '';
    
    public static function parse($filename) {
        $xlsx = new self();
        
        if (!file_exists($filename)) {
            self::$error = 'File tidak ditemukan';
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($filename) !== true) {
            self::$error = 'Gagal membuka file XLSX';
            return false;
        }
        
        // Read shared strings
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            if ($xml && $xml->si) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $xlsx->sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $run) {
                            if (isset($run->t)) {
                                $text .= (string)$run->t;
                            }
                        }
                        $xlsx->sharedStrings[] = $text;
                    }
                }
            }
        }
        
        // Read workbook to get sheet names
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        if ($workbook && $workbook->sheets && $workbook->sheets->sheet) {
            foreach ($workbook->sheets->sheet as $sheet) {
                $xlsx->sheetNames[] = (string)$sheet['name'];
            }
        }
        
        // Read first worksheet
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
        
        if (!$xml) {
            self::$error = 'Gagal membaca worksheet';
            return false;
        }
        
        // Parse rows
        $data = [];
        if (isset($xml->sheetData) && isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $row) {
                $row_data = [];
                $col_index = 0;
                
                if (isset($row->c)) {
                    foreach ($row->c as $cell) {
                        // Get column index from cell reference (e.g., "A1" -> 0)
                        $cell_ref = (string)$cell['r'];
                        $current_col = $xlsx->columnIndex($cell_ref);
                        
                        // Fill empty columns
                        while ($col_index < $current_col) {
                            $row_data[] = '';
                            $col_index++;
                        }
                        
                        $value = '';
                        if (isset($cell->v)) {
                            $value = (string)$cell->v;
                            
                            // Check cell type
                            if (isset($cell['t'])) {
                                $type = (string)$cell['t'];
                                
                                // Shared string
                                if ($type === 's') {
                                    $index = (int)$value;
                                    $value = isset($xlsx->sharedStrings[$index]) ? $xlsx->sharedStrings[$index] : '';
                                }
                                // Boolean
                                elseif ($type === 'b') {
                                    $value = $value ? 'TRUE' : 'FALSE';
                                }
                            }
                        }
                        
                        $row_data[] = $value;
                        $col_index++;
                    }
                }
                
                $data[] = $row_data;
            }
        }
        
        $xlsx->sheets[0] = $data;
        
        return $xlsx;
    }
    
    public function rows($sheet_index = 0) {
        return isset($this->sheets[$sheet_index]) ? $this->sheets[$sheet_index] : [];
    }
    
    public static function parseError() {
        return self::$error;
    }
    
    private function columnIndex($cell_ref) {
        // Extract column letter from cell reference (e.g., "A1" -> "A")
        preg_match('/^([A-Z]+)/', $cell_ref, $matches);
        if (!isset($matches[1])) {
            return 0;
        }
        
        $col = $matches[1];
        $index = 0;
        $len = strlen($col);
        
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        
        return $index - 1;
    }
}
