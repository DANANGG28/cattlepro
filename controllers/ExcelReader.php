<?php
/**
 * ExcelReader - Simple Excel/CSV file reader
 * Supports: CSV, XLSX, XLS
 */

class ExcelReader {
    private $file_path;
    private $file_extension;
    private $data = [];
    
    const ALLOWED_EXTENSIONS = ['csv', 'xlsx'];
    const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    
    public function __construct($file_path) {
        $this->file_path = $file_path;
        $this->file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    }
    
    /**
     * Validate file before processing
     */
    public function validate() {
        $errors = [];
        
        // Check if file exists
        if (!file_exists($this->file_path)) {
            $errors[] = 'File tidak ditemukan.';
            return $errors;
        }
        
        // Check file extension
        if (!in_array($this->file_extension, self::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Format file tidak didukung. Gunakan CSV atau XLSX.';
        }
        
        // Check file size
        $file_size = filesize($this->file_path);
        if ($file_size > self::MAX_FILE_SIZE) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        }
        
        if ($file_size === 0) {
            $errors[] = 'File kosong.';
        }
        
        // Check if file is readable
        if (!is_readable($this->file_path)) {
            $errors[] = 'File tidak dapat dibaca.';
        }
        
        return $errors;
    }
    
    /**
     * Read file and return data as array
     */
    public function read() {
        $validation_errors = $this->validate();
        if (!empty($validation_errors)) {
            throw new Exception(implode(' ', $validation_errors));
        }
        
        switch ($this->file_extension) {
            case 'csv':
                return $this->readCSV();
            case 'xlsx':
            case 'xls':
                return $this->readExcel();
            default:
                throw new Exception('Format file tidak didukung.');
        }
    }
    
    /**
     * Read CSV file
     */
    private function readCSV() {
        $data = [];
        $handle = fopen($this->file_path, 'r');
        
        if (!$handle) {
            throw new Exception('Gagal membuka file CSV.');
        }
        
        // Try to detect delimiter
        $first_line = fgets($handle);
        rewind($handle);
        
        $delimiter = $this->detectDelimiter($first_line);
        
        while (($row = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
            $data[] = $row;
        }
        
        fclose($handle);
        
        if (empty($data)) {
            throw new Exception('File CSV kosong atau tidak valid.');
        }
        
        return $data;
    }
    
    /**
     * Detect CSV delimiter
     */
    private function detectDelimiter($line) {
        $delimiters = [',', ';', "\t", '|'];
        $delimiter_count = [];
        
        foreach ($delimiters as $delimiter) {
            $delimiter_count[$delimiter] = substr_count($line, $delimiter);
        }
        
        return array_search(max($delimiter_count), $delimiter_count);
    }
    
    /**
     * Read Excel file (XLSX/XLS) using SimpleXLSX library
     */
    private function readExcel() {
        // Check if SimpleXLSX library exists
        $simple_xlsx_path = __DIR__ . '/SimpleXLSX.php';
        
        if (file_exists($simple_xlsx_path)) {
            require_once $simple_xlsx_path;
            
            if ($this->file_extension === 'xlsx') {
                if ($xlsx = SimpleXLSX::parse($this->file_path)) {
                    return $xlsx->rows();
                } else {
                    throw new Exception('Gagal membaca file XLSX: ' . SimpleXLSX::parseError());
                }
            }
        }
        
        // Fallback: Try to read as XML for XLSX
        if ($this->file_extension === 'xlsx') {
            return $this->readXLSXManual();
        }
        
        // For XLS, suggest converting to XLSX or CSV
        throw new Exception('File XLS tidak didukung secara langsung. Silakan convert ke XLSX atau CSV terlebih dahulu.');
    }
    
    /**
     * Manual XLSX reader (basic implementation)
     */
    private function readXLSXManual() {
        $data = [];
        
        // XLSX is a ZIP file
        $zip = new ZipArchive();
        
        if ($zip->open($this->file_path) !== TRUE) {
            throw new Exception('Gagal membuka file XLSX. File mungkin corrupt.');
        }
        
        // Read shared strings
        $shared_strings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            if ($xml && $xml->si) {
                foreach ($xml->si as $val) {
                    $shared_strings[] = (string)$val->t;
                }
            }
        }
        
        // Read worksheet
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
        
        if (!$xml) {
            throw new Exception('Gagal membaca data dari file XLSX.');
        }
        
        // Parse rows
        foreach ($xml->sheetData->row as $row) {
            $row_data = [];
            foreach ($row->c as $cell) {
                $value = '';
                
                // Check if cell has a value
                if (isset($cell->v)) {
                    $value = (string)$cell->v;
                    
                    // If it's a shared string, get the actual value
                    if (isset($cell['t']) && $cell['t'] == 's') {
                        $value = $shared_strings[(int)$value];
                    }
                }
                
                $row_data[] = $value;
            }
            $data[] = $row_data;
        }
        
        if (empty($data)) {
            throw new Exception('File XLSX kosong atau tidak valid.');
        }
        
        return $data;
    }
    
    /**
     * Get file extension
     */
    public function getExtension() {
        return $this->file_extension;
    }
    
    /**
     * Get allowed extensions
     */
    public static function getAllowedExtensions() {
        return self::ALLOWED_EXTENSIONS;
    }
    
    /**
     * Get allowed extensions as string for display
     */
    public static function getAllowedExtensionsString() {
        return strtoupper(implode(', ', array_map(function($ext) {
            return '.' . $ext;
        }, self::ALLOWED_EXTENSIONS)));
    }
}
