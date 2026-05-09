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
@include('attendances._import_modal', [
    'autoOpen' => true,
    'redirectOnClose' => true,
    'cancelUrl' => route('attendances.index'),
])
@endsection
