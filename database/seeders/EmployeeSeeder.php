<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dept1 = DB::table('departments')->where('dept_name', 'IT')->first();
        $dept2 = DB::table('departments')->where('dept_name', 'HR')->first();

        DB::table('employees')->insert([
            [
                'id' => (string) Str::uuid(),
                'nik' => '1234567891012',
                'full_name' => 'Ayu Sihombing',
                'dept_id' => $dept1->id,
                'designation' => 'Programmer',
                'gender' => 'Female',
                'birth_place' => 'Banjartoba',
                'birth_date' => '2003-12-12',
                'phone_no' => '0895320130886',
                'join_date' => '2026-05-25',
                'join_end' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'nik' => 'EMPLOYEELAIN1',
                'full_name' => 'Employee Lain',
                'dept_id' => $dept2->id,
                'designation' => 'HR Manager',
                'gender' => 'Male',
                'birth_place' => 'Jakarta',
                'birth_date' => '1999-09-09',
                'phone_no' => '081234567890',
                'join_date' => '2026-05-01',
                'join_end' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}