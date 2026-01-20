<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function ($router) 
{
	Route::post('/login', [AuthController::class, 'login']);
	Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
	Route::post('/refresh_token', [Authcontroller::class , 'refresh_token'])->name('refresh_token');
	Route::post('/hrmstokengeneration', [AuthController::class, 'hrmstokengeneration']);
});

Route::group(['middleware' => ['jwt.verify']], function ()
{
	Route::get('/branches', [AuthController::class, 'list_branch']);
	Route::get('/categories', [AuthController::class, 'list_category']);
	Route::get('/categorieswithpolicy', [AuthController::class, 'categorieswithpolicy']);
	Route::get('/tripTypes', [AuthController::class, 'list_triptype']);
	Route::get('/claimList', [AuthController::class, 'claimList']);
	Route::get('/user-profile', [AuthController::class, 'userProfile']); 
	Route::post('/employeeNames', [AuthController::class, 'employeeNames']); 
	Route::get('/approverNames', [AuthController::class, 'ApproverNames']); 
	Route::post('/approverChange', [AuthController::class, 'approverChange']); 
	Route::post('/approvalStatus', [AuthController::class, 'approvalStatus']);    
	Route::post('/approvalAll', [AuthController::class, 'approvalAll']);    
	Route::post('/specialApprovalAll', [AuthController::class, 'specialApprovalAll']);    
	Route::post('/rejectSingle', [AuthController::class, 'rejectSingle']);    
	Route::post('/removeSingle', [AuthController::class, 'removeSingle']);    
	Route::post('/policies', [AuthController::class, 'policies']);   
	Route::post('/fileUpload', [AuthController::class, 'fileUpload']);
	Route::post('/tripClaim', [AuthController::class, 'tripClaim']);    
	Route::post('/tripClaimSubmit', [AuthController::class, 'tripClaimSubmit']);    
	Route::post('/employeeStatus', [AuthController::class, 'employeeStatus']);   
	Route::post('/claimResubmit', [AuthController::class, 'claimResubmit']);    
	Route::get('/claimsForApproval', [AuthController::class, 'claimsForApproval']);      
	Route::get('/claimsForSpecialApproval', [AuthController::class, 'claimsForSpecialApproval']);      
	Route::get('/claimsForCMDApproval', [AuthController::class, 'claimsForCMDApproval']);      
	Route::get('/notificationList', [AuthController::class, 'notificationList']);    
	Route::get('/notificationCount', [AuthController::class, 'notificationCount']);    
	Route::post('/viewClaim', [AuthController::class, 'viewClaim']);    
	Route::post('/viewClaim_new', [AuthController::class, 'viewClaim_new']);    
	Route::post('/viewClaimSpecialApprover', [AuthController::class, 'viewClaimSpecialApprover']);    
	Route::post('/storeAttendance', [AuthController::class, 'storeAttendance']);
	Route::post('/userUpdate', [AuthController::class, 'userUpdate']);
	Route::post('/specialApproverRejectSingle', [AuthController::class, 'specialApproverRejectSingle']);
	
	Route::post('/advanceRequest', [AuthController::class, 'advanceRequest']);
	Route::get('/advanceList', [AuthController::class, 'advanceList']);
	Route::post('/updateFcmToken', [AuthController::class, 'updateFcmToken']);
	Route::post('/classCalculation', [AuthController::class, 'classCalculation']);
	Route::post('/checkDuplicateClaims', [AuthController::class, 'checkDuplicateClaims']);
	
	Route::post('/locationFrom', [AuthController::class, 'locationFrom']);
	Route::post('/locationTo', [AuthController::class, 'locationTo']);
	
	Route::post('/draft-trip-claim/detail/delete', [AuthController::class, 'deleteTripClaimDetail']);
	Route::post('/draft-trip-claim/delete', [AuthController::class, 'deleteTripClaim']);
	Route::post('/draft-view-claim', [AuthController::class, 'draftViewClaim']);
	Route::post('/draft-claim-list', [AuthController::class, 'draftClaimList']);
	Route::post('/save-draft', [AuthController::class, 'saveDraft']);
	Route::post('/update-draft', [AuthController::class, 'updateDraft']);
});