<?php
namespace App\Models;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\Tripclaim;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalClaims = Tripclaim::whereHas('tripclaimdetails', function ($query) {
            $query->where('Status', 'Pending');
        })->where('Status', 'Pending')->count();

        $totalClaimsAmount = DB::table('myg_08_trip_claim')
            ->join('myg_09_trip_claim_details', 'myg_08_trip_claim.TripClaimID', '=', 'myg_09_trip_claim_details.TripClaimID')
            ->where('myg_08_trip_claim.Status', 'Pending')
            ->where('myg_09_trip_claim_details.Status', 'Pending')
            ->sum('myg_09_trip_claim_details.UnitAmount');


        $pendingCount = Tripclaim::whereHas('tripclaimdetails', function ($query) {
            $query->where('Status', 'Approved');
        })->where('Status', 'Pending')->count();

        $pendingAmount = DB::table('myg_08_trip_claim')
            ->join('myg_09_trip_claim_details', 'myg_08_trip_claim.TripClaimID', '=', 'myg_09_trip_claim_details.TripClaimID')
            ->where('myg_08_trip_claim.Status', 'Pending')
            ->where('myg_09_trip_claim_details.Status', 'Approved')
            ->sum('myg_09_trip_claim_details.UnitAmount');


        $approvedCount = Tripclaim::where('Status', 'Approved')->count();

        $approvedAmount = DB::table('myg_08_trip_claim')
            ->join('myg_09_trip_claim_details', 'myg_08_trip_claim.TripClaimID', '=', 'myg_09_trip_claim_details.TripClaimID')
            ->where('myg_08_trip_claim.Status', 'Approved')
            ->sum('myg_09_trip_claim_details.UnitAmount');


        $settledCount = Tripclaim::where('Status', 'Paid')->count();

        $settledAmount = DB::table('myg_08_trip_claim')
            ->join('myg_09_trip_claim_details', 'myg_08_trip_claim.TripClaimID', '=', 'myg_09_trip_claim_details.TripClaimID')
            ->where('myg_08_trip_claim.Status', 'Paid')
            ->sum('myg_09_trip_claim_details.UnitAmount');

        return view('home', compact(
            'totalClaims', 'pendingCount', 'approvedCount', 'settledCount',
            'totalClaimsAmount', 'pendingAmount', 'approvedAmount', 'settledAmount'
        ));
    }
}
