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
use Auth;
use DB;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon; // Import Carbon
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Personsdetails;

class ClaimManagementController extends Controller
{
	public function getbranchNameByID($id)
	{
		$branchdata     = Branch::where('BranchID', '=', $id)->select('BranchName')->first();
		return $branchdata->BranchName;
	}
	public function approved_claims()
	{
		return view('admin.claim_management.approved_claims');
	}
	public function approved_claims_list(Request $request)
	{
		if (!$request->ajax()) return;

		$pageNumber = ($request->start / $request->length) + 1;
		$pageLength = $request->length;
		$skip = ($pageNumber - 1) * $pageLength;

		$orderColumnIndex = $request->order[0]['column'] ?? 0;
		$orderBy = $request->order[0]['dir'] ?? 'desc';
		$searchValue = $request->search['value'] ?? '';

		$columns = ['TripClaimID', 'created_at'];
		$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';

		/** ðŸ”¹ Base query */
		$query = ClaimManagement::query()
			->select('myg_08_trip_claim.*')
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'userdata:id,emp_id,emp_name',
				'triptypedetails:TripTypeID,TripTypeName'
			])
			->where('Status', 'Approved')
			->whereDoesntHave('tripclaimdetails', function ($q) {
				$q->where('Status', '<>', 'Approved');
			});

		/** ðŸ” Search */
		if ($searchValue) {
			$search = str_replace(' ', '', $searchValue);

			$query->where(function ($q) use ($search) {

				if (!(strlen($search) <= 3 && in_array(strtoupper($search), ['T', 'TM', 'TMG']))) {

					if (strtoupper(substr($search, 0, 3)) === 'TMG') {
						$search = substr($search, 3);
						$q->whereRaw('SUBSTRING(TripClaimID, 9) LIKE ?', ['%' . $search . '%']);
					} else {
						$q->where('TripClaimID', 'like', '%' . $search . '%');
					}
				}

				$q->orWhereHas('userdata', function ($qu) use ($search) {
					$qu->where('emp_name', 'like', '%' . $search . '%')
						->orWhere('emp_id', 'like', '%' . $search . '%');
				});
			});
		}

		/** ðŸ“Š Count */
		$recordsTotal = $query->count();

		/** ðŸ“¥ Fetch paginated data */
		$claims = $query
			->orderBy($orderColumn, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		/** ðŸ’° Preload totals in ONE query */
		$claimIds = $claims->pluck('TripClaimID')->toArray();

		$totals = \DB::table('myg_09_trip_claim_details')
			->select('TripClaimID')
			->selectRaw('SUM(UnitAmount) as TotalAmount')
			->whereIn('TripClaimID', $claimIds)
			->where('Status', 'Approved')
			->groupBy('TripClaimID')
			->get()
			->keyBy('TripClaimID');

		/** ðŸŽ¯ Format for DataTable */
		$formattedData = $claims->map(function ($row) use ($totals) {

			$totalAmount = $totals[$row->TripClaimID]->TotalAmount ?? 0;

			return [
				'TripClaimID'   => $row->TripClaimID,
				'created_at'    => optional($row->created_at)->format('d/m/Y'),
				'emp_name'      => $row->userdata->emp_name ?? '-',
				'emp_id'        => $row->userdata->emp_id ?? '-',
				'VisitBranchID' => $row->visit_branch_list
					->map(fn($b) => $b->BranchName . ' (' . $b->BranchCode . ')')
					->implode(', '),
				'TripTypeID'    => $row->triptypedetails->TripTypeName ?? 'N/A',
				'TotalAmount'   => number_format($totalAmount, 2, '.', ''),
				'action'        => '<a href="' . route('approved_claims_view', $row->TripClaimID) . '" class="btn btn-primary">
										<i class="fa fa-eye"></i> View
								</a>',
			];
		});

		return response()->json([
			"draw" => $request->draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $formattedData
		], 200);
	}
	public function approved_claims_view($id)
	{
		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails', 'tripclaimdetailsforclaim'])
			->where('TripClaimID', $id)
			->first();
		$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
		])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
		$update = ClaimManagement::where('TripClaimID', $id)->update(['AppNotificationFlg' => '1']);
		$userdet = User::where('id', auth()->id())->first();
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
			//"visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			//"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			//"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			//] : null,
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
			})->map(function ($groupedDetails, $categoryID) use ($tripdata) {
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
					"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
					"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $categoryID) {
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
							"DeductAmount" => $detail->DeductAmount, //18-11-2024
							"no_of_persons" => $detail->NoOfPersons,
							"file_url" => $detail->FileUrl,
							"remarks" => $detail->Remarks,
							"approver_id" => $detail->ApproverID,
							"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
							"approver_remarks" => $detail->approver_remarks,
							"notification_flg" => $detail->NotificationFlg,
							"rejection_count" => $detail->RejectionCount,
							"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory) {
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID)
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
		return view('admin.claim_management.approved_claims_view', compact('data', 'totalValue', 'interval', 'dates', 'result', 'advanceBalance', 'userdet'));
	}
	public function approved_claims_viewNew($id)
	{
		$data = ClaimManagement::with([
			'usercodedetails',
			'triptypedetails',
			'userdata',
			'tripclaimdetails',
			'tripclaimdetailsforclaim'
		])->where('TripClaimID', $id)->first();

		if (!$data) {
			return redirect()->back()->withErrors(['error' => 'Claim not found']);
		}

		$tripdata = Tripclaim::with([
			'triptypedetails',
			'approverdetailsView',
			'financeApproverdetailsView',
			'tripuserdetailsView',
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails',
			'visit_branchesView'
		])->where('TripClaimID', $id)->first();

		if (!$tripdata) {
			return redirect()->back()->withErrors(['error' => 'Trip data not found']);
		}

		// Update notification
		$tripdata->NotificationFlg = 1;
		$tripdata->save();

		// Prepare other variables for the view
		$tripDetails   = $tripdata->tripclaimdetails ?? collect();
		$tripUser      = $tripdata->tripuserdetailsView->first();
		$approver      = $tripdata->approverdetailsView->first();
		$financeAppr   = $tripdata->financeApproverdetailsView->first();
		$visitBranches = $tripdata->visit_branchesView ?? collect();

		$advanceBalance = number_format(
			AdvanceList::where('user_id', $tripdata->user_id)
				->where('Status', 'Paid')
				->sum('Amount'),
			2,
			'.',
			''
		);

		// ... your existing $result, $totalValue, $interval logic ...

		return view('admin.claim_management.approved_claims_view', compact(
			'data',
			'totalValue',
			'interval',
			'dates',
			'result',
			'advanceBalance',
			'userdet'
		));
	}


	public function complete_approved_claim(Request $request)
	{
		$TripClaimID = $request->TripClaimID;
		// Perform the status change operation here
		$now = new \DateTime();  // Create a new DateTime object
		$currentdate = $now->format('Y-m-d H:i:s');
		$affected = ClaimManagement::where('TripClaimID', $TripClaimID)
			->update(['Status' => 'Paid', 'transaction_date' => $currentdate]);
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
		if (!$request->ajax()) return;

		$pageNumber = ($request->start / $request->length) + 1;
		$pageLength = $request->length;
		$skip = ($pageNumber - 1) * $pageLength;

		$orderColumnIndex = $request->order[0]['column'] ?? 0;
		$orderBy = $request->order[0]['dir'] ?? 'desc';
		$searchValue = $request->search['value'] ?? '';
		$statusFilter = $request->input('statusFilter');

		$columns = ['TripClaimID', 'Status'];
		$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';

		/** ðŸ”¹ Base query */
		$query = ClaimManagement::query()
			->select('myg_08_trip_claim.*')
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'userdata:id,emp_id,emp_name',
				'triptypedetails:TripTypeID,TripTypeName'
			]);

		/** ðŸ”¹ Status filter */
		if ($statusFilter === 'Pending') {
			$query->whereHas('tripclaimdetails', function ($q) {
				$q->whereNotIn('Status', ['Approved', 'Rejected']);
			});
		} elseif ($statusFilter === 'Resubmission Pending') {
			$query->whereHas('tripclaimdetails', function ($q) {
				$q->where('Status', 'Rejected')->where('RejectionCount', 1);
			});
		} else {
			$query->whereHas('tripclaimdetails', function ($q) {
				$q->where('Status', 'Pending')
					->orWhere(function ($q2) {
						$q2->where('Status', 'Rejected')
							->where('RejectionCount', 1);
					});
			});
		}

		/** ðŸ” Search */
		if ($searchValue) {
			$search = str_replace(' ', '', $searchValue);

			$query->where(function ($q) use ($search) {

				if (!(strlen($search) <= 3 && in_array(strtoupper($search), ['T', 'TM', 'TMG']))) {

					if (strtoupper(substr($search, 0, 3)) === 'TMG') {
						$search = substr($search, 3);
						$q->whereRaw('SUBSTRING(TripClaimID, 9) LIKE ?', ['%' . $search . '%']);
					} else {
						$q->where('TripClaimID', 'like', '%' . $search . '%');
					}
				}

				$q->orWhereHas('userdata', function ($qu) use ($search) {
					$qu->where('emp_name', 'like', '%' . $search . '%')
						->orWhere('emp_id', 'like', '%' . $search . '%');
				});
			});
		}

		/** ðŸ“Š Count */
		$recordsTotal = $query->count();

		/** ðŸ“¥ Fetch paginated data */
		$claims = $query
			->orderBy('created_at', 'DESC')
			->skip($skip)
			->take($pageLength)
			->get();

		/** ðŸ’° Preload totals + status in ONE query */
		$claimIds = $claims->pluck('TripClaimID')->toArray();

		$details = \DB::table('myg_09_trip_claim_details')
			->select('TripClaimID')
			->selectRaw('SUM(UnitAmount) as TotalAmount')
			->selectRaw("
				CASE
					WHEN SUM(CASE WHEN Status='Rejected' AND RejectionCount=1 THEN 1 ELSE 0 END) > 0
					THEN 'Resubmission Pending'
					ELSE 'Pending'
				END as FinalStatus
			")
			->whereIn('TripClaimID', $claimIds)
			->groupBy('TripClaimID')
			->get()
			->keyBy('TripClaimID');

		/** ðŸŽ¯ Format for DataTable */
		$formattedData = $claims->map(function ($row) use ($details) {

			$info = $details[$row->TripClaimID] ?? null;

			return [
				'TripClaimID'   => $row->TripClaimID,
				'created_at'    => optional($row->created_at)->format('d/m/Y'),
				'UserData'      => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
				'VisitBranchID' => $row->visit_branch_list
					->map(fn($b) => $b->BranchName . ' (' . $b->BranchCode . ')')
					->implode(', '),
				'TripTypeID'    => $row->triptypedetails->TripTypeName ?? 'N/A',
				'TotalAmount'   => number_format($info->TotalAmount ?? 0, 2, '.', ''),
				'Status'        => $info->FinalStatus ?? 'Pending',
				'action'        => '<a href="' . route('requested_claims_view', $row->TripClaimID) . '" class="btn btn-primary">
										<i class="fa fa-eye"></i> View
								</a>',
			];
		});

		return response()->json([
			"draw" => $request->draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $formattedData
		], 200);
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
		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails', 'tripclaimdetailsforclaim'])
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
			})->map(function ($groupedDetails, $categoryID) use ($tripdata) {
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
					"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
					"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $categoryID) {
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
							"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory) {
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID)
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
		return view('admin.claim_management.requested_claims_view', compact('data', 'totalValue', 'interval', 'dates', 'result'));
	}
	public function getsubcategoryDetails($categoryID, $userid)
	{
		$user = DB::table('users')->where('id', $userid)->first();
		$gradeID = $user->emp_grade;

		$subcategories = SubCategories::where('CategoryID', $categoryID)
			->with(['policies' => function ($query) use ($gradeID) {
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
		$dates = array_filter($dates, function ($date) {
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
		if (!$request->ajax()) return;

		$pageNumber = ($request->start / $request->length) + 1;
		$pageLength = $request->length;
		$skip = ($pageNumber - 1) * $pageLength;

		$orderColumnIndex = $request->order[0]['column'] ?? 0;
		$orderBy = $request->order[0]['dir'] ?? 'desc';
		$searchValue = $request->search['value'] ?? '';

		$columns = ['TripClaimID', 'created_at'];
		$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';

		/** ðŸ”¹ Base query */
		$query = ClaimManagement::query()
			->select('myg_08_trip_claim.*')
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'userdata:id,emp_id,emp_name',
				'triptypedetails:TripTypeID,TripTypeName'
			])
			->where('Status', 'Paid');

		/** ðŸ” Search */
		if ($searchValue) {
			$search = str_replace(' ', '', $searchValue);

			$query->where(function ($q) use ($search) {

				if (!(strlen($search) <= 3 && in_array(strtoupper($search), ['T', 'TM', 'TMG']))) {

					if (strtoupper(substr($search, 0, 3)) === 'TMG') {
						$search = substr($search, 3);
						$q->whereRaw('SUBSTRING(TripClaimID,9) LIKE ?', ['%' . $search . '%']);
					} else {
						$q->where('TripClaimID', 'like', '%' . $search . '%');
					}
				}

				$q->orWhereHas('userdata', function ($qu) use ($search) {
					$qu->where('emp_name', 'like', '%' . $search . '%')
						->orWhere('emp_id', 'like', '%' . $search . '%');
				});
			});
		}

		/** ðŸ“Š Count */
		$recordsTotal = $query->count();

		/** ðŸ“¥ Fetch page */
		$claims = $query
			->orderBy($orderColumn, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		/** ðŸ’° Preload totals in ONE query */
		$claimIds = $claims->pluck('TripClaimID')->toArray();

		$totals = \DB::table('myg_09_trip_claim_details')
			->select('TripClaimID')
			->selectRaw('SUM(UnitAmount) as TotalAmount')
			->whereIn('TripClaimID', $claimIds)
			->where('Status', 'Approved')
			->groupBy('TripClaimID')
			->get()
			->keyBy('TripClaimID');

		/** ðŸŽ¯ Format DataTable */
		$formattedData = $claims->map(function ($row) use ($totals) {

			$totalAmount = $totals[$row->TripClaimID]->TotalAmount ?? 0;

			return [
				'TripClaimID'   => $row->TripClaimID,
				'created_at'    => optional($row->created_at)->format('d/m/Y'),
				'UserData'      => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
				'VisitBranchID' => $row->visit_branch_list
					->map(fn($b) => $b->BranchName . ' (' . $b->BranchCode . ')')
					->implode(', '),
				'TripTypeID'    => $row->triptypedetails->TripTypeName ?? 'N/A',
				'TotalAmount'   => number_format($totalAmount, 2, '.', ''),
				'TransactionID' => $row->TransactionID,
				'action'        => '<a href="' . route('settled_claims_view', $row->TripClaimID) . '"
									class="btn btn-primary">
									<i class="fa fa-eye"></i> View
								</a>',
			];
		});

		return response()->json([
			"draw" => $request->draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $formattedData
		], 200);
	}
	public function settled_claims_view($id)
	{
		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails', 'tripclaimdetailsforclaim'])
			->where('TripClaimID', $id)
			->first();
		$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
		])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
		$userdet = User::where('id', auth()->id())->first();
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
			//"visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			//"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			//"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			//] : null,
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
			})->map(function ($groupedDetails, $categoryID) use ($tripdata) {
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
					"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
					"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $categoryID) {
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
							"DeductAmount" => $detail->DeductAmount,  //18-11-2024
							"no_of_persons" => $detail->NoOfPersons,
							"file_url" => $detail->FileUrl,
							"remarks" => $detail->Remarks,
							"approver_id" => $detail->ApproverID,
							"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
							"approver_remarks" => $detail->approver_remarks,
							"notification_flg" => $detail->NotificationFlg,
							"rejection_count" => $detail->RejectionCount,
							"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory) {
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID)
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
		return view('admin.claim_management.settled_claims_view', compact('data', 'totalValue', 'interval', 'dates', 'result', 'advanceBalance', 'userdet'));
	}
	public function user_policy($GradeID, $CategoryID, $SubCategoryID)
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
		if (!$request->ajax()) return;

		$pageNumber = ($request->start / $request->length) + 1;
		$pageLength = $request->length;
		$skip = ($pageNumber - 1) * $pageLength;

		$orderColumnIndex = $request->order[0]['column'] ?? 0;
		$orderBy = $request->order[0]['dir'] ?? 'desc';
		$searchValue = $request->search['value'] ?? '';

		$columns = ['TripClaimID', 'created_at'];
		$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';

		/** ðŸ”¹ Base query */
		$query = ClaimManagement::query()
			->select('myg_08_trip_claim.*')
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'userdata:id,emp_id,emp_name',
				'triptypedetails:TripTypeID,TripTypeName'
			])
			->where('Status', 'Rejected');

		/** ðŸ” Search */
		if ($searchValue) {
			$search = str_replace(' ', '', $searchValue);

			$query->where(function ($q) use ($search) {

				if (!(strlen($search) <= 3 && in_array(strtoupper($search), ['T', 'TM', 'TMG']))) {

					if (strtoupper(substr($search, 0, 3)) === 'TMG') {
						$search = substr($search, 3);
						$q->whereRaw('SUBSTRING(TripClaimID,9) LIKE ?', ['%' . $search . '%']);
					} else {
						$q->where('TripClaimID', 'like', '%' . $search . '%');
					}
				}

				$q->orWhereHas('userdata', function ($qu) use ($search) {
					$qu->where('emp_name', 'like', '%' . $search . '%')
						->orWhere('emp_id', 'like', '%' . $search . '%');
				});
			});
		}

		/** ðŸ“Š Count */
		$recordsTotal = $query->count();

		/** ðŸ“¥ Fetch page */
		$claims = $query
			->orderBy($orderColumn, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		/** ðŸ’° Preload totals in ONE query */
		$claimIds = $claims->pluck('TripClaimID')->toArray();

		$totals = \DB::table('myg_09_trip_claim_details')
			->select('TripClaimID')
			->selectRaw('SUM(UnitAmount) as TotalAmount')
			->whereIn('TripClaimID', $claimIds)
			->groupBy('TripClaimID')
			->get()
			->keyBy('TripClaimID');

		/** ðŸŽ¯ Format DataTable */
		$formattedData = $claims->map(function ($row) use ($totals) {

			$totalAmount = $totals[$row->TripClaimID]->TotalAmount ?? 0;

			return [
				'TripClaimID'   => $row->TripClaimID,
				'created_at'    => optional($row->created_at)->format('d/m/Y'),
				'UserData'      => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
				'VisitBranchID' => $row->visit_branch_list
					->map(fn($b) => $b->BranchName . ' (' . $b->BranchCode . ')')
					->implode(', '),
				'TripTypeID'    => $row->triptypedetails->TripTypeName ?? 'N/A',
				'TotalAmount'   => number_format($totalAmount, 2, '.', ''),
				'action'        => '<a href="' . route('rejected_claims_view', $row->TripClaimID) . '"
									class="btn btn-primary">
									<i class="fa fa-eye"></i> View
								</a>',
			];
		});

		return response()->json([
			"draw" => $request->draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $formattedData
		], 200);
	}
	public function rejected_claims_view($id)
	{

		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails', 'tripclaimdetailsforclaim'])
			->where('TripClaimID', $id)
			->first();
		$tripdata = Tripclaim::with([
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
		])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
		$userdet = User::where('id', auth()->id())->first();
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
			//"visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			//"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			//"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			//] : null,
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
			})->map(function ($groupedDetails, $categoryID) use ($tripdata) {
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
					"subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID, $tripdata->tripuserdetails->first()->id),
					"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $categoryID) {
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
							"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory) {
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID)
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
		return view('admin.claim_management.rejected_claims_view', compact('data', 'totalValue', 'interval', 'dates', 'result', 'advanceBalance', 'userdet'));
	}
	public function ro_approved_claims()
	{
		return view('admin.claim_management.ro_approved_claims');
	}
	public function ro_approved_claims_list(Request $request)
	{
		if (!$request->ajax()) return;

		$pageNumber = ($request->start / $request->length) + 1;
		$pageLength = $request->length;
		$skip = ($pageNumber - 1) * $pageLength;

		$orderColumnIndex = $request->order[0]['column'] ?? 0;
		$orderBy = $request->order[0]['dir'] ?? 'desc';
		$searchValue = $request->search['value'] ?? '';

		$columns = ['TripClaimID', 'created_at'];
		$orderColumn = $columns[$orderColumnIndex] ?? 'TripClaimID';

		$query = ClaimManagement::query()
			->select('myg_08_trip_claim.*') // select only main table columns
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'userdata:id,emp_id,emp_name',
				'triptypedetails:TripTypeID,TripTypeName'
			])
			->where('Status', 'Pending')
			->whereDoesntHave('tripclaimdetails', function ($q) {
				$q->where('Status', '<>', 'Approved');
			});

		// Search filter
		if ($searchValue) {
			$searchValueClean = str_replace(' ', '', $searchValue);

			$query->where(function ($q) use ($searchValueClean) {
				if (strlen($searchValueClean) <= 3 && in_array(strtoupper($searchValueClean), ['T', 'TM', 'TMG'])) {
					$q->whereRaw('1=1'); // skip
				} else {
					if (strtoupper(substr($searchValueClean, 0, 3)) === 'TMG') {
						$searchValueClean = substr($searchValueClean, 3);
						$q->whereRaw('SUBSTRING(TripClaimID, 9) like ?', ['%' . $searchValueClean . '%']);
					} else {
						$q->where('TripClaimID', 'like', '%' . $searchValueClean . '%');
					}
				}

				$q->orWhereHas('userdata', function ($qu) use ($searchValueClean) {
					$qu->where('emp_name', 'like', '%' . $searchValueClean . '%')
						->orWhere('emp_id', 'like', '%' . $searchValueClean . '%');
				});
			});
		}

		// Total records
		$recordsTotal = $query->count();

		// Fetch paginated data
		$claims = $query->orderBy($orderColumn, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		// Preload TotalAmount and RODate efficiently
		$claimIds = $claims->pluck('TripClaimID')->toArray();

		$totals = \DB::table('myg_09_trip_claim_details')
			->select('TripClaimID')
			->selectRaw('SUM(UnitAmount) as TotalAmount')
			->selectRaw('MAX(approved_date) as RODate')
			->whereIn('TripClaimID', $claimIds)
			->where('Status', 'Approved')
			->groupBy('TripClaimID')
			->get()
			->keyBy('TripClaimID');

		// Format data for DataTable
		$formattedData = $claims->map(function ($row) use ($totals) {
			$totalAmount = $totals[$row->TripClaimID]->TotalAmount ?? 0;
			$totalAmount = number_format($totalAmount, 2, '.', '');
			$RODate = $totals[$row->TripClaimID]->RODate ?? 'NA';

			return [
				'TripClaimID' => $row->TripClaimID,
				'created_at' => optional($row->created_at)->format('d/m/Y'),
				'UserData' => ($row->userdata->emp_name ?? '-') . '/' . ($row->userdata->emp_id ?? '-'),
				'VisitBranchID' => $row->visit_branch_list->map(fn($b) => $b->BranchName . ' (' . $b->BranchCode . ')')->implode(', '),
				'TripTypeID' => $row->triptypedetails->TripTypeName ?? 'N/A',
				'RODate' => $RODate,
				'TotalAmount' => $totalAmount,
				'action' => '<a href="' . route('ro_approved_claims_view', $row->TripClaimID) . '" class="btn btn-primary"><i class="fa fa-eye"></i> View</a>',
			];
		});

		return response()->json([
			"draw" => $request->draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $formattedData
		], 200);
	}
	public function ro_approved_claims_view($id)
	{

		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails' => function ($query) {
			$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
		}, 'tripclaimdetailsforclaim'])

			->where('TripClaimID', $id)
			->first();
		$RejectedCount = Tripclaimdetails::where('TripClaimID', $id)
			->where('Status', 'Rejected')
			->count();
		$tripdata = Tripclaim::with([
			'tripclaimdetails' => function ($query) {
				$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
			},
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
		])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
		$userdet = User::where('id', auth()->id())->first();
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
			//	"visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			//		"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			//		"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			//		] : null,
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
					"claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $categoryID) {
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
							"DeductAmount" => $detail->DeductAmount,  //18-11-2024
							"no_of_persons" => $detail->NoOfPersons,
							"file_url" => $detail->FileUrl,
							"remarks" => $detail->Remarks,
							"approver_id" => $detail->ApproverID,
							"claim_approver_name" => $this->empNames($detail->ApproverID)->emp_name,
							"approver_remarks" => $detail->approver_remarks,
							"notification_flg" => $detail->NotificationFlg,
							"rejection_count" => $detail->RejectionCount,
							"person_detailsw" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory) {
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID)
									];
								});
							}),
							"person_details" => $detail->personsDetails->flatMap(function ($person) use ($categoryID, $subcategory, $detail) {
								return $person->userDetails->map(function ($user) use ($categoryID, $subcategory, $detail) {
									$is_duplication = [];
									if ($detail->FromDate != "") {
										$is_duplication = $this->checkDuplicateClaimsForView($user->id, $detail->FromDate, $categoryID, $detail->TripClaimDetailID);
									}
									//dd($detail->TripClaimDetailID);
									return [
										"id" => $user->id,
										"emp_id" => $user->emp_id,
										"emp_name" => $user->emp_name,
										"emp_grade" => $user->emp_grade,
										"user_policy" => $this->user_policy($user->emp_grade, $categoryID, $subcategory->SubCategoryID),
										"is_duplication" => !empty($is_duplication),
										"duplication_claim_id" => $is_duplication['trip_claim_id'] ?? null
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
		return view('admin.claim_management.ro_approved_claims_view', compact('data', 'totalValue', 'interval', 'dates', 'result', 'advanceBalance', 'userdet'));
	}
	public function empNames($emp_id)
	{
		try {
			// Fetch employee details
			$employeeDetails = DB::table('users')
				->where('emp_id', $emp_id)
				->select('id', 'emp_id', 'emp_name', 'emp_grade')
				->first();
			if ($employeeDetails)
				return $employeeDetails;
			else
				return 'NA';
		} catch (\Exception $e) {

			return 'NA';
		}
	}
	public function ro_approved_claims_approved_view($id)
	{

		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata', 'tripclaimdetails' => function ($query) {
			$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
		}, 'tripclaimdetailsforclaim'])

			->where('TripClaimID', $id)
			->first();
		$RejectedCount = Tripclaimdetails::where('TripClaimID', $id)
			->where('Status', 'Rejected')
			->count();
		$tripdata = Tripclaim::with([
			'tripclaimdetails' => function ($query) {
				$query->where('Status', 'Approved'); // Filter tripclaimdetails with 'Approved' status
			},
			'tripclaimdetails.policyDet.subCategoryDetails.category',
			'tripclaimdetails.personsDetails.userDetails'
		])
			->where('TripClaimID', $id)
			->first(); // Use first() instead of get() for a single 
		$userdet = User::where('id', auth()->id())->first();
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
		if ($statuses->every(fn($status) => $status === 'Approved')) {
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
			//"visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
			//	"branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
			//	"branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
			//] : null,
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
				"emp_baselocation" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
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
		return view('admin.claim_management.ro_approved_claims_approved_view', compact('data', 'totalValue', 'interval', 'dates', 'result', 'advanceBalance', 'userdet'));
	}
	public function report_management()
	{
		$tripTypes = TripType::where('Status', '1')->get();
		$grades = Grades::where('Status', '1')->get();
		return view('admin.claim_management.report_management', compact('tripTypes', 'grades'));
	}
	public function report_management_list(Request $request)
	{
		if (!$request->ajax()) {
			abort(404);
		}

		$pageLength = $request->length ?? 10;
		$skip       = $request->start ?? 0;
		$orderBy    = $request->order[0]['dir'] ?? 'desc';
		$columns    = ['TripClaimID', 'created_at'];
		$orderCol   = $columns[$request->order[0]['column'] ?? 0] ?? 'TripClaimID';
		$search     = trim(str_replace(' ', '', $request->search['value'] ?? ''));

		$baseQuery = ClaimManagement::query()
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'triptypedetails:TripTypeID,TripTypeName',
				'userdata:id,emp_id,emp_name,emp_grade,emp_department',
			])
			->withSum('tripclaimdetails as total_amount', 'UnitAmount')
			->withSum('tripclaimdetails as deduct_amount', 'DeductAmount')

			->when($request->FromDate, fn($q) =>
				$q->whereDate('created_at', '>=', $request->FromDate)
			)
			->when($request->ToDate, fn($q) =>
				$q->whereDate('created_at', '<=', $request->ToDate)
			)
			->when($request->TripType, fn($q) =>
				$q->where('TripTypeID', $request->TripType)
			)
			// ->when($request->Status, fn($q) =>
			// 	$q->where('Status', $request->Status)
			// )
			->when($request->Status, function ($q) use ($request) {

				if ($request->Status === 'Pending') {
					$q->where('Status', 'Pending')
					->whereDoesntHave('tripclaimdetails', function ($sub) {
						$sub->where('Status', '<>', 'Approved');
					});
				} else {
					$q->where('Status', $request->Status);
				}

			})
			->when($request->EmpID, fn($q) =>
				$q->whereHas('userdata', fn($u) =>
					$u->where('emp_id', 'like', "%{$request->EmpID}%")
				)
			)
			->when($request->GradeID, fn($q) =>
				$q->whereHas('userdata', fn($u) =>
					$u->where('emp_grade', $request->GradeID)
				)
			)
			
			->when($request->BranchID, function ($q) use ($request) {
				$q->where(function ($qb) use ($request) {
					$qb->whereJsonContains('VisitListBranchID', (int) $request->BranchID)
					->orWhere('VisitBranchID', $request->BranchID);
				});
			})
			->when($search, function ($q) use ($search) {
				if (str_starts_with($search, 'TMG')) {
					$search = substr($search, 3);
					if ($search !== '') {
						$q->whereRaw("SUBSTRING(TripClaimID,9) LIKE ?", ["%{$search}%"]);
					}
				} else {
					$q->where('TripClaimID', 'like', "%{$search}%");
				}
			});

		// Clone for count (FAST)
		$recordsTotal = (clone $baseQuery)->count();

		$data = $baseQuery
			->orderBy($orderCol, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		$formattedData = $data->map(fn($row) => [
			'TripClaimID'  => $row->TripClaimID,
			'created_at'   => $row->created_at->format('d/m/Y'),
			'triptype'     => $row->triptypedetails->TripTypeName ?? 'N/A',
			'emp_name'     => $row->userdata->emp_name ?? '-',
			'emp_id'       => $row->userdata->emp_id ?? '-',
			'VisitBranch'  => $row->visitbranchdetails
				? "{$row->visitbranchdetails->BranchName} ({$row->visitbranchdetails->BranchCode})"
				: 'Others',
			'Branch' => $row->visit_branch_list && $row->visit_branch_list->isNotEmpty()
                                                        ? $row->visit_branch_list->map(function ($branch) {
                                                                return $branch->BranchName . ' (' . $branch->BranchCode . ')';
                                                        })->implode(', ')
                                                        : 'Others',
			'Grade'        => $row->userdata->emp_grade ?? '-',
			'Department'   => $row->userdata->emp_department ?? '-',
			'TotalAmount'  => number_format($row->total_amount ?? 0, 2),
			'DeductAmount' => number_format($row->deduct_amount ?? 0, 2),
			'Status'       => $row->Status,
			'ApprovalDate' => $row->ApprovalDate ?? 'NA',
		]);

		return response()->json([
			"draw"            => intval($request->draw),
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data"            => $formattedData,
		]);
	}

	public function report_management_list_old(Request $request)
	{
		if (!$request->ajax()) {
			abort(404);
		}

		$pageLength = $request->length ?? 10;
		$skip       = $request->start ?? 0;
		$orderBy    = $request->order[0]['dir'] ?? 'desc';
		$columns    = ['TripClaimID', 'created_at'];
		$orderCol   = $columns[$request->order[0]['column'] ?? 0] ?? 'TripClaimID';
		$search     = trim(str_replace(' ', '', $request->search['value'] ?? ''));

		$baseQuery = ClaimManagement::query()
			->with([
				'visitbranchdetails:BranchID,BranchName,BranchCode',
				'triptypedetails:TripTypeID,TripTypeName',
				'userdata:id,emp_id,emp_name,emp_grade,emp_department',
			])
			->withSum('tripclaimdetails as total_amount', 'UnitAmount')
			->withSum('tripclaimdetails as deduct_amount', 'DeductAmount')

			->when($request->FromDate, fn($q) =>
				$q->whereDate('created_at', '>=', $request->FromDate)
			)
			->when($request->ToDate, fn($q) =>
				$q->whereDate('created_at', '<=', $request->ToDate)
			)
			->when($request->TripType, fn($q) =>
				$q->where('TripTypeID', $request->TripType)
			)
			->when($request->Status, fn($q) =>
				$q->where('Status', $request->Status)
			)
			->when($request->EmpID, fn($q) =>
				$q->whereHas('userdata', fn($u) =>
					$u->where('emp_id', 'like', "%{$request->EmpID}%")
				)
			)
			->when($request->GradeID, fn($q) =>
				$q->whereHas('userdata', fn($u) =>
					$u->where('emp_grade', $request->GradeID)
				)
			)
			->when($request->BranchID, fn($q) =>
				$q->where('VisitBranchID', $request->BranchID)
			)
			->when($search, function ($q) use ($search) {
				if (str_starts_with($search, 'TMG')) {
					$search = substr($search, 3);
					if ($search !== '') {
						$q->whereRaw("SUBSTRING(TripClaimID,9) LIKE ?", ["%{$search}%"]);
					}
				} else {
					$q->where('TripClaimID', 'like', "%{$search}%");
				}
			});

		// Clone for count (FAST)
		$recordsTotal = (clone $baseQuery)->count();

		$data = $baseQuery
			->orderBy($orderCol, $orderBy)
			->skip($skip)
			->take($pageLength)
			->get();

		$formattedData = $data->map(fn($row) => [
			'TripClaimID'  => $row->TripClaimID,
			'created_at'   => $row->created_at->format('d/m/Y'),
			'triptype'     => $row->triptypedetails->TripTypeName ?? 'N/A',
			'emp_name'     => $row->userdata->emp_name ?? '-',
			'emp_id'       => $row->userdata->emp_id ?? '-',
			'VisitBranch'  => $row->visitbranchdetails
				? "{$row->visitbranchdetails->BranchName} ({$row->visitbranchdetails->BranchCode})"
				: 'Others',
			'Grade'        => $row->userdata->emp_grade ?? '-',
			'Department'   => $row->userdata->emp_department ?? '-',
			'TotalAmount'  => number_format($row->total_amount ?? 0, 2),
			'DeductAmount' => number_format($row->deduct_amount ?? 0, 2),
			'Status'       => $row->Status,
			'ApprovalDate' => $row->ApprovalDate ?? 'NA',
		]);

		return response()->json([
			"draw"            => intval($request->draw),
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data"            => $formattedData,
		]);
	}
	
	public function report_management_view($id)
	{
		$data = ClaimManagement::with(['visitbranchdetails', 'usercodedetails', 'triptypedetails', 'userdata.branchDetails', 'userdata.baselocationDetails', 'tripclaimdetails', 'tripclaimdetailsforclaim'])
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
	public function finance_approve_claim(Request $request)
	{
		try {
			$now = new \DateTime();
			$currentdate = $now->format('Y-m-d H:i:s');

			$trip_det = Tripclaim::where('TripClaimID', $request->TripClaimID)
				->select('user_id')
				->first();

			if ($request->action == 'approve') {
				DB::table('myg_08_trip_claim')
					->where('TripClaimID', $request->TripClaimID)
					->update([
						'Status' => 'Approved',
						'FinanceRemarks' => $request->remarks ?? null,
						'SettleAmount' => str_replace(',', '', $request->SettleAmount),
						'FinanceApproverID' => $request->FinanceApproverID,
						'ApprovalDate' => $currentdate
					]);
				DB::table('myg_12_advancelist')
					->where('Status', 'Approved')
					->where('user_id', $trip_det->user_id)
					->update([
						'Status' => 'Paid',
					]);
			} else {
				DB::table('myg_08_trip_claim')
					->where('TripClaimID', $request->TripClaimID)
					->update([
						'Status' => 'Rejected',
						'FinanceRemarks' => $request->remarks ?? null,
						'SettleAmount' => str_replace(',', '', $request->SettleAmount),
						'FinanceApproverID' => $request->FinanceApproverID,
						'ApprovalDate' => $currentdate
					]);
			}

			$message = "Claim Approved Succesfully";
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
					$transactionID = $rowData[5]; // Assuming TransactionID is in the second column
					if (!empty($transactionID)) {
						// Find the trip claim record by TripClaimID
						$tripClaim = Tripclaim::where('TripClaimID', $tripClaimID)->first();

						// If the record exists, update the TransactionID
						if ($tripClaim) {
							$now = new \DateTime();  // Create a new DateTime object
							$currentdate = $now->format('Y-m-d H:i:s');
							DB::table('myg_08_trip_claim')
								->where('TripClaimID', $tripClaimID)
								->update(['TransactionID' => $transactionID, 'Status' => 'Paid', 'transaction_date' => $currentdate]);
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
		$advance_payment = AdvanceList::where('Status', 'Pending')->get();
		return view('admin.claim_management.advance_payment', compact('advance_payment'));
	}
	public function advance_list(Request $request)
	{
		if ($request->ajax()) {
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
				->where(function ($q) use ($searchValue) {
					$q->where('RequestDate', 'like', '%' . $searchValue . '%')
						->orWhere('TripPurpose', 'like', '%' . $searchValue . '%')
						->orWhere('Amount', 'like', '%' . $searchValue . '%')
						->orWhere('TransactionID', 'like', '%' . $searchValue . '%')
						->orWhere('Status', 'like', '%' . $searchValue . '%')
						->orWhere('Remarks', 'like', '%' . $searchValue . '%');
				})
				->orWhereHas('userdata', function ($q) use ($searchValue) {
					$q->where('emp_name', 'like', '%' . $searchValue . '%')
						->orWhere('emp_id', 'like', '%' . $searchValue . '%');
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

			$formattedData = $data->map(function ($row) {

				$action = "";
				if ($row->Status == 'Pending') {
					$action = '<a href="' . route('advance_approve', $row->id) . '" class="btn btn-success" data-toggle="modal" onclick="approve(\'' . $row->id . '\')"><i class="fa fa-check-square" aria-hidden="true"></i> Approve</a> <a href="' . route('advance_reject', $row->id) . '" class="btn btn-danger" data-toggle="modal" onclick="reject(\'' . $row->id . '\')"><i class="fa fa-times-circle" aria-hidden="true"></i> Reject</a>';
				} else if ($row->Status == 'Approved') {
					$action = '<a href="' . route('advance_settled', $row->id) . '" class="btn btn-success" data-toggle="modal"  onclick="settle(\'' . $row->id . '\')"><i class="fa fa-check-square" aria-hidden="true"></i> Mark as Complete</a>';
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
	public function advance_approve(Request $request)
	{
		$id = $request->id;
		$remark = $request->remarks;
		$affected = AdvanceList::where('id', $id)
			->update(['Remarks' => $remark, 'Status' => 'Approved']);
		if ($affected) {
			return response()->json(['success' => true]);
		} else {
			return response()->json(['success' => false]);
		}
	}
	public function advance_reject(Request $request)
	{
		$id = $request->id;
		$remark = $request->remarks;
		$affected = AdvanceList::where('id', $id)
			->update(['Remarks' => $remark, 'Status' => 'Rejected']);
		if ($affected) {
			return response()->json(['success' => true]);
		} else {
			return response()->json(['success' => false]);
		}
	}
	public function advance_settled(Request $request)
	{
		$id = $request->id;
		$TransactionID = $request->TransactionID;
		$affected = AdvanceList::where('id', $id)
			->update(['TransactionID' => $TransactionID, 'Status' => 'Paid']);
		if ($affected) {
			return response()->json(['success' => true]);
		} else {
			return response()->json(['success' => false, 'sql' => $affected]);
		}
	}
	public function update_tripclaimDetails(Request $request)
	{

		$TripClaimDetailID = $request->TripClaimDetailID;
		$UnitAmount = $request->UnitAmount;
		$approver_remarks = $request->approver_remarks;
		$UnitAmount = number_format((float) $request->UnitAmount, 2, '.', '');

		$affected = Tripclaimdetails::where('TripClaimDetailID', $TripClaimDetailID)
			->update(['UnitAmount' => $UnitAmount, 'approver_remarks' => $approver_remarks]);
		if ($affected) {
			return response()->json(['success' => true]);
		} else {
			return response()->json(['success' => false, 'sql' => $affected]);
		}
	}
	public function reject_tripclaimDetails(Request $request)
	{

		$TripClaimDetailID = $request->TripClaimDetailID;
		$TripClaimID = $request->TripClaimID;
		$approver_remarks = $request->reason;

		$tripClaimDetail = Tripclaimdetails::where('TripClaimDetailID', $TripClaimDetailID)->first();

		if ($tripClaimDetail) {
			// Increment the RejectionCount by 1
			$newRejectionCount = $tripClaimDetail->RejectionCount + 1;

			// Update the Status and RejectionCount
			$affected = Tripclaimdetails::where('TripClaimDetailID', $TripClaimDetailID)->update([
				'Status' => 'Rejected',
				'approver_remarks' => $approver_remarks,
				'RejectionCount' => $newRejectionCount,
			]);
			if ($affected) {
				$approvedCount = Tripclaimdetails::where('TripClaimID', $TripClaimID)
					->where('Status', 'Approved')
					->count();
				return response()->json(['success' => true, 'approvedCount' => $approvedCount]);
			} else {
				return response()->json(['success' => false, 'approvedCount' => 1]);
			}
		}
	}
	public function notifications()
	{
		return view('admin.claim_management.notifications');
	}
	public function notificationcount()
	{
		$notificationCount = ClaimManagement::whereIn("NotificationFlg", ['2', '3'])->where('AppNotificationFlg', '0')->count();
		return response()->json(['count' => $notificationCount]);
	}
	public function notifications_list(Request $request)
	{
		if ($request->ajax()) {
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
				->orderBy('AppNotificationFlg', 'ASC') // This ensures AppNotificationFlg = 0 comes first
				->orderBy('ApprovalDate', 'DESC')
				->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;

			$formattedData = $data->map(function ($row) {
				$tmgid = 'TMG' . substr($row->TripClaimID, 8);

				// Conditionally set action based on AppNotificationFlg value
				$action = ($row->AppNotificationFlg == 0)
					? '<a href="' . route('approved_claims_view', $row->TripClaimID) . '" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a>'
					: '<a href="' . route('approved_claims_view', $row->TripClaimID) . '" class="btn btn-disabled" style="color:#000;"><i class="fa fa-eye" aria-hidden="true"></i> View</a>';

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
	public function checkDuplicateClaimsForView($user_id, $FromDate, $categoryID, $TripClaimDetailID)
	{
		$data = [];

		if (!$FromDate || !strtotime($FromDate)) {
			return response()->json([
				'success' => 'error',
				'statusCode' => 400,
				'data' => [],
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
			->with(['tripClaimDetail.policyDet.subCategoryDetails.category', 'userData']) // Added userData
			->first();

		if (!$personDetails) {
			return $data;
		}

		$data['user_id'] = $user_id;
		$data['emp_id'] = $personDetails->userData->emp_id ?? null;
		$data['emp_name'] = $personDetails->userData->emp_name ?? null;
		$data['trip_claim_id'] = $personDetails->tripClaimDetail->TripClaimID ?? null;
		$data['trip_claim_detail_id'] = $personDetails->tripClaimDetail->TripClaimDetailID ?? null;

		return $data;
	}
}
