<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
class RoleController extends Controller
{
    public function index()
    {
        // $data = User::where("Status",'1')
        // ->whereIn('emp_role',['Finance','Super Admin','HR & Admin','CMD','Auditor']);,compact('data')
        return view('admin.role_management.list');
    } 
    public function roleList(Request $request)
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
	            'created_at',
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
	        $query = User::whereIn('emp_role',['Finance','Super Admin','HR & Admin','CMD','Auditor'])
                        ->orderBy($orderColumn, $orderBy);

			$recordsTotal = $query->count();
			$data = $query->skip($skip)->take($pageLength)->get();
			$recordsFiltered = $recordsTotal;
			$formattedData = $data->map(function($row) {
				// Calculate the total amount using the sumTripClaimDetailsValue method
				$action='<a href="' . route('edit_role', $row->id) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.=' <a onclick="delete_role_modal(' . $row->id . ')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }
				return [
					'ID' => $row->id,
					'EmpID' => $row->emp_id,
					'EmpName' =>$row->emp_name,
					'Role' => $row->emp_role,
                    'checkbox' => '<input type="checkbox" name="item_checkbox[]" value="' . $row->id . '">',
	                'Status' => $row->Status, 
					'action' =>$action,
	                
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

    public function add_role()
    {
        return view('admin.role_management.add');
    }
    public function edit_role($id)
    {
        $data=User::where('id',$id)->first();
        return view('admin.role_management.edit',compact('data'));
    }
    public function search_role_user(Request $request)
    {
        $search = $request->get('search');
        $employees = User::where(function($query) use ($search) {
            $query->where('emp_name', 'like', '%' . $search . '%')
                  ->orWhere('emp_id', 'like', '%' . $search . '%');
        })
        ->where('Status', '1')
        ->get(['emp_id', 'emp_name']);

        return response()->json($employees);
    }
    public function search_branch(Request $request)
    {
        $search = $request->get('search');
        $employees = Branch::where(function($query) use ($search) {
            $query->where('BranchName', 'like', '%' . $search . '%')
                  ->orWhere('BranchCode', 'like', '%' . $search . '%');
        })
        ->where('Status', '1')
        ->get(['BranchName', 'BranchCode', 'BranchID']);

        return response()->json($employees);
    }
    public function add_role_submit(Request $request)
    {
        $employees = User::where('emp_id',$request->EmpID)
                    ->update(['emp_role'=>$request->Role]);

        return redirect()->route('role-management')->with('message','Role Added Successfully!');
    }
    public function edit_role_submit(Request $request)
    {
        
        $employees = User::where('emp_id',$request->EmpID)
                    ->update(['emp_role'=>$request->Role]);

        return redirect()->route('role-management')->with('message','Role Updated Successfully!');
    }


    public function delete_role($id)
    {
        $employees = User::where('id',$id)
                    ->update(['emp_role'=>'']);
        return response()->json(['success' => true]);
       // return redirect()->route('role-management')->with('message','Role Removed Successfully!');
    }

    public function delete_multi_role(Request $request)
    {
        $ids = $request->input('ids');
        User::where('emp_id','!=','MYG_HRMS')->whereIn('id', $ids)->update(['emp_role'=>'']);
        return response()->json(['success' => true]);
    }  
}
