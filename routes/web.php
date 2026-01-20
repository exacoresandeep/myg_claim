<?php

use Illuminate\Support\Facades\Route;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

use App\Exports\ClaimExport;
use Symfony\Component\HttpFoundation\Cookie;


use App\Exports\UsersExport;

Route::get('/export-users', function () {
    // return Excel::download(new UsersExport, 'users.xlsx');
    $date = date('Ymd'); // e.g. 20250604
    $filename = "UsersData_{$date}.xlsx";
    return Excel::download(new UsersExport, $filename);
})->name('export.users');
Route::get('/export-claims', function (Request $request) {
    $fileName = 'Trip_Claims_' . now()->format('Ymd_His') . '.xlsx';

    // Create the cookie (name, value, expiration in seconds)
    $cookie = new Cookie('fileDownloaded', 'true', time() + 15060, '/', null, false, false);

    // Get the Excel response
    $response = Excel::download(new ClaimExport($request), $fileName);

    // Attach the cookie to the response
    $response->headers->setCookie($cookie);

    return $response;
})->name('export-claims');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




##############################  User login logout details#####################################

Route::get('/', function () {
    return view('auth.login');
});
Route::post('/auth_login', [App\Http\Controllers\LogincheckController::class, 'login'])->name('login');
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

####################################################################################################
##############################  Claim details#########################################

Route::get('/claim_request', [App\Http\Controllers\ClaimController::class, 'index'])->name('claim_request');
Route::get('/claim-view', [App\Http\Controllers\ClaimController::class, 'view'])->name('claim-view');

########################################################################################################


