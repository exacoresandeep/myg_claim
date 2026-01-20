<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tripclaimdetails extends Model
{
    public function policyDet()
    {
        return $this->belongsTo(Policy::class, 'PolicyID', 'PolicyID');
    }
    use HasFactory;
    public $table="myg_09_trip_claim_details";
    protected $fillable = [
        'TripClaimDetailID',
        'TripClaimID',
        'PolicyID',
        'FromDate',
        'ToDate',
        'TripFrom',
        'TripTo',
        'DocumentDate',
        'StartMeter',
        'EndMeter',
        'Qty',
        'UnitAmount',
        'DeductAmount',
        'NoOfPersons',
        'FileUrl',
        'Remarks',
        'NotificationFlg',
        'RejectionCount',
        'ApproverID',
        'Status',
        'user_id',
        'created_at',
        'updated_at',
        'approved_date',
        'rejected_date',
        'approver_remarks'
    ];

    


    public function personsDetails()
    {
        return $this->hasMany(Personsdetails::class, 'TripClaimDetailID', 'TripClaimDetailID');
    }
    public function policyDetails()
    {
        return $this->hasMany(Policy::class, 'PolicyID', 'PolicyID');
    }
    
    
    public function subCategoryDetails()
    {
        return $this->policyDetails->subCategoryDetails ?? null;
    }

    public function categorydata()
    {
        return $this->subCategoryDetails ? $this->subCategoryDetails->categorydatafrom : null;
    }
}
