{{-- resources/views/attendances/import.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Import Attendance'])

@section('content')
<div class="card">
    <div class="card-top">
        <h5>
            Attendance Records
        </h5>
        <button class="btn-p" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import XLSX
        </button>
    </div>
    <div class="empty">
        <i class="bi bi-file-earmark-spreadsheet"></i>
        <p>Use the button above to import attendance data from an Excel file.</p>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2" style="color:var(--acc)"></i>Import Attendance
                </h5>
                <button type="button" class="btn-close"
                    onclick="window.location='{{ route('attendances.index') }}'"></button>
            </div>
            <form method="POST" action="{{ route('attendances.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#1d4ed8">Need the Excel format?</div>
                                <div style="font-size:12px;color:#475569;margin-top:2px">
                                    Download the ready-made template, fill it in, then upload it here.
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
                    <input type="file" id="xlsxFile" name="file" accept=".xlsx" style="display:none" required>

                    @error('file')
                    <div class="flash flash-err mt-3" style="margin-bottom:0">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                    @enderror

                    <div class="mt-3 p-3" style="background:#f8fafc;border-radius:8px;border:1px solid var(--bd)">
                        <div
                            style="font-size:11.5px;font-weight:600;color:var(--t2);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">
                            Expected Column Order</div>
                        <div
                            style="font-size:12px;font-family:var(--mo);color:var(--t1);display:flex;gap:8px;flex-wrap:wrap">
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">NIK</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Full
                                Name</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time
                                In</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time
                                Out</span>
                        </div>
                        <div style="font-size:11px;color:var(--t2);margin-top:8px">
                            Use the exact header names from the template and keep the first sheet as the import sheet.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('attendances.index') }}" class="btn-s">Cancel</a>
                    <button type="submit" class="btn-p" id="previewBtn" disabled>
                        <i class="bi bi-table"></i> Preview Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('importModal')).show();
});

const fileInput = document.getElementById('xlsxFile');
const dropZone = document.getElementById('dropZone');
const fileName = document.getElementById('fileName');
const previewBtn = document.getElementById('previewBtn');

fileInput.addEventListener('change', () => {
    const f = fileInput.files[0];
    if (f) {
        fileName.textContent = f.name;
        fileName.style.display = 'block';
        previewBtn.disabled = false;
    }
});

['dragenter', 'dragover'].forEach(e => dropZone.addEventListener(e, ev => {
    ev.preventDefault();
    dropZone.classList.add('over');
}));

['dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, ev => {
    ev.preventDefault();
    dropZone.classList.remove('over');
}));

dropZone.addEventListener('drop', ev => {
    const f = ev.dataTransfer.files[0];
    if (f && f.name.endsWith('.xlsx')) {
        const dt = new DataTransfer();
        dt.items.add(f);
        fileInput.files = dt.files;
        fileName.textContent = f.name;
        fileName.style.display = 'block';
        previewBtn.disabled = false;
    }
});
</script>
@endpush
