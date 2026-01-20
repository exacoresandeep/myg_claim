<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policys extends Model
{
    use HasFactory;
    public $table="myg_06_policies";
    protected $fillable = [
        'PolicyID',
        'SubCategoryID',
        'GradeID',
        'GradeType',
        'GradeClass',
        'GradeAmount',
        'Approver',
        'Status',
        'user_id'
    ];
}
