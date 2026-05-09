<?php
// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('employee')->latest()->paginate(10);
        return view('attendances.index', compact('attendances'));
    }

    public function importForm()
    {
        return view('attendances.import');
    }

    public function downloadTemplate()
    {
        $path = $this->buildAttendanceTemplate();

        return response()->download(
            $path,
            'attendance-import-template.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = $this->parseXlsx($path);

        $employees = Employee::pluck('id', 'nik');

        $preview = collect(array_slice($rows, 1))->map(function ($row) use ($employees) {
            $nik        = trim($row[0] ?? '');
            $employeeId = $employees->get($nik);

            return [
                'nik'         => $nik,
                'employee_id' => $employeeId,
                'full_name'   => trim($row[1] ?? ''),
                'time_in'     => trim($row[2] ?? ''),
                'time_out'    => trim($row[3] ?? ''),
                'valid'       => !is_null($employeeId),
            ];
        })->values();

        session(['attendance_preview' => $preview->toArray()]);

        return view('attendances.preview', compact('preview'));
    }

    public function store(Request $request)
    {
        $rows = session('attendance_preview', []);

        if (empty($rows)) {
            return redirect()->route('attendances.index')->with('error', 'No preview data found. Please upload again.');
        }

        $valid = array_filter($rows, function ($row) {
            return $row['valid'];
        });

        foreach ($valid as $row) {
            Attendance::create([
                'employee_id' => $row['employee_id'],
                'time_in'     => $row['time_in']  ?: null,
                'time_out'    => $row['time_out'] ?: null,
            ]);
        }

        session()->forget('attendance_preview');

        return redirect()->route('attendances.index')->with('success', count($valid) . ' attendance records imported successfully.');
    }

    private function parseXlsx(string $path): array
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            abort(422, 'Unable to open the uploaded file.');
        }

        $sharedStrings = [];
        $sharedXml     = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } else {
                    $parts = '';
                    foreach ($si->r as $r) {
                        $parts .= (string) $r->t;
                    }
                    $sharedStrings[] = $parts;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            abort(422, 'Could not read sheet data from the file.');
        }

        $xml  = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $type  = (string) $cell['t'];
                $value = (string) $cell->v;

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }

                $rowData[] = $value;
            }
            $rows[] = $rowData;
        }

        return $rows;
    }

    private function buildAttendanceTemplate(): string
    {
        $directory = storage_path('app/templates');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = tempnam($directory, 'attendance-template-');

        if ($path === false) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to prepare the attendance template.');
        }

        $xlsxPath = $path . '.xlsx';

        if (!rename($path, $xlsxPath)) {
            @unlink($path);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to prepare the attendance template.');
        }

        $zip = new \ZipArchive;

        if ($zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($xlsxPath);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to create the attendance template.');
        }

        $zip->addFromString('[Content_Types].xml', $this->templateContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->templateRootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->templateWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->templateWorkbookRelationshipsXml());
        $zip->addFromString('xl/sharedStrings.xml', $this->templateSharedStringsXml());
        $zip->addFromString('xl/styles.xml',                  $this->templateStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->templateWorksheetXml());

        $zip->close();

        return $xlsxPath;
    }

    private function templateContentTypesXml(): string
    {
        return <<<'XML'
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
        <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
        <Default Extension="xml" ContentType="application/xml"/>
        <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
        <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
        <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
        <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    </Types>
    XML;
    }

    private function templateStylesXml(): string
    {
        return <<<'XML'
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <fonts count="1">
            <font><sz val="11"/><name val="Calibri"/></font>
        </fonts>
        <fills count="2">
            <fill><patternFill patternType="none"/></fill>
            <fill><patternFill patternType="gray125"/></fill>
        </fills>
        <borders count="1">
            <border><left/><right/><top/><bottom/><diagonal/></border>
        </borders>
        <cellStyleXfs count="1">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
        </cellStyleXfs>
        <cellXfs count="1">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        </cellXfs>
    </styleSheet>
    XML;
    }

    private function templateRootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function templateWorkbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Attendance Import" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private function templateWorkbookRelationshipsXml(): string
    {
        return <<<'XML'
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
        <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
        <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    </Relationships>
    XML;
    }

    private function templateWorksheetXml(): string
    {
        $headers = ['NIK', 'Full Name', 'Time In', 'Time Out'];
        $cells = [];

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);

            $cells[] = sprintf(
                '<c r="%1$s1" t="s"><v>%2$d</v></c>',
                $column,
                $index
            );
        }

        return sprintf(
            <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetViews>
        <sheetView workbookViewId="0"/>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    <sheetData>
        <row r="1">%s</row>
    </sheetData>
</worksheet>
XML,
            implode('', $cells)
        );
    }

    private function templateSharedStringsXml(): string
    {
        $headers = ['NIK', 'Full Name', 'Time In', 'Time Out'];
        $items = [];

        foreach ($headers as $header) {
            $items[] = sprintf(
                '<si><t>%s</t></si>',
                htmlspecialchars($header, ENT_XML1 | ENT_COMPAT, 'UTF-8')
            );
        }

        return sprintf(
            <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="%1$d" uniqueCount="%1$d">
    %2$s
</sst>
XML,
            count($headers),
            implode('', $items)
        );
    }
}
