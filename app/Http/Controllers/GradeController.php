<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grades;
use Auth;
use DB;

class  GradeController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('admin.grade.list');
    }

/***************************************
   Date        : 28/06/2024
   Description :  list for grade
***************************************/    
	public function get_grade_list(Request $request)
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
	            'GradeID',
	            'created_at',
	            'GradeName',
	            'Status'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'BranchID';
	        
	        $query = Grades::where('Status','!=','2')
	                        ->where(function($q) use ($searchValue) {
                           $q->where('GradeName', 'like', '%'.$searchValue.'%');
                       })
                       ->orderBy('Status', 'desc') 
                       ->orderBy($orderColumn, $orderBy);

	        $recordsTotal = $query->count();
	        $data = $query->skip($skip)->take($pageLength)->get();
	        $recordsFiltered = $recordsTotal;

	        $formattedData = $data->map(function($row) 
	        {
                $action='<a href="' . route('view_grade', $row->GradeID) .'"><i class="fa fa-eye button_orange" aria-hidden="true"></i></a><a href="' . route('edit_grade', $row->GradeID) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.='<a onclick="delete_grade_modal(\'' . $row->GradeID . '\')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }
	            return [
	                'GradeID' => $row->GradeID,
	                'GradeName' => $row->GradeName,
	                'action' => $action,
	                'checkbox' => '<input type="checkbox" name="item_checkbox[]" value="' . $row->GradeID. '">',
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
   Description :  add for grade
***************************************/
    public function add_grade()
    {
    	return view('admin.grade.add');
    }
/***************************************
   Date        : 28/06/2024
   Description :  submit for grade
***************************************/    
    public function submit(Request $req)
    {
		$validatedData = $req->validate([
			'GradeName' => 'required',

		], [
			'GradeName.required' => 'Please enter the grade name.',
		]);

        $existing = Grades::where('Status','!=','2')->where('GradeName', $req->GradeName)->first();

        if ($existing) {
            return redirect()->back()->withErrors(['GradeName' => 'Grade Name already exists.']);
        }

		$gradeData=Grades::create([
			'GradeName'=>$req->GradeName,
			'user_id'=>Auth::user()->id,
			'Status'=>'1',
		]); 
		return redirect()->route('grade')->with('message','Grade Added Successfully!');
    }
/***************************************
   Date        : 28/06/2024
   Description :  view for grade
***************************************/
    public function view_grade($id)
    {
    	$data=Grades::where('GradeID', $id)->first();
    	return view('admin.grade.view',compact('data'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  edit for grade
***************************************/
    public function edit_grade($id)
    {
      $grade=Grades::where('GradeID',$id)->first();
      return view('admin.grade.edit',compact('grade'));
    }
/***************************************
   Date        : 28/06/2024
   Description :  update forms for grade
***************************************/    
    public function update_grade_submit(Request $req)
    { 
        $existing = Grades::where('Status','!=','2')->where('GradeName', $req->GradeName)
        ->where('GradeID','!=', $req->id)->first();

        if ($existing) {
            return redirect()->back()->withErrors(['GradeName' => 'Grade Name already exists.']);
        }

        Grades::where('GradeID',$req->id)->update([
          'GradeName'=>$req->GradeName,
          'user_id'=>Auth::user()->id,
          'Status'=>$req->Status,
        ]);
        return redirect()->route('grade')->with('message','Grade updated Successfully!');
    }
/***************************************
   Date        : 28/06/2024
   Description :  delete for grade
***************************************/
    public function delete_grade($id)
    {
        Grades::where('GradeID', $id)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  
/*****************************************************
   Date        : 28/06/2024
   Description :  multiple deletions for grade
*****************************************************/
    public function delete_multi_grade(Request $request)
    {
        $ids = $request->input('ids');
        Grades::whereIn('GradeID', $ids)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  
}
