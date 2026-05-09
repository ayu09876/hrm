{{-- resources/views/attendances/index.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Attendance'])

@section('content')
<div class="card">
    <div class="card-top">
        <h5>
            Attendance Records
            <span class="tag tag-count" style="font-size:12px">{{ $attendances->total() }}</span>
        </h5>
        <button class="btn-p" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import XLSX
        </button>
    </div>

    @if ($attendances->isEmpty())
    <div class="empty">
        <i class="bi bi-clock-history"></i>
        <p>No attendance records yet. Import an Excel file to get started.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>Employee</th>
                    <th>NIK</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $i => $att)
                <tr>
                    <td class="mono" style="color:var(--t2)">{{ $attendances->firstItem() + $i }}</td>
                    <td style="font-weight:500">
                        {{ $att->employee ? $att->employee->full_name : '—' }}
                    </td>
                    <td class="mono">{{ $att->employee ? $att->employee->nik : '—' }}</td>
                    <td class="mono" style="font-size:12px">
                        {{ $att->time_in ? $att->time_in->format('d M Y, H:i') : '—' }}
                    </td>
                    <td class="mono" style="font-size:12px">
                        {{ $att->time_out ? $att->time_out->format('d M Y, H:i') : '—' }}
                    </td>
                    <td style="font-size:12px;color:var(--t2)">
                        @if ($att->time_in && $att->time_out)
                            {{ $att->time_in->diffForHumans($att->time_out, true) }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($attendances->hasPages())
    <div class="pg-wrap">
        <span>Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} of {{ $attendances->total() }}</span>
        {{ $attendances->links('pagination::bootstrap-4') }}
    </div>
    @endif
    @endif
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2" style="color:var(--acc)"></i>Import Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('attendances.preview') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
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
                        <div style="font-size:11.5px;font-weight:600;color:var(--t2);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Expected Column Order</div>
                        <div style="font-size:12px;font-family:var(--mo);color:var(--t1);display:flex;gap:8px;flex-wrap:wrap">
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">NIK</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Full Name</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time In</span>
                            <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px">Time Out</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
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
    const fileInput  = document.getElementById('xlsxFile');
    const dropZone   = document.getElementById('dropZone');
    const fileName   = document.getElementById('fileName');
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

    @if ($errors->has('file'))
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('importModal')).show();
        });
    @endif

    @if (request('import'))
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('importModal')).show();
        });
    @endif
</script>
@endpush