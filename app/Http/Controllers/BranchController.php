<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Auth;
use DB;

class  BranchController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('admin.branch.list');
    }

/***************************************
   Date        : 28/06/2024
   Description :  list for branch
***************************************/    
	public function get_branch_list(Request $request)
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
	            'BranchID',
	            'created_at',
	            'BranchCode',
	            'BranchName',
	            'Status'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'BranchID';
	        
	        $query = Branch::where('Status','!=','2')
	                        ->where(function($q) use ($searchValue) {
                           $q->where('BranchName', 'like', '%'.$searchValue.'%')
                             ->orWhere('BranchCode', 'like', '%'.$searchValue.'%');
                       })
                       ->orderBy('Status', 'desc') 
                       ->orderBy($orderColumn, $orderBy);

	        $recordsTotal = $query->count();
	        $data = $query->skip($skip)->take($pageLength)->get();
	        $recordsFiltered = $recordsTotal;

	        $formattedData = $data->map(function($row) 
	        {
                $action='<a href="' . route('view_branch', $row->BranchID) .'"><i class="fa fa-eye button_orange" aria-hidden="true"></i></a><a href="' . route('edit_branch', $row->BranchID) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.='<a onclick="delete_branch_modal(\'' . $row->BranchID . '\')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }
	            return [
	                'BranchID' => $row->BranchID,
	                'BranchName' => $row->BranchName,
	                'BranchCode' => $row->BranchCode, 
	                'invoice_date' => $row->invoice_date,
	                'action' => $action,
	                'checkbox' => '<input type="checkbox" name="item_checkbox[]" value="' . $row->BranchID . '">',
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
   Description :  add for branch
***************************************/
    public function add_branch()
    {
    	return view('admin.branch.add');
    }
/***************************************
   Date        : 28/06/2024
   Description :  submit for branch
***************************************/    
    public function submit(Request $req)
    {
		$validatedData = $req->validate([
			'branch_name' => 'required',
			'branch_code' => 'required'

		], [
			'branch_name.required' => 'Please enter the branch name.',
			'branch_code.required' => 'Please enter the branch code.',
		]);
        $existingBranch = Branch::where('Status','!=','2')
        ->where(function ($query) use ($req) {
            $query->where('BranchCode', $req->branch_code)
                  ->orWhere('BranchName', $req->branch_name);
        })
        ->first();

        if ($existingBranch) {
            if ($existingBranch->BranchCode == $req->branch_code) {
                return redirect()->back()->withErrors(['branch_code' => 'Branch code already exists.']);
            }
            
            if ($existingBranch->BranchName == $req->branch_name) {
                return redirect()->back()->withErrors(['branch_name' => 'Branch name already exists.']);
            }
        }
		$userData=Branch::create([
			'BranchName'=>$req->branch_name,
			'BranchCode'=>$req->branch_code,
			'user_id'=>Auth::user()->id,
			'Status'=>'1',
		]); 
		return redirect()->route('branch')->with('message','Branch Added Successfully!');
    }
/***************************************
   Date        : 28/06/2024
   Description :  view for branch
***************************************/
    public function view_branch($id)
    {
    	$data=Branch::where('BranchID', $id)->first();
    	return view('admin.branch.view',compact('data'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  edit for branch
***************************************/
    public function edit_branch($id)
    {
      $branch=Branch::where('BranchID',$id)->first();
      return view('admin.branch.edit',compact('branch'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  update forms for branch
***************************************/    
    public function update_branch_submit(Request $req)
    { 
        $existingBranch = Branch::where('Status','!=','2')->where(function ($query) use ($req) {
            $query->where('BranchCode', $req->branch_code)
                  ->orWhere('BranchName', $req->branch_name);
        })
        ->where('BranchID','!=', $req->id)->first();

        if ($existingBranch) {
            if ($existingBranch->BranchCode == $req->branch_code) {
                return redirect()->back()->withErrors(['branch_code' => 'Branch code already exists.']);
            }
            
            if ($existingBranch->BranchName == $req->branch_name) {
                return redirect()->back()->withErrors(['branch_name' => 'Branch name already exists.']);
            }
        }

        Branch::where('BranchID',$req->id)->update([
          'BranchName'=>$req->branch_name,
          'BranchCode'=>$req->branch_code,
          'user_id'=>Auth::user()->id,
          'Status'=>$req->Status,
        ]);
        
        return redirect()->route('branch')->with('message','Branch updated Successfully!');
    }
/***************************************
   Date        : 28/06/2024
   Description :  delete for branch
***************************************/
    public function delete_branch($id)
    {
        Branch::where('BranchID', $id)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  
/***************************************
   Date        : 28/06/2024
   Description :  multiple deletions for branch
***************************************/
    public function delete_multi_branch(Request $request)
    {
        $ids = $request->input('ids');
        Branch::whereIn('BranchID', $ids)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  
}
