{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Employees'])

@section('content')
<div class="card">
    <div class="card-top">
        <h5>
            All Employees
            <span class="tag tag-count" style="font-size:12px">{{ $employees->total() }}</span>
        </h5>
        <button class="btn-p" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Add Employee
        </button>
    </div>

    @if ($employees->isEmpty())
    <div class="empty">
        <i class="bi bi-person-badge"></i>
        <p>No employees found. Add your first employee to get started.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>NIK</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Gender</th>
                    <th>Join Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $i => $emp)
                <tr>
                    <td class="mono" style="color:var(--t2)">{{ $employees->firstItem() + $i }}</td>
                    <td class="mono">{{ $emp->nik }}</td>
                    <td style="font-weight:500">{{ $emp->full_name }}</td>
                    <td>
                        @if ($emp->department)
                        <span class="tag tag-dept">{{ $emp->department->dept_name }}</span>
                        @else
                        <span style="color:var(--t2);font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="color:var(--t2)">{{ $emp->designation }}</td>
                    <td>
                        <span class="tag {{ $emp->gender === 'Male' ? 'tag-male' : 'tag-female' }}">
                            {{ $emp->gender }}
                        </span>
                    </td>
                    <td class="mono" style="color:var(--t2)">{{ $emp->join_date->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('employees.show', $emp) }}" class="act act-view" title="View"><i
                                    class="bi bi-eye"></i></a>
                            <button class="act act-edit" title="Edit" onclick="openEdit({{ json_encode([
                                    'id'          => $emp->id,
                                    'nik'         => $emp->nik,
                                    'full_name'   => $emp->full_name,
                                    'dept_id'     => $emp->dept_id,
                                    'designation' => $emp->designation,
                                    'gender'      => $emp->gender,
                                    'birth_place' => $emp->birth_place,
                                    'birth_date'  => $emp->birth_date->format('Y-m-d'),
                                    'phone_no'    => $emp->phone_no,
                                    'join_date'   => $emp->join_date->format('Y-m-d'),
                                    'join_end'    => $emp->join_end ? $emp->join_end->format('Y-m-d') : '',
                                ]) }})"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                onsubmit="return confirm('Delete this employee record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="act act-del" title="Delete"><i
                                        class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
    <div class="pg-wrap">
        <span>Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}</span>
        {{ $employees->links('pagination::bootstrap-4') }}
    </div>
    @endif
    @endif
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">
                    <i class="bi bi-person-plus me-2" style="color:var(--acc)"></i>New Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                <input type="hidden" name="form_src" value="create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik"
                                class="form-control mono @error('nik') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('nik') : '' }}" placeholder="13-digit NIK"
                                maxlength="13" required>
                            @if(old('form_src')==='create') @error('nik') <div class="invalid-feedback">{{ $message }}
                            </div> @enderror @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name"
                                class="form-control @error('full_name') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('full_name') : '' }}" maxlength="50"
                                required>
                            @if(old('form_src')==='create') @error('full_name') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="dept_id"
                                class="form-select @error('dept_id') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                required>
                                <option value="">Select department</option>
                                @foreach (\App\Models\Department::orderBy('dept_name')->get() as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('form_src')==='create' && old('dept_id')===$dept->id ? 'selected' : '' }}>
                                    {{ $dept->dept_name }}</option>
                                @endforeach
                            </select>
                            @if(old('form_src')==='create') @error('dept_id') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation"
                                class="form-control @error('designation') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('designation') : '' }}" maxlength="50"
                                required>
                            @if(old('form_src')==='create') @error('designation') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender"
                                class="form-select @error('gender') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                required>
                                <option value="">Select</option>
                                <option value="Male"
                                    {{ old('form_src')==='create' && old('gender')==='Male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="Female"
                                    {{ old('form_src')==='create' && old('gender')==='Female' ? 'selected' : '' }}>
                                    Female</option>
                            </select>
                            @if(old('form_src')==='create') @error('gender') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Place</label>
                            <input type="text" name="birth_place"
                                class="form-control @error('birth_place') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('birth_place') : '' }}" maxlength="50"
                                required>
                            @if(old('form_src')==='create') @error('birth_place') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Date</label>
                            <input type="date" name="birth_date"
                                class="form-control @error('birth_date') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('birth_date') : '' }}" required>
                            @if(old('form_src')==='create') @error('birth_date') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone No.</label>
                            <input type="text" name="phone_no"
                                class="form-control mono @error('phone_no') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('phone_no') : '' }}" maxlength="13" required>
                            @if(old('form_src')==='create') @error('phone_no') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join Date</label>
                            <input type="date" name="join_date"
                                class="form-control @error('join_date') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('join_date') : '' }}" required>
                            @if(old('form_src')==='create') @error('join_date') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join End <span
                                    style="color:var(--t2);font-weight:400">(optional)</span></label>
                            <input type="date" name="join_end"
                                class="form-control @error('join_end') @if(old('form_src')==='create') is-invalid @endif @enderror"
                                value="{{ old('form_src')==='create' ? old('join_end') : '' }}">
                            @if(old('form_src')==='create') @error('join_end') <div class="invalid-feedback">
                                {{ $message }}</div> @enderror @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-p">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabel">
                    <i class="bi bi-pencil me-2" style="color:var(--acc)"></i>Edit Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="form_src" value="edit">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" id="e_nik" name="nik" class="form-control mono" maxlength="13" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="e_full_name" name="full_name" class="form-control" maxlength="50"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select id="e_dept_id" name="dept_id" class="form-select" required>
                                <option value="">Select department</option>
                                @foreach (\App\Models\Department::orderBy('dept_name')->get() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" id="e_designation" name="designation" class="form-control" maxlength="50"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select id="e_gender" name="gender" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Place</label>
                            <input type="text" id="e_birth_place" name="birth_place" class="form-control" maxlength="50"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Date</label>
                            <input type="date" id="e_birth_date" name="birth_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone No.</label>
                            <input type="text" id="e_phone_no" name="phone_no" class="form-control mono" maxlength="13"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join Date</label>
                            <input type="date" id="e_join_date" name="join_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join End <span
                                    style="color:var(--t2);font-weight:400">(optional)</span></label>
                            <input type="date" id="e_join_end" name="join_end" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-p">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(data) {
    document.getElementById('e_nik').value = data.nik;
    document.getElementById('e_full_name').value = data.full_name;
    document.getElementById('e_dept_id').value = data.dept_id;
    document.getElementById('e_designation').value = data.designation;
    document.getElementById('e_gender').value = data.gender;
    document.getElementById('e_birth_place').value = data.birth_place;
    document.getElementById('e_birth_date').value = data.birth_date;
    document.getElementById('e_phone_no').value = data.phone_no;
    document.getElementById('e_join_date').value = data.join_date;
    document.getElementById('e_join_end').value = data.join_end;
    document.getElementById('editForm').action = '/employees/' + data.id;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

@if($errors -> any() && old('form_src') === 'create')
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('createModal')).show();
});
@endif
</script>
@endpush