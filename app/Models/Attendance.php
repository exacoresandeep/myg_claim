<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    public $table="attendances";
    protected $fillable = [
        'id', 'date', 'emp_code', 'punch_in', 'location_in', 'punch_out', 'location_out', 'duration', 'remarks'
    ];

    public $incrementing = false; // Disable auto-incrementing for the id column

    protected $keyType = 'string'; // Specify that the primary key is a string
}
