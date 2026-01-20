<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tripclaim;
use App\Models\Tripclaimdetails;
use App\Models\Policy;
use App\Models\Category;
use App\Models\Branch;
use App\Models\SubCategories;
use App\Models\Attendance;
use App\Models\AdvanceList;
use App\Models\Personsdetails;
use Validator;
use App\Http\Controllers\Controller; // Import the base Controller class
use DB;
use Session;
use Hash;
use JWTAuth;
use App\Models\UserManagement;
use App\Models\Sequirityvlunerability;
use Illuminate\Support\Facades\Http;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public $successStatus = 200;
    public function __construct()
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->middleware('jwt.verify', ['except' => ['login','logout','hrmstokengeneration','refresh_token']]);
    }

    public function hrmstokengeneration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->username != "MYG_HRMS" || $request->password != "4fn+Q3OZdv45kE)Bqf") {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the user by username (assuming you have a User model)
        $user = User::where('emp_name', $request->username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $token = JWTAuth::fromUser($user);

        return $this->createNewTokenForHRMS($token);
    }

    protected function createNewTokenForHRMS($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.date' => 'required|date_format:d-m-Y',
            '*.emp_code' => 'required|string',
            // '*.punch_in' => 'required|date_format:h:i A',
            // '*.location_in' => 'required|string',
            // '*.punch_out' => 'required|date_format:h:i A',
            // '*.location_out' => 'required|string',
            // '*.duration' => 'required|date_format:H:i',
            '*.remarks' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }

        foreach ($request->all() as $attendanceData) {
            Attendance::create([
                'id' => $this->generateId(),
                'date' => Carbon::createFromFormat('d-m-Y', $attendanceData['date'])->format('Y-m-d'),
                'emp_code' => $attendanceData['emp_code'],
                'punch_in' => Carbon::createFromFormat('h:i A', $attendanceData['punch_in'])->format('H:i:s'),
                'location_in' => $attendanceData['location_in'],
                'punch_out' => Carbon::createFromFormat('h:i A', $attendanceData['punch_out'])->format('H:i:s'),
                'location_out' => $attendanceData['location_out'],
                'duration' => Carbon::createFromFormat('H:i', $attendanceData['duration'])->format('H:i:s'),
                'remarks' =>  $attendanceData['remarks']
            ]);
        }

        return response()->json(['message' => 'Attendances saved successfully', 'success' => 'success'], 201);
    }
    public function getbranchCodeid($string){
        $branchdata     = Branch::where('BranchCode', '=', $string)->select('BranchID')->first();
        // return $branchdata->BranchID;
        return $branchdata ? $branchdata->BranchID : '';
    }
    public function getbranchNameByID($id){
        $branchdata     = Branch::where('BranchID', '=', $id)->select('BranchName')->first();
        return $branchdata->BranchName;
    }
    public function userUpdate(Request $request){
        $data = $request->json()->all();
        $empDetailsList = $data['lst_emp'];
    
        $results = []; // To store the success or failure messages
    
        foreach ($empDetailsList as $empDetails) {
            $emp_code = $empDetails['Emp_code'];
            $emp_name = $empDetails['Emp_name'];
            $emp_department = $empDetails['Department'];
            $emp_branch = $this->getbranchCodeid($empDetails['Branch']);
            $emp_baselocation = $this->getbranchCodeid($empDetails['Base_location']);
            $emp_designation = $empDetails['Designation'];
            $emp_grade = $empDetails['Grade'];
            $email = $empDetails['Email'];
            $emp_phonenumber = $empDetails['mobile'];
            $reporting_person = $empDetails['Reporting_person_name'];
            $reporting_person_empid = $empDetails['Reporting_person_code'];
            $login_status = $empDetails['Login_status'];
    
            // Find the user by Emp_code
            $user = User::where('emp_id', $emp_code)->first();
    
            if ($user) {
                // Update the user details
                try {
                    $user->update([
                        'emp_name' => $emp_name,
                        'email' => $email,
                        'emp_phonenumber' => $emp_phonenumber,
                        'emp_department' => $emp_department,
                        'emp_branch' => $emp_branch,
                        'emp_baselocation' => $emp_baselocation,
                        'emp_designation' => $emp_designation,
                        'emp_grade' => $emp_grade,
                        'reporting_person' => $reporting_person,
                        'reporting_person_empid' => $reporting_person_empid,
                        'Status' => $login_status,
                    ]);
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'success'];
                } catch (\Exception $e) {
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => $e->getMessage()];
                }
            } else {
                $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => 'User not found'];
            }
        }
    
        return response()->json([
            'message' => 'Update operation completed',
            'results' => $results
        ], 200);
    }

    public function hrms_login_tokeq() {
        try {
            // Making the HTTP POST request with a timeout of 30 seconds
            $firstResponse = Http::timeout(20)->post('http://103.119.254.250:6062/integration/exacore_login_api/', [
                'Username' => 'MYGE-EXACORE',
                'Password' => 'ibGE44QJhDN~<*x86#4U',
            ]);

            // Checking if the request was successful
            if ($firstResponse->successful()) {
                // If the request was successful, return the token
                return response()->json(['token' => $firstResponse->json('token'), 'success' => 'success'], 200);
                // return $firstResponse->json('token');
                
            } else {
                // If the request failed, return an error message with the status code
                return response()->json([
                    'message' => 'Failed to authenticate with exacore_login_api',
                    'statusCode' => $firstResponse->status(),
                    'data' => $firstResponse->json(),
                    'success' => 'error'
                ], 200);
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Catching the RequestException and returning an error message
            return response()->json([
                'message' => 'Failed to connect to exacore_login_api',
                'error' => $e->getMessage(),
                'success' => 'error'
            ], 500);
        } catch (\Exception $e) {
            // Catching any other exception that might occur
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
                'success' => 'error'
            ], 500);
        }
    }
    public function hrms_login_token(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://103.119.254.250:6062/integration/exacore_login_api/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "Username=MYGE-EXACORE&Password=ibGE44QJhDN~<*x86#4U");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        curl_close($ch);
        $decodedResponse = json_decode($response, true);
    
        // Check if the decoding was successful
        if (json_last_error() === JSON_ERROR_NONE) {
            // Extract the token and return JSON response
            $token = $decodedResponse['token'] ?? null;
            return response()->json(['token' => $token, 'success' => 'success'], 200);
        } else {
            // Handle JSON decoding error
            return response()->json(['error' => 'Invalid JSON response'], 500);
        }
    }

/****************************************
   Date        :23/05/2024
   Description :  login
****************************************/
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required',
            'password' => 'required',

        ]);
        if ($validator->fails())
        {
            $errors  = json_decode($validator->errors());
            $emp_id=isset($errors->emp_id[0])? $errors->emp_id[0] : '';
            $password=isset($errors->password[0])? $errors->password[0] : '';
             if($emp_id)
            {
              $msg = $emp_id;
            }
            else if($password)
            {
              $msg = $password;
            }
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }

        

        $user=User::where('emp_id',$request->emp_id)->first();
        $checkexist   = DB::table('users')->where('emp_id',$request->emp_id)->exists();
        if($checkexist==true)
        {
            if (!Hash::check($request->password, $user->password)) 
            {
                return response()->json(['message' => 'Please check the password','statusCode'=>422,'data'=>[],'success'=>'error'],200);
            }
            $register   = User::where('emp_id',$request->emp_id)->first();
            $sequirity_userid=Sequirityvlunerability::where('user_id',$register->id)->update([
                'user_id'=>$register->id,
                'random_string'=>substr(uniqid(), 0,25)
            ]);

            $sequirity_refresh_token=Sequirityvlunerability::select('random_string')->where('user_id',$register->id)->first();
            $userData = User::with([
            'branchDetails' => function ($query) {
                $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Replace with the actual columns you need
            },
            'baselocationDetails' => function ($query) {
            $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Include the foreign key to the user table
            },
            'gradeDetails' => function ($query) {
            $query->select('GradeID as grade_id', 'GradeName as grade_name', 'GradeID'); // Include the foreign key to the user table
            }
            ])->where('emp_id', '=', $request->emp_id)->first();

            // dd($userData);
            $userToken=JWTAuth::fromUser($userData);
            $token   = $this->createNewToken($userToken,$userData);
            $message="verified successfully!";
            return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$userData,'token'=>$token,'success' => 'success','random_string'=>$sequirity_refresh_token->random_string], $this-> successStatus);
        }
        else
        {
            // First API call to get the token
            $exacoreToken =$this->hrms_login_token();
            $responseArray = $exacoreToken->getData(true);
            $token = $responseArray['token'];

            // Second API call using the token from the first call
            $secondResponse = Http::withHeaders([
                'Content-type' => 'application/json',
                'Authorization' => 'JWT ' . $token,
            ])->post('http://103.119.254.250:6062/integration/login_api/', [
                'Username' => $request->emp_id,
                'Password' => $request->password,
                'Key' => 'dv)B45k+Q34fnOZEqf',
            ]);
            
            
            $data=[];
            if ($secondResponse->failed()) {
                return response()->json([
                    'message' => 'Failed to authenticate with login_api',
                    'statusCode' => $secondResponse->status(),
                    'data' => [],
                    'errorDetails' => $secondResponse->body(),
                    'success' => 'error'
                ], 200);
            }else{
                $data = $secondResponse->json();
                // Extract the values you need
                $empDetails = $data['lst_emp'];
                $emp_id = $empDetails['Emp_code'];
                $emp_name = $empDetails['Emp_name'];
                $emp_department = $empDetails['Department'];
                $emp_branch = $empDetails['Branch'];
                $emp_baselocation = $empDetails['Base_location'];
                $emp_designation = $empDetails['Designation'];
                $emp_grade = $empDetails['Grade'];
                $email = $empDetails['Email'];
                $emp_phonenumber = $empDetails['mobile'];
                $reporting_person = $empDetails['Reporting_person_name'];
                $reporting_person_empid = $empDetails['Reporting_person_code'];
                $login_status = $empDetails['Login_status'];
            }

          
            $now = new \DateTime();  // Create a new DateTime object
            $currentDateTime = $now->format('Y-m-d H:i:s'); 
            $register=User::Create([
                'emp_id'=>$emp_id,
                'password'=>Hash::make($request->password),
                'emp_name'=>$emp_name,
                'emp_department'=>$emp_department,
                'emp_branch'=>$this->getbranchCodeid($emp_branch),
                'emp_baselocation'=>$this->getbranchCodeid($emp_baselocation),
                'emp_designation'=>$emp_designation,
                'emp_grade'=>$this->getgrade($emp_grade),
                'emp_role'=>$emp_grade,
                'email'=>$email,
                'emp_phonenumber'=>$emp_phonenumber,
                'reporting_person'=>$reporting_person,
                'reporting_person_empid'=>$reporting_person_empid,
                'created_at'=>$currentDateTime,
                'updated_at'=>$currentDateTime
            ]);

            $sequirity_id=Sequirityvlunerability::create([
                'user_id'=>$register->id,
                'random_string'=>substr(uniqid(), 0,25)
            ]);
            $sequirity_refresh_token=Sequirityvlunerability::select('random_string')->where('id', $sequirity_id->id)->first();

            $userData = User::with([
            'branchDetails' => function ($query) {
                $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Replace with the actual columns you need
            },
            'baselocationDetails' => function ($query) {
            $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Include the foreign key to the user table
            },
            'gradeDetails' => function ($query) {
            $query->select('GradeID as grade_id', 'GradeName as grade_name', 'GradeID'); // Include the foreign key to the user table
            }
            ])->where('emp_id', '=', $request->emp_id)->first();

            $userToken=JWTAuth::fromUser($userData);
            $token   = $this->createNewToken($userToken,$userData);
            $message="user verified successfully!";
            return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$userData,'token'=>$token,'success' => 'success','random_string'=>$sequirity_refresh_token->random_string], $this-> successStatus);    
        }
    }

    public function getgrade($string){
        $arr0=['D1'];//array for CMD
        $arr1=['D2'];//array for GM
        $arr2=['M1'];//array for approver
        $arr3=['M2'];//array for approver
        $arr4=['M3','E1'];//array for employee
        $arr5=['M4','E2','E3','S1','S2'];//array for employee
        if (in_array($string, $arr0)) {
            return 0;
        }
        if (in_array($string, $arr1)) {
            return 1;
        }
        if (in_array($string, $arr2)) {
            return 2;
        }
        if (in_array($string, $arr3)) {
            return 3;
        }
        if (in_array($string, $arr4)) {
            return 4;
        }
        if (in_array($string, $arr5)) {
            return 5;
        }
    }
    public function getbranchid($string){
        $brachdata     = Branch::where('BranchName', '=', $string)->select('BranchID')->first();
        return $brachdata->BranchID;
    }
    public function getEmployeeID($string){
        $emp_data     = User::where('id', '=', $string)->select('emp_id')->first();
        return $emp_data->emp_id;
    }
    /****************************************
     Date        :23/05/2024
    Description :  logout
    ****************************************/
     public function logout() 
    {

        $user = auth()->user();
        $user->fcm_token = null;
        $user->save();
        auth()->logout();
        session()->forget('Role');
        return response()->json(['message' => 'User successfully signed out', 'statusCode' => 200, 'data' => [], 'success' => 'success']);
       
    }
