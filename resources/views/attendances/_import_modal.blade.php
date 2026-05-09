@php
    $autoOpen = $autoOpen ?? false;
    $redirectOnClose = $redirectOnClose ?? false;
    $cancelUrl = $cancelUrl ?? route('attendances.index');
@endphp

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2" style="color:var(--acc)"></i>Import Attendance
                </h5>
                @if ($redirectOnClose)
                <button type="button" class="btn-close" onclick="window.location='{{ $cancelUrl }}'"></button>
                @else
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                @endif
            </div>
            <form method="POST" action="{{ route('attendances.preview') }}" id="attendanceImportForm">
                @csrf
                <textarea name="rows_payload" id="rowsPayload" hidden>{{ old('rows_payload') }}</textarea>

                <div class="modal-body">
                    <div class="mb-3 p-3" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#1d4ed8">Need the Excel format?</div>
                                <div style="font-size:12px;color:#475569;margin-top:2px">
                                    Download the ready-made template, fill it in, then preview it before import.
                                </div>
                            </div>
                            <a href="{{ route('attendances.template.download') }}" class="btn-s">
                                <i class="bi bi-download"></i> Download Template
                            </a>
                        </div>
                    </div>

                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('xlsxFile').click()">
                        <i class="bi bi-cloud-upload"></i>
                        <p>Click to browse or drag & drop your file here</p>
                        <p style="font-size:11px;margin-top:4px;color:#94a3b8">.xlsx files only</p>
                        <div class="picked" id="fileName" style="display:none"></div>
                    </div>
                    <input type="file" id="xlsxFile" accept=".xlsx" style="display:none" required>

                    <div class="flash flash-err mt-3" id="importFeedback" style="display:none;margin-bottom:0"></div>

                    @error('rows_payload')
                    <div class="flash flash-err mt-3" style="margin-bottom:0">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                    @enderror

                    <div class="mt-3 p-3" style="background:#f8fafc;border-radius:8px;border:1px solid var(--bd)">
                        <div style="font-size:11.5px;font-weight:600;color:var(--t2);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">
                            Expected Columns
                        </div>
                        <div style="font-size:12px;font-family:var(--mo);color:var(--t1);display:flex;gap:8px;flex-wrap:wrap">
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Date</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">NIK</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Full Name</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time In</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time Out</span>
                        </div>
                        <div style="font-size:11px;color:var(--t2);margin-top:8px">
                            The file is parsed in your browser first, then only the preview rows are sent for validation and import.
                            <strong>Date</strong> is optional — if omitted, today's date is used. Time In/Out can be times only (e.g. <code>08:00</code>) or full datetimes.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if ($redirectOnClose)
                    <a href="{{ $cancelUrl }}" class="btn-s">Cancel</a>
                    @else
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
                    @endif
                    <button type="submit" class="btn-p" id="previewBtn" disabled>
                        <i class="bi bi-table"></i> Preview Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="{{ asset('attendance-import.js') }}"></script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.initAttendanceImport({
        modalId: 'importModal',
        formId: 'attendanceImportForm',
        fileInputId: 'xlsxFile',
        dropZoneId: 'dropZone',
        fileNameId: 'fileName',
        previewBtnId: 'previewBtn',
        payloadFieldId: 'rowsPayload',
        feedbackId: 'importFeedback',
        autoOpen: {{ $autoOpen ? 'true' : 'false' }},
    });
});
</script>
@endpush
