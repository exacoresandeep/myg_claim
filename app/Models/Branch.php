<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    public $table="myg_11_branch";
    protected $fillable = [
        'BranchID',
        'BranchName',
        'BranchCode',
        'user_id',
        'Status'
    ];
}
