<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik', 13)->unique();
            $table->string('full_name', 50);
            $table->uuid('dept_id');
            $table->string('designation', 50);
            $table->enum('gender', ['Male', 'Female']);
            $table->string('birth_place', 50);
            $table->date('birth_date');
            $table->string('phone_no', 13);
            $table->date('join_date');
            $table->date('join_end')->nullable();
            $table->timestamps();

            $table->foreign('dept_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}