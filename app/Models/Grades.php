<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grades extends Model
{
    use HasFactory;
    public $table="myg_05_grades";
    protected $fillable = [
        'GradeID',
        'GradeName',
        'Status',
        'user_id'
    ];
}
