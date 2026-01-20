<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClaimManagement;
use Auth;
use DB;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon; // Import Carbon

class ClaimController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
/**********************************************
   Date        : 28/06/2024
   Description :  list for claim management
**********************************************/    
    public function index()
    {
        
        return view('admin.claim_management.requested_claims');
    }
/**********************************************
   Date        : 28/06/2024
   Description :  view for claim management
**********************************************/  
    public function view()
    {
        return view('admin.claim_management.requested_claims_view');
    }
}
