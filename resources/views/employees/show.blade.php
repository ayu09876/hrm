{{-- resources/views/employees/show.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Employee'])

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn-s" style="font-size:12px">
        <i class="bi bi-arrow-left"></i> Back to Employees
    </a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-top">
                <h5><i class="bi bi-person-badge me-2" style="color:var(--acc)"></i>Employee Profile</h5>
                <div class="d-flex gap-2">
                    <span class="tag {{ $employee->join_end ? 'tag-invalid' : 'tag-valid' }}">
                        {{ $employee->join_end ? 'Inactive' : 'Active' }}
                    </span>
                </div>
            </div>
            <div class="px-3 py-1">
                <div class="drow">
                    <div class="dlabel">NIK</div>
                    <div class="dvalue mono" style="font-size:13px">{{ $employee->nik }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Full Name</div>
                    <div class="dvalue" style="font-weight:600;font-size:15px">{{ $employee->full_name }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Department</div>
                    <div class="dvalue">
                        @if ($employee->department)
                        <a href="{{ route('departments.show', $employee->department) }}" style="text-decoration:none">
                            <span class="tag tag-dept">{{ $employee->department->dept_name }}</span>
                        </a>
                        @else
                        <span style="color:var(--t2)">—</span>
                        @endif
                    </div>
                </div>
                <div class="drow">
                    <div class="dlabel">Designation</div>
                    <div class="dvalue">{{ $employee->designation }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Gender</div>
                    <div class="dvalue">
                        <span class="tag {{ $employee->gender === 'Male' ? 'tag-male' : 'tag-female' }}">
                            {{ $employee->gender }}
                        </span>
                    </div>
                </div>
                <div class="drow">
                    <div class="dlabel">Birth Place</div>
                    <div class="dvalue">{{ $employee->birth_place }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Birth Date</div>
                    <div class="dvalue mono">{{ $employee->birth_date->format('d M Y') }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Phone No.</div>
                    <div class="dvalue mono">{{ $employee->phone_no }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Join Date</div>
                    <div class="dvalue mono">{{ $employee->join_date->format('d M Y') }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Join End</div>
                    <div class="dvalue mono">
                        {{ $employee->join_end ? $employee->join_end->format('d M Y') : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-top">
                <h5>Attendance Records</h5>
                <span class="tag tag-count" style="font-size:12px">{{ $employee->attendances->count() }}</span>
            </div>

            @if ($employee->attendances->isEmpty())
            <div class="empty">
                <i class="bi bi-clock"></i>
                <p>No attendance records found for this employee.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->attendances->sortByDesc('time_in')->take(20) as $i => $att)
                        <tr>
                            <td class="mono" style="color:var(--t2)">{{ $i + 1 }}</td>
                            <td class="mono">
                                @if ($att->time_in)
                                {{ $att->time_in->format('d M Y, H:i') }}
                                @else
                                <span style="color:var(--t2)">—</span>
                                @endif
                            </td>
                            <td class="mono">
                                @if ($att->time_out)
                                {{ $att->time_out->format('d M Y, H:i') }}
                                @else
                                <span style="color:var(--t2)">—</span>
                                @endif
                            </td>
                            <td style="color:var(--t2);font-size:12px">
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
            @endif
        </div>
    </div>
</div>
@endsection