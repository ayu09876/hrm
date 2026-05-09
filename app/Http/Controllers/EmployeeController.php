<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('department')->latest()->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::orderBy('dept_name')->get();
        return view('employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nik'         => 'required|string|max:13|unique:employees,nik',
            'full_name'   => 'required|string|max:50',
            'dept_id'     => 'required|uuid|exists:departments,id',
            'designation' => 'required|string|max:50',
            'gender'      => 'required|in:Male,Female',
            'birth_place' => 'required|string|max:50',
            'birth_date'  => 'required|date',
            'phone_no'    => 'required|string|max:13',
            'join_date'   => 'required|date',
            'join_end'    => 'nullable|date|after_or_equal:join_date',
        ]);

        Employee::create($data);

        return redirect()->route('employees.index')->with('success', 'Employee added successfully.');
    }

    public function show($id)
    {
        $employee = Employee::with('department')->findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee    = Employee::findOrFail($id);
        $departments = Department::orderBy('dept_name')->get();
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'nik'         => 'required|string|max:13|unique:employees,nik,' . $employee->id . ',id',
            'full_name'   => 'required|string|max:50',
            'dept_id'     => 'required|uuid|exists:departments,id',
            'designation' => 'required|string|max:50',
            'gender'      => 'required|in:Male,Female',
            'birth_place' => 'required|string|max:50',
            'birth_date'  => 'required|date',
            'phone_no'    => 'required|string|max:13',
            'join_date'   => 'required|date',
            'join_end'    => 'nullable|date|after_or_equal:join_date',
        ]);

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}