<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use DB;
use App\Models\Branch;
use App\Models\ClaimManagement;
use App\Models\Tripclaimdetails;

class  UserController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function list_users()
    {
        return view('admin.user.list');
    }

/***************************************
   Date        : 28/06/2024
   Description :  list for user
***************************************/    
	public function get_user_list(Request $request)
	{
	    if ($request->ajax()) 
	    {
	        $pageNumber = ($request->start / $request->length) + 1;
	        $pageLength = $request->length;
	        $skip = ($pageNumber - 1) * $pageLength;
            // dd($request->order);
	        $orderColumnIndex = $request->order[0]['column'] ?? 0;
	        $orderBy = $request->order[0]['dir'] ?? 'desc';
	        $searchValue = $request->search['value'] ?? '';
	        $columns = [
	            'id',
	            'emp_id',
	            'emp_name',
	            'emp_phonenumber',
	            'email',
	            'emp_grade',
	            'branch',
	            'Status'
	        ];
            
                $orderColumn = $columns[$orderColumnIndex] ?? '';
            
	        
	        $query = User::with('branchData')->where(function($q) use ($searchValue) {
                           $q->where('emp_id', 'like', '%'.$searchValue.'%')
                           ->orWhere('emp_name', 'like', '%'.$searchValue.'%')
                           ->orWhere('email', 'like', '%'.$searchValue.'%');
                       })
                       ->orderBy($orderColumn, $orderBy);

	        $recordsTotal = $query->count();
	        $data = $query->skip($skip)->take($pageLength)->get();
	        $recordsFiltered = $recordsTotal;

	        $formattedData = $data->map(function($row) 
	        {
                $action='<a href="' . route('view_user', $row->id) .'"><i class="fa fa-eye button_orange" aria-hidden="true"></i></a><a href="' . route('edit_user', $row->id) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.='<a onclick="delete_user_modal(\'' . $row->id . '\')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }

	            return [
	                'emp_id' => $row->emp_id,
	                'emp_name' => $row->emp_name,
                    'grade'=>$row->emp_grade,
                    'branch'=> optional($row->branchData)->BranchName,
                    'email'=>$row->email,
                    'emp_phonenumber'=>$row->emp_phonenumber,
	                'action' => $action,
	                'Status' => $row->Status, 
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

/***************************************
   Date        : 28/06/2024
   Description :  view for user
***************************************/
    public function view_user($id)
    {
    	$data=User::with('branchData','baselocationDetails')->where('id', $id)->first();
    	return view('admin.user.view',compact('data'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  edit for user
***************************************/
    public function edit_user($id)
    {
      $branch=Branch::where('Status','1')->orderBy('BranchName', 'asc')->get();
      $userData=User::whereIn('emp_grade',['1','2','3','4'])->orderBy('emp_name', 'asc')->get();
      $User=User::where('id',$id)->first();
      return view('admin.user.edit',compact('User','branch','userData'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  update forms for user
***************************************/    
    public function update_user_submit(Request $req)
    { 
        $details=User::where('id',$req->id)->first();
        if($details->reporting_person_empid!=$req->reporting_person){
            ClaimManagement::where('Status','Pending')
                                    ->where('user_id',$req->id)
                                    ->update(['ApproverID'=>$req->reporting_person]);
            Tripclaimdetails::whereNotIn('Status', ['Approved', 'Paid'])
                                    ->where('user_id',$req->id)
                                    ->update(['ApproverID'=>$req->reporting_person]);
        }
        User::where('id',$req->id)->update([
          'emp_baselocation'=>$req->emp_baselocation,
          'reporting_person'=>$req->reporting_person_name,
          'reporting_person_empid'=>$req->reporting_person,
        //   'hrms_baselocation_flag' => $req->hrms_baselocation_flag,
        //     'hrms_reporting_person_flag' => $req->hrms_reporting_person_flag,
        ]);
        return redirect()->route('list_users')->with('message','User updated Successfully!');
    }
/***************************************
   Date        : 28/06/2024
   Description :  delete for user
***************************************/
    public function delete_user($id)
    {
        User::where('id', $id)->delete();
        return response()->json(['success' => true]);
    }  
/*****************************************************
   Date        : 28/06/2024
   Description :  multiple deletions for user
*****************************************************/
    public function delete_multi_user(Request $request)
    {
        $ids = $request->input('ids');
        User::whereIn('id', $ids)->delete();
        return response()->json(['success' => true]);
    }  
}
