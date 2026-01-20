<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceList extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'myg_12_advancelist';

    // Specify the primary key type and column name if it's not an auto-incrementing integer
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string'; // Since 'id' is varchar

    // Define fillable attributes
    protected $fillable = [
        'id',
        'user_id',
        'Amount',
        'RequestDate',
        'TripTypeID',
        'TripPurpose',
        'BranchID',
        'Remarks',
        'Status',
        'ApproverID'
    ];

    // Define attributes that should be cast to native types
    protected $casts = [
        'Amount' => 'float',
        'RequestDate' => 'date',
        'Status' => 'string',
    ];

    
    public function userdata()
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }

    public function triptypedetails()
    {
        return $this->belongsTo(Triptype::class, 'TripTypeID','TripTypeID');
    }
    public function visitbranchdetails()
    {
        return $this->belongsTo(Branch::class, 'BranchID','BranchID');
    }

}
