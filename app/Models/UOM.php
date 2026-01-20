<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UOM extends Model
{
    use HasFactory;
    public $table="myg_02_uom";
    protected $fillable = [
        'UomID',
        'Unit',
        'Measurement',
        'Status',
        'userd_id'
    ];
}
