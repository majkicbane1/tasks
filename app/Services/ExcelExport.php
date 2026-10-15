<?php

namespace App\Services;

use RuntimeException;
use XMLWriter;
use ZipArchive;

class ExcelExport
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public function write(string $path, array $types, ?int $clientId, DataExport $export): void
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Excel fajl nije moguće kreirati.');
        }
        $files = [];
        try {
            $contentTypes = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
            $workbook = '<workbook xmlns="'.self::NS.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
            $relationships = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

            foreach (array_values($types) as $index => $type) {
                $id = $index + 1;
                $name = htmlspecialchars(str_replace('/', '-', DataExport::TYPES[$type]), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $workbook .= '<sheet name="'.$name.'" sheetId="'.$id.'" r:id="rId'.$id.'"/>';
                $relationships .= '<Relationship Id="rId'.$id.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$id.'.xml"/>';
                $contentTypes .= '<Override PartName="/xl/worksheets/sheet'.$id.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
                $sheetPath = tempnam(sys_get_temp_dir(), 'poslovi-sheet-');
                if ($sheetPath === false) {
                    throw new RuntimeException('Excel list nije moguće kreirati.');
                }
                $files[] = $sheetPath;
                $this->writeSheet($sheetPath, $type, $clientId, $export);
                if (! $zip->addFile($sheetPath, 'xl/worksheets/sheet'.$id.'.xml')) {
                    throw new RuntimeException('Excel list nije moguće dodati.');
                }
            }

            $parts = [
                '[Content_Types].xml' => $contentTypes.'</Types>',
                '_rels/.rels' => '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
                'xl/workbook.xml' => $workbook.'</sheets></workbook>',
                'xl/_rels/workbook.xml.rels' => $relationships.'</Relationships>',
            ];
            foreach ($parts as $name => $xml) {
                if (! $zip->addFromString($name, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.$xml)) {
                    throw new RuntimeException('Excel podatke nije moguće zapisati.');
                }
            }
        } finally {
            try {
                if (! $zip->close()) {
                    throw new RuntimeException('Excel fajl nije moguće sačuvati.');
                }
            } finally {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }
    }

    private function writeSheet(string $path, string $type, ?int $clientId, DataExport $export): void
    {
        $xml = new XMLWriter;
        if (! $xml->openUri($path)) {
            throw new RuntimeException('Excel list nije moguće zapisati.');
        }
        $xml->startDocument('1.0', 'UTF-8', 'yes');
        $xml->startElementNS(null, 'worksheet', self::NS);
        $xml->startElement('sheetViews');
        $xml->startElement('sheetView');
        $xml->writeAttribute('workbookViewId', '0');
        $xml->startElement('pane');
        foreach (['ySplit' => '1', 'topLeftCell' => 'A2', 'activePane' => 'bottomLeft', 'state' => 'frozen'] as $key => $value) {
            $xml->writeAttribute($key, $value);
        }
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        $xml->startElement('sheetData');
        $rowNumber = 1;
        $this->writeRow($xml, array_keys($export->columns($type)), $rowNumber);
        foreach ($export->rows($type, $clientId, numeric: true) as $row) {
            if (++$rowNumber > 1048576) {
                throw new RuntimeException('Previše redova za Excel list. Koristite CSV izvoz ili filter po klijentu.');
            }
            $this->writeRow($xml, $row, $rowNumber);
        }
        $xml->endElement();
        $xml->startElement('autoFilter');
        $xml->writeAttribute('ref', 'A1:'.$this->columnName(count($export->columns($type))).$rowNumber);
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();
        $xml->flush();
    }

    private function writeRow(XMLWriter $xml, array $row, int $number): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $number);
        foreach ($row as $index => $value) {
            $xml->startElement('c');
            $xml->writeAttribute('r', $this->columnName($index + 1).$number);
            if (is_int($value) || is_float($value)) {
                $xml->writeElement('v', (string) $value);
            } else {
                // Explicit strings preserve leading zeros and never execute formulas.
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is');
                $xml->startElement('t');
                $xml->writeAttribute('xml:space', 'preserve');
                $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) ($value ?? ''));
                $xml->text(mb_substr($text ?? '', 0, 32767));
                $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + $number % 26).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
