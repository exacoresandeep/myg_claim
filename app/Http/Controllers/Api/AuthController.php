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
use App\Models\Triptype;
use App\Models\Attendance;
use App\Models\AdvanceList;
use App\Models\Personsdetails;
use App\Models\ClaimManagement;
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
use Illuminate\Support\Facades\Log;
use App\Models\DraftPersonsdetails;
use App\Models\DraftTripclaim;
use App\Models\DraftTripclaimdetails;
use App\Models\DraftClaimManagement;
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
           // '*.remarks' => 'string'
        ]);
        //Log::info('Employee Details Attandance:', $request);
        if ($validator->fails()) {
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }

        foreach ($request->all() as $data) {
		Attendance::create([
			'id' => $this->generateId(),
        'date' => isset($data['date']) ? Carbon::createFromFormat('d-m-Y', $data['date'])->format('Y-m-d') : null,
        'emp_code' => $data['emp_code'] ?? null,
        'punch_in' => isset($data['punch_in']) && $data['punch_in'] !== ''
            ? Carbon::createFromFormat('h:i A', trim(str_replace(' ', '', $data['punch_in'])))->format('H:i:s')
            : null,
        'location_in' => $data['location_in'] ?? null,
        'punch_out' => isset($data['punch_out']) && $data['punch_out'] !== ''
            ? Carbon::createFromFormat('h:i A', trim(str_replace(' ', '', $data['punch_out'])))->format('H:i:s')
            : null,
        'location_out' => $data['location_out'] ?? null,
        'duration' => isset($data['duration']) && $data['duration'] !== ''
            ? Carbon::createFromFormat('H:i', trim(str_replace(' ', '', $data['duration'])))->format('H:i:s')
            : null,
        'remarks' => $data['remarks'] ?? null,
               // 'id' => $this->generateId(),
               // 'date' => Carbon::createFromFormat('d-m-Y', $attendanceData['date'])->format('Y-m-d'),
               // 'emp_code' => $attendanceData['emp_code'],
               // 'punch_in' => Carbon::createFromFormat('h:i A', $attendanceData['punch_in'])->format('H:i:s'),
               // 'location_in' => $attendanceData['location_in'],
               // 'punch_out' => Carbon::createFromFormat('h:i A', $attendanceData['punch_out'])->format('H:i:s'),
               // 'location_out' => $attendanceData['location_out'],
               // 'duration' => Carbon::createFromFormat('H:i', $attendanceData['duration'])->format('H:i:s'),
               // 'remarks' =>  $attendanceData['remarks']
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

     public function userUpdate_old(Request $request){
        $data = $request->json()->all();
        $empDetailsList = $data['lst_emp'];

        $results = []; // To store the success or failure messages

        foreach ($empDetailsList as $empDetails) {
            $emp_code = $empDetails['Emp_code'];
            $new_emp_code=$empDetails['New_Emp_code'] ?? null;
            $emp_name = $empDetails['Emp_name'];
            $emp_department = $empDetails['Department'];
            $emp_branch = $this->getbranchCodeid($empDetails['Branch']);
            $emp_baselocation = $this->getbranchCodeid($empDetails['Base_location']);
            $emp_designation = $empDetails['Designation'];
            $emp_grade = $this->getgrade($empDetails['Grade']);
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
                        // 'emp_baselocation' => $emp_baselocation,
                        'emp_designation' => $emp_designation,
                        'emp_grade' => $emp_grade,
                        // 'reporting_person' => $reporting_person,
                        // 'reporting_person_empid' => $reporting_person_empid,
                        'Status' => "$login_status",
                    ]);
                    if($new_emp_code!=null){
                $emp_code=$user->emp_id;
                $user->update([
                        'emp_id' => $new_emp_code]);

                ClaimManagement::where('ApproverID',$emp_code)
                                ->update(['ApproverID'=>$new_emp_code]);
                Tripclaimdetails::where('ApproverID',$emp_code)
                                ->update(['ApproverID'=>$new_emp_code]);
            }
                    if($empDetails['password']!=""){
                $user->update([
                    'password'  => $empDetails['password']
                ]);
                    }
                    if($user->hrms_baselocation_flag!="1"){
                        $user->update([
                                'emp_baselocation' => $emp_baselocation]);
                    }
                   if($empDetails['Reporting_person_code']!="" && $empDetails['Reporting_person_name']!=NULL && $user->hrms_reporting_person_flag!="1"){
                        if($user->reporting_person_empid!=$empDetails['Reporting_person_code']){
                            ClaimManagement::where('Status','Pending')
                                                    ->where('user_id',$user->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                            Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                                    ->where('user_id',$user->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                        }
                        $user->update([
                            'reporting_person' => $reporting_person,
                            'reporting_person_empid' => $reporting_person_empid,
                         ]);
                    }
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'success'];
                } catch (\Exception $e) {
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => $e->getMessage()];
                }
            } else {
                $parts = explode('-', $emp_code);
                $number = $parts[1];
                $changeUser =  DB::table('users')
                ->where(DB::raw("SUBSTRING_INDEX(emp_id, '-', -1)"), '=', $number)
                ->first();
                $changeUser = User::whereRaw("SUBSTRING_INDEX(emp_id, '-', -1) = ?", [$number])->first();

                if(!empty($changeUser)){
                    $changeUser->update([
                        'emp_name' => $emp_name,
                        'emp_id' => $emp_code,
                        'email' => $email,
                        'emp_phonenumber' => $emp_phonenumber,
                        'emp_department' => $emp_department,
                        'emp_branch' => $emp_branch,
                        'emp_designation' => $emp_designation,
                        'emp_grade' => $emp_grade,
                        'Status' => "$login_status",
                    ]);
                    if($changeUser->hrms_baselocation_flag!="1"){
                        $changeUser->update([
                                'emp_baselocation' => $emp_baselocation]);
                    }
                    if($empDetails['Reporting_person_code']!="" && $empDetails['Reporting_person_name']!=NULL && $changeUser->hrms_reporting_person_flag!="1"){

                        if($changeUser->reporting_person_empid!=$empDetails['Reporting_person_code']){
                            ClaimManagement::where('Status','Pending')
                                                    ->where('user_id',$changeUser->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                            Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                                    ->where('user_id',$changeUser->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                        }
                        $changeUser->update([
                            'reporting_person' => $reporting_person,
                            'reporting_person_empid' => $reporting_person_empid,
                        ]);
                    }
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'success'];
                }else{

                    $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => 'User not found'];
                }
            }
        }

        return response()->json([
            'message' => 'Update operation completed',
            'results' => $results
        ], 200);
    }


    public function userUpdate(Request $request){
        $data = $request->json()->all();
        $empDetailsList = $data['lst_emp'];
    
        $results = []; // To store the success or failure messages
    
        foreach ($empDetailsList as $empDetails) {
            $emp_code = $empDetails['Emp_code'];
            $new_emp_code=$empDetails['New_Emp_code'] ?? null;
            $emp_name = $empDetails['Emp_name'];
            $emp_department = $empDetails['Department'];
            $emp_branch = $this->getbranchCodeid($empDetails['Branch']);
            $emp_baselocation = $this->getbranchCodeid($empDetails['Base_location']);
            $emp_designation = $empDetails['Designation'];
            $emp_grade = $this->getgrade($empDetails['Grade']);
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
                        'emp_designation' => $emp_designation,
                        'emp_grade' => $emp_grade,
                        'Status' => "$login_status",
                    ]);
                    if($user->hrms_baselocation_flag!="1"){
                        $user->update([
                                'emp_baselocation' => $emp_baselocation]);
                    }
                    if($new_emp_code!=null){
                        $emp_code=$user->emp_id;
                        $user->update([
                                'emp_id' => $new_emp_code]);

                        ClaimManagement::where('ApproverID',$emp_code)
                                        ->update(['ApproverID'=>$new_emp_code]);
                        Tripclaimdetails::where('ApproverID',$emp_code)
                                        ->update(['ApproverID'=>$new_emp_code]);
                    }
                    if($empDetails['password']!=""){
                        $user->update([
                            'password'  => $empDetails['password']
                        ]);
                    }
		            if($empDetails['Reporting_person_code']!="" && $empDetails['Reporting_person_name']!=NULL && $user->hrms_reporting_person_flag!="1"){

                    
                        if($user->reporting_person_empid!=$empDetails['Reporting_person_code']){
                            ClaimManagement::where('Status','Pending')
                                                    ->where('user_id',$user->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                            Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                                    ->where('user_id',$user->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                        }
                        $user->update([
                            'reporting_person' => $reporting_person,
                            'reporting_person_empid' => $reporting_person_empid,
                        ]);
                    }
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'success'];
                } catch (\Exception $e) {
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => $e->getMessage()];
                }
            } else {
                $parts = explode('-', $emp_code);
                $number = $parts[1];
                $changeUser =  DB::table('users')
                ->where(DB::raw("SUBSTRING_INDEX(emp_id, '-', -1)"), '=', $number)
                ->first();
                if($changeUser){
                    $changeUser->update([
                        'emp_name' => $emp_name,
                        'emp_id' => $emp_code,
                        'email' => $email,
                        'emp_phonenumber' => $emp_phonenumber,
                        'emp_department' => $emp_department,
                        'emp_branch' => $emp_branch,
                        'emp_designation' => $emp_designation,
                        'emp_grade' => $emp_grade,
                        'Status' => "$login_status",
                    ]);
                    if($changeUser->hrms_baselocation_flag!="1"){
                        $changeUser->update([
                                'emp_baselocation' => $emp_baselocation]);
                    }
                    if($empDetails['Reporting_person_code']!="" && $empDetails['Reporting_person_name']!=NULL && $user->hrms_reporting_person_flag!="1"){
                  
                        if($changeUser->reporting_person_empid!=$empDetails['Reporting_person_code']){
                            ClaimManagement::where('Status','Pending')
                                                    ->where('user_id',$changeUser->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                            Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                                    ->where('user_id',$changeUser->id)
                                                    ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                        }
                        $changeUser->update([
                            'reporting_person' => $reporting_person,
                            'reporting_person_empid' => $reporting_person_empid,
                        ]);
                    }
                    $results[] = ['Emp_code' => $emp_code, 'status' => 'success'];
                }else{

                    $results[] = ['Emp_code' => $emp_code, 'status' => 'fail', 'error' => 'User not found'];
                }

            }
        }
    
        return response()->json([
            'message' => 'Update operation completed',
            'results' => $results
        ], 200);
    }

    
    public function hrms_login_token(){
        $ch = curl_init();
        //      curl_setopt($ch, CURLOPT_URL, "http://103.119.254.250:6062/integration/exacore_login_api/");
        curl_setopt($ch, CURLOPT_URL, "https://mygian.mygoal.biz:6062/integration/exacore_login_api/");
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
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required',
            'password' => 'required',

        ]);
        if ($validator->fails())
        {
            $errors  = json_decode($validator->errors());
            $emp_id=isset($errors->emp_id[0])? $errors->emp_id[0] : '';
            $password=isset($errors->password[0])? $errors->password[0] : '';
            if($emp_id){
                $msg = $emp_id;
            }
            else if($password){
                $msg = $password;
            }
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }
        $user=User::where('emp_id',$request->emp_id)->first();
        
        $inactiveUserExist   = DB::table('users')->where('emp_id',$request->emp_id)->where('status','0')->exists();
        if($inactiveUserExist==true){
            return response()->json([
                'message' => "Can't login. Please contact admin.",
                'statusCode' => 400,
                'data' => [],
                'success' => 'error'
            ], 400);
        }
        $checkexist   = DB::table('users')->where('emp_id',$request->emp_id)->exists();
       
        if($checkexist==true){
            if (!Hash::check($request->password, $user->password)){
                //...........code for live
                $exacoreToken =$this->hrms_login_token();
                $responseArray = $exacoreToken->getData(true);
                $token = $responseArray['token'];
                $secondResponse = Http::withHeaders([
                    'Content-type' => 'application/json',
                    'Authorization' => 'JWT ' . $token,
                ])->post('https://mygian.mygoal.biz:6062/integration/login_api/', [
                    'Username' => $request->emp_id,
                    'Password' => $request->password,
                    'Key' => 'dv)B45k+Q34fnOZEqf',
                ]);
                $data=[];
                if ($secondResponse->failed()) {
                    return response()->json(['message' => 'Please check the password','statusCode'=>422,'data'=>[],'success'=>'error'],200);
                }else{
                    $user = User::where('emp_id', $request->emp_id)->first();

                    if ($user) {
                        // Update the user details
                        try {
                            $user->update([
                                'password'=>Hash::make($request->password)
                            ]);
                            $sequirity_id=Sequirityvlunerability::create([
                                'user_id'=>$user->id,
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
                        }catch (\Exception $e){
                            return response()->json([
                                'success'    => 'error',
                                'statusCode' => 500,
                                'data'       => [],
                                'message'    => $e->getMessage(),
                            ]);
                        }
                    }
                }
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
            ])->post('https://mygian.mygoal.biz:6062/integration/login_api/', [
                'Username' => $request->emp_id,
                'Password' => $request->password,
                'Key' => 'dv)B45k+Q34fnOZEqf',
            ]);
            $data=[];
            if ($secondResponse->failed()) {
                $errorDetails = json_decode($secondResponse->body(), true); // Decode the response body
                $errorMessage = $errorDetails['message'] ?? 'Failed to authenticate with login_api'; // Extract the message or use a fallback

                return response()->json([
                'message' => $errorMessage,
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

            if($empDetails['Reporting_person_code']=="" || $empDetails['Reporting_person_name']==NULL){
                return response()->json([
                    'message' => 'Reporting officer is not found. Please contact admin',
                    'statusCode' => 400,
                    'data' => [],
                    'success' => 'error'
                ], 400);
            }
            //------user id exchange--------
            $parts = explode('-', $emp_id);
            $number = $parts[1];
            $changeUser =  DB::table('users')
            ->where(DB::raw("SUBSTRING_INDEX(emp_id, '-', -1)"), '=', $number)
            ->first();
            if($changeUser){
                $changeUser->update([
                    'emp_name' => $emp_name,
                    'emp_id' => $emp_id,
                    'email' => $email,
                    'emp_phonenumber' => $emp_phonenumber,
                    'emp_department' => $emp_department,
                    'emp_branch' => $emp_branch,
                    'emp_designation' => $emp_designation,
                    'emp_grade' => $emp_grade,
                    'Status' => "$login_status",
                ]);
                if($changeUser->hrms_baselocation_flag!="1"){
                    $changeUser->update([
                            'emp_baselocation' => $emp_baselocation]);
                }

                ClaimManagement::where('ApproverID',$changeUser->emp_id)
                                            ->update(['ApproverID'=>$emp_id]);
                ClaimManagement::where('FinanceApproverID',$changeUser->emp_id)
                                    ->update(['FinanceApproverID'=>$emp_id]);
                ClaimManagement::where('SpecialApproverID',$changeUser->emp_id)
                                    ->update(['SpecialApproverID'=>$emp_id]);
                Tripclaimdetails::where('ApproverID',$changeUser->emp_id)
                                            ->update(['ApproverID'=>$emp_id]);


                if($empDetails['Reporting_person_code']!="" && $empDetails['Reporting_person_name']!=NULL && $user->hrms_reporting_person_flag!="1"){

                    if($changeUser->reporting_person_empid!=$empDetails['Reporting_person_code']){
                        ClaimManagement::where('Status','Pending')
                                                ->where('user_id',$changeUser->id)
                                                ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                        Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                                ->where('user_id',$changeUser->id)
                                                ->update(['ApproverID'=>$empDetails['Reporting_person_code']]);
                    }
                    $changeUser->update([
                        'reporting_person' => $reporting_person,
                        'reporting_person_empid' => $reporting_person_empid,
                    ]);
                }
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

    public function loginold(Request $request)
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
        $inactiveUserExist   = DB::table('users')->where('emp_id',$request->emp_id)->where('status','0')->exists();
        if($inactiveUserExist==true)
        {
            return response()->json([
                'message' => "Can't login. Please contact admin.",
                'statusCode' => 400,
                'data' => [],
                'success' => 'error'
            ], 400);
        }
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
            ])->post('https://mygian.mygoal.biz:6062/integration/login_api/', [
                'Username' => $request->emp_id,
                'Password' => $request->password,
                'Key' => 'dv)B45k+Q34fnOZEqf',
            ]);
            
            
            $data=[];
            if ($secondResponse->failed()) {
                $errorDetails = json_decode($secondResponse->body(), true); // Decode the response body
                $errorMessage = $errorDetails['message'] ?? 'Failed to authenticate with login_api'; // Extract the message or use a fallback

                return response()->json([
                    'message' => $errorMessage,
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

	        if($empDetails['Reporting_person_code']=="" || $empDetails['Reporting_person_name']==NULL){
                return response()->json([
                    'message' => 'Reporting officer is not found. Please contact admin',
                    'statusCode' => 400,
                    'data' => [],
                    'success' => 'error'
                ], 400);
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
                
                $user = User::find($vulnerability->user_id);

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
                        "no_of_days" => 31,
                        "class_flg" => ($policy?->GradeClass ?? '') === 'class' ? true : false,
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
		 ->where('emp_id', 'like', '%-%')
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
//		    ->where('emp_id', 'MYGC-5346')
	    ->whereIn('emp_id', ['MYGC-5346', 'MYGE-145'])

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
           'file' => 'required|file|mimes:jpg,jpeg,png,pdf,JPEG|max:15360', // Example validation rules
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Invalid file format or large file size",
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
	$file ="";
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
	    "format"=>$file,
            'success' => 'error',
        ], 400);
    }
    private function extractDates($claimDetails)
    {
        $dates = [];

            foreach ($claimDetails as $claimDetail) {
            if($claimDetail['from_date']!=null && $claimDetail['to_date']!=null)
            {$this->addDatesBetween($claimDetail['from_date'], $claimDetail['to_date'], $dates);}
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
//$dates = array_map(function ($date) {
  //      return ["Date" => $date];
    //}, $dates);
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
            "claim_details" => 'required|array'
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
    
                foreach ($data["claim_details"] as $index => $details) {
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

    public function tripClaim(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'triptype_id' => 'required',
            'trip_purpose' => 'required',
            "claim_details" => 'required|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        //.....................................
        $user = User::where('id', auth()->id())->first();

        if($user){
            return response()->json([
                'message' => "Server is under maintanence. Please try again after 4 PM.",
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        $data = $request->all();
        $empDates = [];

        foreach ($data["claim_details"] as &$details) { // Reference to modify $data directly
            // Add the static person (authenticated user) to the person_details array
            $staticPerson = [
                'id' => auth()->id(),
                'grade' => $user->emp_grade
            ];
            $details['person_details'][] = $staticPerson;

            // Process each person in person_details for the current claim detail
            foreach ($details['person_details'] as $perdet) {
                $userDetail = User::find($perdet['id']);
                
                if ($userDetail) {
                    $empId = $userDetail->emp_id;

                    // Extract dates from the current claim detail
                    $dates = $this->extractDates([$details]); // Assuming extractDates returns an array of dates

                    // Initialize emp_id in empDates if not already set
                    if (!isset($empDates[$empId])) {
                        $empDates[$empId] = [];
                    }

                    // Merge the extracted dates into the emp_id's array, ensuring no duplicates
                    $empDates[$empId] = array_unique(array_merge($empDates[$empId], $dates));
                }
            }
        }


        // Prepare the final $data format
        $resultEmpDates = [];
        foreach ($empDates as $empCode => $dates) {
	$dates = array_values($dates);
		$resultEmpDates[] = [
                'Emp_code' => $empCode,
                'Dates' => $dates
            ];
        }

        $exacoreToken =$this->hrms_login_token();  
        $responseArray = $exacoreToken->getData(true);
        $token = $responseArray['token'];
	
	$send_data = [
		"EmployeeDetails"=>$resultEmpDates
	];
	$secondResponse = Http::withHeaders([
            'Content-type' => 'application/json',
	    'Authorization' => 'JWT ' . $token,
      ])->post('https://mygian.mygoal.biz:6062/integration/status_api/', $send_data);
	$data=[];

	//return $secondResponse;
	if($user->emp_id!="CBDO"){
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

	    $absentMessages = [];

		foreach ($resdata['data'] as $empId => $dates) {
    			foreach ($dates as $arrdate => $arrvalue) {
        			if ($arrvalue == 0) {
            				$absentMessages[] = "$empId is absent on $arrdate";
        			}
    			}
		}
		$falseDatesMessage = implode(', ', $absentMessages);
		$fasleDates=$absentMessages;
		if (!empty($absentMessages)) {
    			return response()->json([
        			'message' =>$falseDatesMessage,

        			'statusCode' => 422,
        			'data' => [
            				'AbsentMessages' => $absentMessages
        			],
        			'success' => 'error',
    			], 200);
		}
            	if (!empty($falseDates)) {
                	return response()->json([
                    		'message' => 'Some dates have a status of false.',
                    		'statusCode' => 422,
                    		'data' => [
                        		'FalseDates' => $falseDates
                    			],
                    		'success' => 'error',
                		], 200);
            	}	
        }
	}
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
                    // 'VisitListBranchID' => $request->visit_branch_id,
                    'VisitListBranchID' => json_encode($request->visit_branch_id),
                    'AdvanceAmount' =>null,
                    'RejectionCount' => 0,
                    'ApprovalDate' => null,
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
                        'FromDate' => ($details['from_date']==null) ? $details['document_date'] : $details['from_date'],
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
		$put_send_data = ["EmployeeDetails" => []];

foreach ($send_data["EmployeeDetails"] as $employee) {
    $emp_code = $employee["Emp_code"];
    $dates = $employee["Dates"];

    // Set Status as True for all dates
    $transformed_dates = [];
    foreach ($dates as $date) {
        $transformed_dates[] = ["Date" => $date, "Status" => true];
    }

    // Append transformed employee data to the result array
    $put_send_data["EmployeeDetails"][] = [
        "Emp_code" => $emp_code,
        "Dates" => $transformed_dates
    ];
}
	//	$url = 'http://103.119.254.250:6062/integration/status_api/';
	  	$url = 'https://mygian.mygoal.biz:6062/integration/status_api/';
               
                $headers = [
                    "Content-Type" => "application/json",
                    "Authorization" => "JWT ".$token,
                ];

                
                $response_hrms = Http::withHeaders($headers)->put($url, $put_send_data);

                if ($response_hrms->successful()) {
                    // The request was successful, handle the response here
                    $responseBody = $response_hrms->json(); // Convert the JSON response to an array

                } else {
                    // Handle the error
                    $statusCode = $response_hrms->status(); // Get the status code of the response
                    $responseBody = $response_hrms->body(); // Get the raw body of the error response
                }
                //dd($responseBody);
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
                    $VisitListBranchID=ClaimManagement::visitlistbranchdetails($trip->VisitListBranchID);
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
                        ] : $VisitListBranchID,  
                        "tmg_id"=>'TMG' . substr($trip->TripClaimID, 8),
                        'date'=> $trip->created_at->format('d/m/Y'),
                        'trip_status'=> $tripStatus,
                        'trip_history_status'=> $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
			'finance_remarks' => $trip->FinanceRemarks,
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
                                    "no_of_days" => 31,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID,$categoryID) {                                        
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
                                            "eligible_amount" => $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                                $userDetails = $person->userDetails;
                                                $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                                    return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                                });
                                                return $amounts->sum();
                                            })->sum(),
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
					    "remarks"=>$detail->Remarks,
					    "approver_id" => $detail->ApproverID,
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
                                           "send_approver_flag" => ($detail->StartMeter) ? false : ($policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID))

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

public function checkDuplicateClaims(Request $request)
{
    $validated = $request->validate([
        'user_ids' => 'required|array|min:1',
        'user_ids.*' => 'required|integer|exists:users,id',
        'from_date' => 'required|date_format:Y-m-d',
        'category_id' => 'required|integer|exists:myg_03_categories,CategoryID',
    ]);

    $userIds = $validated['user_ids'];
    $fromDate = $validated['from_date'];
    $categoryID = $validated['category_id'];

    $results = [];

    foreach ($userIds as $user_id) {
        $personDetails = Personsdetails::where('EmployeeID', $user_id)->get();
        $found = false;

        foreach ($personDetails as $personDetail) {
            $tripDetail = $personDetail->tripClaimDetail;

            if ($tripDetail && $tripDetail->policyDet) {
                $subCategory = $tripDetail->policyDet->subCategoryDetails;

                if ($subCategory && $subCategory->CategoryID == $categoryID) {
                    $tripFromDate = \Carbon\Carbon::parse($tripDetail->FromDate)->format('Y-m-d');

                    if ($tripFromDate == $fromDate) {
                        $user = $personDetail->userData;

                        $results[] = [
                            'user_id' => $user_id,
                            'emp_id' => $user->emp_id ?? null,
                            'emp_name' => $user->emp_name ?? null,
                            'category' => $subCategory->categorydata->CategoryName ?? null,
                            'category_id' => $subCategory->categorydata->CategoryID ?? null,
                            'document_date' => $tripFromDate ?? null,
                            'trip_claim_id' => $tripDetail->TripClaimID ?? null,
                            'trip_claim_detail_id' => $tripDetail->TripClaimDetailID ?? null,
                            'is_duplication' => true,
                        ];

                        $found = true;
                        //  break;
                    }
                }
            }
        }

        if (!$found) {
            $results[] = [
                'user_id' => $user_id,
                'emp_id' => null,
                'emp_name' => null,
                'trip_claim_id' => null,
                'trip_claim_detail_id' => null,
                'is_duplication' => false,
            ];
        }
    }

    return response()->json([
        'message' => 'Duplicate check completed',
        'statusCode' => 200,
        'data' => $results,
        'success' => 'success'
    ]);
}



    public function checkDuplicateClaimsForView($user_id,$FromDate,$categoryID,$TripClaimDetailID)
    {
        $data=[];
            $personDetails = Personsdetails::where('EmployeeID', $user_id)
            ->where("TripClaimDetailID","!=",$TripClaimDetailID)
                    ->whereHas('tripClaimDetail', function ($q) use ($FromDate, $categoryID) {
                        $q->whereDate('FromDate', $FromDate)
                            ->whereHas('policyDet.subCategoryDetails', function ($subQuery) use ($categoryID) {
                                $subQuery->where('CategoryID', $categoryID);
                            });
                    })
            ->with(['tripClaimDetail.policyDet.subCategoryDetails.category'])
            ->get();

        if ($personDetails->isEmpty()) {
            return $data;
        }
        // Get TripClaimID from the first matching record
        $firstPerson = $personDetails->first();
        $data['trip_claim_id'] = $firstPerson->tripClaimDetail->TripClaimID ?? null;
        $data['trip_claim_detail_id'] = $firstPerson->tripClaimDetail->TripClaimDetailID ?? null;
        return $data;      
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
                    $colorStatus = 'Pending'; // Default to Yellow

                if ($statuses->every(fn($status) => $status === 'Approved')) {
                    $colorStatus = 'Approved';
                } elseif ($statuses->contains('Rejected')) {
                    $colorStatus = 'Rejected';
		}
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
                    $VisitListBranchID=ClaimManagement::visitlistbranchdetails($trip->VisitListBranchID);
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
                        // "visit_branch_detail" => $trip->visitbranchdetails->first() ? [
                        //     "branch_id" => $trip->visitbranchdetails->first()->BranchID,
                        //     "branch_name" => $trip->visitbranchdetails->first()->BranchName,
                        // ] : $VisitListBranchID,
                        "visit_branch_detail" => $trip->visitbranchdetails && $trip->visitbranchdetails->count() > 0
                            ? $trip->visitbranchdetails->map(function ($branch) {
                                return [
                                    "branch_id" => $branch->BranchID,
                                    "branch_name" => $branch->BranchName,
                                ];
                            })->values()->all()
                            : $VisitListBranchID,
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
			'finance_remarks' => $trip->FinanceRemarks,
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
			'approver_level_status' => $colorStatus,
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
                                     "no_of_days" => 31,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID,$categoryID,$category) {                                        
                                          $eligible_amount_tot= $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                            $userDetails = $person->userDetails;
                                            $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                                return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                            });
                                            return $amounts->sum();
					  })->sum();

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
                                            "eligible_amount" =>$eligible_amount_tot,
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
					    "approver_remarks"=>$detail->approver_remarks,
					    "approver_id" => $detail->ApproverID,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
                                            "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
						                        return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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
                                                        "is_duplication" => !empty($is_duplication),
                                                        "duplication_claim_id"=> $is_duplication['trip_claim_id'] ?? null
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
                                         "send_approver_flag" => ($detail->EndMeter) ? false : (($policy->GradeAmount !== null && $detail->UnitAmount > $eligible_amount_tot && ($reportingPersonEmpID == $detail->ApproverID)) ? true : false)
					//  "send_approver_flag" => ($detail->StartMeter && $detail->EndMeter) ? false : ($policy->GradeAmount !== null && $detail->UnitAmount > $eligible_amount_tot && $reportingPersonEmpID == $detail->ApproverID) ? true :false  //"send_approver_flag" => ($detail->StartMeter) ? false : ($policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID))
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
                    $VisitListBranchID=ClaimManagement::visitlistbranchdetails($trip->VisitListBranchID);
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
                        ] : $VisitListBranchID,
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
			'finance_remarks' => $trip->FinanceRemarks,
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
                                     "no_of_days" => 31,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID,$categoryID) {                                        
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
                                            "eligible_amount" => $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                                $userDetails = $person->userDetails;
                                                $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                                    return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                                });
                                                return $amounts->sum();
                                            })->sum(),
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
					    "approver_remarks"=>$detail->approver_remarks,
					    "approver_id" => $detail->ApproverID,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
                                            "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
						                        return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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
                                                        "is_duplication" => !empty($is_duplication),
                                                        "duplication_claim_id"=> $is_duplication['trip_claim_id'] ?? null
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
                                        // "send_approver_flag" => $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)  
                                        "send_approver_flag" => ($detail->StartMeter) ? false : ($policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)  )
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
    public function claimsForSpecialApprovalnew(){
        try{
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
                //->where('created_at', '>=', Carbon::now()->subMonths(6))
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
                    $VisitListBranchID=ClaimManagement::visitlistbranchdetails($trip->VisitListBranchID);
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
                        "visit_branch_detail" => $trip->visitbranchdetails && $trip->visitbranchdetails->count() > 0
                            ? $trip->visitbranchdetails->map(function ($branch) {
                                return [
                                    "branch_id" => $branch->BranchID,
                                    "branch_name" => $branch->BranchName,
                                ];
                            })->values()->all()
                            : $VisitListBranchID,
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
                        'finance_remarks' => $trip->FinanceRemarks,
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
			'finance_remarks' => $trip->FinanceRemarks,
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
                                     "no_of_days" => 31,
                                    "class_flg" => true, 
                                    "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory,$policy,$reportingPersonEmpID,$categoryID) {                                        
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
                                            "eligible_amount" => $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                                $userDetails = $person->userDetails;
                                                $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                                    return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                                });
                                                return $amounts->sum();
                                            })->sum(),
                                            "no_of_persons"=>$detail->NoOfPersons,
                                            "file_url"=>$detail->FileUrl,
                                            "remarks"=>$detail->Remarks,
					    "approver_remarks"=>$detail->approver_remarks,
					    "approver_id" => $detail->ApproverID,
                                            "notification_flg"=>$detail->NotificationFlg,
                                            "rejection_count"=>$detail->RejectionCount,
                                           "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
						                        return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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
                                                        "is_duplication" => !empty($is_duplication),
                                                        "duplication_claim_id"=> $is_duplication['trip_claim_id'] ?? null
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
                    'ApproverRemarks' => $request->remarks,
                    'NotificationFlg' => '2',
                ]);
                $message = "Claims approved successfully!";

                $rows = DB::table('myg_09_trip_claim_details as tcd')
                    ->join('myg_06_policies as p', 'tcd.PolicyID', '=', 'p.PolicyID') 
                    ->join('myg_04_subcategories as sc', 'p.SubCategoryID', '=', 'sc.SubCategoryID') 
                    ->where('tcd.TripClaimID', $trip_claim_id)
                    //->whereNull('tcd.StartMeter') 
                    //->whereNull('tcd.EndMeter') 
                    ->where('tcd.ApproverID', auth()->user()->emp_id)
                    ->where('tcd.Status', 'Pending')
                    // ->where('p.GradeType', 'Amount')
                    ->select('tcd.*','p.GradeType','sc.CategoryID')
                    ->get();


                if($rows)
                {
                    
                    foreach($rows as $row){
                   
                        if($row->GradeType == 'Amount' && $row->StartMeter === null && $row->EndMeter === null) {
                            $unitamount=$row->UnitAmount;
                            $calc_Amount=0;
                            $persons = DB::table('myg_10_persons_details')
                                ->where('TripClaimDetailID', $row->TripClaimDetailID)
                                ->get();
                            foreach($persons as $person){
                                $policy = Policy::join('myg_04_subcategories', 'myg_06_policies.SubCategoryID', '=', 'myg_04_subcategories.SubCategoryID')
                                ->where('myg_06_policies.GradeID', $person->Grade)
                                ->where('myg_04_subcategories.CategoryID', $row->CategoryID)
                                ->orderBy('myg_06_policies.GradeAmount', 'desc') 
                                ->first();
                                if ($policy) {
                                    if($row->CategoryID == 4){
                                        $from = Carbon::parse($row->FromDate);
                                        $to = Carbon::parse($row->ToDate);
                                        $datediff = $from->diffInDays($to);
                                        if($datediff==0){
                                            $datediff =1;
                                        }
                                        $calc_Amount+=$policy->GradeAmount * $datediff;
                                    }else{
                                        $calc_Amount+=$policy->GradeAmount;
                                    }
                                } 
                            }
    
                            
                            if($calc_Amount>$row->UnitAmount){
                                DB::table('myg_09_trip_claim_details')
                                    ->where('TripClaimID', $trip_claim_id)
                                    ->where('TripClaimDetailID', $row->TripClaimDetailID)
                                    ->where('ApproverID', auth()->user()->emp_id)
                                    ->where('Status','Pending')
                                    ->update([
                                        'Status' => $status,
                                        'approved_date' => Carbon::now()->toDateString(),
                                        'rejected_date' => null,
                                        'approver_remarks' => $request->remarks,
                                    ]);
    
                            }else{
                                DB::table('myg_09_trip_claim_details')
                                    ->where('TripClaimID', $trip_claim_id)
                                    ->where('TripClaimDetailID', $row->TripClaimDetailID)
                                    ->where('ApproverID', auth()->user()->emp_id)
                                    ->where('Status','Pending')
                                    ->update([
                                        'Status' => $status,
                                        'UnitAmount' => $calc_Amount,
                                        'approved_date' => Carbon::now()->toDateString(),
                                        'rejected_date' => null,
                                        'approver_remarks' => $request->remarks,
                                    ]);
    
                            }
                        }else{
                            // dd($row);
                            DB::table('myg_09_trip_claim_details')
                                    ->where('TripClaimID', $trip_claim_id)
                                    ->where('TripClaimDetailID', $row->TripClaimDetailID)
                                    ->where('ApproverID', auth()->user()->emp_id)
                                    ->where('Status','Pending')
                                    ->update([
                                        'Status' => $status,
                                        // 'UnitAmount' => $calc_Amount,
                                        'approved_date' => Carbon::now()->toDateString(),
                                        'rejected_date' => null,
                                        'approver_remarks' => $request->remarks,
                                    ]);
                        }
                    }
                }


                
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
                    // 'Status' => $tripStatus,
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

            if ($Trip) { // Check if $Trip is not null
                $t = DB::table('myg_08_trip_claim')
                    ->where('TripClaimID', $Trip->TripClaimID)
                    ->update([
                        'NotificationFlg' => '9'
                    ]);
            }

            DB::table('myg_09_trip_claim_details')
            ->where('TripClaimDetailID', $request->trip_claim_details_id)
            ->update(['status' => 'Rejected','RejectionCount'=>$rej_count+1,'approved_date'=>null,'rejected_date' => Carbon::now()->toDateString(),'approver_remarks'=>$request->remarks]);
            
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
            $Trip = DB::table('myg_09_trip_claim_details')
                    ->where('TripClaimDetailID', $request->trip_claim_details_id)
                    ->first(); // first() returns a single result object, not a collection

            if ($Trip) { // Check if $Trip is not null
                $t = DB::table('myg_08_trip_claim')
                    ->where('TripClaimID', $Trip->TripClaimID)
                    ->update([
                        'NotificationFlg' => '9'
                    ]);
            }
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
			'DeductAmount' => $details['unit_amount'] ?? null,
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
		    $persondetails = Personsdetails::create([
                        'PersonDetailsID' => $this->generateId(),
                        'TripClaimDetailID' => $details['trip_claim_details_id'],
                        'EmployeeID' => auth()->id(),
                        'Grade' => $user->emp_grade,
                        'ClaimOwner' =>'1',
                        'user_id' => auth()->id()
                    ]);
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
                            ->orwhere(function ($query) use ($id) {
                                $query->where('user_id', $id) 
                                ->where('NotificationFlg','9');
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
                        case '9':
                            $message = "Your claim category of request [orange]".$tmg_id."[/orange] has been rejected.";
                            $status="read";
                            $viewtype="User_View";
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
                    } elseif ($statuses->contains('Rejected')) {
                        $tripStatus = 'Rejected';
                    } else {
                        $tripStatus = 'Pending';
                    }
                    $VisitListBranchID=ClaimManagement::visitlistbranchdetails($tripdata->VisitListBranchID);
                    // dd($VisitListBranchID);
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

                        "visit_branch_detail" => $tripdata->visitbranchdetails && $tripdata->visitbranchdetails->count() > 0
                        ? $tripdata->visitbranchdetails->map(function ($branch) {
                            return [
                                "branch_id" => $branch->BranchID,
                                "branch_name" => $branch->BranchName,
                            ];
                        })->values()->all()
                        : $VisitListBranchID,
                       
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
                        'finance_remarks' => $tripdata->FinanceRemarks,
                        'total_amount' => $tripAmount,
                        'trip_approved_date' => $tripApprovedDate,
                        'trip_rejected_date' => $tripRejectedDate,
                        'approver_status' => $tripStatus,
                        'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
                        'finance_status_change_date' => $tripdata->transaction_date ? \Carbon\Carbon::parse($tripdata->transaction_date)->format('d/m/Y') : null,
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
                                "no_of_days" =>31 ,
                                "class_flg" => $policy->GradeClass === 'class' ? true : false,
                                "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $reportingPersonEmpID,$categoryID,$category) {
        $eligible_amount_tot= $detail->personsDetails->map(function ($person) use ($categoryID,$category) {
                                            $userDetails = $person->userDetails;
                                            $amounts = $userDetails->map(function ($user) use ($categoryID,$category) {
                                                return $this->classCalc($user->emp_grade, $categoryID,$category->FromDate,$category->ToDate);
                                            });
                                            return $amounts->sum();
                                        })->sum();
                               
                                    $location = $detail->TripFrom ? $this->fetchLocationFromNominatim($detail->TripFrom) : null;
                                    return [
                                        "trip_claim_details_id" => $detail->TripClaimDetailID,
                                        "from_date" => $detail->FromDate,
                                        "to_date" => $detail->ToDate,
                                        "trip_from" => $detail->TripFrom,
                                        "lat" => $location['lat'] ?? null,
                                        "lon" => $location['lon'] ?? null,
                                        "trip_to" => $detail->TripTo,
                                        "document_date" => $detail->DocumentDate,
                                        "start_meter" => $detail->StartMeter,
                                        "end_meter" => $detail->EndMeter,
                                        "qty" => $detail->Qty,
                                        "status" => $detail->Status,
                                        "unit_amount" => $detail->UnitAmount,
                                        "deduct_amount" => ($detail->DeductAmount-$detail->UnitAmount),
					"eligible_amount" => $eligible_amount_tot,
					
                                        "no_of_persons" => $detail->NoOfPersons,
                                        "file_url" => $detail->FileUrl,
                                        "remarks" => $detail->Remarks,
					"approver_remarks" => $detail->approver_remarks,
					"approver_id" => $detail->ApproverID,
                                        "notification_flg" => $detail->NotificationFlg,
                                        "rejection_count" => $detail->RejectionCount,
                                        "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
						                        return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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
                                       
                                       // "send_approver_flag" => ((bool)$category->StartMeter==true) ? false : (auth()->user()->emp_id !== 'MYGE-1' && $policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID))
            "send_approver_flag" => ($detail->EndMeter!=null) ? false : (($policy->GradeAmount !== null && $detail->UnitAmount > $eligible_amount_tot && ($reportingPersonEmpID == $detail->ApproverID)) ? true : false)
                                   

                                    ];
                                }),
                            ];
                        })->values()
                    ];

                    if($reportingPersonEmpID != $tripdata->ApproverID && $currentFlg=='9')
                    Tripclaim::where('TripClaimID', $request->trip_claim_id)->update([
                        'NotificationFlg' => '5'
                    ]); 

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
			'finance_remarks' => $tripdata->FinanceRemarks,
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
                                "no_of_days" => 31,
                                "class_flg" => $policy->GradeClass === 'class' ? true : false,
                                "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $reportingPersonEmpID,$categoryID) {
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
                                        "eligible_amount" => $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                            $userDetails = $person->userDetails;
                                            $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                                return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                            });
                                            return $amounts->sum();
                                        })->sum(),
                                        "no_of_persons" => $detail->NoOfPersons,
                                        "file_url" => $detail->FileUrl,
                                        "remarks" => $detail->Remarks,
					"approver_remarks" => $detail->approver_remarks,
					"approver_id" => $detail->ApproverID,
                                        "notification_flg" => $detail->NotificationFlg,
                                        "rejection_count" => $detail->RejectionCount,
                                        "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
						                        return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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
                                        
					"send_approver_flag" => ($detail->StartMeter) ? false : ($policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID))

                                        // "send_approver_flag" =>$policy->GradeAmount !== null && $detail->UnitAmount > $policy->GradeAmount && ($reportingPersonEmpID == $detail->ApproverID)
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

    public function classCalc($GradeID, $CategoryID,$fromDate,$toDate)
	{

        
		$policy = Policy::join('myg_04_subcategories', 'myg_06_policies.SubCategoryID', '=', 'myg_04_subcategories.SubCategoryID')
			->where('myg_06_policies.GradeID', $GradeID)
			->where('myg_04_subcategories.CategoryID', $CategoryID)
			->orderBy('myg_06_policies.GradeAmount', 'desc') // Order by GradeAmount in descending order
			->first(); // Get the first record, which will have the max GradeAmount
		if ($policy) {
            
			if ($policy->GradeType == 'Class') {
				return 0;
			} else {
                if($CategoryID==4){
                    $from = Carbon::parse($fromDate);
                    $to = Carbon::parse($toDate);
                    $datediff = $from->diffInDays($to);
                    if($datediff==0){
                        $datediff =1;
                    }
                    return (int) $policy->GradeAmount * $datediff;
                }
				return (int) $policy->GradeAmount;
			}
		} else {
			return 0;
		}
	}


    public function classCalculation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'grade_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }     
        try {
            if (auth()->user()) {
                $eligible_amount = 0;
                foreach ($request->grade_ids as $gradeID) {
                    $policy =  Policy::join('myg_04_subcategories', 'myg_06_policies.SubCategoryID', '=', 'myg_04_subcategories.SubCategoryID')
                        ->where('myg_06_policies.GradeID', $gradeID)
                        ->where('myg_06_policies.Status', '1 ')
                        ->where('myg_04_subcategories.CategoryID', $request->category_id)
                        ->orderBy('myg_06_policies.GradeAmount', 'desc') 
                        ->first(); 
                    if ($policy) {
                        $eligible_amount += $policy->GradeAmount;
                    }
                }
                return response()->json(['success' => true,'eligible_amount' =>$eligible_amount]);
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
        
    }

    public function locationFrom(Request $request)
    {
        $request->validate([
            'search_key' => 'required|string|max:255',
        ]);

        $searchKey = $request->input('search_key');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'MyLaravelApp/1.0 (example@example.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $searchKey . ', India',
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 10,
            ]);

            if ($response->successful()) {
                $filtered = collect($response->json())->map(function ($item) {
                    return [
                        'name' => $item['display_name'] ?? '',
                        'lat' => $item['lat'] ?? '',
                        'lon' => $item['lon'] ?? '',
                    ];
                });

                return response()->json([
                    'success' => true,
                    'results' => $filtered,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch location data.',
                'error' => $response->body(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching location.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function fetchLocationFromNominatim($searchKey)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'MyLaravelApp/1.0 (example@example.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $searchKey . ', India',
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json()[0] ?? null;

                return $data ? [
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                    'name' => $data['display_name'] ?? null,
                ] : null;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    public function locationTo(Request $request)
    {
        $request->validate([
            'search_key' => 'required|string|max:255',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $searchKey = $request->input('search_key');
        $fromLat = $request->input('lat');
        $fromLon = $request->input('lon');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'MyLaravelApp/1.0 (example@example.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $searchKey . ', India',
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 10,
            ]);

            if ($response->successful()) {
                $results = collect($response->json())->map(function ($item) use ($fromLat, $fromLon) {
                    $toLat = $item['lat'];
                    $toLon = $item['lon'];

                    return [
                        'name' => $item['display_name'] ?? '',
                        'lat' => $toLat,
                        'lon' => $toLon,
                        'distance_km' => $this->calculateDistance($fromLat, $fromLon, $toLat, $toLon)
                    ];
                });

                return response()->json([
                    'success' => true,
                    'results' => $results,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch location data.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching location.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius in km

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return round($earthRadius * $c, 2); // Rounded to 2 decimal places
    }

    
    
    public function deleteTripClaimDetail(Request $request)
    {
        $request->validate(['TripClaimDetailID' => 'required']);

        $detail = DraftTripclaimdetails::find($request->TripClaimDetailID);

        if (!$detail) {
            return response()->json(['message' => 'Detail not found', 'success' => 'error'], 404);
        }

        DraftPersonsdetails::where('TripClaimDetailID', $request->TripClaimDetailID)->delete();
        $detail->delete();

        return response()->json(['message' => 'Detail deleted successfully', 'success' => 'success'], 200);
    }
    //     {
    //     "TripClaimDetailID": "12345"
    // }

    public function deleteTripClaim(Request $request)
    {
        $request->validate(['trip_claim_id' => 'required']);

        $claim = DraftTripclaim::find($request->trip_claim_id);

        if (!$claim) {
            return response()->json(['message' => 'Claim not found', 'success' => 'error'], 404);
        }

        $details = DraftTripclaimdetails::where('TripClaimID', $request->trip_claim_id)->get();

        foreach ($details as $detail) {
            DraftPersonsdetails::where('TripClaimDetailID', $detail->TripClaimDetailID)->delete();
            $detail->delete();
        }

        $claim->delete();

        return response()->json(['message' => 'Claim deleted successfully', 'success' => 'success'], 200);
    }
    //     {
    //     "TripClaimID": "TMG6789"
    // }

    public function draftViewClaim(Request $request){
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
        try{
            if (auth()->user()) {
            $reportingPersonEmpID = auth()->user()->emp_id;
            
            $tripdata = DraftTripclaim::with([
                'tripclaimdetails' => function ($query) {
                    $query->orderBy('sortOrder', 'ASC'); 
                },
                'tripclaimdetails.policyDet.subCategoryDetails.category',
                'tripclaimdetails.personsDetails.userDetails','tripclaimdetails.personsDetails'
            ])
            ->where('TripClaimID', $request->trip_claim_id)
             ->orderBy('created_at', 'DESC')
            ->first();
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
                DraftTripclaim::where('TripClaimID', $request->trip_claim_id)->update([
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
                $VisitListBranchID = DraftClaimManagement::visitlistbranchdetails($tripdata->VisitListBranchID);

                // Prepare the result
                $result = [
                    "trip_claim_id" => $tripdata->TripClaimID,
                    "trip_type_details" => $tripdata->triptypedetails->first() ? [
                        "triptype_id" => $tripdata->triptypedetails->first()->TripTypeID,
                        "triptype_name" => $tripdata->triptypedetails->first()->TripTypeName
                    ] : null,
                    "trip_type_details" => $trip->triptypedetails?->first() ? [
                        "triptype_id" => $trip->triptypedetails?->first()?->TripTypeID,
                        "triptype_name" => $trip->triptypedetails?->first()?->TripTypeName,
                    ] : null,
                    "approver_details" => $tripdata->approverdetails->first() ? [
                        "id" => $tripdata->approverdetails->first()->id,
                        "emp_id" => $tripdata->approverdetails->first()->emp_id,
                        "emp_name" => $tripdata->approverdetails->first()->emp_name
                    ] : null,
                    "trip_purpose" => $tripdata->TripPurpose,
                    
                    

                        "visit_branch_detail" => $tripdata->visit_branches->map(function ($branch) {
                            return [
                                "branch_id" => $branch->BranchID,
                                "branch_name" => $branch->BranchName
                            ];
                        })->toArray(),
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
                    'finance_remarks' => $tripdata->FinanceRemarks,
                    'total_amount' => $tripAmount,
                    'trip_approved_date' => $tripApprovedDate,
                    'trip_rejected_date' => $tripRejectedDate,
                    'approver_status' => $tripStatus,
                    'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($tripdata->Status === 'Paid' ? 'Approved' : $tripdata->Status),
                    'finance_status_change_date' => $tripdata->transaction_date ? \Carbon\Carbon::parse($tripdata->transaction_date)->format('d/m/Y') : null,
                    "finance_approver_details" => $tripdata->financeApproverdetails->first() ? [
                        "id" => $tripdata->financeApproverdetails->first()->id,
                        "emp_id" => $tripdata->financeApproverdetails->first()->emp_id,
                        "emp_name" => $tripdata->financeApproverdetails->first()->emp_name
                    ] : null,
                    'categories' => $tripdata->tripclaimdetails->groupBy(function ($detail) {
                        return $detail->policyDet->subCategoryDetails->category->CategoryID ?? 'Unknown';
                    })->map(function ($groupedDetails, $categoryID) use ($reportingPersonEmpID) {
                        $detail = $groupedDetails->first();
                        $category = $groupedDetails->first()->policyDet->subCategoryDetails->category;
                        $subcategory = $groupedDetails->first()->policyDet->subCategoryDetails;
                        $policy = $groupedDetails->first()->policyDet;

                        
                        $catDetail = Category::where('CategoryID',$detail->CategoryID)
                            ->select("CategoryID", "CategoryName", "ImageUrl")
                            ->first();
                        return [
                            "category_id" => $categoryID,
                            "category_name" => $category->CategoryName ?? $catDetail->CategoryName,
                            "image_url" => env('APP_URL') . '/images/category/' . $category->ImageUrl,
                            "trip_from_flag" => (bool)$catDetail->TripFrom,
                            "trip_to_flag" => (bool)$catDetail->TripTo,
                            "from_date_flag" => (bool)$catDetail->FromDate,
                            "to_date_flag" => (bool)$catDetail->ToDate,
                            "document_date_flag" => (bool)$catDetail->DocumentDate,
                            "start_meter_flag" => (bool)$catDetail->StartMeter,
                            "end_meter_flag" => (bool)$catDetail->EndMeter,
                            "subcategorydetails" => $this->getsubcategoryDetails($subcategory->CategoryID),
                            "no_of_days" =>31 ,
                            "class_flg" => $policy->GradeClass === 'class' ? true : false,
                            "claim_details" => $groupedDetails->map(function ($detail) use ($subcategory, $policy, $reportingPersonEmpID,$categoryID) {
                                        $eligible_amount_tot= $detail->personsDetails->map(function ($person) use ($categoryID,$detail) {
                                        $userDetails = $person->userDetails;
                                        $amounts = $userDetails->map(function ($user) use ($categoryID,$detail) {
                                            return $this->classCalc($user->emp_grade, $categoryID,$detail->FromDate,$detail->ToDate);
                                        });
                                        return $amounts->sum();
                                    })->sum();

                                return [
                                    "trip_claim_details_id" => $detail->TripClaimDetailID,
                                    "from_date" => $detail->FromDate,
                                    "to_date" => $detail->ToDate,
                                    "trip_from" => $detail->TripFrom,
                                    "trip_to" => $detail->TripTo,
                                    "lat" => null,
                                    "lon" =>null,
                                    //       "lat" => $location['lat'] ?? null,
                                    //       "lon" => $location['lon'] ?? null,
                                        "document_date" => $detail->DocumentDate,
                                    "start_meter" => $detail->StartMeter,
                                    "end_meter" => $detail->EndMeter,
                                    "qty" => $detail->Qty,
                                    "status" => $detail->Status,
                                    "unit_amount" => $detail->UnitAmount,
                                    "deduct_amount" => ($detail->DeductAmount-$detail->UnitAmount),
                                    "eligible_amount" => $eligible_amount_tot,

                                    "no_of_persons" => $detail->NoOfPersons,
                                    "file_url"=>asset('storage/' . $detail->FileUrl),
                                    "remarks" => $detail->Remarks,
                                    "approver_remarks" => $detail->approver_remarks,
                                    "approver_id" => $detail->ApproverID,
                                    "notification_flg" => $detail->NotificationFlg,
                                    "rejection_count" => $detail->RejectionCount,
                                    "person_details" => $detail->personsDetails->flatMap(function ($person) use($categoryID,$detail){
                                                                    return $person->userDetails->map(function ($user) use ($categoryID,$detail) {
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

                                    "send_approver_flag" => ($detail->EndMeter!=null) ? false : (($policy->GradeAmount !== null && $detail->UnitAmount > $eligible_amount_tot && ($reportingPersonEmpID == $detail->ApproverID)) ? true : false)


                                ];
                            }),
                        ];
                    })->values()
                ];
                    if($reportingPersonEmpID != $tripdata->ApproverID && $currentFlg=='9')
                    DraftTripclaim::where('TripClaimID', $request->trip_claim_id)->update([
                        'NotificationFlg' => '5'
                    ]);

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


    public function draftClaimList()
    {
        try {
            if (auth()->user()) {
                $reportingPersonEmpID = auth()->user()->emp_id;

                $tripdata = DraftTripclaim::with([
                    'tripclaimdetails.policyDet.subCategoryDetails.category',
                    'tripclaimdetails.personsDetails.userDetails'
                ])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map(function ($trip) use ($reportingPersonEmpID) {

                    $dates = collect();

                    foreach ($trip->tripclaimdetails as $detail) {
                        if (!empty($detail->FromDate)) {
                            $dates->push(Carbon::parse($detail->FromDate));
                        }
                        if (!empty($detail->DocumentDate)) {
                            $dates->push(Carbon::parse($detail->DocumentDate));
                        }
                    }

                    $today = Carbon::today();
                    $maxDate = null;
                    $difference = 0;
                    $expiryDate = null;
                    $reminder = false;
                    $remainderMessage = null;
                    if ($dates->isNotEmpty()) {
                        $maxDate = $dates->sort()->first()->startOfDay();
                        $expiryDate = $maxDate->copy()->addDays(31);
                        $daysLeft = $today->diffInDays($expiryDate, false); 
                        if ($daysLeft <= 10 && $daysLeft >= 0) {
                            $reminder = true;
                        }
                        $difference = $maxDate->diffInDays($today);
                        if ($difference > 31) {
                            $remainderMessage = "Can't submit the claim";
                        } elseif ($difference > 21 && $difference <= 31) {
                            $remainderMessage = "Submit claim before ".$expiryDate->format("d-m-Y");
                        } else {
                            $remainderMessage = null;
                        }
                    }

                    $tripAmount = $trip->tripclaimdetails->sum(function ($detail) {
                        return $detail->UnitAmount * $detail->Qty;
                    });
                    $tripAmount = number_format($tripAmount, 2, '.', '');
                    $statuses = $trip->tripclaimdetails->pluck('Status');
                    $rejectionCounts = $trip->tripclaimdetails->pluck('RejectionCount');

                    if ($statuses->every(fn($status) => $status === 'Approved')) {
                        $appStatus = 'Approved';
                        if ($trip->Status === 'Paid') {
                            $tripStatus = 'Paid';
                        } else if ($trip->Status === 'Pending') {
                            $tripStatus = 'Pending';
                        } else if ($trip->Status === 'Rejected') {
                            $tripStatus = 'Rejected';
                        } else {
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
                    $tripApprovedDate = $tripApprovedDate ? Carbon::parse($tripApprovedDate)->format('d/m/Y') : null;
                    $tripRejectedDate = $tripRejectedDate ? Carbon::parse($tripRejectedDate)->format('d/m/Y') : null;

                    $VisitListBranchID = DraftClaimManagement::visitlistbranchdetails($trip->VisitListBranchID);
                    $triptypedetails = Triptype::where("id",$trip->TripTypeID)->select("TripTypeID","TripTypeName")->first();

                    return [
                        "trip_claim_id" => $trip->TripClaimID,
                        "tmg_id" => 'TMG' . substr($trip->TripClaimID, 8),
                        "trip_purpose" => $trip->TripPurpose,
                        'date' => $trip->created_at->format('d/m/Y'),
                        'trip_status' => $tripStatus,
                        'trip_history_status' => $tripHistoryStatus,
                        'trip_approver_remarks' => $trip->ApproverRemarks,
                        'finance_remarks' => $trip->FinanceRemarks,
                        'total_amount' => $tripAmount,
                        'trip_approved_date' => $tripApprovedDate,
                        'trip_rejected_date' => $tripRejectedDate,
                        'approver_status' => $appStatus,
                        'finance_status' => $tripStatus === 'Rejected' ? 'Rejected' : ($trip->Status === 'Paid' ? 'Approved' : $trip->Status),
                        'finance_status_change_date' => $trip->ApprovalDate ? \Carbon\Carbon::parse($trip->ApprovalDate)->format('d/m/Y') : null,
                        "trip_type_details" => $triptypedetails ? [
                            "triptype_id" => $triptypedetails->TripTypeID,
                            "triptype_name" => $triptypedetails->TripTypeName,
                        ] : null,

                        "visit_branch_detail" => $trip->visit_branches->map(function ($branch) {
                            return [
                                "branch_id" => $branch->BranchID,
                                "branch_name" => $branch->BranchName
                            ];
                        })->toArray(),
                        'age_date' => $maxDate ? $maxDate->format('d/m/Y') : null,
                        'expiry_date' => $expiryDate ? $expiryDate->format('d/m/Y') : null,
                        'last_date' => $difference,
                        'reminder' => $reminder, // true or false
                        'remainder_message' => $remainderMessage,
                    ];
                });

                return response()->json([
                    'message' => "Result fetched successfully!",
                    'statusCode' => $this->successStatus,
                    'data' => $tripdata,
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

    public function saveDraft(Request $request){
        $validator = Validator::make($request->all(), [
            'triptype_id' => 'required',
            'trip_purpose' => 'required',
            "claim_details" => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
        //.....................................
        $user = User::where('id', auth()->id())->first();
      
        try {
            if (auth()->user()) {
                $claim_id = $this->generateId();
                $data = $request->all(); // Get all request data as an array
                $now = new \DateTime();  // Create a new DateTime object
                $currentdate = $now->format('Y-m-d H:i:s');
                $tripClaim = DraftTripclaim::create([
                    'TripClaimID' => $claim_id,
                    'TripTypeID' => $request->triptype_id,
                    'ApproverID' => $user->reporting_person_empid,
                    'TripPurpose' => $request->trip_purpose ?? null,
                    'VisitListBranchID' => json_encode(
                        is_array($request->visit_branch_id)
                            ? $request->visit_branch_id
                            : [$request->visit_branch_id]
                    ),
                    'AdvanceAmount' =>null,
                    'RejectionCount' => 0,
                    'ApprovalDate' => null,
                    'NotificationFlg' => "0",
                    'Status' => "Pending",
                    'user_id' => auth()->id(),
                ]);
                foreach ($data["claim_details"] as $index => $details) {
                    $TripClaimDetailID=$this->generateId();
                    $Tripclaimdetails = DraftTripclaimdetails::create([
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'TripClaimID' => $claim_id,
                        'PolicyID' => $details['policy_id'],
                        'CategoryID' => $details['category_id'],
                        'FromDate' => ($details['from_date']==null) ? $details['document_date'] : $details['from_date'],
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
                        'SortOrder' => $index+1,
                        'user_id' => auth()->id(),
                    ]);
                    $persondetails = DraftPersonsdetails::create([
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

                        $persondetails = DraftPersonsdetails::create([
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

    public function updateDraft(Request $request, $TripClaimID)
    {
        $validator = Validator::make($request->all(), [
            // 'triptype_id' => 'required',
            // 'trip_purpose' => 'required',
            // "claim_details" => 'required|array'
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
            $user = User::find(auth()->id());
            $tripClaim = DraftTripclaim::where('TripClaimID', $TripClaimID)->first();

            if (!$tripClaim) {
                return response()->json([
                    'message' => 'Trip claim not found',
                    'statusCode' => 404,
                    'success' => 'error'
                ], 404);
            }

            // ---- Update Main Trip Claim ----
            $tripClaim->update([
                'TripTypeID' => $request->triptype_id,
                'ApproverID' => $user->reporting_person_empid,
                'TripPurpose' => $request->trip_purpose ?? null,
                'VisitListBranchID' => json_encode(
                    is_array($request->visit_branch_id)
                        ? $request->visit_branch_id
                        : [$request->visit_branch_id]
                ),
                'user_id' => auth()->id(),
            ]);

            // ---- Sync Trip Claim Details ----
            $existingDetailIDs = DraftTripclaimdetails::where('TripClaimID', $TripClaimID)->pluck('TripClaimDetailID')->toArray();
            $requestDetailIDs = array_filter(array_column($request->claim_details, 'TripClaimDetailID'));

            // Delete Removed Details
            $detailsToDelete = array_diff($existingDetailIDs, $requestDetailIDs);
            if (!empty($detailsToDelete)) {
                DraftPersonsdetails::whereIn('TripClaimDetailID', $detailsToDelete)->delete();
                DraftTripclaimdetails::whereIn('TripClaimDetailID', $detailsToDelete)->delete();
            }

            // ---- Update or Insert Each Detail ----
            foreach ($request->claim_details as $index => $details) {
                $TripClaimDetailID = $details['TripClaimDetailID'] ?? $this->generateId();

                $tripDetail = DraftTripclaimdetails::updateOrCreate(
                    ['TripClaimDetailID' => $TripClaimDetailID],
                    [
                        'TripClaimID' => $TripClaimID,
                        'PolicyID' => $details['policy_id'],
                        'FromDate' => $details['from_date'] ?? $details['document_date'],
                        'ToDate' => $details['to_date'] ?? null,
                        'TripFrom' => $details['trip_from'] ?? null,
                        'TripTo' => $details['trip_to'] ?? null,
                        'DocumentDate' => $details['document_date'] ?? null,
                        'StartMeter' => $details['start_meter'] ?? null,
                        'EndMeter' => $details['end_meter'] ?? null,
                        'Qty' => $details['qty'] ?? null,
                        'UnitAmount' => $details['unit_amount'] ?? null,
                        'DeductAmount' => $details['deduct_amount'] ?? null,
                        'NoOfPersons' => $details['no_of_person'],
                        'FileUrl' => $details['file_url'] ?? null,
                        'Remarks' => $details['remarks'] ?? null,
                        'ApproverID' => $user->reporting_person_empid,
                        'Status' => 'Pending',
                        'SortOrder' => $index+1,
                        'user_id' => auth()->id(),
                    ]
                );

                // Delete Old Person Details
                Personsdetails::where('TripClaimDetailID', $TripClaimDetailID)->delete();

                // Insert Owner
                Personsdetails::create([
                    'PersonDetailsID' => $this->generateId(),
                    'TripClaimDetailID' => $TripClaimDetailID,
                    'EmployeeID' => auth()->id(),
                    'Grade' => $user->emp_grade,
                    'ClaimOwner' => '1',
                    'user_id' => auth()->id()
                ]);

                // Insert Other Persons
                foreach ($details['person_details'] as $perdet) {
                    if (!isset($perdet['id']) || !isset($perdet['grade'])) {
                        return response()->json([
                            'message' => 'Invalid person detail structure',
                            'statusCode' => 400,
                            'data' => $perdet,
                            'success' => 'error',
                        ], 400);
                    }

                    DraftPersonsdetails::create([
                        'PersonDetailsID' => $this->generateId(),
                        'TripClaimDetailID' => $TripClaimDetailID,
                        'EmployeeID' => $perdet['id'],
                        'Grade' => $perdet['grade'],
                        'ClaimOwner' => '0',
                        'user_id' => auth()->id()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Draft updated successfully!',
                'statusCode' => 200,
                'success' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 500,
                'data' => [],
                'message' => 'An error occurred while updating the draft. Please try again later.',
                'errmessage' => $e->getMessage(),
            ], 500);
        }
    }


}
