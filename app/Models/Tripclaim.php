<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Tripclaim extends Model
{
    public function tripclaimdetails()
    {
        return $this->hasMany(Tripclaimdetails::class, 'TripClaimID','TripClaimID');
    }
    
    
    use HasFactory;
    public $table="myg_08_trip_claim";
    protected $fillable = [
        'TripClaimID',
        'TripTypeID',
        'ApproverID',
        'SpecialApproverID',
        'CMDApproverID',
        'TripPurpose',
        'VisitBranchID',
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
        return $this->belongsTo(Branch::class, 'BranchID','VisitBranchID');
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
