<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Triptype;
use App\Models\User;
use Auth;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('admin.trip_type.list');
    }

    public function view_user($id)
    {
        $user=User::where('id',$id)->first();
        return view('admin.user.view',compact('user'));
    }
    

/***************************************
   Date        : 01/07/2024
   Description :  list for triptype
***************************************/    
	public function get_triptype_list(Request $request)
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
	            'TripTypeID',
	            'created_at',
	            'TripTypeName',
	            'Status'
	        ];
	        $orderColumn = $columns[$orderColumnIndex] ?? 'TripTypeID';
	        
	        $query = Triptype::where('Status','!=','2')
	                       ->where(function($q) use ($searchValue) {
                           $q->where('TripTypeName', 'like', '%'.$searchValue.'%');
                       })
                       ->orderBy('Status', 'desc') 
                       ->orderBy($orderColumn, $orderBy);

	        $recordsTotal = $query->count();
	        $data = $query->skip($skip)->take($pageLength)->get();
	        $recordsFiltered = $recordsTotal;

	        $formattedData = $data->map(function($row) 
	        {
                $action='<a href="' . route('view_triptype', $row->TripTypeID) .'"><i class="fa fa-eye button_orange" aria-hidden="true"></i></a><a href="' . route('edit_triptype', $row->TripTypeID) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.='<a onclick="delete_triptype_modal(\'' . $row->TripTypeID . '\')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }
	            return [
	                'TripTypeID' => $row->TripTypeID,
	                'TripTypeName' => $row->TripTypeName,
	                'action' => $action,
	                'checkbox' => '<input type="checkbox" name="item_checkbox[]" value="' . $row->TripTypeID . '">',
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
   Date        : 01/07/2024
   Description :  add data for triptype
***************************************/ 
    public function add_triptype()
    {
    	return view('admin.trip_type.add');
    }

/**********************************************
   Date        : 01/07/2024
   Description :  submit datas for triptype
**********************************************/     
    public function submit(Request $req)
    {
		$validatedData = $req->validate([
			'triptype' => 'required',

		], [
			'triptype.required' => 'Please enter the trip type.',
		]);
        $existing = Triptype::where('Status','!=','2')->where('TripTypeName', $req->triptype)->first();

		if ($existing) {
            return redirect()->back()->withErrors(['triptype' => 'TripType Name already exists.']); // Changed key from 'TripTypeName' to 'triptype'
        }
    
		$userData=Triptype::create([
			'TripTypeName'=>$req->triptype,
			'user_id'=>Auth::user()->id,
			'Status'=>'1',
		]); 
		return redirect()->route('trip_type_mgmt')->with('message','Triptype Added Successfully!');
    }

/***********************************************
   Date        : 01/07/2024
   Description :  view data lists for triptype
***********************************************/ 
    public function view_triptype($id)
    {
    	$data=Triptype::where('TripTypeID', $id)->first();
    	return view('admin.trip_type.view',compact('data'));
    }

/***************************************
   Date        : 01/07/2024
   Description :  edit for triptype
***************************************/ 
    public function edit_triptype($id)
    {
      $triptype=Triptype::where('TripTypeID',$id)->first();
      return view('admin.trip_type.edit',compact('triptype'));
    }


/*********************************************
   Date        : 01/07/2024
   Description :  update datas  for triptype
*********************************************/     
    public function update_triptype_submit(Request $req)
    { 
        $existing = Triptype::where('Status','!=','2')->where('TripTypeName', $req->triptype)
        ->where('TripTypeID','!=', $req->id)->first();

        if ($existing) {
            return redirect()->back()->withErrors(['TripTypeName' => 'TripType Name already exists.']);
        }
        Triptype::where('TripTypeID',$req->id)->update([
          'TripTypeName'=>$req->triptype,
          'user_id'=>Auth::user()->id,
          'Status'=>$req->Status,
        ]);
        return redirect()->route('trip_type_mgmt')->with('message','Triptype updated Successfully!');
    }

/*************************************************
   Date        : 01/07/2024
   Description :  delete datas for triptype
*************************************************/ 
    public function delete_triptype($id)
    {
        Triptype::where('TripTypeID', $id)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  

/****************************************************
   Date        : 01/07/2024
   Description :  delete multiple datas for triptype
****************************************************/ 
    public function delete_multi_triptype(Request $request)
    {
        $ids = $request->input('ids');
        $st=Triptype::whereIn('TripTypeID', $ids)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }  

}
