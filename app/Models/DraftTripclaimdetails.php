<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftTripclaimdetails extends Model
{
    public function policyDet()
    {
        return $this->belongsTo(Policy::class, 'PolicyID', 'PolicyID');
    }
    use HasFactory;
    public $table="myg_14_draft_trip_claim_details";
    protected $primaryKey = 'TripClaimDetailID'; // replace with your actual PK column

    public $incrementing = false; // if your PK is not auto-increment
    protected $keyType = 'string'; 
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
        'SortOrder',
        'approver_remarks'
    ];

    


    public function personsDetails()
    {
        return $this->hasMany(DraftPersonsdetails::class, 'TripClaimDetailID', 'TripClaimDetailID');
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