/****************************************
   Date        :23/05/2024
   Description :  refresh token
****************************************/
    public function refresh_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'random_string'  => 'required'
        ]);
        if ($validator->fails())
        {
            $errors  = json_decode($validator->errors());
            $random_string=isset($errors->random_string[0])? $errors->random_string[0] : '';
             if($random_string)
            {
              $msg = $random_string;
            }
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }
        
        $checkexist = DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->exists();
        if ($checkexist == true) 
        {
            $token = $request->bearerToken();
            $vulnerability = DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->first();
            
            if ($vulnerability) 
            {
                // Assuming you want to fetch the user associated with the vulnerability
                //$user = User::where('id', $vulnerability->user_id)->first();
                // dd($user);
                $user = User::find($vulnerability->user_id);
                // dd($user);

                if ($user) 
                {
                    // Generate a new token
                    $newToken = JWTAuth::fromUser($user);
                    return response()->json([
                    'statusCode' => $this->successStatus,
                    'data' => $user,
                    'token' => $newToken,
                    'success' => 'success'
                    ], $this->successStatus);
                } 
                else 
                {

                    return response()->json([
                    'statusCode' => 404,
                    'message' => 'User not found'
                    ], 404);
                }
            } else 
            {
                return response()->json([
                'statusCode' => 404,
                'message' => 'Vulnerability not found'
                ], 404);
            }
        }
        else
        {
            $error="User does not exist.";
            return response()->json(['message'=>$error,'statusCode'=>401,'data'=>[],'success' => 'error'],$this-> successStatus);
        }
    }   
