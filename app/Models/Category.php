<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public $table="myg_03_categories";
    protected $fillable = [
        'CategoryID',
        'CategoryName',
        'TripFrom',
        'TripTo',
        'FromDate',
        'ToDate',
        'DocumentDate',
        'ImageUrl',
        'user_id',
        'Status',
        'StartMeter',
        'EndMeter'
    ];

    public function subcategorydetails()
    {
        return $this->hasMany(SubCategories::class, 'CategoryID', 'CategoryID');
    }
    public function subcategories()
    {
        return $this->hasMany(SubCategories::class, 'CategoryID', 'CategoryID');
    }
}
