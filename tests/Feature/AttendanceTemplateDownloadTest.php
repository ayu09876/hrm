<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttendanceTemplateDownloadTest extends TestCase
{
    public function test_attendance_template_can_be_downloaded(): void
    {
        $response = $this->get(route('attendances.template.download'));

        $response->assertOk();
        $this->assertStringContainsString(
            'attendance-import-template.xlsx',
            (string) $response->headers->get('content-disposition')
        );
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );

        $templatePath = $response->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive;

        $this->assertTrue($zip->open($templatePath) === true);
        $this->assertNotFalse($zip->getFromName('xl/workbook.xml'));

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        $this->assertNotFalse($sheetXml);
        $this->assertNotFalse($sharedStringsXml);
        $this->assertStringContainsString('<c r="A1" t="s"><v>0</v></c>', $sheetXml);
        $this->assertStringContainsString('<c r="D1" t="s"><v>3</v></c>', $sheetXml);
        $this->assertStringContainsString('<t>NIK</t>', $sharedStringsXml);
        $this->assertStringContainsString('<t>Full Name</t>', $sharedStringsXml);
        $this->assertStringContainsString('<t>Time In</t>', $sharedStringsXml);
        $this->assertStringContainsString('<t>Time Out</t>', $sharedStringsXml);
    }
}
