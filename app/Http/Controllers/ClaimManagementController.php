<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClaimManagement;
use App\Models\Tripclaim;
use App\Models\SubCategories;
use App\Models\AdvanceList;
use App\Models\Tripclaimdetails;
use App\Models\Branch;
use App\Models\Triptype;
use App\Models\Grades;
use App\Models\Policy;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Personsdetails;
use Auth;
use DB;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon; // Import Carbon
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\ReportManagementExport;


class ClaimManagementController extends Controller
{
	// public function report_management_exporta(Request $request)
	// {
	// 	$fileName = 'Trip_Claims_' . now()->format('Ymd_His') . '.xlsx';

	// 	return Excel::download(new ReportManagementExport($request), $fileName)->withCookie(cookie('fileDownload', true, 5));;
	// }
	public function getbranchNameByID($id){
        $branchdata     = Branch::where('BranchID', '=', $id)->select('BranchName')->first();
        return $branchdata->BranchName;
    }
    public function approved_claims()
    {
        return view('admin.claim_management.approved_claims');
    }
    public function approved_claims_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'TripClaimID',
	            'created_at'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
	        $query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
                         ->where('Status', 'Approved')
						->whereDoesntHave('tripclaimdetails', function ($q) {
							$q->where('Status', '<>', 'Approved');
						})
						 ->where(function ($q) use ($searchValue) {
							$searchValue = str_replace(' ', '', $searchValue); 
							if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
								$q->whereRaw('1 = 1');
							} else {
								if (strpos($searchValue, 'TMG') === 0) {
									$searchValue = substr($searchValue, 3); // Remove 'TMG' prefix
									if (!empty($searchValue)) {
										$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
									} else {
										$q->where('TripClaimID', 'like', '%');
									}
								} else {
									$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
								}
							}
			
							$q->orWhereHas('userdata', function ($q) use ($searchValue) {
								$q->where('emp_name', 'like', '%' . $searchValue . '%')
								->orWhere('emp_id', 'like', '%' . $searchValue . '%');
							});
						})
                        ->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');

				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'emp_name' => ($row->userdata->emp_name ?? '-'),
					'emp_id' => ($row->userdata->emp_id ?? '-'),
					// 'VisitBranchID' => ($row->visitbranchdetails->BranchName ?? 'N/A') . '/' . ($row->visitbranchdetails->BranchCode ?? 'N/A'),
					'VisitBranchID' => $row->visit_branch_list->map(function ($branch) {
						return $branch->BranchName . ' (' . $branch->BranchCode . ')';
					})->implode(', '),
					'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
					'TotalAmount' => $TotalAmount,
					'action' => '<a href="'. route('approved_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>',
				];
			});
			// <a .href="javascript:void(0);" onclick="openCompleteModal(\''.$row->TripClaimID.'\')" class="btn btn-success"><i class="fa fa-check-square" aria-hidden="true"></i> Mark as Complete</a>
	        return response()->json([
	            "draw" => $request->draw,
	            "recordsTotal" => $recordsTotal,
	            "recordsFiltered" => $recordsFiltered,
	            'data' => $formattedData
	        ], 200);
	    }
    }
	public function approved_claims_view($id)
    {
        $data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails','tripclaimdetailsforclaim'])
			->where('TripClaimID', $id)
			->first();
			$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
			])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
			$update=ClaimManagement::where('TripClaimID', $id)->update(['AppNotificationFlg'=>'1']);
			$userdet=User::where('id',auth()->id() )->first();
			$advanceBalance = AdvanceList::where('user_id', $tripdata->user_id)
			->where('Status', 'Paid')
			->sum('Amount');
			$advanceBalance = number_format($advanceBalance, 2, '.', '');
			// Calculate the total amount for the trip claim
			$tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
			return $detail->UnitAmount * $detail->Qty;
			});
			$tripAmount = number_format($tripAmount, 2, '.', '');
	
			// Determine the status of the trip claim
			$statuses = $tripdata->tripclaimdetails->pluck('Status');
			$rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
			if ($statuses->every(fn($status) => $status === 'Approved')){
			$tripStatus = 'Approved';
			// if ($tripdata->Status === 'Paid'){
			//     $tripStatus = 'Paid';
			// } elseif ($tripdata->Status === 'Pending'){
			//     $tripStatus = 'Pending';
			// }
			} elseif ($statuses->contains('Rejected')) {
			$tripStatus = 'Rejected';
			} else {
			$tripStatus = 'Pending';
			}
	
			// Determine the trip history status
			$tripHistoryStatus = $tripStatus;
			if ($tripStatus === 'Pending') {
			$maxRejectionCount = $rejectionCounts->max();
			if ($maxRejectionCount == 1) {
				$tripHistoryStatus = 'ReSubmited';
			} elseif ($maxRejectionCount == 0) {
				$tripHistoryStatus = 'Pending';
			} elseif ($maxRejectionCount == 2) {
				$tripHistoryStatus = 'Rejected';
			}
			}
	
			// Format the approved and rejected dates
			$tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
			$tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
			$tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
			$tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
	
			$result = [
			"trip_claim_id" => $tripdata->TripClaimID,
			"trip_type_details" => $tripdata->triptypedetails->first() ? [
			"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
			"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
			] : null,
			"approver_details" => $tripdata->approverdetails->first() ? [
			"id" => $tripdata->approverdetails->first()->id,
			"emp_id" => $tripdata->approverdetails->first()->emp_id,
			"emp_name" => $tripdata->approverdetails->first()->emp_name
			] : null,
			"trip_purpose" => $tripdata->TripPurpose,
			// "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			// "branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			// "branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			// ] : null,
			"visit_branch_detail" => $tripdata->visitbranchdetails->map(function ($branch) {
				return [
					"branch_id" => $branch->BranchID,
					"branch_name" => $branch->BranchName,
					"branch_code" => $branch->BranchCode
				];
			}),
			"user_details" => $tripdata->tripuserdetails->first() ? [
			"id" => $tripdata->tripuserdetails->first()->id,
			"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
			"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
			"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
			"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
			] : null,
			"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
			'date' => $tripdata->created_at->format('d/m/Y'),
			'trip_status' => $tripStatus,
			'trip_history_status' => $tripHistoryStatus,
			'trip_approver_remarks' => $tripdata->ApproverRemarks,
			'total_amount' => $tripAmount,
			'trip_approved_date' => $tripApprovedDate,
			'trip_rejected_date' => $tripRejectedDate,
			'approver_status' => $tripStatus,
			'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
			'finance_status_change_date' => $tripdata->ApprovalDate ? \Carbon\Carbon::parse($tripdata->ApprovalDate)->format('d/m/Y') : null,
			"finance_remarks" => $tripdata->FinanceRemarks,
			"transaction_id" => $tripdata->TransactionID,
			"settle_amount" => $tripdata->SettleAmount,
			"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
			"id" => $tripdata->financeApproverdetails->first()->id,
			"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
			"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
			] : null,
			'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
			return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
			})->map(function ($groupedDetails, $categoryID) use($tripdata){
			$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
			$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
			$policy = $groupedDetails->first()->policyDet;
			return [
			"category_id" => $categoryID,
			"category_name" => $category->CategoryName ?? null,
			"image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
			"trip_from_flag" => (bool)$category->TripFrom,
			"trip_to_flag" => (bool)$category->TripTo,
			"from_date_flag" => (bool)$category->FromDate,
			"to_date_flag" => (bool)$category->ToDate,
			"document_date_flag" => (bool)$category->DocumentDate,
			"start_meter_flag" => (bool)$category->StartMeter,
			"end_meter_flag" => (bool)$category->EndMeter,
			"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID,$tripdata->tripuserdetails->first()->id),
			"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy,$categoryID) {
				return [
					"trip_claim_details_id" => $detail->TripClaimDetailID,
					"from_date" => $detail->FromDate,
					"to_date" => $detail->ToDate,
					"trip_from" => $detail->TripFrom,
					"trip_to" => $detail->TripTo,
					"document_date" => $detail->DocumentDate,
					"start_meter" => $detail->StartMeter,
					"end_meter" => $detail->EndMeter,
					"qty" => $detail->Qty,
					"status" => $detail->Status,
					"unit_amount" => $detail->UnitAmount,
					"no_of_persons" => $detail->NoOfPersons,
					"file_url" => $detail->FileUrl,
					"remarks" => $detail->Remarks,
					"approver_id" => $detail->ApproverID,
					"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
					"approver_remarks" => $detail->approver_remarks,
					"notification_flg" => $detail->NotificationFlg,
					"rejection_count" => $detail->RejectionCount,
					"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID,$subcategory) {
						return $person->userDetails->map(function ($user) use ($categoryID,$subcategory){
							return [
								"id" => $user->id,
								"emp_id" => $user->emp_id,
								"emp_name" => $user->emp_name,
								"emp_grade" => $user->emp_grade,
								"user_policy" => $this->user_policy($user->emp_grade,$categoryID,$subcategory->SubCategoryID)
							];
						});
					}),
					"policy_details" => $subcategory->SubCategoryID ? [
						"sub_category_id" => $subcategory->SubCategoryID,
						"sub_category_name" => $subcategory->SubCategoryName,
						"policy_id" => $policy->PolicyID,
						"grade_id" => $policy->GradeID,
						"grade_type" => $policy->GradeType,
						"grade_class" => $policy->GradeClass,
						"grade_amount" => $policy->GradeAmount,
					] : null,
					
				];
			}),
			];
			})->values()
			];						
			if (!$data) {
			return redirect()->back()->withErrors(['error' => 'Claim not found']);
			}
	
			$totalValue = $data->sumTripClaimDetailsValue();
			$claimDetails = $data->tripclaimdetailsforclaim->toArray();
			$dates = $this->extractDate($claimDetails);
	
			// Check if $dates contains elements
			if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
			} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
			}
			return view('admin.claim_management.approved_claims_view', compact('data', 'totalValue', 'interval', 'dates','result','advanceBalance','userdet'));
		
    }

	public function complete_approved_claim(Request $request)
    {
        $TripClaimID = $request->TripClaimID;
        // Perform the status change operation here
		$now = new \DateTime();  // Create a new DateTime object
		$currentdate = $now->format('Y-m-d H:i:s'); 
        $affected = ClaimManagement::where('TripClaimID', $TripClaimID)
                               ->update(['Status' => 'Paid','transaction_date'=>$currentdate]);
        if ($affected) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function requested_claims()
    {
        return view('admin.claim_management.approved_claims');
    }

    public function requested_claims_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'TripClaimID',
	            'Status'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
			$statusFilter = $request->input('statusFilter'); // add this

			if ($statusFilter == 'Pending') {
				$query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
					->whereHas('tripclaimdetails', function ($q) {
						$q->where('Status','!=','Approved')->where('Status', '!=', 'Rejected');
					});
			
			} elseif ($statusFilter == 'Resubmission Pending') {
				$query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
					->whereHas('tripclaimdetails', function ($q) {
						$q->where('Status', 'Rejected')->where('RejectionCount', 1);
					});
			
			} else {
				$query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
					->whereHas('tripclaimdetails', function ($q) {
						$q->where('Status', 'Pending')
						  ->orWhere(function ($q2) {
							  $q2->where('Status', 'Rejected')->where('RejectionCount', 1);
						  });
					});
			}
			
			
	        
			$query->where(function ($q) use ($searchValue) {
				$searchValue = str_replace(' ', '', $searchValue); 
				if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
					$q->whereRaw('1 = 1');
				} else {
					if (strpos($searchValue, 'TMG') === 0) {
						$searchValue = substr($searchValue, 3); // Remove 'TMG' prefix
						if (!empty($searchValue)) {
							$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
						} else {
							$q->where('TripClaimID', 'like', '%');
						}
					} else {
						$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
					}
				}

				$q->orWhereHas('userdata', function ($q) use ($searchValue) {
					$q->where('emp_name', 'like', '%' . $searchValue . '%')
					->orWhere('emp_id', 'like', '%' . $searchValue . '%');
				});
			})
			->orderBy('created_at', 'DESC');

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue1();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');
				
				
			
				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
					// 'VisitBranchID' => ($row->visitbranchdetails->BranchName ?? 'N/A') . '/' . ($row->visitbranchdetails->BranchCode ?? 'N/A'),
					'VisitBranchID' => $row->visit_branch_list->map(function ($branch) {
						return $branch->BranchName . ' (' . $branch->BranchCode . ')';
					})->implode(', '),
					'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
					'TotalAmount' => $TotalAmount,
					'Status' => $this->checkResubmissionStatus($row->TripClaimID),
					'action' => '<a href="'. route('requested_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>',
				];
			});
			
	        return response()->json([
	            "draw" => $request->draw,
	            "recordsTotal" => $recordsTotal,
	            "recordsFiltered" => $recordsFiltered,
	            'data' => $formattedData,
	        ], 200);
	    }
    }
	public function checkResubmissionStatus($tripClaimID)
	{
		$details = Tripclaimdetails::where('TripClaimID', $tripClaimID)->get();

		if ($details->contains(fn($item) => $item->Status === 'Rejected' && $item->RejectionCount == 1)) {
			return 'Resubmission Pending';
		}

		return 'Pending';
	}
	public function requested_claims_view($id)
    {
        $data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails','tripclaimdetailsforclaim'])
                                ->where('TripClaimID', $id)
                                ->first();
		$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
			])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
			
		// Calculate the total amount for the trip claim
		$tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
			return $detail->UnitAmount * $detail->Qty;
		});
		$tripAmount = number_format($tripAmount, 2, '.', '');

		// Determine the status of the trip claim
		$statuses = $tripdata->tripclaimdetails->pluck('Status');
		$rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
		if ($statuses->every(fn($status) => $status === 'Approved')){
			$tripStatus = 'Approved';
			// if ($tripdata->Status === 'Paid'){
			//     $tripStatus = 'Paid';
			// } elseif ($tripdata->Status === 'Pending'){
			//     $tripStatus = 'Pending';
			// }
		} elseif ($statuses->contains('Rejected')) {
			$tripStatus = 'Rejected';
		} else {
			$tripStatus = 'Pending';
		}

		// Determine the trip history status
		$tripHistoryStatus = $tripStatus;
		if ($tripStatus === 'Pending') {
			$maxRejectionCount = $rejectionCounts->max();
			if ($maxRejectionCount == 1) {
				$tripHistoryStatus = 'ReSubmited';
			} elseif ($maxRejectionCount == 0) {
				$tripHistoryStatus = 'Pending';
			} elseif ($maxRejectionCount == 2) {
				$tripHistoryStatus = 'Rejected';
			}
		}

		// Format the approved and rejected dates
		$tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
		$tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
		$tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
		$tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                   
			$result = [
				"trip_claim_id" => $tripdata->TripClaimID,
				"trip_type_details" => $tripdata->triptypedetails->first() ? [
					"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
					"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
				] : null,
				"approver_details" => $tripdata->approverdetails->first() ? [
					"id" => $tripdata->approverdetails->first()->id,
					"emp_id" => $tripdata->approverdetails->first()->emp_id,
					"emp_name" => $tripdata->approverdetails->first()->emp_name
				] : null,
				"trip_purpose" => $tripdata->TripPurpose,
				
				"user_details" => $tripdata->tripuserdetails->first() ? [
					"id" => $tripdata->tripuserdetails->first()->id,
					"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
					"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
					"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
					"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
				] : null,
				"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
				'date' => $tripdata->created_at->format('d/m/Y'),
				'trip_status' => $tripStatus,
				'trip_history_status' => $tripHistoryStatus,
				'trip_approver_remarks' => $tripdata->ApproverRemarks,
				'total_amount' => $tripAmount,
				'trip_approved_date' => $tripApprovedDate,
				'trip_rejected_date' => $tripRejectedDate,
				'approver_status' => $tripStatus,
				'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
				'finance_status_change_date' => $tripdata->ApprovalDate ? \Carbon\Carbon::parse($tripdata->ApprovalDate)->format('d/m/Y') : null,
				"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
					"id" => $tripdata->financeApproverdetails->first()->id,
					"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
					"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
				] : null,
				'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
					return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
				})->map(function ($groupedDetails, $categoryID) use($tripdata){
					$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
					$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
					$policy = $groupedDetails->first()->policyDet;
					return [
						"category_id" => $categoryID,
						"category_name" => $category->CategoryName ?? null,
						"image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
						"trip_from_flag" => (bool)$category->TripFrom,
						"trip_to_flag" => (bool)$category->TripTo,
						"from_date_flag" => (bool)$category->FromDate,
						"to_date_flag" => (bool)$category->ToDate,
						"document_date_flag" => (bool)$category->DocumentDate,
						"start_meter_flag" => (bool)$category->StartMeter,
						"end_meter_flag" => (bool)$category->EndMeter,
						"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID,$tripdata->tripuserdetails->first()->id),
						"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy,$categoryID) {
							return [
								"trip_claim_details_id" => $detail->TripClaimDetailID,
								"from_date" => $detail->FromDate,
								"to_date" => $detail->ToDate,
								"trip_from" => $detail->TripFrom,
								"trip_to" => $detail->TripTo,
								"document_date" => $detail->DocumentDate,
								"start_meter" => $detail->StartMeter,
								"end_meter" => $detail->EndMeter,
								"qty" => $detail->Qty,
								"status" => $detail->Status,
								"unit_amount" => $detail->UnitAmount,
								"no_of_persons" => $detail->NoOfPersons,
								"file_url" => $detail->FileUrl,
								"remarks" => $detail->Remarks,
								"approver_remarks" => $detail->approver_remarks,
								"notification_flg" => $detail->NotificationFlg,
								"rejection_count" => $detail->RejectionCount,
								"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID,$subcategory) {
									return $person->userDetails->map(function ($user) use ($categoryID,$subcategory){
										return [
											"id" => $user->id,
											"emp_id" => $user->emp_id,
											"emp_name" => $user->emp_name,
											"emp_grade" => $user->emp_grade,
											"user_policy" => $this->user_policy($user->emp_grade,$categoryID,$subcategory->SubCategoryID)
										];
									});
								}),
								"policy_details" => $subcategory->SubCategoryID ? [
									"sub_category_id" => $subcategory->SubCategoryID,
									"sub_category_name" => $subcategory->SubCategoryName,
									"policy_id" => $policy->PolicyID,
									"grade_id" => $policy->GradeID,
									"grade_type" => $policy->GradeType,
									"grade_class" => $policy->GradeClass,
									"grade_amount" => $policy->GradeAmount,
								] : null,
								
							];
						}),
					];
				})->values()
			];	
		if (!empty($tripdata->VisitBranchID)) {
			// Case: Single Branch
			$branches = \App\Models\Branch::where('BranchID', $tripdata->VisitBranchID)
				->get()
				->map(function ($branch) {
					return [
						'branch_id' => $branch->BranchID,
						'branch_name' => $branch->BranchName,
						'branch_code' => $branch->BranchCode,
					];
				});
		} else {
			// Case: Multiple Branches
			$visitListIds = json_decode($tripdata->VisitListBranchID, true);
			$branches = ClaimManagement::adminvisitlistbranchdetails($visitListIds);
		}

		// Attach to result
		$result['visit_branch_detail'] = $branches;						
        if (!$data) {
            return redirect()->back()->withErrors(['error' => 'Claim not found']);
        }
		
		$totalValue = $data->sumTripClaimDetailsValue1();
		$claimDetails = $data->tripclaimdetailsforclaim->toArray();
		$dates = $this->extractDate($claimDetails);

		// Check if $dates contains elements
		if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
		} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
		}
		return view('admin.claim_management.requested_claims_view', compact('data', 'totalValue', 'interval', 'dates','result'));
    }
    
	
    public function getsubcategoryDetails($categoryID,$userid){
        $user = DB::table('users')->where('id', $userid)->first();
        $gradeID = $user->emp_grade;

        $subcategories = SubCategories::where('CategoryID', $categoryID)
            ->with(['policies' => function($query) use ($gradeID) {
                $query->where('GradeID', $gradeID);
            }])
            ->get();

        if ($subcategories->isEmpty()) {
            return [];
        }

        $subcategoryDetails = $subcategories->map(function ($subcategory) use ($gradeID) {
            $policyObject = $subcategory->policies->first(function ($policy) use ($gradeID) {
                return $policy->GradeID == $gradeID;
            });

            if ($policyObject) {
                return [
                    'subcategory_id' => $subcategory->SubCategoryID,
                    'subcategory_name' => $subcategory->SubCategoryName,
                    'status' => '1',
                    'policies' => (object) [
                        'policy_id' => $policyObject->PolicyID,
                        'grade_id' => $policyObject->GradeID,
                        'grade_type' => $policyObject->GradeType,
                        'grade_class' => $policyObject->GradeClass,
                        'grade_amount' => $policyObject->GradeAmount,
                        'approver' => $policyObject->Approver ?? 'NA',
                        'status' => '1',
                    ]
                ];
            }

            return null; // Exclude subcategories without matching policies
        })->filter()->values();

        return $subcategoryDetails;
    }

    private function extractDate($claimDetails)
    {
        $dates = [];
        foreach ($claimDetails as $claimDetail) {
            $this->addDatesBet($claimDetail['FromDate'], $claimDetail['ToDate'], $dates);
            $dates[] = substr($claimDetail['DocumentDate'], 0, 10);
        }

        // Remove blank entries
        $dates = array_filter($dates, function($date) {
            return !empty($date);
        });

        // Remove duplicate dates
        $dates = array_unique($dates);

        // Sort the dates
        sort($dates);

        return $dates;
    }

    private function addDatesBet($fromDate, $toDate, &$dates)
    {
        $period = new DatePeriod(
            new DateTime(substr($fromDate, 0, 10)),
            new DateInterval('P1D'),
            (new DateTime(substr($toDate, 0, 10)))->modify('+1 day')
        );

        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }
    }

    public function settled_claims()
    {
        return view('admin.claim_management.settled_claims');
    }

	public function settled_claims_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'TripClaimID',
	            'created_at'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
	        $query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
                        ->where('Status', 'Paid')
						->where(function ($q) use ($searchValue) {
							$searchValue = str_replace(' ', '', $searchValue); 
							if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
								$q->whereRaw('1 = 1');
							} else {
								if (strpos($searchValue, 'TMG') === 0) {
									$searchValue = substr($searchValue, 3); // Remove 'TMG' prefix
									if (!empty($searchValue)) {
										$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
									} else {
										$q->where('TripClaimID', 'like', '%');
									}
								} else {
									$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
								}
							}
			
							$q->orWhereHas('userdata', function ($q) use ($searchValue) {
								$q->where('emp_name', 'like', '%' . $searchValue . '%')
								->orWhere('emp_id', 'like', '%' . $searchValue . '%');
							});
						})
                        ->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');

				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
					// 'VisitBranchID' => ($row->visitbranchdetails->BranchName ?? 'N/A') . '/' . ($row->visitbranchdetails->BranchCode ?? 'N/A'),
					'VisitBranchID' => $row->visit_branch_list->map(function ($branch) {
						return $branch->BranchName . ' (' . $branch->BranchCode . ')';
					})->implode(', '),
					'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
					'TotalAmount' => $TotalAmount,
					'TransactionID' =>  $row->TransactionID,
					'action' => '<a href="'. route('settled_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>',
				];
			});

	        return response()->json([
	            "draw" => $request->draw,
	            "recordsTotal" => $recordsTotal,
	            "recordsFiltered" => $recordsFiltered,
	            'data' => $formattedData
	        ], 200);
	    }
    }

    public function settled_claims_view($id)
    {
		$data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails','tripclaimdetailsforclaim'])
		->where('TripClaimID', $id)
		->first();
		$tripdata = Tripclaim::with([
		'tripclaimdetails.policyDet.subCategoryDetails.category',
		'tripclaimdetails.personsDetails.userDetails'
		])
		->where('TripClaimID', $id)
		->first(); // Use first() instead of get() for a single 
		$userdet=User::where('id',auth()->id() )->first();
		$advanceBalance = AdvanceList::where('user_id', $tripdata->user_id)
		->where('Status', 'Paid')
		->sum('Amount');
		$advanceBalance = number_format($advanceBalance, 2, '.', '');
		// Calculate the total amount for the trip claim
		$tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
		return $detail->UnitAmount * $detail->Qty;
		});
		$tripAmount = number_format($tripAmount, 2, '.', '');

		// Determine the status of the trip claim
		$statuses = $tripdata->tripclaimdetails->pluck('Status');
		$rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
		if ($statuses->every(fn($status) => $status === 'Approved')){
		$tripStatus = 'Approved';
		} elseif ($statuses->contains('Rejected')) {
		$tripStatus = 'Rejected';
		} else {
		$tripStatus = 'Pending';
		}

		// Determine the trip history status
		$tripHistoryStatus = $tripStatus;
		if ($tripStatus === 'Pending') {
		$maxRejectionCount = $rejectionCounts->max();
		if ($maxRejectionCount == 1) {
			$tripHistoryStatus = 'ReSubmited';
		} elseif ($maxRejectionCount == 0) {
			$tripHistoryStatus = 'Pending';
		} elseif ($maxRejectionCount == 2) {
			$tripHistoryStatus = 'Rejected';
		}
		}

		// Format the approved and rejected dates
		$tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
		$tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
		$tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
		$tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;

		$result = [
		"trip_claim_id" => $tripdata->TripClaimID,
		"trip_type_details" => $tripdata->triptypedetails->first() ? [
		"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
		"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
		] : null,
		"approver_details" => $tripdata->approverdetails->first() ? [
		"id" => $tripdata->approverdetails->first()->id,
		"emp_id" => $tripdata->approverdetails->first()->emp_id,
		"emp_name" => $tripdata->approverdetails->first()->emp_name
		] : null,
		"trip_purpose" => $tripdata->TripPurpose,
		 "visit_branch_detail" => $tripdata->visitbranchdetails->map(function ($branch) {
                                return [
                                        "branch_id" => $branch->BranchID,
                                        "branch_name" => $branch->BranchName,
                                        "branch_code" => $branch->BranchCode
                                ];
                        }),

		"user_details" => $tripdata->tripuserdetails->first() ? [
		"id" => $tripdata->tripuserdetails->first()->id,
		"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
		"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
		"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
		"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
		] : null,
		"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
		'date' => $tripdata->created_at->format('d/m/Y'),
		'trip_status' => $tripStatus,
		'trip_history_status' => $tripHistoryStatus,
		'trip_approver_remarks' => $tripdata->ApproverRemarks,
		'total_amount' => $tripAmount,
		'trip_approved_date' => $tripApprovedDate,
		'trip_rejected_date' => $tripRejectedDate,
		'approver_status' => $tripStatus,
		"finance_remarks" => $tripdata->FinanceRemarks,
		"transaction_id" => $tripdata->TransactionID,
		"settle_amount" => $tripdata->SettleAmount,
		'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
		'finance_status_change_date' => $tripdata->transaction_date ? \Carbon\Carbon::parse($tripdata->transaction_date)->format('d/m/Y') : null,
		"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
		"id" => $tripdata->financeApproverdetails->first()->id,
		"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
		"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
		] : null,
		'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
		return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
		})->map(function ($groupedDetails, $categoryID) use($tripdata){
		$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
		$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
		$policy = $groupedDetails->first()->policyDet;
		return [
		"category_id" => $categoryID,
		"category_name" => $category->CategoryName ?? null,
		"image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
		"trip_from_flag" => (bool)$category->TripFrom,
		"trip_to_flag" => (bool)$category->TripTo,
		"from_date_flag" => (bool)$category->FromDate,
		"to_date_flag" => (bool)$category->ToDate,
		"document_date_flag" => (bool)$category->DocumentDate,
		"start_meter_flag" => (bool)$category->StartMeter,
		"end_meter_flag" => (bool)$category->EndMeter,
		"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID,$tripdata->tripuserdetails->first()->id),
		"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy,$categoryID) {
			return [
				"trip_claim_details_id" => $detail->TripClaimDetailID,
				"from_date" => $detail->FromDate,
				"to_date" => $detail->ToDate,
				"trip_from" => $detail->TripFrom,
				"trip_to" => $detail->TripTo,
				"document_date" => $detail->DocumentDate,
				"start_meter" => $detail->StartMeter,
				"end_meter" => $detail->EndMeter,
				"qty" => $detail->Qty,
				"status" => $detail->Status,
				"unit_amount" => $detail->UnitAmount,
				"no_of_persons" => $detail->NoOfPersons,
				"file_url" => $detail->FileUrl,
				"remarks" => $detail->Remarks,
				"approver_id" => $detail->ApproverID,
				"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
				"approver_remarks" => $detail->approver_remarks,
				"notification_flg" => $detail->NotificationFlg,
				"rejection_count" => $detail->RejectionCount,
				"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID,$subcategory) {
					return $person->userDetails->map(function ($user) use ($categoryID,$subcategory){
						return [
							"id" => $user->id,
							"emp_id" => $user->emp_id,
							"emp_name" => $user->emp_name,
							"emp_grade" => $user->emp_grade,
							"user_policy" => $this->user_policy($user->emp_grade,$categoryID,$subcategory->SubCategoryID)
						];
					});
				}),
				"policy_details" => $subcategory->SubCategoryID ? [
					"sub_category_id" => $subcategory->SubCategoryID,
					"sub_category_name" => $subcategory->SubCategoryName,
					"policy_id" => $policy->PolicyID,
					"grade_id" => $policy->GradeID,
					"grade_type" => $policy->GradeType,
					"grade_class" => $policy->GradeClass,
					"grade_amount" => $policy->GradeAmount,
				] : null,
				
			];
		}),
		];
		})->values()
		];						
		if (!$data) {
		return redirect()->back()->withErrors(['error' => 'Claim not found']);
		}

		$totalValue = $data->sumTripClaimDetailsValue();
		$claimDetails = $data->tripclaimdetailsforclaim->toArray();
		$dates = $this->extractDate($claimDetails);

		// Check if $dates contains elements
		if (empty($dates)) {
		$interval = 'N/A'; // Or any default value you want to set
		} else {
		// Calculate interval for display purposes
		$fromDate = min($dates);
		$toDate = max($dates);
		$interval = "{$fromDate} to {$toDate}";
		}
		return view('admin.claim_management.settled_claims_view', compact('data', 'totalValue', 'interval', 'dates','result','advanceBalance','userdet'));
		
    }

	public function user_policy($GradeID, $CategoryID,$SubCategoryID)
	{
		$policy = Policy::join('myg_04_subcategories', 'myg_06_policies.SubCategoryID', '=', 'myg_04_subcategories.SubCategoryID')
			->where('myg_06_policies.GradeID', $GradeID)
			->where('myg_06_policies.SubCategoryID', $SubCategoryID)
			->where('myg_04_subcategories.CategoryID', $CategoryID)
			->orderBy('myg_06_policies.GradeAmount', 'desc') // Order by GradeAmount in descending order
			->first(); // Get the first record, which will have the max GradeAmount


		if ($policy) {
			if ($policy->GradeType == 'Class') {
				return $policy->GradeClass;
			} else {
				return $policy->GradeAmount;
			}
		} else {
			return 'NA';
		}
	}

    public function rejected_claims()
    {
        return view('admin.claim_management.rejected_claims');
    }

	public function rejected_claims_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'TripClaimID',
	            'created_at'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
	        $query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
                        ->where('Status', 'Rejected')
						
						->where(function ($q) use ($searchValue) {
							$searchValue = str_replace(' ', '', $searchValue); 
							if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
								$q->whereRaw('1 = 1');
							} else {
								if (strpos($searchValue, 'TMG') === 0) {
									$searchValue = substr($searchValue, 3); // Remove 'TMG' prefix
									if (!empty($searchValue)) {
										$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
									} else {
										$q->where('TripClaimID', 'like', '%');
									}
								} else {
									$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
								}
							}
			
							$q->orWhereHas('userdata', function ($q) use ($searchValue) {
								$q->where('emp_name', 'like', '%' . $searchValue . '%')
								->orWhere('emp_id', 'like', '%' . $searchValue . '%');
							});
						})
                        ->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');

				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
					// 'VisitBranchID' => ($row->visitbranchdetails->BranchName ?? 'N/A') . '/' . ($row->visitbranchdetails->BranchCode ?? 'N/A'),
					'VisitBranchID' => $row->visit_branch_list->map(function ($branch) {
						return $branch->BranchName . ' (' . $branch->BranchCode . ')';
					})->implode(', '),
					'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
					'TotalAmount' => $TotalAmount,
					'action' => '<a href="'. route('rejected_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>',
					
				];
			});

	        return response()->json([
	            "draw" => $request->draw,
	            "recordsTotal" => $recordsTotal,
	            "recordsFiltered" => $recordsFiltered,
	            'data' => $formattedData
	        ], 200);
	    }
    }
    public function rejected_claims_view($id)
    {
		
			$data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails','tripclaimdetailsforclaim'])
			->where('TripClaimID', $id)
			->first();
			$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
			])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
			$userdet=User::where('id',auth()->id() )->first();
			$advanceBalance = AdvanceList::where('user_id', $tripdata->user_id)
			->where('Status', 'Paid')
			->sum('Amount');
			$advanceBalance = number_format($advanceBalance, 2, '.', '');
			// Calculate the total amount for the trip claim
			$tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
			return $detail->UnitAmount * $detail->Qty;
			});
			$tripAmount = number_format($tripAmount, 2, '.', '');
	
			// Determine the status of the trip claim
			$statuses = $tripdata->tripclaimdetails->pluck('Status');
			$rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
			if ($statuses->every(fn($status) => $status === 'Approved')){
			$tripStatus = 'Approved';
			// if ($tripdata->Status === 'Paid'){
			//     $tripStatus = 'Paid';
			// } elseif ($tripdata->Status === 'Pending'){
			//     $tripStatus = 'Pending';
			// }
			} elseif ($statuses->contains('Rejected')) {
			$tripStatus = 'Rejected';
			} else {
			$tripStatus = 'Pending';
			}
	
			// Determine the trip history status
			$tripHistoryStatus = $tripStatus;
			if ($tripStatus === 'Pending') {
			$maxRejectionCount = $rejectionCounts->max();
			if ($maxRejectionCount == 1) {
				$tripHistoryStatus = 'ReSubmited';
			} elseif ($maxRejectionCount == 0) {
				$tripHistoryStatus = 'Pending';
			} elseif ($maxRejectionCount == 2) {
				$tripHistoryStatus = 'Rejected';
			}
			}
	
			// Format the approved and rejected dates
			$tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
			$tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
			$tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
			$tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
	
			$result = [
			"trip_claim_id" => $tripdata->TripClaimID,
			"trip_type_details" => $tripdata->triptypedetails->first() ? [
			"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
			"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
			] : null,
			"approver_details" => $tripdata->approverdetails->first() ? [
			"id" => $tripdata->approverdetails->first()->id,
			"emp_id" => $tripdata->approverdetails->first()->emp_id,
			"emp_name" => $tripdata->approverdetails->first()->emp_name
			] : null,
			"trip_purpose" => $tripdata->TripPurpose,
			// "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			// "branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			// "branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			// ] : null,

			"visit_branch_detail" => $tripdata->visitbranchdetails->map(function ($branch) {
				return [
					"branch_id" => $branch->BranchID,
					"branch_name" => $branch->BranchName,
					"branch_code" => $branch->BranchCode
				];
			}),
			"user_details" => $tripdata->tripuserdetails->first() ? [
			"id" => $tripdata->tripuserdetails->first()->id,
			"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
			"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
			"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
			"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
			] : null,
			"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
			'date' => $tripdata->created_at->format('d/m/Y'),
			'trip_status' => $tripStatus,
			'trip_history_status' => $tripHistoryStatus,
			'trip_approver_remarks' => $tripdata->ApproverRemarks,
			'total_amount' => $tripAmount,
			'trip_approved_date' => $tripApprovedDate,
			'trip_rejected_date' => $tripRejectedDate,
			'approver_status' => $tripStatus,
			"finance_remarks" => $tripdata->FinanceRemarks,
			"transaction_id" => $tripdata->TransactionID,
			"settle_amount" => $tripdata->SettleAmount,
			'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
			'finance_status_change_date' => $tripdata->ApprovalDate ? \Carbon\Carbon::parse($tripdata->ApprovalDate)->format('d/m/Y') : null,
			"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
			"id" => $tripdata->financeApproverdetails->first()->id,
			"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
			"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
			] : null,
			'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
			return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
			})->map(function ($groupedDetails, $categoryID) use($tripdata){
			$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
			$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
			$policy = $groupedDetails->first()->policyDet;
			return [
			"category_id" => $categoryID,
			"category_name" => $category->CategoryName ?? null,
			"image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
			"trip_from_flag" => (bool)$category->TripFrom,
			"trip_to_flag" => (bool)$category->TripTo,
			"from_date_flag" => (bool)$category->FromDate,
			"to_date_flag" => (bool)$category->ToDate,
			"document_date_flag" => (bool)$category->DocumentDate,
			"start_meter_flag" => (bool)$category->StartMeter,
			"end_meter_flag" => (bool)$category->EndMeter,
			"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID,$tripdata->tripuserdetails->first()->id),
			"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy,$categoryID) {
				return [
					"trip_claim_details_id" => $detail->TripClaimDetailID,
					"from_date" => $detail->FromDate,
					"to_date" => $detail->ToDate,
					"trip_from" => $detail->TripFrom,
					"trip_to" => $detail->TripTo,
					"document_date" => $detail->DocumentDate,
					"start_meter" => $detail->StartMeter,
					"end_meter" => $detail->EndMeter,
					"qty" => $detail->Qty,
					"status" => $detail->Status,
					"unit_amount" => $detail->UnitAmount,
					"no_of_persons" => $detail->NoOfPersons,
					"file_url" => $detail->FileUrl,
					"remarks" => $detail->Remarks,
					"approver_id" => $detail->ApproverID,
					"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
					"approver_remarks" => $detail->approver_remarks,
					"notification_flg" => $detail->NotificationFlg,
					"rejection_count" => $detail->RejectionCount,
					"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID,$subcategory) {
						return $person->userDetails->map(function ($user) use ($categoryID,$subcategory){
							return [
								"id" => $user->id,
								"emp_id" => $user->emp_id,
								"emp_name" => $user->emp_name,
								"emp_grade" => $user->emp_grade,
								"user_policy" => $this->user_policy($user->emp_grade,$categoryID,$subcategory->SubCategoryID)
							];
						});
					}),
					"policy_details" => $subcategory->SubCategoryID ? [
						"sub_category_id" => $subcategory->SubCategoryID,
						"sub_category_name" => $subcategory->SubCategoryName,
						"policy_id" => $policy->PolicyID,
						"grade_id" => $policy->GradeID,
						"grade_type" => $policy->GradeType,
						"grade_class" => $policy->GradeClass,
						"grade_amount" => $policy->GradeAmount,
					] : null,
					
				];
			}),
			];
			})->values()
			];						
			if (!$data) {
			return redirect()->back()->withErrors(['error' => 'Claim not found']);
			}
	
			$totalValue = $data->sumTripClaimDetailsValue();
			$claimDetails = $data->tripclaimdetailsforclaim->toArray();
			$dates = $this->extractDate($claimDetails);
	
			// Check if $dates contains elements
			if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
			} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
			}
			return view('admin.claim_management.rejected_claims_view', compact('data', 'totalValue', 'interval', 'dates','result','advanceBalance','userdet'));

		
    }
    public function ro_approved_claims()
    {
        return view('admin.claim_management.ro_approved_claims');
    }

	public function ro_approved_claims_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'TripClaimID',
	            'created_at'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
	        $query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
					->whereDoesntHave('tripclaimdetails', function ($q) {
						$q->where('Status', '<>', 'Approved');
					})
				->where('Status','Pending')
				->where(function ($q) use ($searchValue) {
					$searchValue = str_replace(' ', '', $searchValue); 
					if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
						$q->whereRaw('1 = 1');
					} else {
						if (strpos($searchValue, 'TMG') === 0) {
							$searchValue = substr($searchValue, 3); // Remove 'TMG' prefix
							if (!empty($searchValue)) {
								$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
							} else {
								$q->where('TripClaimID', 'like', '%');
							}
						} else {
							$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
						}
					}
	
					$q->orWhereHas('userdata', function ($q) use ($searchValue) {
						$q->where('emp_name', 'like', '%' . $searchValue . '%')
						->orWhere('emp_id', 'like', '%' . $searchValue . '%');
					});
				})
				->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');
				 $RODate = $row->tripclaimdetails ? $row->tripclaimdetails->where('Status', 'Approved')->where('TripClaimID',$row->TripClaimID)->max('approved_date'):"NA";

				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
					//'UserData' => "NA",
					// 'VisitBranchID' => ($row->visitbranchdetails->BranchName ?? 'N/A') . '/' . ($row->visitbranchdetails->BranchCode ?? 'N/A'),
					'VisitBranchID' => $row->visit_branch_list->map(function ($branch) {
						return $branch->BranchName . ' (' . $branch->BranchCode . ')';
					})->implode(', '),
					'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
					'RODate' => $RODate , // Format date properly
					'TotalAmount' => $TotalAmount ,
					'action' => '<a href="'. route('ro_approved_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>',
					
				];
			});



			// dd($formattedData);
			return response()->json([
				"draw" => $request->draw,
				"recordsTotal" => $recordsTotal,
				"recordsFiltered" => $recordsFiltered,
				'data' => $formattedData
			], 200);
	    }
    }

    public function ro_approved_claims_view($id)
    {
		
        $data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails' => function ($query) {
			$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
		},'tripclaimdetailsforclaim'])
								
                                ->where('TripClaimID', $id)
                                ->first();
		$RejectedCount = Tripclaimdetails::where('TripClaimID', $id)
		->where('Status', 'Rejected')
		->count();
		$tripdata = Tripclaim::with(['tripclaimdetails' => function ($query) {
				$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
			},
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
			])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
			$userdet=User::where('id',auth()->id() )->first();
			$advanceBalance = AdvanceList::where('user_id', $tripdata->user_id)
			->where('Status', 'Paid')
			->sum('Amount');
			$advanceBalance = number_format($advanceBalance, 2, '.', '');


			// Extract unique dates and employee IDs from claim details
			$uniqueCombos = collect();

			foreach ($tripdata->tripclaimdetails as $detail) {
				foreach ($detail->personsDetails as $person) {
					foreach ($person->userDetails as $user) {
						$uniqueCombos->push([
							'emp_id' => $user->emp_id,
							'dates' => collect([$detail->FromDate, $detail->ToDate, $detail->DocumentDate])->filter()->unique()->values()->toArray(),
						]);
					}
				}
			}
		   
			 // Create a map of attendance details by emp_id and date
			$attendanceMap = collect();
			$uniqueCombinations = [];
			foreach ($uniqueCombos as $combo) {
				foreach ($combo['dates'] as $date) {
					$attendance = Attendance::where('emp_code', $combo['emp_id'])
						->whereDate('date', $date)
						->first();
			
					if ($attendance) {
						// Create a unique key based on emp_id and date
						$uniqueKey = $combo['emp_id'] . '|' . $date;
			
						// Check if this combination has already been added
						if (!array_key_exists($uniqueKey, $uniqueCombinations)) {
							// Add to unique combinations array
							$uniqueCombinations[$uniqueKey] = true;
			
							// Add the attendance data to the map
							$attendanceMap->push([
								'emp_id' => $combo['emp_id'],
								'date' => $date,
								'punch_in' => $attendance->punch_in,
								'punch_out' => $attendance->punch_out,
								'duration' => $attendance->duration,
								'location_in' => $attendance->location_in,
								'location_out' => $attendance->location_out,
								'remarks' => $attendance->remarks,
							]);
						}
					}
				}
			}
                    // Calculate the total amount for the trip claim
                $tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');

                    // Determine the status of the trip claim
                    $statuses = $tripdata->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')){
                        $tripStatus = 'Approved';
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                    }

                    // Determine the trip history status
                    $tripHistoryStatus = $tripStatus;
                    if ($tripStatus === 'Pending') {
                        $maxRejectionCount = $rejectionCounts->max();
                        if ($maxRejectionCount == 1) {
                            $tripHistoryStatus = 'ReSubmited';
                        } elseif ($maxRejectionCount == 0) {
                            $tripHistoryStatus = 'Pending';
                        } elseif ($maxRejectionCount == 2) {
                            $tripHistoryStatus = 'Rejected';
                        }
                    }

                    // Format the approved and rejected dates
                    $tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
			$result = [
				"trip_claim_id" => $tripdata->TripClaimID,
				"RejectedCount" => $RejectedCount,
				"trip_type_details" => $tripdata->triptypedetails->first() ? [
					"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
					"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
				] : null,
				"approver_details" => $tripdata->approverdetails->first() ? [
					"id" => $tripdata->approverdetails->first()->id,
					"emp_id" => $tripdata->approverdetails->first()->emp_id,
					"emp_name" => $tripdata->approverdetails->first()->emp_name
				] : null,
				"trip_purpose" => $tripdata->TripPurpose,
				// "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
				// 	"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
				// 	"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
				// ] : null,
				"visit_branch_detail" => $tripdata->visitbranchdetails->map(function ($branch) {
					return [
						"branch_id" => $branch->BranchID,
						"branch_name" => $branch->BranchName,
						"branch_code" => $branch->BranchCode
					];
				}),
				"user_details" => $tripdata->tripuserdetails->first() ? [
					"id" => $tripdata->tripuserdetails->first()->id,
					"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
					"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
					"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
					"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
				] : null,
				"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
				'date' => $tripdata->created_at->format('d/m/Y'),
				'trip_status' => $tripStatus,
				'trip_history_status' => $tripHistoryStatus,
				'trip_approver_remarks' => $tripdata->ApproverRemarks,
				'total_amount' => $tripAmount,
				'trip_approved_date' => $tripApprovedDate,
				'trip_rejected_date' => $tripRejectedDate,
				'approver_status' => $tripStatus,
				'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
				'finance_status_change_date' => $tripdata->ApprovalDate ? \Carbon\Carbon::parse($tripdata->ApprovalDate)->format('d/m/Y') : null,
				"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
					"id" => $tripdata->financeApproverdetails->first()->id,
					"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
					"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
				] : null,
				'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
        return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
			})->map(function ($groupedDetails, $categoryID) use ($tripdata, $attendanceMap) {
				$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
				$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
				$policy = $groupedDetails->first()->policyDet;

        // Initialize unique combos for this category
        $uniqueCombos = collect();

        // Extract unique employee IDs and dates for this category
        foreach ($groupedDetails as $detail) {
            foreach ($detail->personsDetails as $person) {
                foreach ($person->userDetails as $user) {
                    $uniqueCombos->push([
                        'emp_id' => $user->emp_id,
                        'dates' => collect([$detail->FromDate, $detail->ToDate, $detail->DocumentDate])->filter()->unique()->values()->toArray(),
                    ]);
                }
            }
        }

        // Create a map of attendance details by emp_id and date for this category
        $attendanceMapForCategory = collect();
        $uniqueCombinations = [];
        foreach ($uniqueCombos as $combo) {
            foreach ($combo['dates'] as $date) {
                $attendance = Attendance::where('emp_code', $combo['emp_id'])
                    ->whereDate('date', $date)
                    ->first();

                if ($attendance) {
                    // Create a unique key based on emp_id and date
                    $uniqueKey = $combo['emp_id'] . '|' . $date;

                    // Check if this combination has already been added
                    if (!array_key_exists($uniqueKey, $uniqueCombinations)) {
                        // Add to unique combinations array
                        $uniqueCombinations[$uniqueKey] = true;

                        // Add the attendance data to the map for this category
                        $attendanceMapForCategory->push([
                            'emp_id' => $combo['emp_id'],
                            'date' => $date,
                            'punch_in' => $attendance->punch_in,
                            'punch_out' => $attendance->punch_out,
                            'duration' => $attendance->duration,
                            'location_in' => $attendance->location_in,
                            'location_out' => $attendance->location_out,
                            'remarks' => $attendance->remarks,
                        ]);
                    }
                }
            }
        }

        // Return category data with the attendance details for this category
        return [
            "category_id" => $categoryID,
            "category_name" => $category->CategoryName ?? null,
            "image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
            "trip_from_flag" => (bool)$category->TripFrom,
            "trip_to_flag" => (bool)$category->TripTo,
            "from_date_flag" => (bool)$category->FromDate,
            "to_date_flag" => (bool)$category->ToDate,
            "document_date_flag" => (bool)$category->DocumentDate,
            "start_meter_flag" => (bool)$category->StartMeter,
            "end_meter_flag" => (bool)$category->EndMeter,
			"class_flg" => $policy->GradeType === 'Class' ? 1 : 0,
            "subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
            "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy,$categoryID) {
                return [
					
					// "dollarsubcategory"=>$subcategory,
                    "trip_claim_details_id" => $detail->TripClaimDetailID,
                    "from_date" => $detail->FromDate,
                    "to_date" => $detail->ToDate,
                    "trip_from" => $detail->TripFrom,
                    "trip_to" => $detail->TripTo,
                    "document_date" => $detail->DocumentDate,
                    "start_meter" => $detail->StartMeter,
                    "end_meter" => $detail->EndMeter,
                    "qty" => $detail->Qty,
                    "status" => $detail->Status,
                    "unit_amount" => $detail->UnitAmount,
                    "no_of_persons" => $detail->NoOfPersons,
                    "file_url" => $detail->FileUrl,
                    "remarks" => $detail->Remarks,
					"approver_id" => $detail->ApproverID,
					"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
                    "approver_remarks" => $detail->approver_remarks,
                    "notification_flg" => $detail->NotificationFlg,
                    "rejection_count" => $detail->RejectionCount,
                    "person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID,$subcategory,$detail) {
						return $person->userDetails->map(function ($user) use ($categoryID,$subcategory,$detail){
							$is_duplication=[];
							if($detail->FromDate!="")
							{
								$is_duplication=$this->checkDuplicateClaimsForView($user->id,$detail->FromDate,$categoryID,$detail->TripClaimDetailID);
							}
						
							return [
								"id" => $user->id,
								"emp_id" => $user->emp_id,
								"emp_name" => $user->emp_name,
								"emp_grade" => $user->emp_grade,
								"user_policy" => $this->user_policy($user->emp_grade,$categoryID,$subcategory->SubCategoryID),
								"is_duplication" => !empty($is_duplication),
                                "duplication_claim_id"=> $is_duplication['trip_claim_id'] ?? null
							];
						});
					}),
                    "policy_details" => $subcategory->SubCategoryID ? [
                        "sub_category_id" => $subcategory->SubCategoryID,
                        "sub_category_name" => $subcategory->SubCategoryName,
                        "policy_id" => $policy->PolicyID,
                        "grade_id" => $policy->GradeID,
                        "grade_type" => $policy->GradeType,
                        "grade_class" => $policy->GradeClass,
                        "grade_amount" => $policy->GradeAmount,
                    ] : null,
                ];
            }),
            'attendance_details' => $attendanceMapForCategory
        ];
			})->values()
		];			
        if (!$data) {
            return redirect()->back()->withErrors(['error' => 'Claim not found']);
        }
		
		$totalValue = $data->sumTripClaimDetailsValue();
		$claimDetails = $data->tripclaimdetailsforclaim->toArray();
		$dates = $this->extractDate($claimDetails);

		// Check if $dates contains elements
		if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
		} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
		}
		return view('admin.claim_management.ro_approved_claims_view', compact('data', 'totalValue', 'interval', 'dates','result','advanceBalance','userdet'));
    }
	public function checkDuplicateClaimsForView($user_id, $FromDate, $categoryID, $TripClaimDetailID)
	{
		$data = [];
		 if (!$FromDate || !strtotime($FromDate)) {
					return response()->json([
						'success' => 'error',
						'statusCode' => 400,
						'data' =>  [],
						'message' => 'Invalid or missing FromDate',
					]);
				}
	
		$personDetails = Personsdetails::where('EmployeeID', $user_id)
			->where("TripClaimDetailID", "!=", $TripClaimDetailID)
			->whereHas('tripClaimDetail', function ($q) use ($FromDate, $categoryID) {
				if (!empty($FromDate) && strtotime($FromDate)) {
					$q->whereDate('FromDate', $FromDate);
				}
				$q->whereHas('policyDet.subCategoryDetails', function ($subQuery) use ($categoryID) {
					$subQuery->where('CategoryID', $categoryID);
				});
			})
			->with(['tripClaimDetail.policyDet.subCategoryDetails.category'])
			->first();
	
		if (!$personDetails) {
			return $data;
		}
		$emp_id = $personDetails->userData->emp_id ?? null;
		$emp_name = $personDetails->userData->emp_name ?? null;
	
		$firstPerson = $personDetails->first();
		 $data['user_id'] = $user_id;
		 $data['emp_id'] = $emp_id ?? null;
		 $data['emp_name'] = $emp_name ?? null;
	
		$data['trip_claim_id'] = $firstPerson->tripClaimDetail->TripClaimID ?? null;
		$data['trip_claim_detail_id'] = $firstPerson->tripClaimDetail->TripClaimDetailID ?? null;
	
		return $data;
	}
	public function empNames($emp_id)
    {
        try {
            // Fetch employee details
            $employeeDetails = DB::table('users')
                ->where('emp_id', $emp_id)
                ->select('id','emp_id','emp_name','emp_grade')
				->first();
				if($employeeDetails)
            		return $employeeDetails;
				else
					return 'NA';
        } catch (\Exception $e) {
            
            return 'NA';
        }
    }

	public function ro_approved_claims_approved_view($id)
    {
		
        $data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails' => function ($query) {
			$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
		},'tripclaimdetailsforclaim'])
								
                                ->where('TripClaimID', $id)
                                ->first();
		$RejectedCount = Tripclaimdetails::where('TripClaimID', $id)
		->where('Status', 'Rejected')
		->count();
		$tripdata = Tripclaim::with(['tripclaimdetails' => function ($query) {
				$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
			},
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
			])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
			$userdet=User::where('id',auth()->id() )->first();
			$advanceBalance = AdvanceList::where('user_id', $tripdata->user_id)
			->where('Status', 'Paid')
			->sum('Amount');
			$advanceBalance = number_format($advanceBalance, 2, '.', '');


			// Extract unique dates and employee IDs from claim details
			$uniqueCombos = collect();

			foreach ($tripdata->tripclaimdetails as $detail) {
				foreach ($detail->personsDetails as $person) {
					foreach ($person->userDetails as $user) {
						$uniqueCombos->push([
							'emp_id' => $user->emp_id,
							'dates' => collect([$detail->FromDate, $detail->ToDate, $detail->DocumentDate])->filter()->unique()->values()->toArray(),
						]);
					}
				}
			}
		   
			 // Create a map of attendance details by emp_id and date
			$attendanceMap = collect();
			$uniqueCombinations = [];
			foreach ($uniqueCombos as $combo) {
				foreach ($combo['dates'] as $date) {
					$attendance = Attendance::where('emp_code', $combo['emp_id'])
						->whereDate('date', $date)
						->first();
			
					if ($attendance) {
						// Create a unique key based on emp_id and date
						$uniqueKey = $combo['emp_id'] . '|' . $date;
			
						// Check if this combination has already been added
						if (!array_key_exists($uniqueKey, $uniqueCombinations)) {
							// Add to unique combinations array
							$uniqueCombinations[$uniqueKey] = true;
			
							// Add the attendance data to the map
							$attendanceMap->push([
								'emp_id' => $combo['emp_id'],
								'date' => $date,
								'punch_in' => $attendance->punch_in,
								'punch_out' => $attendance->punch_out,
								'duration' => $attendance->duration,
								'location_in' => $attendance->location_in,
								'location_out' => $attendance->location_out,
								'remarks' => $attendance->remarks,
							]);
						}
					}
				}
			}
                    // Calculate the total amount for the trip claim
                $tripAmount = $tripdata->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');

                    // Determine the status of the trip claim
                    $statuses = $tripdata->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $tripdata->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')){
                        $tripStatus = 'Approved';
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                    }

                    // Determine the trip history status
                    $tripHistoryStatus = $tripStatus;
                    if ($tripStatus === 'Pending') {
                        $maxRejectionCount = $rejectionCounts->max();
                        if ($maxRejectionCount == 1) {
                            $tripHistoryStatus = 'ReSubmited';
                        } elseif ($maxRejectionCount == 0) {
                            $tripHistoryStatus = 'Pending';
                        } elseif ($maxRejectionCount == 2) {
                            $tripHistoryStatus = 'Rejected';
                        }
                    }

                    // Format the approved and rejected dates
                    $tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
			$result = [
				"trip_claim_id" => $tripdata->TripClaimID,
				"RejectedCount" => $RejectedCount,
				"trip_type_details" => $tripdata->triptypedetails->first() ? [
					"triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
					"triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
				] : null,
				"approver_details" => $tripdata->approverdetails->first() ? [
					"id" => $tripdata->approverdetails->first()->id,
					"emp_id" => $tripdata->approverdetails->first()->emp_id,
					"emp_name" => $tripdata->approverdetails->first()->emp_name
				] : null,
				"trip_purpose" => $tripdata->TripPurpose,
				// "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
				// 	"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
				// 	"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
				// ] : null,
				"visit_branch_detail" => $tripdata->visitbranchdetails->map(function ($branch) {
					return [
						"branch_id" => $branch->BranchID,
						"branch_name" => $branch->BranchName,
						"branch_code" => $branch->BranchCode
					];
				}),
				"user_details" => $tripdata->tripuserdetails->first() ? [
					"id" => $tripdata->tripuserdetails->first()->id,
					"emp_id" => $tripdata->tripuserdetails->first()->emp_id,
					"emp_name" => $tripdata->tripuserdetails->first()->emp_name,
					"emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
					"emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
				] : null,
				"tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
				'date' => $tripdata->created_at->format('d/m/Y'),
				'trip_status' => $tripStatus,
				'trip_history_status' => $tripHistoryStatus,
				'trip_approver_remarks' => $tripdata->ApproverRemarks,
				'total_amount' => $tripAmount,
				'trip_approved_date' => $tripApprovedDate,
				'trip_rejected_date' => $tripRejectedDate,
				'approver_status' => $tripStatus,
				'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
				'finance_status_change_date' => $tripdata->ApprovalDate ? \Carbon\Carbon::parse($tripdata->ApprovalDate)->format('d/m/Y') : null,
				"finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
					"id" => $tripdata->financeApproverdetails->first()->id,
					"emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
					"emp_name" => $tripdata->financeApproverdetails->first()->emp_name
				] : null,
				'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
        return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
			})->map(function ($groupedDetails, $categoryID) use ($tripdata, $attendanceMap) {
				$category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
				$subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
				$policy = $groupedDetails->first()->policyDet;

        // Initialize unique combos for this category
        $uniqueCombos = collect();

        // Extract unique employee IDs and dates for this category
        foreach ($groupedDetails as $detail) {
            foreach ($detail->personsDetails as $person) {
                foreach ($person->userDetails as $user) {
                    $uniqueCombos->push([
                        'emp_id' => $user->emp_id,
                        'dates' => collect([$detail->FromDate, $detail->ToDate, $detail->DocumentDate])->filter()->unique()->values()->toArray(),
                    ]);
                }
            }
        }

        // Create a map of attendance details by emp_id and date for this category
        $attendanceMapForCategory = collect();
        $uniqueCombinations = [];
        foreach ($uniqueCombos as $combo) {
            foreach ($combo['dates'] as $date) {
                $attendance = Attendance::where('emp_code', $combo['emp_id'])
                    ->whereDate('date', $date)
                    ->first();

                if ($attendance) {
                    // Create a unique key based on emp_id and date
                    $uniqueKey = $combo['emp_id'] . '|' . $date;

                    // Check if this combination has already been added
                    if (!array_key_exists($uniqueKey, $uniqueCombinations)) {
                        // Add to unique combinations array
                        $uniqueCombinations[$uniqueKey] = true;

                        // Add the attendance data to the map for this category
                        $attendanceMapForCategory->push([
                            'emp_id' => $combo['emp_id'],
                            'date' => $date,
                            'punch_in' => $attendance->punch_in,
                            'punch_out' => $attendance->punch_out,
                            'duration' => $attendance->duration,
                            'location_in' => $attendance->location_in,
                            'location_out' => $attendance->location_out,
                            'remarks' => $attendance->remarks,
                        ]);
                    }
                }
            }
        }

        // Return category data with the attendance details for this category
        return [
            "category_id" => $categoryID,
            "category_name" => $category->CategoryName ?? null,
            "image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
            "trip_from_flag" => (bool)$category->TripFrom,
            "trip_to_flag" => (bool)$category->TripTo,
            "from_date_flag" => (bool)$category->FromDate,
            "to_date_flag" => (bool)$category->ToDate,
            "document_date_flag" => (bool)$category->DocumentDate,
            "start_meter_flag" => (bool)$category->StartMeter,
            "end_meter_flag" => (bool)$category->EndMeter,
			"class_flg" => $policy->GradeType === 'Class' ? 1 : 0,
            "subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
            "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy) {
                return [
                    "trip_claim_details_id" => $detail->TripClaimDetailID,
                    "from_date" => $detail->FromDate,
                    "to_date" => $detail->ToDate,
                    "trip_from" => $detail->TripFrom,
                    "trip_to" => $detail->TripTo,
                    "document_date" => $detail->DocumentDate,
                    "start_meter" => $detail->StartMeter,
                    "end_meter" => $detail->EndMeter,
                    "qty" => $detail->Qty,
                    "status" => $detail->Status,
                    "unit_amount" => $detail->UnitAmount,
                    "DeductAmount" => $detail->DeductAmount,
                    "no_of_persons" => $detail->NoOfPersons,
                    "file_url" => $detail->FileUrl,
                    "remarks" => $detail->Remarks,
                    "approver_id" => $detail->ApproverID,
                    "approver_remarks" => $detail->approver_remarks,
                    "notification_flg" => $detail->NotificationFlg,
                    "rejection_count" => $detail->RejectionCount,
                    "person_details" => $detail->personsDetails->flatMap(function ($person) {
                        return $person->userDetails->map(function ($user) {
                            return [
                                "id" => $user->id,
                                "emp_id" => $user->emp_id,
                                "emp_name" => $user->emp_name,
                                "emp_grade" => $user->emp_grade,
                            ];
                        });
                    }),
                    "policy_details" => $subcategory->SubCategoryID ? [
                        "sub_category_id" => $subcategory->SubCategoryID,
                        "sub_category_name" => $subcategory->SubCategoryName,
                        "policy_id" => $policy->PolicyID,
                        "grade_id" => $policy->GradeID,
                        "grade_type" => $policy->GradeType,
                        "grade_class" => $policy->GradeClass,
                        "grade_amount" => $policy->GradeAmount,
                    ] : null,
                ];
            }),
            'attendance_details' => $attendanceMapForCategory
        ];
			})->values()
		];			
        if (!$data) {
            return redirect()->back()->withErrors(['error' => 'Claim not found']);
        }
		
		$totalValue = $data->sumTripClaimDetailsValue();
		$claimDetails = $data->tripclaimdetailsforclaim->toArray();
		$dates = $this->extractDate($claimDetails);

		// Check if $dates contains elements
		if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
		} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
		}
		return view('admin.claim_management.ro_approved_claims_approved_view', compact('data', 'totalValue', 'interval', 'dates','result','advanceBalance','userdet'));
    }

	public function report_management()
    {
		$tripTypes=TripType::where('Status', '1')->get();
		$grades=Grades::where('Status', '1')->get();
        return view('admin.claim_management.report_management',compact('tripTypes','grades'));
    }
	
	
	public function report_management_list(Request $request)
	{
		if ($request->ajax()) 
		{ 
			$pageNumber = ($request->start / $request->length) + 1;
			$pageLength = $request->length;
			$skip = ($pageNumber - 1) * $pageLength;
			$orderColumnIndex = $request->order[0]['column'] ?? 0;
			$orderBy = $request->order[0]['dir'] ?? 'desc';
			$searchValue = $request->search['value'] ?? '';

			$columns = [
				'TripClaimID',
				'created_at'
			];
			$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
			$query = ClaimManagement::with(['visitbranchdetails', 'userdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
				->when($request->FromDate, function ($query, $fromDate) {
					return $query->whereDate('created_at', '>=', $fromDate);
				})
				->when($request->ToDate, function ($query, $toDate) {
					return $query->whereDate('created_at', '<=', $toDate);
				})
				
				->when($request->TripType, function ($query, $tripType) {
					return $query->whereHas('triptypedetails', function($query) use ($tripType) {
						return $query->where('TripTypeID', $tripType);
					});
				})
				->when($request->EmpID, function ($query, $EmpID) {
					return $query->whereHas('userdata', function($query) use ($EmpID) {
						return $query->where('emp_id', 'like', '%' . $EmpID . '%');
					});
				})
				->when($request->Status, function ($query, $status) {
					return $query->where('Status', $status);
				})
				->when($request->GradeID, function ($query, $grade) {
					return $query->whereHas('userdata', function($query) use ($grade) {
						return $query->where('emp_grade', $grade);
					});
				})
				->when($request->BranchID, function ($query, $branch) {
					return $query->whereHas('visitbranchdetails', function($query) use ($branch) {
						return $query->where('BranchID',$branch);
					});
				})
				->where(function($q) use ($searchValue) {
					$searchValue = str_replace(' ', '', $searchValue);
					if (strlen($searchValue) <= 3 && in_array(strtoupper($searchValue), ['T', 'TM', 'TMG'])) {
						$q->whereRaw('1 = 1');
					} else {
						if (strpos($searchValue, 'TMG') === 0) {
							$searchValue = substr($searchValue, 3);
							if (!empty($searchValue)) {
								$q->where(DB::raw("SUBSTRING(TripClaimID, 9)"), 'like', '%' . $searchValue . '%');
							} else {
								$q->where('TripClaimID', 'like', '%');
							}
						} else {
							$q->where('TripClaimID', 'like', '%' . $searchValue . '%');
						}
					}
				})
				->orderBy($orderColumn, $orderBy);

				$recordsTotal = $query->count();
				$data = $query->skip($skip)->take($pageLength)->get();
				$recordsFiltered = $recordsTotal;
		
				$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$TotalAmount = $row->sumTripClaimDetailsValue1();
				$TotalAmount = number_format($TotalAmount, 2, '.', '');
				$DeductAmount = $row->sumTripClaimDetailsValue2();
				$DeductAmount = $DeductAmount-$TotalAmount;
				$DeductAmount = number_format($DeductAmount, 2, '.', '');
				return [
					'TripClaimID' => $row->TripClaimID,
					'created_at' => $row->created_at->format('d/m/Y'),
					'triptype' =>$row->triptypedetails->TripTypeName ?? 'N/A',
					'emp_name' => $row->userdata->emp_name ?? '-',
					'emp_id' => $row->userdata->emp_id ?? '-',
					'VisitBranch' => $row->visitbranchdetails
						? $row->visitbranchdetails->BranchName . ' (' . $row->visitbranchdetails->BranchCode . ')'
						: 'Others',
						'Branch' => $row->visit_branch_list && $row->visit_branch_list->isNotEmpty()
							? $row->visit_branch_list->map(function ($branch) {
								return $branch->BranchName . ' (' . $branch->BranchCode . ')';
							})->implode(', ')
							: 'Others',
					'Grade' => $row->userData->emp_grade ?? null,
					'Department' =>  ($row->userData->emp_department ?? '-') ?? null,
					'TotalAmount' => $TotalAmount,
					'DeductAmount' => $DeductAmount,
					'Status' => $row->Status,
					'ApprovalDate' => $row->ApprovalDate ?? "NA",
				];
			});
	
			return response()->json([
				"draw" => $request->draw,
				"recordsTotal" => $recordsTotal,
				"recordsFiltered" => $recordsFiltered,
				'data' => $formattedData,"ass"=>$request->BranchID
			], 200);
		}
	}
	
    public function report_management_view($id)
    {
		$data=ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata.branchDetails', 'userdata.baselocationDetails', 'tripclaimdetails','tripclaimdetailsforclaim'])
                                ->where('TripClaimID', $id)
                                ->first();

								
        if (!$data) {
            return redirect()->back()->withErrors(['error' => 'Claim not found']);
        }
		
		$totalValue = $data->sumTripClaimDetailsValue();
		$claimDetails = $data->tripclaimdetailsforclaim->toArray();
		$dates = $this->extractDate($claimDetails);

		if (empty($dates)) {
			$interval = 'N/A'; // Or any default value you want to set
		} else {
			// Calculate interval for display purposes
			$fromDate = min($dates);
			$toDate = max($dates);
			$interval = "{$fromDate} to {$toDate}";
		}
		
       
        
		// admin.claim_management.approved_claims_view
		return view('admin.claim_management.settled_claims_view', compact('data', 'totalValue', 'interval', 'dates'));
    }

    public function finance_approve_claim(Request $request){
		try{
			$now = new \DateTime(); 
			$currentdate = $now->format('Y-m-d H:i:s'); 
				
			$trip_det = Tripclaim::where('TripClaimID', $request->TripClaimID)
				->select('user_id')
				->first();
				
			if($request->action=='approve'){
				DB::table('myg_08_trip_claim')
				->where('TripClaimID',$request->TripClaimID)
				->update([
					'Status' => 'Approved',
					'FinanceRemarks' => $request->remarks ?? null,
					'SettleAmount' => str_replace(',', '', $request->SettleAmount),
					'FinanceApproverID' => $request->FinanceApproverID,
					'ApprovalDate' => $currentdate
				]);
				DB::table('myg_12_advancelist')
				->where('Status','Approved')
				->where('user_id',$trip_det->user_id)
				->update([
					'Status' => 'Paid',
				]);
			}else{
				DB::table('myg_08_trip_claim')
				->where('TripClaimID',$request->TripClaimID)
				->update([
					'Status' => 'Rejected',
					'FinanceRemarks' => $request->remarks ?? null,
					'SettleAmount' =>str_replace(',', '', $request->SettleAmount),
					'FinanceApproverID' => $request->FinanceApproverID,
					'ApprovalDate' => $currentdate
				]);
			}
			
			$message="Claim Approved Succesfully";
			 return redirect()->route('ro_approved_claims')->with('message', $message);
			
		} catch (\Exception $e) {
			dd($e);
			 return redirect()->back()->withErrors(['error' => 'Something went error']);
			
		}
	}

	public function importExcelData(Request $request)
	{
		try {
			if ($request->hasFile('excel_file')) {
				$file = $request->file('excel_file');

				// Load the Excel file
				$spreadsheet = IOFactory::load($file->getPathname());

				// Get the active sheet
				$sheet = $spreadsheet->getActiveSheet();

				foreach ($sheet->getRowIterator(2) as $row) {
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
	
					$rowData = [];
					foreach ($cellIterator as $cell) {
						$rowData[] = $cell->getValue();
					}
	
					// Assuming your Excel columns are in this order: [TripClaimID, TransactionID]
					// Adjust the index based on the actual order in your Excel file
					$tripClaimID = $rowData[1];  // Assuming TripClaimID is in the first column
					$transactionID = $rowData[3]; // Assuming TransactionID is in the second column
					if(!empty($transactionID)){
						// Find the trip claim record by TripClaimID
						$tripClaim = Tripclaim::where('TripClaimID', $tripClaimID)->first();
		
						// If the record exists, update the TransactionID
						if ($tripClaim) {
							$now = new \DateTime();  // Create a new DateTime object
							$currentdate = $now->format('Y-m-d H:i:s'); 
							DB::table('myg_08_trip_claim')
							->where('TripClaimID', $tripClaimID)
							->update(['TransactionID' => $transactionID,'Status'=>'Paid','transaction_date'=>$currentdate]);
						} else {
							// Handle the case where TripClaimID is not found
							// Optionally, you could create a new record or skip the row
						}
					}
				}

				return response()->json(['success' => true, 'message' => 'Claims settled successfully.']);
			} else {
				return response()->json(['success' => false, 'message' => 'No file uploaded.']);
			}
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	
	public function advance_payment()
    {
		$advance_payment=AdvanceList::where('Status', 'Pending')->get();
        return view('admin.claim_management.advance_payment',compact('advance_payment'));
    }

	public function advance_list(Request $request)
    {       
		if ($request->ajax()) 
		{ 
			$pageNumber = ($request->start / $request->length) + 1;
			$pageLength = $request->length;
			$skip = ($pageNumber - 1) * $pageLength;
			$orderColumnIndex = $request->order[0]['column'] ?? 0;
			$orderBy = $request->order[0]['dir'] ?? 'desc';
			$searchValue = $request->search['value'] ?? '';
			$columns = [
				'id',
				'created_at'
			];
			$orderColumn = $columns[$orderColumnIndex] ?? 'id';
			$query = AdvanceList::with(['userdata'])
						->where(function($q) use ($searchValue) {
							$q->where('RequestDate', 'like', '%'.$searchValue.'%')
							->orWhere('TripPurpose', 'like', '%'.$searchValue.'%')
							->orWhere('Amount', 'like', '%'.$searchValue.'%')
							->orWhere('TransactionID', 'like', '%'.$searchValue.'%')
							->orWhere('Status', 'like', '%'.$searchValue.'%')
							->orWhere('Remarks', 'like', '%'.$searchValue.'%');
						})
						->orWhereHas('userdata', function ($q) use ($searchValue) {
							$q->where('emp_name', 'like', '%'.$searchValue.'%')
							  ->orWhere('emp_id', 'like', '%'.$searchValue.'%');
						})
						->orderByRaw("
						CASE
							WHEN Status = 'Pending' THEN 1
							WHEN Status = 'Approved' THEN 2
							WHEN Status = 'Paid' THEN 3
							WHEN Status = 'Rejected' THEN 4
							ELSE 5
						END
					")
					->orderBy($orderColumn, $orderBy);
			
				$recordsTotal = $query->count();
				$data = $query->skip($skip)->take($pageLength)->get();
				$recordsFiltered = $recordsTotal;
		
				$formattedData = $data->map(function($row) {
			
				$action="";
				if($row->Status=='Pending'){
					$action='<a href="'. route('advance_approve', $row->id).'" class="btn btn-success" data-toggle="modal" onclick="approve(\''.$row->id.'\')"><i class="fa fa-check-square" aria-hidden="true"></i> Approve</a> <a href="'. route('advance_reject', $row->id).'" class="btn btn-danger" data-toggle="modal" onclick="reject(\''.$row->id.'\')"><i class="fa fa-times-circle" aria-hidden="true"></i> Reject</a>';
				}else if($row->Status=='Approved'){
					$action='<a href="'. route('advance_settled', $row->id).'" class="btn btn-success" data-toggle="modal"  onclick="settle(\''.$row->id.'\')"><i class="fa fa-check-square" aria-hidden="true"></i> Mark as Complete</a>';
				}
	
				return [
					'id' => $row->id,
					'created_at' => $row->created_at->format('d/m/Y'),
					'Purpose' => $row->TripPurpose,
					'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
					'TotalAmount' => $row->Amount,
					'Status' => $row->Status,
					'TransactionID' => ($row->TransactionID ?? '-'),
					'Remarks' => $row->Remarks,
					'action' => $action,
				];
			});
	
			return response()->json([
				"draw" => $request->draw,
				"recordsTotal" => $recordsTotal,
				"recordsFiltered" => $recordsFiltered,
				'data' => $formattedData
			], 200);
		}
    }

	public function advance_approve(Request $request){
		$id=$request->id;
		$remark=$request->remarks;
		$affected = AdvanceList::where('id', $id)
		->update(['Remarks'=>$remark,'Status'=>'Approved']);
			if ($affected) {
			return response()->json(['success' => true]);
			} else {
			return response()->json(['success' => false]);
			}
	}

	public function advance_reject(Request $request){
		$id=$request->id;
		$remark=$request->remarks;
		$affected = AdvanceList::where('id', $id)
		->update(['Remarks'=>$remark,'Status'=>'Rejected']);
			if ($affected) {
			return response()->json(['success' => true]);
			} else {
			return response()->json(['success' => false]);
			}
	}
	public function advance_settled(Request $request){
		$id=$request->id;
		$TransactionID=$request->TransactionID;
		$affected = AdvanceList::where('id', $id)
		->update(['TransactionID'=>$TransactionID,'Status'=>'Paid']);
			if ($affected) {
			return response()->json(['success' => true]);
			} else {
			return response()->json(['success' => false,'sql' => $affected]);
			}
	}
	public function update_tripclaimDetails(Request $request){
		
		$TripClaimDetailID=$request->TripClaimDetailID;
		$UnitAmount=$request->UnitAmount;
		$approver_remarks=$request->approver_remarks;
		$UnitAmount = number_format((float) $request->UnitAmount, 2, '.', '');

		$affected = Tripclaimdetails::where('TripClaimDetailID', $TripClaimDetailID)
		->update(['UnitAmount'=>$UnitAmount,'approver_remarks'=>$approver_remarks]);
			if ($affected) {
			return response()->json(['success' => true]);
			} else {
			return response()->json(['success' => false,'sql' => $affected]);
			}
	}
	public function reject_tripclaimDetails(Request $request){
		
		$TripClaimDetailID=$request->TripClaimDetailID;
		$TripClaimID=$request->TripClaimID;
		$approver_remarks=$request->reason;
		
		$tripClaimDetail = Tripclaimdetails::where('TripClaimDetailID',$TripClaimDetailID)->first();

		if ($tripClaimDetail) {
			// Increment the RejectionCount by 1
			$newRejectionCount = $tripClaimDetail->RejectionCount + 1;

			// Update the Status and RejectionCount
			$affected = Tripclaimdetails::where('TripClaimDetailID',$TripClaimDetailID)->update([
				'Status' => 'Rejected',
				'approver_remarks' => $approver_remarks,
				'RejectionCount' => $newRejectionCount,
			]);
			if ($affected) {
				$approvedCount = Tripclaimdetails::where('TripClaimID', $TripClaimID)
                ->where('Status', 'Approved')
                ->count();
				return response()->json(['success' => true,'approvedCount' => $approvedCount]);
			} else {
				return response()->json(['success' => false,'approvedCount' => 1]);
			}
		}
			
	}
	public function notifications(){
		return view('admin.claim_management.notifications');
	}
	public function notificationcount(){
		$notificationCount = ClaimManagement::whereIn("NotificationFlg",['2','3'])->where('AppNotificationFlg','0')->count();
        return response()->json(['count' => $notificationCount]);
	}
	public function notifications_list(Request $request)
	{
	    if ($request->ajax()) 
	    { 
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
				'created_at',
	            'TripClaimID'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';
			$query = ClaimManagement::with(['userdetails', 'userdata'])
				->whereIn("NotificationFlg", ['2', '3'])
				->orderBy('AppNotificationFlg', 'ASC') 
				  ->orderBy('ApprovalDate', 'DESC')
				  // This ensures AppNotificationFlg = 0 comes first
				->orderBy($orderColumn, $orderBy);
			
			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;
			
			$formattedData = $data->map(function($row) {
				$tmgid = 'TMG' . substr($row->TripClaimID, 8);
				
				// Conditionally set action based on AppNotificationFlg value
				$action = ($row->AppNotificationFlg == 0)
					? '<a href="'. route('approved_claims_view', $row->TripClaimID).'" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>'
					: '<a href="'. route('approved_claims_view', $row->TripClaimID).'" class="btn btn-disabled" style="color:#000;"><i class="fa fa-eye" aria-hidden="true"></i> View</a>';
			
				return [
					'TripClaimID' => $row->TripClaimID,
					'Date' => date('Y-m-d', strtotime($row->ApprovalDate)),
					'Message' => 'You have a new claim request from ' . ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-') . '. Claim ID:' . $tmgid,
					'action' => $action, // Add the action field here
				];
			});
	        return response()->json([
	            "draw" => $request->draw,
	            "recordsTotal" => $recordsTotal,
	            "recordsFiltered" => $recordsFiltered,
	            'data' => $formattedData
	        ], 200);
	    }
	}
}
