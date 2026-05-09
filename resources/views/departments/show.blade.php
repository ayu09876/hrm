{{-- resources/views/departments/show.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Department'])

@section('content')
<div class="mb-3">
    <a href="{{ route('departments.index') }}" class="btn-s" style="font-size:12px">
        <i class="bi bi-arrow-left"></i> Back to Departments
    </a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-top">
                <h5><i class="bi bi-diagram-3 me-2" style="color:var(--acc)"></i>Department Info</h5>
            </div>
            <div class="px-3 py-1">
                <div class="drow">
                    <div class="dlabel">Name</div>
                    <div class="dvalue" style="font-weight:600;font-size:15px">{{ $department->dept_name }}</div>
                </div>
                <div class="drow">
                    <div class="dlabel">Employees</div>
                    <div class="dvalue">
                        <span class="tag tag-count">{{ $department->employees->count() }}</span>
                    </div>
                </div>
                <div class="drow">
                    <div class="dlabel">Created</div>
                    <div class="dvalue mono">{{ $department->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-top">
                <h5>Employees in this Department</h5>
                <a href="{{ route('employees.index') }}" class="btn-s" style="font-size:12px">
                    <i class="bi bi-people"></i> All Employees
                </a>
            </div>

            @if ($department->employees->isEmpty())
            <div class="empty">
                <i class="bi bi-person-x"></i>
                <p>No employees in this department yet.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Full Name</th>
                            <th>Designation</th>
                            <th>Gender</th>
                            <th>Join Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($department->employees as $emp)
                        <tr>
                            <td class="mono">{{ $emp->nik }}</td>
                            <td style="font-weight:500">{{ $emp->full_name }}</td>
                            <td style="color:var(--t2)">{{ $emp->designation }}</td>
                            <td>
                                <span class="tag {{ $emp->gender === 'Male' ? 'tag-male' : 'tag-female' }}">
                                    {{ $emp->gender }}
                                </span>
                            </td>
                            <td class="mono" style="color:var(--t2)">{{ $emp->join_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('employees.show', $emp) }}" class="act act-view" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
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