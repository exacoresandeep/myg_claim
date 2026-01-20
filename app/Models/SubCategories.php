<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategories extends Model
{
    public function category() {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }
    public $table="myg_04_subcategories";
    protected $fillable = [
        'SubCategoryID',
        'UomID',
        'CategoryID',
        'SubCategoryName',
        'Status',
        'user_id'
    ];

    public function policies()
    {
        return $this->hasMany(Policy::class, 'SubCategoryID', 'SubCategoryID');
    }
    
    public function categoryDetails()
    {
        return $this->hasMany(Category::class, 'CategoryID','CategoryID');
    }

    public function uomdetails()
    {
        return $this->hasMany(UOM::class, 'UomID', 'UomID');
    }

    public function categorydata()
    {
        return $this->hasOne(Category::class, 'CategoryID','CategoryID');
    }

    
}
