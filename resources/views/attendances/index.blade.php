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

    {{-- ── Date filter ── --}}
    <div style="padding:12px 16px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <form method="GET" action="{{ route('attendances.index') }}"
              style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0">
            <label style="font-size:12px;color:var(--t2);white-space:nowrap">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   style="font-size:12px;padding:4px 8px;border:1px solid var(--bd);border-radius:6px;color:var(--t1);background:var(--bg)">

            <label style="font-size:12px;color:var(--t2);white-space:nowrap">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   style="font-size:12px;padding:4px 8px;border:1px solid var(--bd);border-radius:6px;color:var(--t1);background:var(--bg)">

            <button type="submit" class="btn-s" style="font-size:12px">
                <i class="bi bi-funnel"></i> Filter
            </button>

            @if(request('date_from') || request('date_to'))
            <a href="{{ route('attendances.index') }}" class="btn-s" style="font-size:12px">
                <i class="bi bi-x"></i> Clear
            </a>
            @endif
        </form>
    </div>

    @if ($attendances->isEmpty())
    <div class="empty">
        <i class="bi bi-clock-history"></i>
        <p>No attendance records found{{ request('date_from') || request('date_to') ? ' for the selected date range.' : '. Import an Excel file to get started.' }}</p>
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
                    <td style="font-weight:500">{{ $att->employee ? $att->employee->full_name : '—' }}</td>
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

@include('attendances._import_modal', [
    'autoOpen' => $errors->has('rows_payload') || request()->boolean('import'),
])
@endsection