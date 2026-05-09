<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee')->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('time_in', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('time_in', '<=', $request->date_to);
        }

        $attendances = $query->paginate(10)->appends($request->only('date_from', 'date_to'));

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
        $rows    = $this->decodePreviewRows($request->input('rows_payload'));
        $preview = $this->buildPreviewRows($rows);

        session(['attendance_preview' => $preview->toArray()]);

        return view('attendances.preview', compact('preview'));
    }

    public function store(Request $request)
    {
        $rows = session('attendance_preview', []);

        if (empty($rows)) {
            return redirect()->route('attendances.index')
                ->with('error', 'No preview data found. Please upload again.');
        }

        $valid = array_filter($rows, function($row) { return $row['valid'] && !$row['duplicate']; });

        $imported  = 0;
        $skipped   = 0;

        foreach ($valid as $row) {
            $date = $row['time_in']
                ? Carbon::parse($row['time_in'])->toDateString()
                : Carbon::today()->toDateString();

            // Final duplicate guard at write-time (race-condition safety)
            $exists = Attendance::where('employee_id', $row['employee_id'])
                ->whereDate('time_in', $date)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Attendance::create([
                'employee_id' => $row['employee_id'],
                'time_in'     => $row['time_in']  ?: null,
                'time_out'    => $row['time_out'] ?: null,
            ]);

            $imported++;
        }

        session()->forget('attendance_preview');

        $message = "{$imported} attendance record(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} duplicate(s) were skipped.";
        }

        return redirect()->route('attendances.index')->with('success', $message);
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function decodePreviewRows(?string $payload): array
    {
        if (!is_string($payload) || trim($payload) === '') {
            throw ValidationException::withMessages([
                'rows_payload' => 'Choose an Excel file before previewing the import.',
            ]);
        }

        $rows = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($rows)) {
            throw ValidationException::withMessages([
                'rows_payload' => 'The selected Excel file could not be read. Please try again.',
            ]);
        }

        $sanitizedRows = collect($rows)
            ->map(function ($row) {
                if (!is_array($row)) return null;

                $sanitized = [
                    'nik'       => trim((string) ($row['nik']       ?? '')),
                    'full_name' => trim((string) ($row['full_name'] ?? '')),
                    'time_in'   => trim((string) ($row['time_in']   ?? '')),
                    'time_out'  => trim((string) ($row['time_out']  ?? '')),
                ];

                $hasAnyValue = collect($sanitized)->contains(function($v) { return $v !== ''; });

                return $hasAnyValue ? $sanitized : null;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($sanitizedRows)) {
            throw ValidationException::withMessages([
                'rows_payload' => 'No attendance rows were found in the selected Excel file.',
            ]);
        }

        return $sanitizedRows;
    }

    private function buildPreviewRows(array $rows)
    {
        $employees = Employee::pluck('id', 'nik');

        // Pre-load existing attendance dates per employee to detect duplicates
        // without N+1 queries. Shape: ['employee_id' => ['2025-05-09', ...]]
        $employeeIds = collect($rows)
            ->map(function($r) use ($employees) { return $employees->get($r['nik']); })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existingDates = [];

        if (!empty($employeeIds)) {
            Attendance::whereIn('employee_id', $employeeIds)
                ->get(['employee_id', 'time_in'])
                ->each(function ($att) use (&$existingDates) {
                    if ($att->time_in) {
                        $existingDates[$att->employee_id][] =
                            Carbon::parse($att->time_in)->toDateString();
                    }
                });
        }

        // Track dates seen within this import batch itself to catch intra-file dupes
        $seenInBatch = []; // ['employee_id:date' => true]

        return collect($rows)->map(function (array $row) use ($employees, $existingDates, &$seenInBatch) {
            $nik        = $row['nik'];
            $employeeId = $employees->get($nik);
            $timeIn     = $this->normalizeDateTimeValue($row['time_in']);
            $timeOut    = $this->normalizeDateTimeValue($row['time_out']);

            $isDuplicate = false;

            if ($employeeId && $timeIn) {
                $date    = Carbon::parse($timeIn)->toDateString();
                $batchKey = "{$employeeId}:{$date}";

                // Check against DB
                if (in_array($date, $existingDates[$employeeId] ?? [], true)) {
                    $isDuplicate = true;
                }

                // Check within this batch
                if (isset($seenInBatch[$batchKey])) {
                    $isDuplicate = true;
                }

                $seenInBatch[$batchKey] = true;
            }

            return [
                'nik'              => $nik,
                'employee_id'      => $employeeId,
                'full_name'        => $row['full_name'],
                'time_in'          => $timeIn,
                'time_out'         => $timeOut,
                'time_in_display'  => $row['time_in'],
                'time_out_display' => $row['time_out'],
                'valid'            => !is_null($employeeId),
                'duplicate'        => $isDuplicate,
            ];
        })->values();
    }

    private function normalizeDateTimeValue(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') return null;

        try {
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
                return Carbon::parse(now()->toDateString() . ' ' . $value)->format('Y-m-d H:i:s');
            }

            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    // ─── Template builder (unchanged) ───────────────────────────────────────────

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

        $zip->addFromString('[Content_Types].xml',          $this->templateContentTypesXml());
        $zip->addFromString('_rels/.rels',                  $this->templateRootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml',              $this->templateWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels',   $this->templateWorkbookRelationshipsXml());
        $zip->addFromString('xl/sharedStrings.xml',         $this->templateSharedStringsXml());
        $zip->addFromString('xl/styles.xml',                $this->templateStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml',     $this->templateWorksheetXml());

        $zip->close();

        return $xlsxPath;
    }

    private function templateContentTypesXml(): string { /* unchanged */ return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML; }

    private function templateStylesXml(): string { return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
    </fills>
    <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>
</styleSheet>
XML; }

    private function templateRootRelationshipsXml(): string { return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML; }

    private function templateWorkbookXml(): string { return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets><sheet name="Attendance Import" sheetId="1" r:id="rId1"/></sheets>
</workbook>
XML; }

    private function templateWorkbookRelationshipsXml(): string { return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML; }

    private function templateWorksheetXml(): string
    {
        $headers = ['NIK', 'Full Name', 'Time In', 'Time Out'];
        $cells = [];
        foreach ($headers as $index => $header) {
            $cells[] = sprintf('<c r="%s1" t="s"><v>%d</v></c>', chr(65 + $index), $index);
        }
        return sprintf(<<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetViews><sheetView workbookViewId="0"/></sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    <sheetData><row r="1">%s</row></sheetData>
</worksheet>
XML, implode('', $cells));
    }

    private function templateSharedStringsXml(): string
    {
        $headers = ['NIK', 'Full Name', 'Time In', 'Time Out'];
        $items = [];
        foreach ($headers as $header) {
            $items[] = sprintf('<si><t>%s</t></si>', htmlspecialchars($header, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        }
        return sprintf(<<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="%1$d" uniqueCount="%1$d">%2$s</sst>
XML, count($headers), implode('', $items));
    }
}