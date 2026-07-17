<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('../kazeem_Salary.xlsx');
$worksheet = $spreadsheet->getActiveSheet();

$header = [];
foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE);
    foreach ($cellIterator as $cell) {
        $header[] = $cell->getValue();
    }
    break; // only get header
}
print_r($header);
