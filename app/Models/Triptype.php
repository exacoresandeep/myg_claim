<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triptype extends Model
{
    use HasFactory;
    public $table="myg_01_triptypes";
    protected $fillable = [
        'TripTypeID',
        'TripTypeName',
        'Status',
        'user_id'
    ];
}
