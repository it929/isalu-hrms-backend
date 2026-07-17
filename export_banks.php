<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "Loading main file...\n";
$spreadsheet = IOFactory::load('../kazeem_Salary.xlsx');
$worksheet = $spreadsheet->getActiveSheet();

$header = [];
$rows = [];

$rowIndex = 0;
foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE);
    $rowData = [];
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    
    if ($rowIndex === 0) {
        $header = $rowData;
    } else {
        $rows[] = $rowData;
    }
    $rowIndex++;
}

// Indices
$idnoIdx = array_search('IDNO', $header);
$accountNoIdx = array_search('Account No', $header);
$bankIdx = array_search('Bank', $header);

if ($idnoIdx === false || $accountNoIdx === false || $bankIdx === false) {
    die("Missing columns!\n");
}

$banks = [];
foreach ($rows as $row) {
    $bank = trim((string)($row[$bankIdx] ?? ''));
    if ($bank === '') {
        $bank = 'Unknown';
    }
    
    // Clean bank name for filename
    $safeBankName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $bank);
    
    if (!isset($banks[$safeBankName])) {
        $banks[$safeBankName] = [];
    }
    
    $banks[$safeBankName][] = [
        $row[$idnoIdx] ?? '',
        $row[$accountNoIdx] ?? '',
        $row[$bankIdx] ?? ''
    ];
}

echo "Found " . count($banks) . " banks. Creating files...\n";

foreach ($banks as $safeBankName => $bankRows) {
    $newSpreadsheet = new Spreadsheet();
    $sheet = $newSpreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'IDNO');
    $sheet->setCellValue('B1', 'Account No');
    $sheet->setCellValue('C1', 'Bank');
    
    $r = 2;
    foreach ($bankRows as $bRow) {
        $sheet->setCellValue('A' . $r, $bRow[0]);
        // Format account number as string to prevent scientific notation
        $sheet->setCellValueExplicit('B' . $r, $bRow[1], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('C' . $r, $bRow[2]);
        $r++;
    }
    
    $writer = new Xlsx($newSpreadsheet);
    $filename = "../bank_exports/export_" . $safeBankName . ".xlsx";
    if (!is_dir("../bank_exports")) {
        mkdir("../bank_exports", 0777, true);
    }
    $writer->save($filename);
    echo "Saved: $filename\n";
}

echo "Done!\n";