/****************************************
   Date        :23/05/2024
   Description :  get user details
****************************************/   
    public function userProfile()
    {
        try
        {
            if(auth()->user())
            {
                $userData = User::with([
                    'branchDetails' => function ($query) {
                        $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Replace with the actual columns you need
                    },
                    'baselocationDetails' => function ($query) {
                    $query->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code', 'BranchID'); // Include the foreign key to the user table
                    },
                    'gradeDetails' => function ($query) {
                    $query->select('GradeID as grade_id', 'GradeName as grade_name', 'GradeID'); // Include the foreign key to the user table
                    }
                    ])->where('id', auth()->user()->id)->first();
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$userData,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }
/****************************************
   Date        :23/05/2024
   Description :  create a new token
****************************************/
    protected function createNewToken($token,$user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }
/****************************************
   Date        :29/06/2024
   Description :  list of branches
****************************************/
    public function list_branch()
    {
        try
        {
            if(auth()->user())
            {
                $branch   = DB::table('myg_11_branch')
                                ->where('Status', '1')
                                ->select('BranchID as branch_id', 'BranchName as branch_name', 'BranchCode as branch_code')
                                ->orderBy('branch_name', 'ASC')
                                ->get();
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$branch,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }
/****************************************
   Date        :29/06/2024
   Description :  list of trip types
****************************************/
    public function list_triptype()
    {
        try
        {
            if(auth()->user())
            {
                $triptype   = DB::table('myg_01_triptypes')
                                ->where('Status', '1')
                                ->select('TripTypeID as triptype_id', 'TripTypeName as triptype_name')
                                ->get();
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$triptype,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }
/****************************************
   Date        :29/06/2024
   Description :  list of category 
****************************************/
    public function list_category()
    {
        try
        {
            if(auth()->user())
            {
                $catgeory   = DB::table('myg_03_categories')->where('Status', '1')
                            ->select("CategoryID as category_id","CategoryName as category_name","TripFrom as trip_from","TripTo as trip_to","FromDate as from_date","ToDate as to_date","DocumentDate as document_date","StartMeter as start_meter","EndMeter as end_meter","ImageUrl as image_url")
                            ->get();
                $message="Result fetched successfully!";
                $catgeory->map(function($category) {
                    $category->trip_from = (bool)$category->trip_from;
                    $category->trip_to = (bool)$category->trip_to;
                    $category->from_date = (bool)$category->from_date;
                    $category->to_date = (bool)$category->to_date;
                    $category->document_date = (bool)$category->document_date;
                    $category->start_meter = (bool)$category->start_meter;
                    $category->end_meter = (bool)$category->end_meter;
                    $imagePath = 'images/category/' . $category->image_url;
                    $category->image_url = url($imagePath);
                    return $category;
                });
                // $catgeory->map(function($item) {
                //     $item->Expand = false;
                //     $item->Oncheck = false;
                //     return $item;
                // });
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$catgeory,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function categorieswithpolicy(Request $request)
    {
        try
        {
            if (auth()->user()) {
                $user = DB::table('users')->where('id', auth()->user()->id)->first();
                $gradeID = $user->emp_grade;

                // $categories = Category::with(['subcategorydetails' => function($query) use ($gradeID) {
                //     $query->whereHas('policies', function($q) use ($gradeID) {
                //         $q->where('GradeID', $gradeID);
                        
                //     })->with(['policies' => function($q) use ($gradeID) {
                //         $q->where('GradeID', $gradeID);
                //     }]);
                // }])->where('Status', '1')
                // ->select("CategoryID", "CategoryName", "TripFrom", "TripTo", "FromDate", "ToDate", "DocumentDate", "StartMeter", "EndMeter", "ImageUrl")
                // ->get()
                // ->filter(function ($category) {
                //     return $category->subcategorydetails->isNotEmpty();
                // });
                $categories = Category::with(['subcategorydetails' => function($query) use ($gradeID) {
                    $query->whereHas('policies', function($q) use ($gradeID) {
                        $q->where('GradeID', $gradeID)
                        ->where('Status', "1");
                    })->with(['policies' => function($q) use ($gradeID) {
                        $q->where('GradeID', $gradeID);
                    }]);
                }])->where('Status', '1')
                ->select("CategoryID", "CategoryName", "TripFrom", "TripTo", "FromDate", "ToDate", "DocumentDate", "StartMeter", "EndMeter", "ImageUrl")
                ->get()
                ->filter(function ($category) {
                    return $category->subcategorydetails->isNotEmpty();
                });
                $message = "Result fetched successfully!";

                $categories = $categories->map(function($category) {
                    $category->trip_from_flag = (bool)$category->TripFrom;
                    $category->trip_to_flag = (bool)$category->TripTo;
                    $category->from_date_flag = (bool)$category->FromDate;
                    $category->to_date_flag = (bool)$category->ToDate;
                    $category->document_date_flag = (bool)$category->DocumentDate;
                    $category->start_meter_flag = (bool)$category->StartMeter;
                    $category->end_meter_flag = (bool)$category->EndMeter;
                    $category->image_url = env('APP_URL').'/images/category/' . $category->ImageUrl;
                
                    // Initialize class_flg to false
                    $classFlg = false;
                
                    // Check if subcategorydetails are available
                    if ($category->subcategorydetails->isNotEmpty()) {
                        // Check policies for each subcategory
                        $category->subcategorydetails->each(function($subcategory) use (&$classFlg) {
                            if ($subcategory->policies->isNotEmpty()) {
                                // Check if any policy has GradeType "Class"
                                foreach ($subcategory->policies as $policy) {
                                    if ($policy->GradeType === "Class") {
                                        $classFlg = true; // Set class_flg to true if found
                                        break;
                                    }
                                }
                            }
                        });
                
                        // Map subcategorydetails
                        $category->subcategorydetails = $category->subcategorydetails->map(function($subcategory) {
                            $policies = $subcategory->policies->map(function($policy) {
                                return (object)[
                                    "policy_id" => $policy->PolicyID,
                                    "grade_id" => $policy->GradeID,
                                    "grade_type" => $policy->GradeType,
                                    "grade_class" => $policy->GradeClass,
                                    "grade_amount" => $policy->GradeAmount,
                                    "approver" => $policy->Approver,
                                    "status" => $policy->Status,
                                    // "class_flg" => $policy->GradeType === "Class"
                                ];
                            });
                
                            $firstPolicy = $policies->first();
                            return (object)[
                                "subcategory_id" => $subcategory->SubCategoryID,
                                "subcategory_name" => $subcategory->SubCategoryName,
                                "status" => $subcategory->Status,
                                "policies" => $firstPolicy ?? $policies->toArray() // Single or multiple policies
                            ];
                        });
                    }
                
                    // Return the mapped object with class_flg before subcategorydetails
                    return (object)[
                        "category_id" => $category->CategoryID,
                        "category_name" => $category->CategoryName,
                        "trip_from_flag" => $category->trip_from_flag,
                        "trip_to_flag" => $category->trip_to_flag,
                        "from_date_flag" => $category->from_date_flag,
                        "to_date_flag" => $category->to_date_flag,
                        "document_date_flag" => $category->document_date_flag,
                        "start_meter_flag" => $category->start_meter_flag,
                        "end_meter_flag" => $category->end_meter_flag,
                        "no_of_days" => 15,
                        "class_flg" => $classFlg,  // Set the class_flg here
                        "image_url" => $category->image_url,
                        "subcategorydetails" => $category->subcategorydetails
                    ];
                });
                
                $categories = $categories->values();
                return response()->json([
                    'message' => $message,
                    'statusCode' => $this->successStatus,
                    'data' => $categories,
                    'success' => 'success'
                ], $this->successStatus);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }


    /****************************************
    Date        :06/07/2024
    Description :  Employee Names
    ****************************************/
    public function employeeNames(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required',
        ]);
        // dd($request);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);  // Change the status code here to 422
        }
        try {
            // Fetch employee details
            $employeeDetails = DB::table('users')
                ->where(function($query) use ($request) {
                    $query->where('emp_id', 'like', '%' . $request->emp_id . '%')
                        ->orWhere('emp_name', 'like', '%' . $request->emp_id . '%');
                })
                ->select('id', 'emp_id', 'emp_name', 'emp_grade')
                ->where('id', '!=', auth()->id())
                ->get();
            $message = "Result fetched successfully!";
            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => $employeeDetails,
                'success' => 'success'
            ], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error fetching employee details:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function ApproverNames()
    {
       
        try {
            // Fetch employee details
            $employeeDetails = DB::table('users')
                ->where('emp_id', 'MYGC-5346')
                ->select('id','emp_id','emp_name','emp_grade')
                ->get();
            $message = "Result fetched successfully!";
            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => $employeeDetails,
                'success' => 'success'
            ], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error fetching employee details:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function fileUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048', // Example validation rules
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads'); // Store the file in the 'uploads' directory

            return response()->json([
                'message' => 'File uploaded successfully.',
                'statusCode' => 200,
                'data' => [
                    'filePath' => $path
                ],
                'success' => 'success',
            ], 200);
        }

        return response()->json([
            'message' => 'No file uploaded.',
            'statusCode' => 400,
            'data' => [],
            'success' => 'error',
        ], 400);
    }
    private function extractDates($claimDetails)
    {
        $dates = [];

        foreach ($claimDetails as $claimDetail) {
            $this->addDatesBetween($claimDetail['from_date'], $claimDetail['to_date'], $dates);
            $dates[] = substr($claimDetail['document_date'], 0, 10);
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

    private function addDatesBetween($fromDate, $toDate, &$dates)
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

    public function tripClaimOld(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'triptype_id' => 'required',
            'visit_branch_id' => 'required',
            'trip_purpose' => 'required',
            "claim_details" => 'required|array',
            // 'claim_details.*.person_details' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        $user=User::where('id',auth()->id())->first();
                
        try {
            if (auth()->user()) {
                $claim_id = $this->generateId();
                $data = $request->all(); // Get all request data as an array
                $now = new \DateTime();  // Create a new DateTime object
                $currentdate = $now->format('Y-m-d H:i:s'); 
                $tripClaim = Tripclaim::create([
                    'TripClaimID' => $claim_id,
                    'TripTypeID' => $request->triptype_id,
                    'ApproverID' => $user->reporting_person_empid,
                    'TripPurpose' => $request->trip_purpose ?? null,
                    'VisitBranchID' => $request->visit_branch_id,
                    'AdvanceAmount' =>null,
                    'RejectionCount' => 0,
                    'ApprovalDate' => $currentdate,
                    'NotificationFlg' => "0",
                    'Status' => "Pending",
                    'user_id' => auth()->id(),
                ]);
    
                foreach ($data["claim_details"] as $details) {
                    $TripClaimDetailID=$this->generateId();
                    $Tripclaimdetails = Tripclaimdetails::create([
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'TripClaimID' => $claim_id,
                        'PolicyID' => $details['policy_id'],
                        'FromDate' => $details['from_date'] ?? null,
                        'ToDate' => $details['to_date'] ?? null,
                        'TripFrom' => $details['trip_from'] ?? null,
                        'TripTo' => $details['trip_to'] ?? null,
                        'DocumentDate' => $details['document_date'] ?? null,
                        'StartMeter' => $details['start_meter'] ?? null,
                        'EndMeter' => $details['end_meter'] ?? null,
                        'Qty' => $details['qty'] ?? null,
                        'UnitAmount' => $details['unit_amount'] ?? null,
                        'NoOfPersons' => $details['no_of_person'],
                        'FileUrl' => $details['file_url'] ?? null,
                        'Remarks' => $details['remarks'] ?? null,
                        'NotificationFlg' => "0",
                        'RejectionCount' => 0,
                        'ApproverID' => $user->reporting_person_empid,
                        'Status' => "Pending",
                        'user_id' => auth()->id(),
                    ]);
                   
                    $persondetails = Personsdetails::create([
                        'PersonDetailsID' => $this->generateId(),
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'EmployeeID' => auth()->id(),
                        'Grade' => $user->emp_grade,
                        'ClaimOwner' =>'1',
                        'user_id' => auth()->id()
                    ]);
                    foreach($details['person_details'] as $perdet){
                        if (!isset($perdet['id']) || !isset($perdet['grade'])) {
                            return response()->json([
                                'message' => 'Invalid person detail structure',
                                'statusCode' => 400,
                                'data' => $perdet,
                                'success' => 'error',
                            ], 400);
                        }

                        $persondetails = Personsdetails::create([
                            'PersonDetailsID' => $this->generateId(),
                            'TripClaimDetailID' => $TripClaimDetailID,
                            'EmployeeID' => $perdet['id'],
                            'Grade' => $perdet['grade'],
                            'ClaimOwner' =>'0',
                            'user_id' => auth()->id()
                        ]);
                    }
                }
                $message = "Claim submitted successfully!";
                    return response()->json(['message' => $message, 'statusCode' => 200, 'success' => 'success'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => 'An error occurred while submitting the claim. Please try again later.',
            ], 500);
        }
    }

    public function tripClaimSubmit(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'triptype_id' => 'required',
            'visit_branch_id' => 'required',
            'trip_purpose' => 'required',
            "claim_details" => 'required|array',
            // 'claim_details.*.person_details' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
       

        $user=User::where('id',auth()->id())->first();
        $data = $request->all();
        $dates = $this->extractDates($data['claim_details']);
        
        $exacoreToken =$this->hrms_login_token();  
        $responseArray = $exacoreToken->getData(true);
        $token = $responseArray['token'];
        $secondResponse = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'JWT ' . $token,
        ])->post('http://103.119.254.250:6062/integration/status_api/', [
            'Emp_code' => $user->emp_id,
            'Dates' =>  $dates
        ]);
        $data=[];
        if ($secondResponse->failed()) {
            return response()->json([
                'message' => 'Failed to authenticate with status_api',
                'statusCode' => $secondResponse->status(),
                'data' => [],
                'errorDetails' => $secondResponse->body(),
                'success' => 'error',
                'user' => $user->emp_id,
                'Dates' => $dates
            ], 200);
        }else{
            $resdata = $secondResponse->json();
            $falseDates = [];

            foreach ($resdata['data'][$user->emp_id] as $arrdate => $arrvalue) {
                if ($arrvalue == 0) {
                    $falseDates[] = $arrdate;
                }
            }

            if (!empty($falseDates)) {
                return response()->json([
                    'message' => 'Some dates have a status of false.',
                    'statusCode' => 200,
                    'data' => [
                        'FalseDates' => $falseDates
                    ],
                    'success' => 'error',
                ], 200);
            }
        }

        try {
            if (auth()->user()) {
                $claim_id = $this->generateId();
                $data = $request->all(); // Get all request data as an array
                $now = new \DateTime();  // Create a new DateTime object
                $currentdate = $now->format('Y-m-d H:i:s'); 
                // $tripClaim = Tripclaim::create([
                //     'TripClaimID' => $claim_id,
                //     'TripTypeID' => $request->triptype_id,
                //     'ApproverID' => $user->reporting_person_empid,
                //     'TripPurpose' => $request->trip_purpose ?? null,
                //     'VisitBranchID' => $request->visit_branch_id,
                //     'AdvanceAmount' =>null,
                //     'RejectionCount' => 0,
                //     'ApprovalDate' => $currentdate,
                //     'NotificationFlg' => "0",
                //     'Status' => "Pending",
                //     'user_id' => auth()->id(),
                // ]);
    
                foreach ($data["claim_details"] as $details) {
                    $TripClaimDetailID=$this->generateId();
                    // $Tripclaimdetails = Tripclaimdetails::create([
                    //     'TripClaimDetailID' => $TripClaimDetailID,
                    //     'TripClaimID' => $claim_id,
                    //     'PolicyID' => $details['policy_id'],
                    //     'FromDate' => $details['from_date'] ?? null,
                    //     'ToDate' => $details['to_date'] ?? null,
                    //     'TripFrom' => $details['trip_from'] ?? null,
                    //     'TripTo' => $details['trip_to'] ?? null,
                    //     'DocumentDate' => $details['document_date'] ?? null,
                    //     'StartMeter' => $details['start_meter'] ?? null,
                    //     'EndMeter' => $details['end_meter'] ?? null,
                    //     'Qty' => $details['qty'] ?? null,
                    //     'UnitAmount' => $details['unit_amount'] ?? null,
                    //     'NoOfPersons' => $details['no_of_person'],
                    //     'FileUrl' => $details['file_url'] ?? null,
                    //     'Remarks' => $details['remarks'] ?? null,
                    //     'NotificationFlg' => "0",
                    //     'RejectionCount' => 0,
                    //     'ApproverID' => $user->reporting_person_empid,
                    //     'Status' => "Pending",
                    //     'user_id' => auth()->id(),
                    // ]);
                   
                    // $persondetails = Personsdetails::create([
                    //     'PersonDetailsID' => $this->generateId(),
                    //     'TripClaimDetailID' => $TripClaimDetailID,
                    //     'EmployeeID' => auth()->id(),
                    //     'Grade' => $user->emp_grade,
                    //     'ClaimOwner' =>'1',
                    //     'user_id' => auth()->id()
                    // ]);
                    foreach($details['person_details'] as $perdet){
                        if (!isset($perdet['id']) || !isset($perdet['grade'])) {
                            return response()->json([
                                'message' => 'Invalid person detail structure',
                                'statusCode' => 400,
                                'data' => $perdet,
                                'success' => 'error',
                            ], 400);
                        }

                        $persondetails = Personsdetails::create([
                            'PersonDetailsID' => $this->generateId(),
                            'TripClaimDetailID' => $TripClaimDetailID,
                            'EmployeeID' => $perdet['id'],
                            'Grade' => $perdet['grade'],
                            'ClaimOwner' =>'0',
                            'user_id' => auth()->id()
                        ]);
                    }
                }

                $url = 'http://103.119.254.250:6062/integration/status_api/';
                $data = [
                    "EmployeeDetails" => [
                        [
                            "Emp_code" => $user->emp_id,
                            "Date" => array_map(function($date) {
                                return ["Date" => $date, "Status" => true];
                            }, $dates),
                        ],
                    ],
                ];
                
                // dd($data);

                $headers = [
                    "Content-Type" => "application/json",
                    "Authorization" => "JWT ".$token,
                ];

                $response_hrms = Http::withHeaders($headers)->post($url, $data);

                if ($response_hrms->successful()) {
                    // The request was successful, handle the response here
                    $responseBody = $response_hrms->json(); // Convert the JSON response to an array

                } else {
                    // Handle the error
                    $statusCode = $response_hrms->status(); // Get the status code of the response
                    $responseBody = $response_hrms->body(); // Get the raw body of the error response
                }
    dd($responseBody);

                $message = "Claim submitted successfully!";
                return response()->json(['message' => $message, 'statusCode' => 200, 'success' => 'success'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => 'An error occurred while submitting the claim. Please try again later.',
            ], 500);
        }
    }
    public function tripClaim(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'triptype_id' => 'required',
            'visit_branch_id' => 'required',
            'trip_purpose' => 'required',
            "claim_details" => 'required|array',
            // 'claim_details.*.person_details' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
       

        $user=User::where('id',auth()->id())->first();
        // $data = $request->all();
        // $dates = $this->extractDates($data['claim_details']);
        
        // $exacoreToken =$this->hrms_login_token();  
        // // dd($exacoreToken);
        // $responseArray = $exacoreToken->getData(true);
        // $token = $responseArray['token'];
        // $secondResponse = Http::withHeaders([
        //     'Content-type' => 'application/json',
        //     'Authorization' => 'JWT ' . $token,
        // ])->post('http://103.119.254.250:6062/integration/status_api/', [
        //     'Emp_code' => $user->emp_id,
        //     'Dates' =>  $dates
        // ]);
        // $data=[];
        // if ($secondResponse->failed()) {
        //     return response()->json([
        //         'message' => 'Failed to authenticate with status_api',
        //         'statusCode' => $secondResponse->status(),
        //         'data' => [],
        //         'errorDetails' => $secondResponse->body(),
        //         'success' => 'error',
        //         'user' => $user->emp_id,
        //         'Dates' => $dates
        //     ], 200);
        // }else{
        //     $resdata = $secondResponse->json();
        //     $falseDates = [];

        //     foreach ($resdata['data'][$user->emp_id] as $arrdate => $arrvalue) {
        //         if ($arrvalue == 0) {
        //             $falseDates[] = $arrdate;
        //         }
        //     }

        //     if (!empty($falseDates)) {
        //         return response()->json([
        //             'message' => 'Some dates have a status of false.',
        //             'statusCode' => 200,
        //             'data' => [
        //                 'FalseDates' => $falseDates
        //             ],
        //             'success' => 'error',
        //         ], 200);
        //     }
        // }

        try {
            if (auth()->user()) {
                $claim_id = $this->generateId();
                $data = $request->all(); // Get all request data as an array
                $now = new \DateTime();  // Create a new DateTime object
                $currentdate = $now->format('Y-m-d H:i:s'); 
                $tripClaim = Tripclaim::create([
                    'TripClaimID' => $claim_id,
                    'TripTypeID' => $request->triptype_id,
                    'ApproverID' => $user->reporting_person_empid,
                    'TripPurpose' => $request->trip_purpose ?? null,
                    'VisitBranchID' => $request->visit_branch_id,
                    'AdvanceAmount' =>null,
                    'RejectionCount' => 0,
                    'ApprovalDate' => $currentdate,
                    'NotificationFlg' => "0",
                    'Status' => "Pending",
                    'user_id' => auth()->id(),
                ]);
    
                foreach ($data["claim_details"] as $details) {
                    $TripClaimDetailID=$this->generateId();
                    $Tripclaimdetails = Tripclaimdetails::create([
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'TripClaimID' => $claim_id,
                        'PolicyID' => $details['policy_id'],
                        'FromDate' => $details['from_date'] ?? null,
                        'ToDate' => $details['to_date'] ?? null,
                        'TripFrom' => $details['trip_from'] ?? null,
                        'TripTo' => $details['trip_to'] ?? null,
                        'DocumentDate' => $details['document_date'] ?? null,
                        'StartMeter' => $details['start_meter'] ?? null,
                        'EndMeter' => $details['end_meter'] ?? null,
                        'Qty' => $details['qty'] ?? null,
                        'UnitAmount' => $details['unit_amount'] ?? null,
                        'DeductAmount' => $details['unit_amount'] ?? null,
                        'NoOfPersons' => $details['no_of_person'],
                        'FileUrl' => $details['file_url'] ?? null,
                        'Remarks' => $details['remarks'] ?? null,
                        'approver_remarks' => null,
                        'NotificationFlg' => "0",
                        'RejectionCount' => 0,
                        'ApproverID' => $user->reporting_person_empid,
                        'Status' => "Pending",
                        'user_id' => auth()->id(),
                    ]);      
                    $persondetails = Personsdetails::create([
                        'PersonDetailsID' => $this->generateId(),
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'EmployeeID' => auth()->id(),
                        'Grade' => $user->emp_grade,
                        'ClaimOwner' =>'1',
                        'user_id' => auth()->id()
                    ]);
                    foreach($details['person_details'] as $perdet){
                        if (!isset($perdet['id']) || !isset($perdet['grade'])) {
                            return response()->json([
                                'message' => 'Invalid person detail structure',
                                'statusCode' => 400,
                                'data' => $perdet,
                                'success' => 'error',
                            ], 400);
                        }

                        $persondetails = Personsdetails::create([
                            'PersonDetailsID' => $this->generateId(),
                            'TripClaimDetailID' => $TripClaimDetailID,
                            'EmployeeID' => $perdet['id'],
                            'Grade' => $perdet['grade'],
                            'ClaimOwner' =>'0',
                            'user_id' => auth()->id()
                        ]);
                    }
                }
                $message = "Claim submitted successfully!";
                return response()->json(['message' => $message, 'statusCode' => 200, 'success' => 'success'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => 'An error occurred while submitting the claim. Please try again later.',
                'errmessage' =>  $e->getMessage(),
            ], 500);
        }
    }

/****************************************
   Date        :29/06/2024
   Description :  list of claims 
   edited by sandeep on 11-07-2024
****************************************/
    public function claimList(){
        try
        {
            if(auth()->user())
            {
                $reportingPersonEmpID = auth()->user()->emp_id;
                $tripStatus="";
                $total_amount="";
                $tripdata = Tripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('user_id', auth()->id())
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->get()                
                ->map(function ($trip) use ($reportingPersonEmpID) {
                    $tripAmount = $trip->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');
                    $statuses = $trip->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $trip->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')){
                        $appStatus = 'Approved';
                        if($trip->Status==='Paid'){
                            $tripStatus = 'Paid';
                        }else if($trip->Status==='Pending'){
                            $tripStatus = 'Pending';
                        }else if($trip->Status==='Rejected'){
                            $tripStatus = 'Rejected';
                        }
                        else{
                            $tripStatus = 'Approved';
                        }
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                        $appStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                        $appStatus = 'Pending';
                    }
                    

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

                    $tripApprovedDate = $trip->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $trip->tripclaimdetails->max('rejected_date');

                    // Convert dates to the desired format if not null
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
                    return [
                        "trip_claim_id"=>$trip->TripClaimID,
                        "trip_type_details" => $trip->triptypedetails->first() ? [
                            "triptype_id" => $trip->triptypedetails->first()->TripTypeID,
                            "triptype_name" => $trip->triptypedetails->first()->TripTypeName
                        ] : null,
                        "approver_details" => $trip->approverdetails->first() ? [
                            "id" => $trip->approverdetails->first()->id,
                            "emp_id" => $trip->approverdetails->first()->emp_id,
                            "emp_name" => $trip->approverdetails->first()->emp_name
                        ] : null,
                        "trip_purpose"=>$trip->TripPurpose,
                        "visit_branch_detail" => $trip->visitbranchdetails->first() ? [
                            "branch_id" => $trip->visitbranchdetails->first()->BranchID,
                            "branch_name" => $trip->visitbranchdetails->first()->BranchName,
                        ] : null,  
                        "tmg_id"=>'TMG' . substr($trip->TripClaimID, 8),
                        'date'=> $trip->created_at->format('d/m/Y'),
                        'trip_status'=> $tripStatus,
                        'trip_history_status'=> $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
                        'pending_from' => $tripStatus=='Pending' ? ( $appStatus=='Pending' ? 'From RO' : 'From Finance' ) : null,
                        'total_amount'=> $tripAmount,
                        'trip_approved_date'=> $tripApprovedDate,
                        'trip_rejected_date'=> $tripRejectedDate,

                        'approver_status' => $appStatus,

                        'finance_status' => $tripStatus==='Rejected' ? 'Rejected' : ($trip->Status === 'Paid' ? 'Approved' : $trip->Status),
                        'finance_status_change_date' => $trip->ApprovalDate ? \Carbon\Carbon::parse($trip->ApprovalDate)->format('d/m/Y') : null,
                        "finance_approver_details" => $trip->financeApproverdetails->first() ? [
                            "id" => $trip->financeApproverdetails->first()->id,
                            "emp_id" => $trip->financeApproverdetails->first()->emp_id,
                            "emp_name" => $trip->financeApproverdetails->first()->emp_name
                        ] : null,
                        'categories' => $trip->tripclaimdetails->groupBy(function ($detail) {
                            return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
                            })->map(function ($groupedDetails, $categoryID) use($reportingPersonEmpID) {
                                $category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
                                $subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
                                $policy = $groupedDetails->first()->policyDet;
                                return [
                                    "category_id" => $categoryID,
                                    "category_name" => $category->CategoryName ?? null,
                                    "image_url" => env('APP_URL').'/images/category/' .$category->ImageUrl,
                                    "trip_from_flag" =>  (bool)$category->TripFrom,
                                    "trip_to_flag" =>  (bool)$category->TripTo,
                                    "from_date_flag" =>  (bool)$category->FromDate,
                                    "to_date_flag" =>  (bool)$category->ToDate,
                                    "document_date_flag" =>  (bool)$category->DocumentDate,
                                    "start_meter_flag" =>  (bool)$category->StartMeter,
                                    "end_meter_flag" =>  (bool)$category->EndMeter,
                                    "no_of_days" => 15,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID) {                                        
                                        return [
                                            "trip_claim_details_id" => $detail->TripClaimDetailID,
                                            "from_date"=> $detail->FromDate,
                                            "to_date"=> $detail->ToDate,
                                            "trip_from"=> $detail->TripFrom,
                                            "trip_to"=> $detail->TripTo,
                                            "document_date"=>$detail->DocumentDate,
                                            "start_meter"=> $detail->StartMeter,
                                            "end_meter"=> $detail->EndMeter,
                                            "qty"=> $detail->Qty,
                                            "status"=> $detail->Status,
                                            "unit_amount"=> $detail->UnitAmount,
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
                                            "approver_remarks"=>$detail->approver_remarks,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
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
                                            "policy_details" =>  $subcategory->SubCategoryID ? [
                                                "sub_category_id" => $subcategory->SubCategoryID,
                                                "sub_category_name" => $subcategory->SubCategoryName,
                                                "policy_id" => $policy->PolicyID,
                                                "grade_id" => $policy->GradeID,
                                                "grade_type" => $policy->GradeType,
                                                "grade_class" => $policy->GradeClass,
                                                "grade_amount" => $policy->GradeAmount,
                                            ] : null,
                                            "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)

                                        ];
                                    }),
                                ];
                            })->values()
                    ];
                });
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$tripdata,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function claimsForApproval(){ 
        try
        {
            if(auth()->user())
            {
                $reportingPersonEmpID = auth()->user()->emp_id;
                $tripStatus="";
                $total_amount="";
                $tripHistoryStatus="";
                $finance_approved_date="";
                $tripdata = Tripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('Status', 'Pending')
                ->where('ApproverID', auth()->user()->emp_id)
                ->whereHas('tripclaimdetails', function ($query) {
                    $query->where('ApproverID', auth()->user()->emp_id);
                })
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map(function ($trip) use ($reportingPersonEmpID) {
                    $tripAmount = $trip->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');
                    $statuses = $trip->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $trip->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')) {
                        $appStatus = 'Approved';
                        if($trip->Status==='Paid'){
                            $tripStatus = 'Paid';
                        }else if($trip->Status==='Pending'){
                            $tripStatus = 'Pending';
                        }else{
                            $tripStatus = 'Approved';
                        }
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                        $appStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                        $appStatus = 'Pending';
                    }

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
                    $tripApprovedDate = $trip->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $trip->tripclaimdetails->max('rejected_date');

                    // Convert dates to the desired format if not null
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
                    return [
                        "trip_claim_id"=>$trip->TripClaimID,
                        "trip_type_details" => $trip->triptypedetails->first() ? [
                            "triptype_id" => $trip->triptypedetails->first()->TripTypeID,
                            "triptype_name" => $trip->triptypedetails->first()->TripTypeName
                        ] : null,
                        "approver_details" => $trip->approverdetails->first() ? [
                            "id" => $trip->approverdetails->first()->id,
                            "emp_id" => $trip->approverdetails->first()->emp_id,
                            "emp_name" => $trip->approverdetails->first()->emp_name
                        ] : null,
                        "trip_purpose"=>$trip->TripPurpose,
                        "visit_branch_detail" => $trip->visitbranchdetails->first() ? [
                            "branch_id" => $trip->visitbranchdetails->first()->BranchID,
                            "branch_name" => $trip->visitbranchdetails->first()->BranchName,
                        ] : null,
                        "user_details" => $trip->tripuserdetails->first() ? [
                            "id" => $trip->tripuserdetails->first()->id,
                            "emp_id" => $trip->tripuserdetails->first()->emp_id,
                            "emp_name" => $trip->tripuserdetails->first()->emp_name,
                            "emp_branch" => $this->getbranchNameByID($trip->tripuserdetails->first()->emp_branch),
                            "emp_baselocation"=> $this->getbranchNameByID($trip->tripuserdetails->first()->emp_baselocation)
                        ] : null,  
                        "tmg_id"=>'TMG' . substr($trip->TripClaimID, 8),
                        'date'=> $trip->created_at->format('d/m/Y'),
                        'trip_status'=> $tripStatus,
                        'trip_history_status'=> $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
                        'total_amount'=> $tripAmount,
                        'trip_approved_date'=> $tripApprovedDate,
                        'trip_rejected_date'=> $tripRejectedDate,

                        'approver_status' => $appStatus,

                        'finance_status' => $tripStatus==='Rejected' ? 'Rejected' : ($trip->Status === 'Paid' ? 'Approved' : $trip->Status),
                        'finance_status_change_date' => $trip->ApprovalDate ? \Carbon\Carbon::parse($trip->ApprovalDate)->format('d/m/Y') : null,
                        "finance_approver_details" => $trip->financeApproverdetails->first() ? [
                            "id" => $trip->financeApproverdetails->first()->id,
                            "emp_id" => $trip->financeApproverdetails->first()->emp_id,
                            "emp_name" => $trip->financeApproverdetails->first()->emp_name
                        ] : null,
                        'categories' => $trip->tripclaimdetails->groupBy(function ($detail)  {
                            return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
                            })->map(function ($groupedDetails, $categoryID) use ($reportingPersonEmpID) {
                                $category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
                                $subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
                                $policy = $groupedDetails->first()->policyDet;
                                return [
                                    "category_id" => $categoryID,
                                    "category_name" => $category->CategoryName ?? null,
                                    "image_url" => env('APP_URL').'/images/category/' .$category->ImageUrl,
                                    "trip_from_flag" =>  (bool)$category->TripFrom,
                                    "trip_to_flag" =>  (bool)$category->TripTo,
                                    "from_date_flag" =>  (bool)$category->FromDate,
                                    "to_date_flag" =>  (bool)$category->ToDate,
                                    "document_date_flag" =>  (bool)$category->DocumentDate,
                                    "start_meter_flag" =>  (bool)$category->StartMeter,
                                    "end_meter_flag" =>  (bool)$category->EndMeter,
                                     "no_of_days" => 15,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID) {                                        
                                            return [
                                            "trip_claim_details_id" => $detail->TripClaimDetailID,
                                            "from_date"=> $detail->FromDate,
                                            "to_date"=> $detail->ToDate,
                                            "trip_from"=> $detail->TripFrom,
                                            "trip_to"=> $detail->TripTo,
                                            "document_date"=>$detail->DocumentDate,
                                            "start_meter"=> $detail->StartMeter,
                                            "end_meter"=> $detail->EndMeter,
                                            "qty"=> $detail->Qty,
                                            "status"=> $detail->Status,
                                            "unit_amount"=> $detail->UnitAmount,
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
                                            "approver_remarks"=>$detail->approver_remarks,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
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

                                            "policy_details" =>  $subcategory->SubCategoryID ? [
                                                "sub_category_id" => $subcategory->SubCategoryID,
                                                "sub_category_name" => $subcategory->SubCategoryName,
                                                "policy_id" => $policy->PolicyID,
                                                "grade_id" => $policy->GradeID,
                                                "grade_type" => $policy->GradeType,
                                                "grade_class" => $policy->GradeClass,
                                                "grade_amount" => $policy->GradeAmount,
                                            ] : null,
                                        "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)
                                        ];
                                    }),
                                ];
                            })->values()
                    ];
                });
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$tripdata,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function claimsForSpecialApproval(){ 
        try
        {
            if(auth()->user())
            {
                $reportingPersonEmpID = auth()->user()->emp_id;
                $tripStatus="";
                $total_amount="";
                $tripHistoryStatus="";
                $finance_approved_date="";
                $tripdata = Tripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails',
                ])
                ->where('SpecialApproverID', auth()->user()->emp_id)
                ->whereHas('tripclaimdetails', function ($query) {
                    $query->where('ApproverID', auth()->user()->emp_id);
                })
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map(function ($trip) use ($reportingPersonEmpID) {
                    $tripAmount = $trip->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');
                    $statuses = $trip->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $trip->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')) {
                        $appStatus = 'Approved';
                        if($trip->Status==='Paid'){
                            $tripStatus = 'Paid';
                        }else if($trip->Status==='Pending'){
                            $tripStatus = 'Pending';
                        }else{
                            $tripStatus = 'Approved';
                        }
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                        $appStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                        $appStatus = 'Pending';
                    }

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
                    $tripApprovedDate = $trip->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $trip->tripclaimdetails->max('rejected_date');

                    // Convert dates to the desired format if not null
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
                    return [
                        "trip_claim_id"=>$trip->TripClaimID,
                        "trip_type_details" => $trip->triptypedetails->first() ? [
                            "triptype_id" => $trip->triptypedetails->first()->TripTypeID,
                            "triptype_name" => $trip->triptypedetails->first()->TripTypeName
                        ] : null,
                        "approver_details" => $trip->approverdetails->first() ? [
                            "id" => $trip->approverdetails->first()->id,
                            "emp_id" => $trip->approverdetails->first()->emp_id,
                            "emp_name" => $trip->approverdetails->first()->emp_name
                        ] : null,
                        "trip_purpose"=>$trip->TripPurpose,
                        "visit_branch_detail" => $trip->visitbranchdetails->first() ? [
                            "branch_id" => $trip->visitbranchdetails->first()->BranchID,
                            "branch_name" => $trip->visitbranchdetails->first()->BranchName,
                        ] : null,
                        "user_details" => $trip->tripuserdetails->first() ? [
                            "id" => $trip->tripuserdetails->first()->id,
                            "emp_id" => $trip->tripuserdetails->first()->emp_id,
                            "emp_name" => $trip->tripuserdetails->first()->emp_name,
                            "emp_branch" => $this->getbranchNameByID($trip->tripuserdetails->first()->emp_branch),
                            "emp_baselocation"=> $this->getbranchNameByID($trip->tripuserdetails->first()->emp_baselocation)
                        ] : null,  
                        "tmg_id"=>'TMG' . substr($trip->TripClaimID, 8),
                        'date'=> $trip->created_at->format('d/m/Y'),
                        'trip_status'=> $tripStatus,
                        'trip_history_status'=> $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
                        'total_amount'=> $tripAmount,
                        'trip_approved_date'=> $tripApprovedDate,
                        'trip_rejected_date'=> $tripRejectedDate,

                        'approver_status' => $appStatus,

                        'finance_status' => $tripStatus==='Rejected' ? 'Rejected' : ($trip->Status === 'Paid' ? 'Approved' : $trip->Status),
                        'finance_status_change_date' => $trip->ApprovalDate ? \Carbon\Carbon::parse($trip->ApprovalDate)->format('d/m/Y') : null,
                        "finance_approver_details" => $trip->financeApproverdetails->first() ? [
                            "id" => $trip->financeApproverdetails->first()->id,
                            "emp_id" => $trip->financeApproverdetails->first()->emp_id,
                            "emp_name" => $trip->financeApproverdetails->first()->emp_name
                        ] : null,
                        'categories' => $trip->tripclaimdetails->groupBy(function ($detail) {
                            return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
                            })->map(function ($groupedDetails, $categoryID) use ($reportingPersonEmpID){
                                $category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
                                $subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
                                $policy = $groupedDetails->first()->policyDet;
                                return [
                                    "category_id" => $categoryID,
                                    "category_name" => $category->CategoryName ?? null,
                                    "image_url" => env('APP_URL').'/images/category/' .$category->ImageUrl,
                                    "trip_from_flag" =>  (bool)$category->TripFrom,
                                    "trip_to_flag" =>  (bool)$category->TripTo,
                                    "from_date_flag" =>  (bool)$category->FromDate,
                                    "to_date_flag" =>  (bool)$category->ToDate,
                                    "document_date_flag" =>  (bool)$category->DocumentDate,
                                    "start_meter_flag" =>  (bool)$category->StartMeter,
                                    "end_meter_flag" =>  (bool)$category->EndMeter,
                                     "no_of_days" => 15,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID) {                                        
                                            return [
                                            "trip_claim_details_id" => $detail->TripClaimDetailID,
                                            "from_date"=> $detail->FromDate,
                                            "to_date"=> $detail->ToDate,
                                            "trip_from"=> $detail->TripFrom,
                                            "trip_to"=> $detail->TripTo,
                                            "document_date"=>$detail->DocumentDate,
                                            "start_meter"=> $detail->StartMeter,
                                            "end_meter"=> $detail->EndMeter,
                                            "qty"=> $detail->Qty,
                                            "status"=> $detail->Status,
                                            "unit_amount"=> $detail->UnitAmount,
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
                                            "approver_remarks"=>$detail->approver_remarks,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
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
                                            "policy_details" =>  $subcategory->SubCategoryID ? [
                                                "sub_category_id" => $subcategory->SubCategoryID,
                                                "sub_category_name" => $subcategory->SubCategoryName,
                                                "policy_id" => $policy->PolicyID,
                                                "grade_id" => $policy->GradeID,
                                                "grade_type" => $policy->GradeType,
                                                "grade_class" => $policy->GradeClass,
                                                "grade_amount" => $policy->GradeAmount,
                                            ] : null,
                                        "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)  
                                        ];
                                    }),
                                ];
                            })->values()
                    ];
                });
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$tripdata,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function claimsForCMDApproval(){ 
        try
        {
            if(auth()->user())
            {
                $reportingPersonEmpID = auth()->user()->emp_id;
                $tripStatus="";
                $total_amount="";
                $tripHistoryStatus="";
                $finance_approved_date="";
                $tripdata = Tripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('CMDApproverID', auth()->user()->emp_id)
                ->whereHas('tripclaimdetails', function ($query) {
                    $query->where('ApproverID', auth()->user()->emp_id);
                })
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map(function ($trip) use ($reportingPersonEmpID){
                    $tripAmount = $trip->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');
                    $statuses = $trip->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $trip->tripclaimdetails->pluck('RejectionCount');
                    if ($statuses->every(fn($status) => $status === 'Approved')) {
                        $appStatus = 'Approved';
                        if($trip->Status==='Paid'){
                            $tripStatus = 'Paid';
                        }else if($trip->Status==='Pending'){
                            $tripStatus = 'Pending';
                        }else{
                            $tripStatus = 'Approved';
                        }
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                        $appStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                        $appStatus = 'Pending';
                    }

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
                    $tripApprovedDate = $trip->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $trip->tripclaimdetails->max('rejected_date');

                    // Convert dates to the desired format if not null
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
                    return [
                        "trip_claim_id"=>$trip->TripClaimID,
                        "trip_type_details" => $trip->triptypedetails->first() ? [
                            "triptype_id" => $trip->triptypedetails->first()->TripTypeID,
                            "triptype_name" => $trip->triptypedetails->first()->TripTypeName
                        ] : null,
                        "approver_details" => $trip->approverdetails->first() ? [
                            "id" => $trip->approverdetails->first()->id,
                            "emp_id" => $trip->approverdetails->first()->emp_id,
                            "emp_name" => $trip->approverdetails->first()->emp_name
                        ] : null,
                        "trip_purpose"=>$trip->TripPurpose,
                        "visit_branch_detail" => $trip->visitbranchdetails->first() ? [
                            "branch_id" => $trip->visitbranchdetails->first()->BranchID,
                            "branch_name" => $trip->visitbranchdetails->first()->BranchName,
                        ] : null,
                        "user_details" => $trip->tripuserdetails->first() ? [
                            "id" => $trip->tripuserdetails->first()->id,
                            "emp_id" => $trip->tripuserdetails->first()->emp_id,
                            "emp_name" => $trip->tripuserdetails->first()->emp_name,
                            "emp_branch" => $this->getbranchNameByID($trip->tripuserdetails->first()->emp_branch),
                            "emp_baselocation"=> $this->getbranchNameByID($trip->tripuserdetails->first()->emp_baselocation)
                        ] : null,  
                        "tmg_id"=>'TMG' . substr($trip->TripClaimID, 8),
                        'date'=> $trip->created_at->format('d/m/Y'),
                        'trip_status'=> $tripStatus,
                        'trip_history_status'=> $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
                        'total_amount'=> $tripAmount,
                        'trip_approved_date'=> $tripApprovedDate,
                        'trip_rejected_date'=> $tripRejectedDate,

                        'approver_status' => $appStatus,

                        'finance_status' => $tripStatus==='Rejected' ? 'Rejected' : ($trip->Status === 'Paid' ? 'Approved' : $trip->Status),
                        'finance_status_change_date' => $trip->ApprovalDate ? \Carbon\Carbon::parse($trip->ApprovalDate)->format('d/m/Y') : null,
                        "finance_approver_details" => $trip->financeApproverdetails->first() ? [
                            "id" => $trip->financeApproverdetails->first()->id,
                            "emp_id" => $trip->financeApproverdetails->first()->emp_id,
                            "emp_name" => $trip->financeApproverdetails->first()->emp_name
                        ] : null,
                        'categories' => $trip->tripclaimdetails->groupBy(function ($detail) {
                            return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
                            })->map(function ($groupedDetails, $categoryID) use($reportingPersonEmpID) {
                                $category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
                                $subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
                                $policy = $groupedDetails->first()->policyDet;
                                return [
                                    "category_id" => $categoryID,
                                    "category_name" => $category->CategoryName ?? null,
                                    "image_url" => env('APP_URL').'/images/category/' .$category->ImageUrl,
                                    "trip_from_flag" =>  (bool)$category->TripFrom,
                                    "trip_to_flag" =>  (bool)$category->TripTo,
                                    "from_date_flag" =>  (bool)$category->FromDate,
                                    "to_date_flag" =>  (bool)$category->ToDate,
                                    "document_date_flag" =>  (bool)$category->DocumentDate,
                                    "start_meter_flag" =>  (bool)$category->StartMeter,
                                    "end_meter_flag" =>  (bool)$category->EndMeter,
                                     "no_of_days" => 15,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID) {                                        
                                            return [
                                            "trip_claim_details_id" => $detail->TripClaimDetailID,
                                            "from_date"=> $detail->FromDate,
                                            "to_date"=> $detail->ToDate,
                                            "trip_from"=> $detail->TripFrom,
                                            "trip_to"=> $detail->TripTo,
                                            "document_date"=>$detail->DocumentDate,
                                            "start_meter"=> $detail->StartMeter,
                                            "end_meter"=> $detail->EndMeter,
                                            "qty"=> $detail->Qty,
                                            "status"=> $detail->Status,
                                            "unit_amount"=> $detail->UnitAmount,
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
                                            "approver_remarks"=>$detail->approver_remarks,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
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

                                            "policy_details" =>  $subcategory->SubCategoryID ? [
                                                "sub_category_id" => $subcategory->SubCategoryID,
                                                "sub_category_name" => $subcategory->SubCategoryName,
                                                "policy_id" => $policy->PolicyID,
                                                "grade_id" => $policy->GradeID,
                                                "grade_type" => $policy->GradeType,
                                                "grade_class" => $policy->GradeClass,
                                                "grade_amount" => $policy->GradeAmount,
                                            ] : null,
                                        "send_approver_flag" => false  
                                        ];
                                    }),
                                ];
                            })->values()
                    ];
                });
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$tripdata,'success' => 'success'], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }    

    public function approvalAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_id' => 'required',  // Expect an array of IDs
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }

        try {
            $now = new \DateTime();
            $currentdate = $now->format('Y-m-d');
            $trip_claim_id=$request->trip_claim_id;
            $message = "Claims updated successfully!";
            $status = $request->status;
            
            if ($status == 'Rejected') {
                DB::table('myg_08_trip_claim')
                ->where('TripClaimID',$trip_claim_id)
                ->update([
                    // 'Status' => $status,
                    'ApproverRemarks' => $request->remarks,
                    'NotificationFlg' => '4',
                ]);
                $message = "Claims rejected successfully!";
                DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimID',$trip_claim_id)
                    ->where('Status','Pending')
                    ->update([
                        'Status' => $status,
                        'RejectionCount' => DB::raw('RejectionCount + 1'),
                        'approved_date' => null,
                        'rejected_date' => Carbon::now()->toDateString(),
                        'approver_remarks' => $request->remarks,
                    ]);
            } elseif ($status == 'Approved') {
                DB::table('myg_08_trip_claim')
                ->where('TripClaimID',$trip_claim_id)
                ->update([
                    // 'Status' => $status,
                    'ApproverRemarks' => $request->remarks,
                    'NotificationFlg' => '2',
                ]);
                $message = "Claims approved successfully!";
                DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimID', $trip_claim_id)
                    ->where('ApproverID', auth()->user()->emp_id)
                    ->where('Status','Pending')
                    ->update([
                        'Status' => $status,
                        'approved_date' => Carbon::now()->toDateString(),
                        'rejected_date' => null,
                        'approver_remarks' => $request->remarks,
                    ]);
            } else {
                return response()->json([
                    'success' => 'error',
                    'statusCode' => 500,
                    'data' => [],
                    'message' => "Status is missing or invalid",
                ]);
            }

            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => [],
                'success' => 'success',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in Claim Updation:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function specialApprovalAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_id' => 'required',  // Expect an array of IDs
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }

        try {
            $now = new \DateTime();
            $currentdate = $now->format('Y-m-d');
            $trip_claim_id=$request->trip_claim_id;
            $message = "Claims updated successfully!";
            $status = $request->status;
            $reqstatus = $request->status;
            $user = DB::table('users')->where('id', auth()->user()->id)->first();
            $emp_id = $user->emp_id;
            $tripStatus="";

            $tripdata = Tripclaim::with([
                'tripclaimdetails.policyDet.subCategoryDetails.category',
                'tripclaimdetails.personsDetails.userDetails'
            ])
            ->where('TripClaimID', $request->trip_claim_id)
            ->orderBy('created_at', 'DESC')
            ->first();  
           
            if ($tripdata) {                
                $statuses = $tripdata->tripclaimdetails->pluck('Status');
                if ($statuses->every(fn($status) => $status === 'Approved')){
                    $tripStatus = 'Approved';
                    if($tripdata->Status === 'Paid'){
                        $tripStatus = 'Paid';
                    } elseif($tripdata->Status === 'Pending'){
                        $tripStatus = 'Pending';
                    }
                } elseif ($statuses->contains('Rejected')) {
                    $tripStatus = 'Rejected';
                } else {
                    $tripStatus = 'Pending';
                }

            } else {
                return response()->json([
                    'message' => 'Trip claim not found',
                    'statusCode' => 404,
                    'data' => [],
                    'success' => 'error'
                ], 404);
            }
            
            if ($reqstatus == 'Rejected') {
                DB::table('myg_08_trip_claim')
                ->where('TripClaimID',$trip_claim_id)
                ->update([
                    'Status' => $tripStatus,
                    'ApproverRemarks' => $request->remarks,
                    'NotificationFlg' => '4',
                ]);
                $message = "Claims rejected successfully!";
                DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimID',$trip_claim_id)
                    ->where('ApproverID',$emp_id)
                    ->where('Status','Pending')
                    ->update([
                        'Status' => $reqstatus,
                        'RejectionCount' => DB::raw('RejectionCount + 1'),
                        'approved_date' => null,
                        'rejected_date' => Carbon::now()->toDateString(),
                        'approver_remarks' => $request->remarks,
                    ]);
            } elseif ($reqstatus == 'Approved') {
                DB::table('myg_08_trip_claim')
                ->where('TripClaimID',$trip_claim_id)
                ->update([
                    'Status' => $tripStatus,
                    'ApproverRemarks' => $request->remarks, 
                    'NotificationFlg' => '2',
                ]);
                $message = "Claims approved successfully!";
                DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimID', $trip_claim_id)
                    ->where('ApproverID',$emp_id)
                    ->where('Status','Pending')
                    ->update([
                        'Status' => $reqstatus,
                        'approved_date' => Carbon::now()->toDateString(),
                        'rejected_date' => null,
                        'approver_remarks' => $request->remarks,
                    ]);
            } else {
                return response()->json([
                    'success' => 'error',
                    'statusCode' => 500,
                    'data' => [],
                    'message' => "Status is missing or invalid",
                ]);
            }

            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => [],
                'success' => 'success',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in Claim Updation:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function rejectSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_details_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);  // Change the status code here to 422
        }
        try {
            $now = new \DateTime();  // Create a new DateTime object
            $currentdate = $now->format('Y-m-d'); 
            $categoryName = DB::table('myg_09_trip_claim_details as tcd')
                ->join('myg_06_policies as p', 'tcd.PolicyID', '=', 'p.PolicyID')
                ->join('myg_04_subcategories as sc', 'p.SubCategoryID', '=', 'sc.SubCategoryID')
                ->join('myg_03_categories as c', 'sc.CategoryID', '=', 'c.CategoryID')
                ->where('tcd.TripClaimDetailID', $request->trip_claim_details_id)
                ->value('c.CategoryName');

            $message = "$categoryName category rejected successfully!";

            $rej_count = DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->value('RejectionCount'); 
            
            $Trip = DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimDetailID', $request->trip_claim_details_id)
                    ->first(); // first() returns a single result object, not a collection

            

            DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->update(['status' => 'Rejected','RejectionCount'=>$rej_count+1,'approved_date'=>null,'rejected_date' => Carbon::now()->toDateString(),'approver_remarks'=>$request->remarks]);
            if ($Trip) { // Check if $Trip is not null
                $t = DB::table('myg_08_trip_claim')
                    ->where('TripClaimID', $Trip->TripClaimID)
                    ->update([
                        'NotificationFlg' => '4'
                    ]);
            }
            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => [],
                'success' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function specialApproverRejectSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_details_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);  // Change the status code here to 422
        }
        try {
            $now = new \DateTime();  // Create a new DateTime object
            $currentdate = $now->format('Y-m-d'); 
            $message = "Claim Rejected successfully!";

            $deductAmount = Tripclaimdetails::select('myg_09_trip_claim_details.*', 'myg_06_policies.GradeAmount')
            ->where('myg_06_policies.Status', '1')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->join('myg_06_policies', 'myg_06_policies.PolicyID', '=', 'myg_09_trip_claim_details.PolicyID')->first();
            $ReAmount=$deductAmount->GradeAmount;

            DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->update(['status' => 'Approved','RejectionCount'=>3,'approved_date'=>null,'rejected_date' => Carbon::now()->toDateString(),'approver_remarks'=>$request->remarks,'UnitAmount'=>$ReAmount]);

            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => [],
                'success' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function removeSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_details_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);  // Change the status code here to 422
        }
        try {
            $message = "Claim Removed successfully!";
            $deletedRows = DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->delete();

            if ($deletedRows > 0) {
                return response()->json([
                    'message' => $message,
                    'statusCode' => 200,
                    'data' => [],
                    'success' => 'success'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No record found to delete',
                    'statusCode' => 404,
                    'data' => [],
                    'success' => 'error'
                ], 404);
            }
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in Claim Detail Deletion:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function approverChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_claim_details_id' => 'required',
            'approver_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);  // Change the status code here to 422
        }
        try {
            // Fetch employee details
            $now = new \DateTime();  // Create a new DateTime object
            $currentdate = $now->format('Y-m-d'); 
            $message = "Claim send for higher approval";

            $tripclaimid     = Tripclaimdetails::where('TripClaimDetailID', '=', $request->trip_claim_details_id)->select('TripClaimID')->first();

            $tripclaimid = $tripclaimid->TripClaimID; // Extract the TripClaimID from the model instance

            $updatedRows1 = DB::table('myg_08_trip_claim')
                ->where('TripClaimID', $tripclaimid)
                ->update(['SpecialApproverID' => $request->approver_id,'NotificationFlg'=>'0']);
            
        // $updatedRows2 = DB::table('myg_08_trip_claim')
        //     ->where('TripClaimID', $tripclaimid)
        //     ->update(['ApproverID' => $request->approver_id]);
        
        // if ($updatedRows1 === 0 || $updatedRows2 === 0) {
        //     Log::info('No rows were updated for SpecialApproverID or ApproverID', [
        //         'tripclaimid' => $tripclaimid,
        //         'approver_id' => $request->approver_id,
        //     ]);
        // }
            DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->update(['approver_remarks'=>$request->remarks,'ApproverID'=>$request->approver_id]);
            return response()->json([
                'message' => $message,
                'statusCode' => 200,
                'data' => [],
                'success' => 'success'
            ], 200);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in Claim Updation:', ['exception' => $e]);
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function generateId (){
        $micro = gettimeofday()['usec'];
        $todate =  date("YmdHis");
        $alpha = substr(md5(rand()), 0, 2);
        return($todate.$micro.$alpha);
    }
    
    public function claimResubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "claim_details" => 'required|array',
            // 'claim_details.*.person_details' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        try {
            if (auth()->user()) {
                $user=User::where('id',auth()->id())->first();
                foreach ($request["claim_details"] as $details) {
                    $rej_count=0;
                    $rej_count = DB::table('myg_09_trip_claim_details')
                ->where('TripClaimDetailID', $details['trip_claim_details_id'])
                ->value('RejectionCount'); 
                    $Tripclaimdetails = DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimDetailID', $details['trip_claim_details_id'])->update([
                        'PolicyID' => $details['policy_id'],
                        'FromDate' => $details['from_date'] ?? null,
                        'ToDate' => $details['to_date'] ?? null,
                        'TripFrom' => $details['trip_from'] ?? null,
                        'TripTo' => $details['trip_to'] ?? null,
                        'DocumentDate' => $details['document_date'] ?? null,
                        'StartMeter' => $details['start_meter'] ?? null,
                        'EndMeter' => $details['end_meter'] ?? null,
                        'Qty' => $details['qty'] ?? null,
                        'UnitAmount' => $details['unit_amount'] ?? null,
                        'NoOfPersons' => $details['no_of_person'],
                        'FileUrl' => $details['file_url'] ?? null,
                        'Remarks' => $details['remarks'] ?? null,
                        'NotificationFlg' => '0',
                        'RejectionCount' =>$rej_count+1,
                        'ApproverID' => $user->reporting_person_empid,
                        'Status' => "Pending",
                        'user_id' => auth()->id(),
                    ]);
                    $Trip = DB::table('myg_09_trip_claim_details')
                        ->where('TripClaimDetailID', $details['trip_claim_details_id'])
                        ->first(); // first() returns a single result object, not a collection

                    if ($Trip) { // Check if $Trip is not null
                        $t = DB::table('myg_08_trip_claim')
                            ->where('TripClaimID', $Trip->TripClaimID)
                            ->update([
                                'NotificationFlg' => '8'
                            ]);
                    }
                    // dd($t);
                    Personsdetails::where('TripClaimDetailID', $details['trip_claim_details_id'])->delete();
                    foreach($details['person_details'] as $perdet){
                        $claimowner = '0';
                        if ($perdet['id'] == auth()->id()) {
                            $claimowner = '1';
                        }

                        // Debugging: Ensure $perdet array structure
                        if (!isset($perdet['id']) || !isset($perdet['grade'])) {
                            return response()->json([
                                'message' => 'Invalid person detail structure',
                                'statusCode' => 400,
                                'data' => $perdet,
                                'success' => 'error',
                            ], 400);
                        }
                        
                        $persondetails = Personsdetails::create([
                            'PersonDetailsID' => $this->generateId(),
                            'TripClaimDetailID' => $details['trip_claim_details_id'],
                            'EmployeeID' => $perdet['id'],
                            'Grade' => $perdet['grade'],
                            'ClaimOwner' => $claimowner,
                            'user_id' => auth()->id()
                        ]);
                    }
                }
    
                $message = "Claim Resubmitted successfully!";
                return response()->json(['message' => $message, 'statusCode' => 200, 'success' => 'success'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => 'An error occurred while Resubmitting the claim. Please try again later.',
                'messageerr' =>  $e->getMessage(),
            ], 500);
        }
    }

    public function notificationList()
    {
        try
        {
            if(auth()->user())
            {
                $user = DB::table('users')->where('id', auth()->user()->id)->first();
                $id=$user->id;
                $emp_id=$user->emp_id;
                $unreadTripData = DB::table('myg_08_trip_claim')
                            ->join('users', 'myg_08_trip_claim.user_id', '=', 'users.id') 
                            ->where(function ($query) use ($id) {
                                $query->where('user_id', $id) 
                                ->where('NotificationFlg','2');
                            })
                            ->orwhere(function ($query) use ($id) {
                                $query->where('user_id', $id) 
                                ->where('NotificationFlg','4');
                            })
                            ->orwhere(function ($query) use ($id) {
                                $query->where('user_id', $id) 
                                ->where('NotificationFlg','6');
                            })
                            ->orWhere(function ($query) use ($emp_id) {
                                $query->where('ApproverID', $emp_id)
                                    ->where('NotificationFlg', '0');
                            })
                            ->orWhere(function ($query) use ($emp_id) {
                                $query->where('ApproverID', $emp_id)
                                    ->where('NotificationFlg', '8');
                            })
                            ->orWhere(function ($query) use ($emp_id) {
                                $query->where('SpecialApproverID', $emp_id)
                                    ->where('NotificationFlg', '0');
                            })
                            ->orWhere(function ($query) use ($emp_id) {
                                $query->where('CMDApproverID', $emp_id)
                                    ->where('NotificationFlg', '0');
                            })
                            ->select('myg_08_trip_claim.*', 'users.emp_id', 'users.emp_name') 
                            ->orderBy('created_at', 'DESC')
                            ->limit(50)
                            ->get(); // Fetch the data
                if ($unreadTripData->count() < 50) {
                    $remaining = 50 - $unreadTripData->count(); 
                    $readTripData = DB::table('myg_08_trip_claim')
                    ->join('users', 'myg_08_trip_claim.user_id', '=', 'users.id') 
                    ->where(function ($query) use ($id) {
                        $query->where('user_id', $id) 
                        ->where('NotificationFlg','3');
                    })
                    ->orWhere(function ($query) use ($id) {
                        $query->where('user_id', $id) 
                        ->where('NotificationFlg','5');
                    })
                    ->orWhere(function ($query) use ($id) {
                        $query->where('user_id', $id) 
                        ->where('NotificationFlg','7');
                    })
                    
                    ->orWhere(function ($query) use ($emp_id) {
                        $query->where('ApproverID', $emp_id)
                            ->where('NotificationFlg', '1');
                    })
                    ->orWhere(function ($query) use ($emp_id) {
                        $query->where('SpecialApproverID', $emp_id)
                            ->where('NotificationFlg', '1');
                    })
                    ->orWhere(function ($query) use ($emp_id) {
                        $query->where('CMDApproverID', $emp_id)
                            ->where('NotificationFlg', '1');
                    })

                    ->select('myg_08_trip_claim.*', 'users.emp_id', 'users.emp_name')
                    ->limit($remaining)
                    ->orderBy('created_at', 'DESC')
                    ->get();
                    $tripdata = $unreadTripData->merge($readTripData);
                } else {
                    $tripdata = $unreadTripData;
                }           
                    // Map the results to create a notifications array
                    $notifications = $tripdata->map(function ($item) use ($emp_id){
                    $message =$viewtype=$status ='';
                    $tmg_id='TMG' . substr($item->TripClaimID, 8);
                    
                    switch ($item->NotificationFlg) {
                        case '0':
                            $message = "You have a new claim request from [orange]".$item->emp_id."[/orange]/[orange]".$item->emp_name."[/orange] recieved for approval.";
                            $status="unread";
                            if($item->SpecialApproverID==$emp_id){
                                $viewtype="SpecialApprover_View";  
                            }else{
                                $viewtype="Approver_View";  
                            }                        
                            break;
                        case '1':
                            $message = "You have a new claim request from [orange]".$item->emp_id."[/orange]/[orange]".$item->emp_name."[/orange] recieved for approval.";
                            $status="read";
                            if($item->SpecialApproverID==$emp_id){
                                $viewtype="SpecialApprover_View";  
                            }else{
                                $viewtype="Approver_View";  
                            }                                  
                            break;
                        case '2':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been [green]approved[/green] by reporting person.";
                            $status="unread";
                            $viewtype="User_View";
                            break;
                        case '3':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been [green]approved[/green] by reporting person.";
                            $status="read";
                            $viewtype="User_View";
                            break;
                        case '4':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been [red]rejected[/red] by reporting person. Click for more details.";
                            $status="unread";
                            $viewtype="User_View";
                            break;
                        case '5':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been [red]rejected[/red] by reporting person. Click for more details.";
                            $status="read";
                            $viewtype="User_View";
                            break;
                        case '6':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been is paid and completed.";
                            $status="unread";
                            $viewtype="User_View";
                            break;
                        case '7':
                            $message = "Your claim request [orange]".$tmg_id."[/orange] has been is paid and completed.";
                            $status="read";
                            $viewtype="User_View";
                            break;
                        case '8':
                            $message = "You have a resubmitted claim request from [orange]".$item->emp_id."[/orange]/[orange]".$item->emp_name."[/orange] recieved for approval.";
                            $status="read";
                            if($item->SpecialApproverID==$emp_id){
                                $viewtype="SpecialApprover_View";  
                            }else{
                                $viewtype="Approver_View";  
                            }                                  
                            break;
                    }
                    $approvalDate = Carbon::parse($item->ApprovalDate);
                    $now = Carbon::now();
                    // Determine the appropriate time format
                    if ($approvalDate->isToday()) {
                        if ($approvalDate->diffInHours($now) < 1) {
                            $time = $approvalDate->format('h:i A'); 
                        } else {
                            $time = 'Today';
                        }
                    } elseif ($approvalDate->isYesterday()) {
                        $time = 'Yesterday';
                    } else {
                        $time = $approvalDate->format('d-m-Y'); // e.g., "15-08-2024"
                    }
                    return [
                        'trip_claim_id' => $item->TripClaimID,
                        'message' => $message,
                        'status' => $status,
                        'view_type' => $viewtype,
                        'time' => $time,
                    ];
                });
                
                $message="Result fetched successfully!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$notifications,'success' => 'success'], $this->successStatus);

            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

public function notificationCount()
    {
        try
        {
            if(auth()->user())
            {
                $user = DB::table('users')->where('id', auth()->user()->id)->first();
                $id = $user->id;
                $emp_id = $user->emp_id;
    
                // Count the notifications based on the conditions
                $notificationCount = DB::table('myg_08_trip_claim')
                ->join('users', 'myg_08_trip_claim.user_id', '=', 'users.id') 
                ->where(function ($query) use ($id) {
                    $query->where('user_id', $id) 
                    ->where('NotificationFlg','2');
                })
                ->orwhere(function ($query) use ($id) {
                    $query->where('user_id', $id) 
                    ->where('NotificationFlg','4');
                })
                ->orwhere(function ($query) use ($id) {
                    $query->where('user_id', $id) 
                    ->where('NotificationFlg','6');
                })
                ->orWhere(function ($query) use ($emp_id) {
                    $query->where('ApproverID', $emp_id)
                        ->where('NotificationFlg', '0');
                })
                ->orWhere(function ($query) use ($emp_id) {
                    $query->where('ApproverID', $emp_id)
                        ->where('NotificationFlg', '8');
                })
                ->orWhere(function ($query) use ($emp_id) {
                    $query->where('SpecialApproverID', $emp_id)
                        ->where('NotificationFlg', '0');
                })
                ->orWhere(function ($query) use ($emp_id) {
                    $query->where('CMDApproverID', $emp_id)
                        ->where('NotificationFlg', '0');
                })
                ->count(); // Get the count of notifications
                    
                $message = "Notification count fetched successfully!";
                return response()->json([
                    'message' => $message,
                    'statusCode' => $this->successStatus,
                    'data' => $notificationCount,
                    'success' => 'success'
                ], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }
    public function notificationCount1()
    {
        try
        {
            if(auth()->user())
            {
                $user = DB::table('users')->where('id', auth()->user()->id)->first();
                $id = $user->id;
                $emp_id = $user->emp_id;
    
                // Count the notifications based on the conditions
                $notificationCount = DB::table('myg_08_trip_claim')
                    ->where(function ($query) use ($id) {
                        $query->where('NotificationFlg', '!=', '0')
                              ->where('user_id', $id)
                              ->where('NotificationFlg', '!=', '1');
                    })
                    ->orWhere(function ($query) use ($emp_id) {
                        $query->where('ApproverID', $emp_id)
                              ->where('NotificationFlg', '0')
                              ->where('NotificationFlg', '!=', '1');
                    })
                    ->count(); // Get the count of notifications
                    
                $message = "Notification count fetched successfully!";
                return response()->json([
                    'message' => $message,
                    'statusCode' => $this->successStatus,
                    'data' => $notificationCount,
                    'success' => 'success'
                ], $this->successStatus);
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

   
    public function viewClaim(Request $request){
        $validator = Validator::make($request->all(), [
            "trip_claim_id" => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        try
        {
            if (auth()->user()) {
                $reportingPersonEmpID = auth()->user()->emp_id;

                // Update the NotificationFlag for the specified TripClaimID
                // Tripclaim::where('TripClaimID', $request->trip_claim_id)->update([
                //     'NotificationFlg' => "1"
                // ]); 

                // Fetch the trip claim data
                $tripdata = Tripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('TripClaimID', $request->trip_claim_id)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->first(); // Use first() instead of get() for a single result

                if ($tripdata) {
                    $currentFlg=$tripdata->NotificationFlg;
                    if($currentFlg==0){
                        $newflg='1';
                    }else if($currentFlg==2){
                        $newflg='3';
                    }else if($currentFlg==4){
                        $newflg='5';
                    }else if($currentFlg==6){
                        $newflg='7';
                    }else if($currentFlg==8){
                        $newflg='1';
                    }else{
                        $newflg=$currentFlg;
                    }
                    Tripclaim::where('TripClaimID', $request->trip_claim_id)->update([
                        'NotificationFlg' => $newflg
                    ]); 

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
                    // if ($tripStatus === 'Pending' ) {
                    if ($tripStatus === 'Pending' || $tripStatus === 'Approved' ) {
                        $maxRejectionCount = $rejectionCounts->max();
                        if ($maxRejectionCount == 1) {
                            $tripHistoryStatus = 'ReSubmited';
                        } elseif ($maxRejectionCount == 0) {
                            // $tripHistoryStatus = 'Pending';
                            $tripHistoryStatus = $tripdata->Status;
                        } elseif ($maxRejectionCount == 2) {
                            $tripHistoryStatus = 'Rejected';
                        }
                    }

                    // Format the approved and rejected dates
                    $tripApprovedDate = $tripdata->tripclaimdetails->max('approved_date');
                    $tripRejectedDate = $tripdata->tripclaimdetails->max('rejected_date');
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;
                    
                    // Prepare the result
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
                        "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
                            "branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
                            "branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
                        ] : null,
                        "user_details" => $tripdata->tripuserdetails->first() ? [
                            "id" => $tripdata->tripuserdetails->first()->id,
                            "emp_id" => $tripdata->tripuserdetails->first()->emp_id,
                            "emp_name" => $tripdata->tripuserdetails->first()->emp_name,
                            "emp_branch" => $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_branch),
                            "emp_baselocation"=> $this->getbranchNameByID($tripdata->tripuserdetails->first()->emp_baselocation)
                        ] : null,
                        "tmg_id" => 'TMG' . substr($tripdata->TripClaimID, 8),
                        'date' => $tripdata->created_at->format('d/m/Y'),
                        // 'trip_status' => $tripStatus,
                        'trip_status' => $tripdata->Status,
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
                        })->map(function ($groupedDetails, $categoryID) use ($reportingPersonEmpID) {
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
                                "subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID),
                                "no_of_days" => 15,
                                "class_flg" => $policy->GradeClass === 'class' ? true : false,
                                "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $reportingPersonEmpID) {
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
                                       //   "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)
                                      "send_approver_flag" => auth()->user()->emp_id !== 'MYGE-1' && $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)

                                    ];
                                }),
                            ];
                        })->values()
                    ];

                    $message = "Result fetched successfully!";
                    return response()->json([
                        'message' => $message,
                        'statusCode' => $this->successStatus,
                        'data' => $result,
                        'success' => 'success'
                    ], $this->successStatus);
                } else {
                    return response()->json([
                        'message' => 'Trip claim not found',
                        'statusCode' => 404,
                        'data' => [],
                        'success' => 'error'
                    ], 404);
                }
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function viewClaimSpecialApprover(Request $request){
        $validator = Validator::make($request->all(), [
            "trip_claim_id" => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        try
        {
            if (auth()->user()) {
                $reportingPersonEmpID = auth()->user()->emp_id;

                $tripdata = Tripclaim::with([
                    'tripclaimdetails' => function($query) use ($reportingPersonEmpID) {
                        $query->where('ApproverID', $reportingPersonEmpID);
                    },
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('TripClaimID', $request->trip_claim_id)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->orderBy('created_at', 'DESC')
                ->first(); // Use first() instead of get() for a single result

                if ($tripdata) {
                    $currentFlg=$tripdata->NotificationFlg;
                    if($currentFlg==0){
                        $newflg='1';
                    }else if($currentFlg==2){
                        $newflg='3';
                    }else if($currentFlg==4){
                        $newflg='5';
                    }else if($currentFlg==6){
                        $newflg='7';
                    }else{
                        $newflg=$currentFlg;
                    }
                    Tripclaim::where('TripClaimID', $request->trip_claim_id)->update([
                        'NotificationFlg' => $newflg
                    ]); 

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
                        if ($tripdata->Status === 'Paid'){
                            $tripStatus = 'Paid';
                        } elseif ($tripdata->Status === 'Pending'){
                            $tripStatus = 'Pending';
                        }
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
                    
                    // Prepare the result
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
                        "visit_branch_detail" => $tripdata->visitbranchdetails->first() ? [
                            "branch_id" => $tripdata->visitbranchdetails->first()->BranchID,
                            "branch_name" => $tripdata->visitbranchdetails->first()->BranchName,
                        ] : null,
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
                        })->map(function ($groupedDetails, $categoryID) use ($reportingPersonEmpID) {
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
                                "subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID),
                                "no_of_days" => 15,
                                "class_flg" => $policy->GradeClass === 'class' ? true : false,
                                "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $reportingPersonEmpID) {
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
                                        "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)
                                    ];
                                }),
                            ];
                        })->values()
                    ];

                    $message = "Result fetched successfully!";
                    return response()->json([
                        'message' => $message,
                        'statusCode' => $this->successStatus,
                        'data' => $result,
                        'success' => 'success'
                    ], $this->successStatus);
                } else {
                    return response()->json([
                        'message' => 'Trip claim not found',
                        'statusCode' => 404,
                        'data' => [],
                        'success' => 'error'
                    ], 404);
                }
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function getsubcategoryDetails($categoryID){
        $user = DB::table('users')->where('id', auth()->user()->id)->first();
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
    
    public function advanceRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'triptype_id' => 'required|integer',
            'trip_purpose' => 'required|string',
            'branch_id' => 'required|integer',
            'remarks' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        try
        {
            if (auth()->user()) {

                $advanceList = AdvanceList::create([
                    'id' => $this->generateId(),
                    'user_id' => Auth::id(), // Use authenticated user's ID
                    'Amount' => $request->amount,
                    'RequestDate' => now(), // Set current date and time
                    'TripTypeID' => $request->triptype_id,
                    'TripPurpose' => $request->trip_purpose,
                    'BranchID' => $request->branch_id,
                    'Remarks' => $request->remarks,
                    'Status' => 'Pending',
                    'ApproverID' => null, // Set ApproverID to null
                ]);

                $message = "Advance submitted successfully.";
                    return response()->json([
                        'message' => $message,
                        'statusCode' => $this->successStatus,
                        'data' => [],
                        'success' => 'success'
                    ], $this->successStatus);
                
            }
        }
        catch (\Exception $e) 
        {
            return response()->json([
                'success'    => 'error',
                'statusCode' => 500,
                'data'       => [],
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function advanceList()
    {
        try
        {
            $userId = Auth::id();
            $advances = AdvanceList::with('triptypedetails','visitbranchdetails')->where('user_id', $userId)->get();

            $formattedAdvances = $advances->map(function($advance) {
                return [
                    'id' => $advance->id,
                    // 'user_id' => $advance->user_id,
                    'amount' => $advance->Amount,
                    'request_date' => $advance->RequestDate,
                    'triptype_details' =>  [ 
                        "triptype_id"=>$advance->triptypedetails->TripTypeID,
                        "triptype_name"=>$advance->triptypedetails->TripTypeName,
                    ],
                    'trip_purpose' => $advance->TripPurpose,
                    'branch_details' =>  [ 
                        "branch_id"=>$advance->visitbranchdetails->BranchID,
                        "branch_name"=>$advance->visitbranchdetails->BranchName,
                    ],
                    'remarks' => $advance->Remarks,
                    'status' => $advance->Status,
                    // 'approver_id' => $advance->approver_id,
                ];
            });

            return response()->json([
                'success' => true,
                'statusCode' => $this->successStatus,
                'data' => $formattedAdvances,
                'message' => 'Advance list retrieved successfully.',
            ], $this->successStatus);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }     
        try {
            if (auth()->user()) {
                $user = auth()->user();
                $user->fcm_token = $request->fcm_token;
                $user->save();
                return response()->json(['success' => true]);
            }
        }
        catch (\Exception $e)
        {
            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage(),
            ]);
        }
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
    }

    
}