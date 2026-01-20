<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usermanagement extends Model
{
    use HasFactory;
    public $table="myg_07_user_management";
    protected $fillable = [
        'EmployeeID',
        'ApproverID',
        'Role',
        'Status',
        'user_id'
    ];
}
