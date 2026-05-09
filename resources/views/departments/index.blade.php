{{-- resources/views/departments/index.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'Departments'])

@section('content')
<div class="card">
    <div class="card-top">
        <h5>
            All Departments
            <span class="tag tag-count" style="font-size:12px">{{ $departments->total() }}</span>
        </h5>
        <button class="btn-p" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Add Department
        </button>
    </div>

    @if ($departments->isEmpty())
    <div class="empty">
        <i class="bi bi-diagram-3"></i>
        <p>No departments found. Add your first department to get started.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>Department Name</th>
                    <th>Employees</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departments as $i => $dept)
                <tr>
                    <td class="mono" style="color:var(--t2)">{{ $departments->firstItem() + $i }}</td>
                    <td style="font-weight:500">{{ $dept->dept_name }}</td>
                    <td><span class="tag tag-count">{{ $dept->employees_count }}</span></td>
                    <td class="mono" style="color:var(--t2)">{{ $dept->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('departments.show', $dept) }}" class="act act-view" title="View"><i
                                    class="bi bi-eye"></i></a>
                            <button class="act act-edit" title="Edit"
                                onclick="openEdit('{{ $dept->id }}', '{{ addslashes($dept->dept_name) }}')"><i
                                    class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('departments.destroy', $dept) }}"
                                onsubmit="return confirm('Delete this department? This cannot be undone.')">
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

    @if ($departments->hasPages())
    <div class="pg-wrap">
        <span>Showing {{ $departments->firstItem() }}–{{ $departments->lastItem() }} of
            {{ $departments->total() }}</span>
        {{ $departments->links('pagination::bootstrap-4') }}
    </div>
    @endif
    @endif
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">
                    <i class="bi bi-diagram-3 me-2" style="color:var(--acc)"></i>New Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('departments.store') }}">
                @csrf
                <input type="hidden" name="form_src" value="create">
                <div class="modal-body">
                    <div>
                        <label class="form-label">Department Name</label>
                        <input type="text" name="dept_name"
                            class="form-control @error('dept_name') @if(old('form_src') === 'create') is-invalid @endif @enderror"
                            value="{{ old('form_src') === 'create' ? old('dept_name') : '' }}"
                            placeholder="e.g. Engineering" maxlength="50" required>
                        @if (old('form_src') === 'create')
                        @error('dept_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-p">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabel">
                    <i class="bi bi-pencil me-2" style="color:var(--acc)"></i>Edit Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="form_src" value="edit">
                <div class="modal-body">
                    <div>
                        <label class="form-label">Department Name</label>
                        <input type="text" id="editDeptName" name="dept_name" class="form-control" maxlength="50"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-s" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-p">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name) {
    document.getElementById('editDeptName').value = name;
    document.getElementById('editForm').action = '/departments/' + id;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

@if($errors -> any() && old('form_src') === 'create')
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('createModal')).show();
});
@endif
</script>
@endpush