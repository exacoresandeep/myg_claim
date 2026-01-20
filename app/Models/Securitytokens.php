<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Securitytokens extends Model
{
    use HasFactory;
    public $table="myg_00_security_tokens";
    protected $fillable = [
        'EmployeeID',
        'UserToken',
        'user_id'
    ];
}
