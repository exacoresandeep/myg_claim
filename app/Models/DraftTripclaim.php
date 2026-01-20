<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Branch;
class DraftTripclaim extends Model
{
    public function tripclaimdetails()
    {
        return $this->hasMany(DraftTripclaimdetails::class, 'TripClaimID','TripClaimID');
    }
    
    public function getVisitBranchesAttribute()
    {
        // Decode JSON value safely
        $branchIds = json_decode($this->VisitListBranchID, true);

        // If it’s not an array, or null, return empty collection
        if (empty($branchIds) || !is_array($branchIds)) {
            return collect();
        }

        // Fetch all branches from Branch model
        return \App\Models\Branch::whereIn('BranchID', $branchIds)->get(['BranchID', 'BranchName']);
    }

    
    use HasFactory;
    public $table="myg_13_draft_trip_claim";
    protected $primaryKey = 'TripClaimID'; // replace with your actual PK column

    public $incrementing = false; // if your PK is not auto-increment
    protected $keyType = 'string'; 
    protected $fillable = [
        'TripClaimID',
        'TripTypeID',
        'ApproverID',
        'SpecialApproverID',
        'CMDApproverID',
        'TripPurpose',
        'VisitBranchID',
        'VisitListBranchID',
        'ApprovalDate',
        'AdvanceAmount',
        'ApprovalDate',
        'RejectionCount',
        'NotificationFlg',
        'Status',
        'user_id',
        'transaction_date'
    ];

    public function userdata() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function gradedetails() {
        return $this->belongsTo(Grade::class, 'GradeID');
    }

    public function visitbranchdetails()
    {
        return $this->belongsTo(Branch::class,'VisitBranchID','BranchID');
    }

    
    public function approverdetails()
    {
        return $this->hasMany(User::class, 'emp_id','ApproverID');
    }

    public function specialapproverdetails()
    {
        return $this->hasMany(User::class, 'emp_id','SpecialApproverID');
    }
    public function cmdapproverdetails()
    {
        return $this->hasMany(User::class, 'emp_id','CMDApproverID');
    }

    public function financeApproverdetails()
    {
        return $this->hasMany(User::class, 'emp_id','FinanceApproverID');
    }
    
    public function triptypedetails()
    {
        return $this->belongsTo(Triptype::class, 'TripTypeID','TripTypeID');
    }
    public function tripuserdetails()
    {
        return $this->hasMany(User::class, 'id','user_id');
    }

}
