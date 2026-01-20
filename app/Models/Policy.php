<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    public function subCategoryDetails()
    {
        return $this->belongsTo(SubCategories::class, 'SubCategoryID','SubCategoryID');
    }
    public $table="myg_06_policies";
    protected $fillable = [
        'PolicyID',
        'SubCategoryID',
        'GradeID',
        'GradeClass',
        'GradeType',
        'GradeAmount',
        'Approver',
        'Status',
        'user_id'
    ];
  
    public function gradeDetails()
    {
        return $this->hasMany(Grades::class, 'GradeID','GradeID');
    }
    
    public function viewgradeDetails()
    {
        return $this->belongsTo(Grades::class, 'GradeID', 'GradeID');
    }
}
