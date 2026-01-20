<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategorys extends Model
{
    use HasFactory;
    public $table="myg_04_subcategories";
    protected $fillable = [
        'SubCategoryID',
        'UomID',
        'CategoryID',
        'SubCategoryName',
        'Status',
        'user_id'
    ];
}
