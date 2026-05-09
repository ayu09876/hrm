<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employee = DB::table('employees')->where('nik', '1234567891012')->first();

        DB::table('attendances')->insert([
            [
                'id' => (string) Str::uuid(),
                'employee_id' => $employee->id,
                'time_in' => '2026-05-08 08:00:00',
                'time_out' => '2026-05-08 17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}