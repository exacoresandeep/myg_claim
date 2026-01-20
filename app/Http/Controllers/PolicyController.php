<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Grades;
use App\Models\SubCategories;
use App\Models\Policy;
use Validator;
class PolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = Category::where('Status','1')->get(); 
        $grades = Grades::where('Status','1')->get(); 
        $subCategories = SubCategories::where('Status','1')->get(); 
        return view('admin.policy.list',compact('categories','grades','subCategories'));
    }

    public function policy_management_list(Request $request)
    {
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;
        
            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $columns = [
                'PolicyID',
                // 'SubCategoryName',
                // 'CategoryName',
                // 'GradeType'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'PolicyID';
        
            $query = Policy::select('myg_06_policies.*', 'myg_04_subcategories.SubCategoryName', 'myg_03_categories.CategoryName')
                ->leftJoin('myg_04_subcategories', 'myg_06_policies.SubCategoryID', '=', 'myg_04_subcategories.SubCategoryID')
                ->leftJoin('myg_03_categories', 'myg_03_categories.CategoryID', '=', 'myg_04_subcategories.CategoryID')
                ->where('myg_06_policies.Status', '!=', '2');
        
            // Apply grade filter (mandatory)
            if ($request->has('grade_id') && $request->grade_id != '') {
                $query->where('GradeID', $request->grade_id);
            } else {
                // If grade_id is missing, return empty response as grade_id is required
                return response()->json([
                    "draw" => $request->draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    'data' => []
                ], 200);
            }
        
            // Apply category filter if provided
            if ($request->has('category_id') && $request->category_id != '') {
                $query->where('myg_03_categories.CategoryID', $request->category_id);
            }
        
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($query) use ($searchValue) {
                    $query->where('myg_03_categories.CategoryName', 'LIKE', "%$searchValue%")
                        ->orWhere('myg_04_subcategories.SubCategoryName', 'LIKE', "%$searchValue%")
                        ->orWhere('GradeType', 'LIKE', "%$searchValue%");
                });
            }
        
            // Order by
            $query->orderBy('myg_06_policies.Status', 'desc')
                ->orderBy('myg_03_categories.CategoryID', 'desc')
                ->orderBy($orderColumn, $orderBy);
        
            $recordsTotal = $query->count();
            $data = $query->skip($skip)->take($pageLength)->get();
            $recordsFiltered = $recordsTotal;
        
            $formattedData = $data->map(function ($row) {
                $action='<a href="' . route('view_policy_management', $row->PolicyID) .'"><i class="fa fa-eye button_orange" aria-hidden="true"></i></a><a href="' . route('edit_policy_management', $row->PolicyID) .'"><i class="fa fa-pencil-square-o button_orange" aria-hidden="true"></i></a>';
                if(session('Role') === 'Super Admin'){
                    $action.='<a onclick="delete_category_modal(\'' . $row->PolicyID . '\')"><i class="fa fa-trash button_orange" aria-hidden="true"></i></a>';
                }
                return [
                    'PolicyID' => $row->PolicyID,
		    'SubCategoryName' => $row->SubCategoryName,
		    'CategoryName'=>$row->CategoryName,
                    'GradeType' => $row->GradeType,
                    'GradeClass' => $row->GradeClass,
                    'GradeAmount' => $row->GradeAmount,
                    'Status' => $row->Status, 
                    'action' => $action,
                    'checkbox' => '<input type="checkbox" name="item_checkbox[]" value="' . $row->PolicyID. '">',
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

    public function getSubCategories(Request $request)
    {   
        $subCategories = SubCategories::where('CategoryID', $request->category_id)->where('Status', '1')->get();
        return response()->json($subCategories);
    }

    public function add_policy_management()
    {
        $categories = Category::where('Status','1')->get(); 
        $grades = Grades::where('Status','1')->get(); 
        $subCategories = SubCategories::where('Status','1')->get();
        return view('admin.policy.add',compact('categories','grades','subCategories'));
    }

    public function edit_policy_management($id)
    {
        $policy=Policy::where('PolicyID',$id)->first();
        $categories = Category::where('Status','1')->get(); 
        $grades = Grades::where('Status','1')->get(); 
        $subCategories = SubCategories::where('Status','1')->get();
        $data=Policy::with('viewgradeDetails','subCategoryDetails','subCategoryDetails.category')->where('PolicyID', $id)->first();
        return view('admin.policy.edit',compact('policy','categories','grades','subCategories','data'));
    }

    public function view_policy_management($id)
    {
        $data=Policy::with('viewgradeDetails','subCategoryDetails','subCategoryDetails.category')->where('PolicyID', $id)->first();
        return view('admin.policy.view',compact('data'));
    }

    public function add_policy_management_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'GradeID' => 'required', // Ensure GradeID exists in the grades table
            'GradeType' => 'required|string',         // Ensure GradeType is a valid string
            'GradeClass' => 'nullable|string',        // GradeClass can be null or a valid string
            'GradeAmount' => 'nullable|numeric',      // GradeAmount can be null or a valid number
        ], [
            // Custom error messages
            'GradeID.required' => 'The grade field is required.',
            'GradeID.exists' => 'The selected grade does not exist.',
            'GradeType.required' => 'The grade type field is required.',
            'GradeType.string' => 'The grade type must be a valid string.',
            'GradeClass.string' => 'The grade class must be a valid string.',
            'GradeAmount.numeric' => 'The grade amount must be a valid number.',
        ]);
        // Apply additional validation based on GradeType
        $validator->sometimes('GradeClass', 'required|string', function ($input) {
            return $input->GradeType === 'Class';
        });

        $validator->sometimes('GradeAmount', 'required|numeric', function ($input) {
            return $input->GradeType === 'Amount';
        });
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
        if ($request->GradeType === 'Class') {
            $existingPolicy = Policy::where('GradeID', $request->GradeID)
                ->where('SubCategoryID', $request->SubCategoryID)
                ->where('Status','!=' ,'2')
                ->exists();
    
            if ($existingPolicy) {
                return redirect()->back()
                                 ->with('error', 'A policy with this Grade and SubCategory already exists.')
                                 ->withInput();
            }
        } elseif ($request->GradeType === 'Amount') {
            $existingSubCategory = SubCategories::where('SubCategoryID', $request->SubCategoryID)->first();
            $subCategoryIDs = SubCategories::where('CategoryID', $existingSubCategory->CategoryID)
            ->pluck('SubCategoryID');
            $existingPolicy = Policy::whereIn('SubCategoryID', $subCategoryIDs)
            ->where('GradeID', $request->GradeID)
            ->where('Status','!=' ,'2')
            ->exists();
    
            if ($existingPolicy) {
                return redirect()->back()
                                 ->with('error', 'A policy with this Grade and Category already exists.')
                                 ->withInput();
            }
        }
    
        Policy::create([
            'SubCategoryID' =>$request->SubCategoryID,
            'GradeID' => $request->GradeID,        
            'GradeType' => $request->GradeType, 
            'GradeClass' => $request->GradeClass, 
            'GradeAmount' => $request->GradeAmount, 
            'Approver' => 'NA', 
            'Status' => '1',     
            'user_id' => '3'       
        ]);
        return redirect()->route('policy_management') // Assuming you have a route named 'policy.index'
        ->with('message', 'Policy created successfully.');
    }

    public function update_policy_management_submit(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'GradeID' => 'required', // Ensure GradeID exists in the grades table
            'GradeType' => 'required|string',         // Ensure GradeType is a valid string
            'GradeClass' => 'nullable|string',        // GradeClass can be null or a valid string
            'GradeAmount' => 'nullable|numeric',      // GradeAmount can be null or a valid number
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
        if ($request->GradeType === 'Class') {
            $existingPolicy = Policy::where('GradeID', $request->GradeID)
                ->where('SubCategoryID', $request->SubCategoryID)
                ->where('Status','!=' ,'2')
                ->where('PolicyID','!=' ,$request->PolicyID)
                ->exists();
    
            if ($existingPolicy) {
                return redirect()->back()
                                 ->with('error', 'A policy with this Grade and SubCategory already exists.')
                                 ->withInput();
            }
        } elseif ($request->GradeType === 'Amount') {
            $existingSubCategory = SubCategories::where('SubCategoryID', $request->SubCategoryID)->first();
            $subCategoryIDs = SubCategories::where('CategoryID', $existingSubCategory->CategoryID)
            ->pluck('SubCategoryID');
            $existingPolicy = Policy::whereIn('SubCategoryID', $subCategoryIDs)
            ->where('GradeID', $request->GradeID)
            ->where('Status','!=' ,'2')
            ->where('PolicyID','!=' ,$request->PolicyID)
            ->exists();
    
            if ($existingPolicy) {
                return redirect()->back()
                                 ->with('error', 'A policy with this Grade and Category already exists.')
                                 ->withInput();
            }
        }

        if($request->GradeType=="Class"){
            $request->GradeAmount=null;
        }
        else{
            $request->GradeClass=null;
        }
        Policy::where('PolicyID',$request->PolicyID)->update([
            'SubCategoryID' =>$request->SubCategoryID,
            'GradeID' => $request->GradeID,        
            'GradeType' => $request->GradeType, 
            'GradeClass' => $request->GradeClass, 
            'GradeAmount' => $request->GradeAmount, 
            'Approver' => 'NA', 
            'Status' => '1',     
            'user_id' =>'3'            
        ]);
        return redirect()->route('policy_management') // Assuming you have a route named 'policy.index'
        ->with('message', 'Policy Updated successfully.');
    }
  
    public function delete_policy_management($id)
    {
        Policy::where('PolicyID', $id)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }

    public function delete_multi_policy_management(Request $request)
    {
        $ids = $request->input('ids');
        Policy::whereIn('PolicyID', $ids)->update(['Status'=>'2']);
        return response()->json(['success' => true]);
    }
}
