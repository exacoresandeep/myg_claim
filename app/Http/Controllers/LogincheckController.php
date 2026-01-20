<?php

namespace App\Http\Controllers;
// use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Redirect;
use Session;
use Hash;
use Auth;

class LogincheckController extends Controller
{
/*****************************************
   Date        : 01/03/2024
   Description :  Login for Submission
******************************************/
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
    public function login(Request $req)
    {
        $validatedData = $req->validate([
          'username' => 'required',
          'password'=>'required',
        ], 
        [
          'username.required' => 'Please enter the name.',
          'password.required' => 'Please enter the password.',
        ]);
        $credentials = $req->only('username', 'password');
        $check_exist=User::where('emp_id',$req->username)
        ->whereIn('emp_role',['Finance','Super Admin','HR & Admin','CMD','Auditor'])
        ->exists();
        $user = User::where('emp_id', $credentials['username'])->first();
        if($check_exist!=true)
        {
            return Redirect::back()->withErrors(['msg' => 'Incorrect username.']);
        }
        else if ($check_exist==true && !Hash::check($credentials['password'], $user->password)) {

            //...........code for live
            $exacoreToken =$this->hrms_login_token();
            $responseArray = $exacoreToken->getData(true);
            $token = $responseArray['token'];
            $secondResponse = Http::withHeaders([
                'Content-type' => 'application/json',
                'Authorization' => 'JWT ' . $token,
            ])->post('https://mygian.mygoal.biz:6062/integration/login_api/', [
                'Username' => $req->username,
                'Password' => $req->password,
                'Key' => 'dv)B45k+Q34fnOZEqf',
            ]);


            $data=[];
            if ($secondResponse->failed()) {
                return Redirect::back()->withErrors(['msg' => 'Incorrect password.']);
            }else{
                $user = User::where('emp_id', $req->emp_id)->first();

                if ($user) {
                    // Update the user details
                    try {
                        $user->update([
                            'password'=>Hash::make($req->password)
                        ]);
                        $sequirity_id=Sequirityvlunerability::create([
                            'user_id'=>$user->id,
                            'random_string'=>substr(uniqid(), 0,25)
                        ]);
                        $sequirity_refresh_token=Sequirityvlunerability::select('random_string')->where('id', $sequirity_id->id)->first();

                        Session::put(['Role' => $user->emp_role,'Login_User_ID'=>$user->emp_id]);
                        return redirect()->route('home');
                    }catch (\Exception $e)
                    {
                        return response()->json([
                            'success'    => 'error',
                            'statusCode' => 500,
                            'data'       => [],
                            'message'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            //.............new code..........//
            return Redirect::back()->withErrors(['msg' => 'Incorrect password.']);
        }
        else if (Auth::attempt(['emp_id' => $credentials['username'], 'password' => $credentials['password']])) 
        {
            Session::put(['Role' => $user->emp_role,'Login_User_ID'=>$user->emp_id]);
            return redirect()->route('home');
        }
        else
        {
            return Redirect::back()->withErrors(['msg' => 'Invalid credentials. Please try again.']);
        }
              
    }
    
}
