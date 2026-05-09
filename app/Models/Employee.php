<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nik',
        'full_name',
        'dept_id',
        'designation',
        'gender',
        'birth_place',
        'birth_date',
        'phone_no',
        'join_date',
        'join_end',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date'  => 'date',
        'join_end'   => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}