{{-- resources/views/attendances/preview.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Import Preview'])

@section('content')
@php
    $validCount   = collect($preview)->where('valid', true)->count();
    $invalidCount = collect($preview)->where('valid', false)->count();
@endphp

<div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('attendances.import') }}" class="btn-s" style="font-size:12px">
            <i class="bi bi-arrow-left"></i> Upload Different File
        </a>
        <div style="font-size:13px;color:var(--t2)">
            <span class="tag tag-valid me-1">{{ $validCount }} valid</span>
            @if ($invalidCount)
            <span class="tag tag-invalid">{{ $invalidCount }} will be skipped</span>
            @endif
        </div>
    </div>

    @if ($validCount > 0)
    <form method="POST" action="{{ route('attendances.store') }}">
        @csrf
        <button type="submit" class="btn-p">
            <i class="bi bi-check2-circle"></i> Confirm Import ({{ $validCount }} records)
        </button>
    </form>
    @endif
</div>

<div class="card">
    <div class="card-top">
        <h5>
            Preview
            <span class="tag tag-count" style="font-size:12px">{{ count($preview) }} rows</span>
        </h5>
        <div style="font-size:12px;color:var(--t2);display:flex;align-items:center;gap:12px">
            <span style="display:flex;align-items:center;gap:5px">
                <span style="width:10px;height:10px;background:#dcfce7;border-radius:2px;display:inline-block;border:1px solid #86efac"></span> Valid
            </span>
            <span style="display:flex;align-items:center;gap:5px">
                <span style="width:10px;height:10px;background:#fee2e2;border-radius:2px;display:inline-block;border:1px solid #fca5a5"></span> Skipped
            </span>
        </div>
    </div>

    @if (empty($preview))
    <div class="empty">
        <i class="bi bi-file-earmark-x"></i>
        <p>No data found in the uploaded file.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>NIK</th>
                    <th>Full Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($preview as $i => $row)
                <tr class="{{ $row['valid'] ? 'row-ok' : 'row-bad' }}">
                    <td class="mono" style="color:var(--t2)">{{ $i + 1 }}</td>
                    <td class="mono">{{ $row['nik'] ?: '—' }}</td>
                    <td style="font-weight:{{ $row['valid'] ? '500' : '400' }}">
                        {{ $row['full_name'] ?: '—' }}
                    </td>
                    <td class="mono" style="font-size:12px">{{ $row['time_in'] ?: '—' }}</td>
                    <td class="mono" style="font-size:12px">{{ $row['time_out'] ?: '—' }}</td>
                    <td>
                        @if ($row['valid'])
                        <span class="tag tag-valid"><i class="bi bi-check-circle-fill" style="font-size:10px;margin-right:3px"></i>Valid</span>
                        @else
                        <span class="tag tag-invalid"><i class="bi bi-x-circle-fill" style="font-size:10px;margin-right:3px"></i>NIK not found</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pg-wrap" style="justify-content:flex-end">
        @if ($validCount > 0)
        <form method="POST" action="{{ route('attendances.store') }}">
            @csrf
            <button type="submit" class="btn-p">
                <i class="bi bi-check2-circle"></i> Confirm Import ({{ $validCount }} records)
            </button>
        </form>
        @else
        <span style="font-size:13px;color:var(--t2)">No valid rows to import.</span>
        @endif
    </div>
    @endif
</div>
@endsection