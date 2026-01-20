<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class DraftClaimManagement extends Model
{
    use HasFactory;
    public $table="myg_13_draft_trip_claim";
    protected $fillable = [
        'TripClaimID',
        'TripTypeID',
        'ApproverID',
        'TripPurpose',
        'VisitBranchID',
        'VisitListBranchID',
        'AdvanceAmount',
        'ApprovalDate',
        'RejectionCount',
        'NotificationFlg',
        'AppNotificationFlg',
        'Status',
        'user_id',
        'transaction_date'
    ];

    public function getVisitBranchListAttribute()
    {
        // If single branch is available
        if (!empty($this->VisitBranchID)) {
            $branch = Branch::where('BranchID', $this->VisitBranchID)->first();
            return collect([$branch])->filter(); // Wrap in collection for consistency
        }

        // Else decode list of branch IDs
        $ids = $this->VisitListBranchID;
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        if (!is_array($ids)) {
            return collect(); // Return empty collection if invalid
        }

        return Branch::whereIn('BranchID', $ids)->get();
    }

    public function tripclaimdetails()
    {
        return $this->belongsTo(DraftTripclaimdetails::class, 'TripClaimID','TripClaimID');
    }
    public function tripclaimdetailsforclaim(){
        return $this->hasMany(DraftTripclaimdetails::class, 'TripClaimID','TripClaimID');
    }
    public function sumTripClaimDetailsValue()
    {
        return $this->tripclaimdetails()
            ->select(DB::raw('SUM(Qty * UnitAmount) as total_value'))
            ->where('Status', 'Approved')  // Filter where status is 'Approved'
            ->pluck('total_value')
            ->first(); // Get the first item since we expect a single value
    }
    public function sumTripClaimDetailsValue1()
    {
        return $this->tripclaimdetails()
            ->select(DB::raw('SUM(Qty * UnitAmount) as total_value'))
            // ->where('Status', 'Approved')  // Filter where status is 'Approved'
            ->pluck('total_value')
            ->first(); // Get the first item since we expect a single value
    }
    public function sumTripClaimDetailsValue2()
    {
        return $this->tripclaimdetails()
            ->select(DB::raw('SUM(Qty * DeductAmount) as total_value'))
            ->pluck('total_value')
            ->first(); // Get the first item since we expect a single value
    }
    
    public function visitbranchdetails()
    {
        return $this->belongsTo(Branch::class, 'VisitBranchID','BranchID');
    }
public static function adminvisitlistbranchdetails($VisitListBranchID)
{
    if (!is_array($VisitListBranchID)) {
        return collect();
    }

    return \App\Models\Branch::whereIn('BranchID', $VisitListBranchID)
        ->get()
        ->map(function ($branch) {
            return [
                'branch_id' => $branch->BranchID,
                'branch_name' => $branch->BranchName,
                'branch_code' => $branch->BranchCode,
            ];
        });
}

    public static function visitlistbranchdetails($VisitListBranchID)
    {
        $ids = $VisitListBranchID;
        if (!is_array($ids)) {
            return collect();
        }

        return Branch::whereIn('BranchID', $ids)
            ->get()
            ->map(function ($branch) {
                return [
                    'branch_id' => $branch->BranchID,
                    'branch_name' => $branch->BranchName,
                ];
            });
    //    return is_array($ids) ? Branch::whereIn('BranchID', $ids)->get() : collect();
    }

    public function userdetails()
    {
        return $this->belongsTo(User::class,'ApproverID', 'id');
    }

    public function usercodedetails()
    {
        return $this->belongsTo(User::class,'ApproverID', 'emp_id');
    }
    
    public function userdata()
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }
    
    public function triptypedetails()
    {
        return $this->belongsTo(Triptype::class, 'TripTypeID','TripTypeID');
    }

    

}
