<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;
use App\Models\Tripclaim;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    

    public function exportReport(Request $request)
    {
        $filters = $request->only([
            'FromDate', 'ToDate', 'Status', 'TripType', 'EmpID', 'GradeID', 'BranchID'
        ]);

        $query = Tripclaim::query()
            ->leftJoin('users', 'users.id', '=', 'myg_08_trip_claim.user_id')
            ->leftJoin('myg_01_triptypes as triptypes', 'triptypes.TripTypeID', '=', 'myg_08_trip_claim.TripTypeID')
            ->leftJoin('myg_11_branch as branches', 'branches.BranchID', '=', 'myg_08_trip_claim.VisitBranchID')
            ->leftJoin(DB::raw('(
                SELECT TripClaimID, SUM(Qty * UnitAmount) as total_amount
                FROM myg_09_trip_claim_details
                GROUP BY TripClaimID
            ) as totals'), 'totals.TripClaimID', '=', 'myg_08_trip_claim.TripClaimID')
              ->leftJoin(DB::raw('(
                    SELECT TripClaimID, 
                        SUM(DeductAmount) as deduct_total, 
                        SUM(UnitAmount) as unit_total,
                        SUM(DeductAmount - UnitAmount) as deduct_difference
                    FROM myg_09_trip_claim_details
                    GROUP BY TripClaimID
                ) as deducts'), 'deducts.TripClaimID', '=', 'myg_08_trip_claim.TripClaimID')

            ->select([
                'myg_08_trip_claim.*',
                'users.emp_name',
                'users.emp_id',
                'users.emp_grade',
                'users.emp_department',
                'triptypes.TripTypeName',
                'branches.BranchName',
                DB::raw('COALESCE(totals.total_amount, 0) as total_amount'),
                DB::raw('COALESCE(deducts.deduct_difference, 0) as deduct_amount')
            ]);

        
        // Filters
        if (!empty($filters['FromDate'])) {
            $query->whereDate('myg_08_trip_claim.created_at', '>=', $filters['FromDate']);
        }

        if (!empty($filters['ToDate'])) {
            $query->whereDate('myg_08_trip_claim.created_at', '<=', $filters['ToDate']);
        }

        if (!empty($filters['Status'])) {
            $query->where('myg_08_trip_claim.Status', $filters['Status']);
        }

        if (!empty($filters['TripType'])) {
            $query->where('myg_08_trip_claim.TripTypeID', $filters['TripType']);
        }

        if (!empty($filters['EmpID'])) {
            $query->where('users.emp_id', $filters['EmpID']);
        }

        if (!empty($filters['GradeID'])) {
            $query->where('users.emp_grade', $filters['GradeID']);
        }

        if (!empty($filters['BranchID'])) {
            $query->where('myg_08_trip_claim.VisitBranchID', $filters['BranchID']);
        }

        $results = $query->get();

        $branchList = DB::table('myg_11_branch')
        ->select('BranchID', 'BranchName', 'BranchCode')
        ->get()
        ->mapWithKeys(function ($b) {
            return [
                $b->BranchID => $b->BranchName . ' (' . $b->BranchCode . ')'
            ];
        });
        // Prepare data
        $rows = $results->map(function ($row) use($branchList) {
            
            $visitBranchIDs = collect(explode(',', $row->VisitBranchID));
            $branchNames = $visitBranchIDs
                ->map(fn($id) => $branchList[trim($id)] ?? null)
                ->filter()
                ->values();

            // If VisitBranchID failed (empty or all unknown), use VisitListBranchID
            if ($branchNames->isEmpty() && !empty($row->VisitListBranchID)) {
                $listIDs = json_decode($row->VisitListBranchID, true); // assumes it's a JSON array like "[1,4]"
                $branchNames = collect($listIDs)
                    ->map(fn($id) => $branchList[$id] ?? null)
                    ->filter()
                    ->values();
            }
            return [
                'Trip ID'        => 'TMG' . substr($row->TripClaimID, 8),
                'Date'           => \Carbon\Carbon::parse($row->created_at)->format('Y-m-d'),
                'Trip Type'      => $row->TripTypeName,
                'Employee Name'  => $row->emp_name,
                'Employee ID'    => $row->emp_id,
                'Visit Branch'   => $branchNames->implode(', '),
                'Grade'          => $row->emp_grade,
                'Department'     => $row->emp_department,
                'Amount'         => $row->total_amount,
                'Deduct Amount'  => $row->deduct_amount,
                'Status'         => $row->Status,
                'Approval Date'  => $row->ApprovalDate ?? 'NA'
            ];
        });

        // Write CSV file
        $filePath = storage_path('app/public/report_management_export.csv');

        SimpleExcelWriter::create($filePath)
            ->addRows($rows->toArray());

        return response()->download($filePath)->deleteFileAfterSend();
    }

}
