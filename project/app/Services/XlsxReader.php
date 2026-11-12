<?php

namespace App\Services;

use ZipArchive;
use Exception;

class XlsxReader
{
    /**
     * Read all rows from sheet1 of an XLSX file into an indexed array.
     */
    public static function readRows(string $xlsxPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            throw new Exception("Cannot open XLSX file: {$xlsxPath}");
        }

        // Shared strings
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $xml = simplexml_load_string($stringsXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $sharedStrings[] = $t;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new Exception("No worksheet found in: {$xlsxPath}");
        }

        $xml = simplexml_load_string($sheetXml);
        $allRows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $cols = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $col = preg_replace('/[0-9]/', '', $ref);
                $type = (string)$cell['t'];
                $val = (string)$cell->v;

                if ($type === 's') {
                    $val = $sharedStrings[(int)$val] ?? $val;
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $val = (string)$cell->is->t;
                }
                $cols[$col] = trim($val);
            }
            $allRows[$rowNum] = $cols;
        }

        $zip->close();
        return $allRows;
    }
}