Route::get('/requested_claims', [App\Http\Controllers\ClaimManagementController::class, 'requested_claims'])->name('requested_claims');
Route::get('/requested_claims_list', [App\Http\Controllers\ClaimManagementController::class, 'requested_claims_list'])->name('requested_claims_list');
Route::get('/requested_claims_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'requested_claims_view'])->name('requested_claims_view');

Route::get('/approved_claims', [App\Http\Controllers\ClaimManagementController::class, 'approved_claims'])->name('approved_claims');
Route::get('/approved_claims_list', [App\Http\Controllers\ClaimManagementController::class, 'approved_claims_list'])->name('approved_claims_list');
Route::get('/approved_claims_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'approved_claims_view'])->name('approved_claims_view');
Route::post('/complete_approved_claim', [App\Http\Controllers\ClaimManagementController::class, 'complete_approved_claim'])->name('complete_approved_claim');

Route::get('/settled_claims', [App\Http\Controllers\ClaimManagementController::class, 'settled_claims'])->name('settled_claims');
Route::get('/settled_claims_list', [App\Http\Controllers\ClaimManagementController::class, 'settled_claims_list'])->name('settled_claims_list');
Route::get('/settled_claims_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'settled_claims_view'])->name('settled_claims_view');

Route::get('/rejected_claims', [App\Http\Controllers\ClaimManagementController::class, 'rejected_claims'])->name('rejected_claims');
Route::get('/rejected_claims_list', [App\Http\Controllers\ClaimManagementController::class, 'rejected_claims_list'])->name('rejected_claims_list');
Route::get('/rejected_claims_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'rejected_claims_view'])->name('rejected_claims_view');

Route::get('/ro_approved_claims', [App\Http\Controllers\ClaimManagementController::class, 'ro_approved_claims'])->name('ro_approved_claims');
Route::get('/ro_approved_claims_list', [App\Http\Controllers\ClaimManagementController::class, 'ro_approved_claims_list'])->name('ro_approved_claims_list');
Route::get('/ro_approved_claims_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'ro_approved_claims_view'])->name('ro_approved_claims_view');
Route::get('/ro_approved_claims_approved_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'ro_approved_claims_approved_view'] )->name('ro_approved_claims_approved_view');

Route::get('/report_management', [App\Http\Controllers\ClaimManagementController::class, 'report_management'])->name('report_management');
Route::get('/report_management_list', [App\Http\Controllers\ClaimManagementController::class, 'report_management_list'])->name('report_management_list');
Route::get('/report_management_view/{id}', [App\Http\Controllers\ClaimManagementController::class, 'report_management_view'])->name('report_management_view');



##############################  branch details#########################################


Route::get('/branch', [App\Http\Controllers\BranchController::class, 'index'])->name('branch');
Route::get('/get_branch_list', [App\Http\Controllers\BranchController::class, 'get_branch_list'])->name('get_branch_list');
Route::get('/add_branch', [App\Http\Controllers\BranchController::class, 'add_branch'])->name('add_branch');
Route::get('/view_branch/{id}', [App\Http\Controllers\BranchController::class, 'view_branch'])->name('view_branch');
Route::get('/edit_branch/{id}', [App\Http\Controllers\BranchController::class, 'edit_branch'])->name('edit_branch');
Route::post('/update_branch_submit', [App\Http\Controllers\BranchController::class, 'update_branch_submit'])->name('update_branch_submit');
Route::post('/add_branch_submit', [App\Http\Controllers\BranchController::class, 'submit'])->name('add_branch_submit');
Route::get('/delete_branch/{id}', [App\Http\Controllers\BranchController::class, 'delete_branch'])->name('delete_branch');
Route::post('/delete_multi_branch', [App\Http\Controllers\BranchController::class, 'delete_multi_branch'])->name('delete_multi_branch'); 

########################################################################################################

Route::get('/policy_management', [App\Http\Controllers\PolicyController::class, 'index'])->name('policy_management');
Route::get('/get_policy_management_list', [App\Http\Controllers\PolicyController::class, 'policy_management_list'])->name('policy_management_list');
Route::get('/add_policy_management', [App\Http\Controllers\PolicyController::class, 'add_policy_management'])->name('add_policy_management');
Route::get('/view_policy_management/{id}', [App\Http\Controllers\PolicyController::class, 'view_policy_management'])->name('view_policy_management');
Route::get('/edit_policy_management/{id}', [App\Http\Controllers\PolicyController::class, 'edit_policy_management'])->name('edit_policy_management');
Route::post('/add_policy_management_submit', [App\Http\Controllers\PolicyController::class, 'add_policy_management_submit'])->name('add_policy_management_submit');
Route::post('/update_policy_management_submit', [App\Http\Controllers\PolicyController::class, 'update_policy_management_submit'])->name('update_policy_management_submit');
Route::get('/delete_policy_management/{id}', [App\Http\Controllers\PolicyController::class, 'delete_policy_management'])->name('delete_policy_management');
Route::post('/delete_multi_policy_management', [App\Http\Controllers\PolicyController::class, 'delete_multi_policy_management'])->name('delete_multi_policy_management'); 
Route::post('/get-subcategories', [App\Http\Controllers\PolicyController::class, 'getSubCategories'])->name('get-subcategories');

##############################  grade details#########################################


Route::get('/grade', [App\Http\Controllers\GradeController::class, 'index'])->name('grade');
Route::get('/get_grade_list', [App\Http\Controllers\GradeController::class, 'get_grade_list'])->name('get_grade_list');
Route::get('/add_grade', [App\Http\Controllers\GradeController::class, 'add_grade'])->name('add_grade');
Route::get('/view_grade/{id}', [App\Http\Controllers\GradeController::class, 'view_grade'])->name('view_grade');
Route::get('/edit_grade/{id}', [App\Http\Controllers\GradeController::class, 'edit_grade'])->name('edit_grade');
Route::post('/update_grade_submit', [App\Http\Controllers\GradeController::class, 'update_grade_submit'])->name('update_grade_submit');
Route::post('/add_grade_submit', [App\Http\Controllers\GradeController::class, 'submit'])->name('add_grade_submit');
Route::get('/delete_grade/{id}', [App\Http\Controllers\GradeController::class, 'delete_grade'])->name('delete_grade');
Route::post('/delete_multi_grade', [App\Http\Controllers\GradeController::class, 'delete_multi_grade'])->name('delete_multi_grade'); 

########################################################################################################


##############################  Users details#########################################


Route::get('/list_users', [App\Http\Controllers\UserController::class, 'list_users'])->name('list_users');
Route::get('/get_user_list', [App\Http\Controllers\UserController::class, 'get_user_list'])->name('get_user_list');
Route::get('/add_user', [App\Http\Controllers\UserController::class, 'add_user'])->name('add_user');
Route::get('/view_user/{id}', [App\Http\Controllers\UserController::class, 'view_user'])->name('view_user');
Route::get('/edit_user/{id}', [App\Http\Controllers\UserController::class, 'edit_user'])->name('edit_user');
Route::post('/update_user_submit', [App\Http\Controllers\UserController::class, 'update_user_submit'])->name('update_user_submit');
Route::post('/add_user_submit', [App\Http\Controllers\UserController::class, 'submit'])->name('add_user_submit');
Route::get('/delete_user/{id}', [App\Http\Controllers\UserController::class, 'delete_user'])->name('delete_user');
Route::post('/delete_multi_user', [App\Http\Controllers\UserController::class, 'delete_multi_user'])->name('delete_multi_user'); 

########################################################################################################


##############################  category details#########################################


Route::get('/claim_category', [App\Http\Controllers\CategoryController::class, 'index'])->name('claim_category');
Route::get('/get_category_list', [App\Http\Controllers\CategoryController::class, 'get_category_list'])->name('get_category_list');
Route::get('/add_category', [App\Http\Controllers\CategoryController::class, 'add_category'])->name('add_category');
Route::get('/view_category/{id}', [App\Http\Controllers\CategoryController::class, 'view_category'])->name('view_category');
Route::get('/edit_category/{id}', [App\Http\Controllers\CategoryController::class, 'edit_category'])->name('edit_category');
Route::post('/update_category_submit', [App\Http\Controllers\CategoryController::class, 'update_category_submit'])->name('update_category_submit');
Route::post('/add_category_submit', [App\Http\Controllers\CategoryController::class, 'submit'])->name('add_category_submit');
Route::get('/delete_category/{id}', [App\Http\Controllers\CategoryController::class, 'delete_category'])->name('delete_category');
Route::post('/delete_multi_category', [App\Http\Controllers\CategoryController::class, 'delete_multi_category'])->name('delete_multi_category'); 

########################################################################################################


##############################  sub category details#########################################


Route::get('/sub_claim_category', [App\Http\Controllers\CategoryController::class, 'sub_claim_category'])->name('sub_claim_category');
Route::get('/get_subcategory_list', [App\Http\Controllers\CategoryController::class, 'get_subcategory_list'])->name('get_subcategory_list');
Route::get('/add_subcategory', [App\Http\Controllers\CategoryController::class, 'add_subcategory'])->name('add_subcategory');
Route::get('/view_subcategory/{id}', [App\Http\Controllers\CategoryController::class, 'view_subcategory'])->name('view_subcategory');
Route::get('/edit_subcategory/{id}', [App\Http\Controllers\CategoryController::class, 'edit_subcategory'])->name('edit_subcategory');
Route::post('/update_subcategory_submit', [App\Http\Controllers\CategoryController::class, 'update_subcategory_submit'])->name('update_subcategory_submit');
Route::post('/add_subcategory_submit', [App\Http\Controllers\CategoryController::class, 'subcategorysubmit'])->name('add_category_submit');
Route::get('/delete_subcategory/{id}', [App\Http\Controllers\CategoryController::class, 'delete_subcategory'])->name('delete_subcategory');
Route::post('/delete_multi_subcategory', [App\Http\Controllers\CategoryController::class, 'delete_multi_subcategory'])->name('delete_multi_subcategory'); 

########################################################################################################


##############################  Trip type mgmt details#########################################


Route::get('/trip_type_mgmt', [App\Http\Controllers\TripController::class, 'index'])->name('trip_type_mgmt');
Route::get('/get_triptype_list', [App\Http\Controllers\TripController::class, 'get_triptype_list'])->name('get_triptype_list');
Route::get('/add_triptype', [App\Http\Controllers\TripController::class, 'add_triptype'])->name('add_triptype');
Route::get('/view_triptype/{id}', [App\Http\Controllers\TripController::class, 'view_triptype'])->name('view_triptype');
Route::get('/edit_triptype/{id}', [App\Http\Controllers\TripController::class, 'edit_triptype'])->name('edit_triptype');
Route::post('/update_triptype_submit', [App\Http\Controllers\TripController::class, 'update_triptype_submit'])->name('update_triptype_submit');
Route::post('/add_triptype_submit', [App\Http\Controllers\TripController::class, 'submit'])->name('add_triptype_submit');
Route::get('/delete_triptype/{id}', [App\Http\Controllers\TripController::class, 'delete_triptype'])->name('delete_triptype');
Route::post('/delete_multi_triptype', [App\Http\Controllers\TripController::class, 'delete_multi_triptype'])->name('delete_multi_triptype'); 

########################################################################################################
Route::get('/view_user/{id}', [App\Http\Controllers\TripController::class, 'view_user'])->name('view_user');


Route::get('/role-management', [App\Http\Controllers\RoleController::class, 'index'])->name('role-management');
Route::get('/roleList', [App\Http\Controllers\RoleController::class, 'roleList'])->name('roleList');
Route::get('/edit_role/{id}', [App\Http\Controllers\RoleController::class, 'edit_role'])->name('edit_role');
Route::get('/delete_role/{id}', [App\Http\Controllers\RoleController::class, 'delete_role'])->name('delete_role');
Route::get('/add_role', [App\Http\Controllers\RoleController::class, 'add_role'])->name('add_role');
Route::post('/add_role_submit', [App\Http\Controllers\RoleController::class, 'add_role_submit'])->name('add_role_submit');
Route::post('/edit_role_submit', [App\Http\Controllers\RoleController::class, 'edit_role_submit'])->name('edit_role_submit');
Route::get('/search_role_user', [App\Http\Controllers\RoleController::class, 'search_role_user'])->name('search_role_user');
Route::get('/search_branch', [App\Http\Controllers\RoleController::class, 'search_branch'])->name('search_branch');
Route::post('/delete_multi_role', [App\Http\Controllers\RoleController::class, 'delete_multi_role'])->name('delete_multi_role'); 

// Route::get('/attachment/download/{filename}', [App\Http\Controllers\FileDownloadController::class, 'download'])->name('attachment.download');
Route::get('files/{filename}', [App\Http\Controllers\FileDownloadController::class, 'show'])->name('files.show');
Route::get('/files/view/{filename}', [App\Http\Controllers\FileDownloadController::class, 'view'])->name('filesview.view');

Route::post('finance_approve_claim', [App\Http\Controllers\ClaimManagementController::class, 'finance_approve_claim'])->name('finance_approve_claim');
Route::post('/import_excel_data', [App\Http\Controllers\ClaimManagementController::class, 'importExcelData'] )->name('import_excel_data');

Route::get('/advance_payment', [App\Http\Controllers\ClaimManagementController::class, 'advance_payment'] )->name('advance_payment');
Route::get('/advance_list', [App\Http\Controllers\ClaimManagementController::class, 'advance_list'] )->name('advance_list');

Route::post('/advance_approve', [App\Http\Controllers\ClaimManagementController::class, 'advance_approve'] )->name('advance_approve');
Route::post('/advance_reject', [App\Http\Controllers\ClaimManagementController::class, 'advance_reject'] )->name('advance_reject');
Route::post('/advance_settled', [App\Http\Controllers\ClaimManagementController::class, 'advance_settled'] )->name('advance_settled');
Route::post('/update_tripclaimDetails', [App\Http\Controllers\ClaimManagementController::class, 'update_tripclaimDetails'] )->name('update_tripclaimDetails');
Route::post('/reject_tripclaimDetails', [App\Http\Controllers\ClaimManagementController::class, 'reject_tripclaimDetails'] )->name('reject_tripclaimDetails');
// routes/web.php
Route::post('/notificationcount', [App\Http\Controllers\ClaimManagementController::class, 'notificationcount'])->name('notificationcount');
Route::get('/notifications', [App\Http\Controllers\ClaimManagementController::class, 'notifications'] )->name('notifications');
Route::get('/notifications_list', [App\Http\Controllers\ClaimManagementController::class, 'notifications_list'] )->name('notifications_list');

Route::get('/report/export', [App\Http\Controllers\ReportController::class, 'exportReport'])->name('report.export');

// Route::get('/rejected_advance_payment', [App\Http\Controllers\ClaimManagementController::class, 'rejected_advance_payment'] )->name('rejected_advance_payment');
// Route::get('/rejected_advance_payment_list', [App\Http\Controllers\ClaimManagementController::class, 'rejected_advance_payment_list'] )->name('rejected_advance_payment_list');
// Route::get('/approved_advance_payment', [App\Http\Controllers\ClaimManagementController::class, 'approved_advance_payment'] )->name('approved_advance_payment');
// Route::get('/approved_advance_payment_list', [App\Http\Controllers\ClaimManagementController::class, 'approved_advance_payment_list'] )->name('approved_advance_payment_list');
// Route::get('/settled_advance_payment', [App\Http\Controllers\ClaimManagementController::class, 'settled_advance_payment'] )->name('settled_advance_payment');
// Route::get('/settled_advance_payment_list', [App\Http\Controllers\ClaimManagementController::class, 'settled_advance_payment_list'] )->name('settled_advance_payment_list');
// Route::post('/advance_import_excel_data', [App\Http\Controllers\ClaimManagementController::class, 'advance_import_excel_data'] )->name('advance_import_excel_data');