<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeavePolicyController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\MasterSettingsController;


Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::post('/auth/login-via-code', [AuthController::class, 'loginViaCode']);

// Route::get('country-list', [MasterSettingsController::class, 'countryList']);
Route::get('division-list', [LocationController::class, 'divisionList']);
Route::get('district-list/{division_id}', [LocationController::class, 'districtListByID']);
Route::get('upazila-list/{district_id}', [LocationController::class, 'upazilaListByID']);
Route::get('upazila-list/{upazila_id}', [LocationController::class, 'unionListByID']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('company-save-or-update', [OrganizationController::class, 'saveOrUpdateCompany']);
    Route::get('company-list', [OrganizationController::class, 'companyList']);

    Route::post('branch-save-or-update', [OrganizationController::class, 'saveOrUpdateBranch']);
    Route::get('branch-list', [OrganizationController::class, 'branchList']);
    Route::get('branch-list-by-company-id/{company_id}', [OrganizationController::class, 'branchListByCompanyID']);

    //Designation
    Route::post('designation-save-or-update', [MasterSettingsController::class, 'saveOrUpdateDesignation']);
    Route::get('designation-list', [MasterSettingsController::class, 'designationList']);
    Route::get('designation-list-by-id/{company_id}/{branch_id}', [MasterSettingsController::class, 'designationListByID']);

    //Department
    Route::post('department-save-or-update', [OrganizationController::class, 'saveOrUpdateDepartment']);
    Route::get('department-list', [OrganizationController::class, 'departmentList']);
    Route::get('department-list-by-id/{company_id}/{branch_id}', [OrganizationController::class, 'departmentListByID']);

    //Employee 
    Route::post('add-employee', [EmployeeController::class, 'saveEmployee']);
    Route::post('update-employee', [EmployeeController::class, 'updateEmployee']);

    //Leave Policy
    Route::post('leave-policy-save-or-update', [LeavePolicyController::class, 'saveOrUpdateLeavePolicy']);

    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('profile-update', [AuthController::class, 'updateUser']);
    Route::get('admin/expert-list', [AuthController::class, 'getAdminExpertList']);
    Route::post('admin/save-update-expert', [AuthController::class, 'saveOrUpdateUser']);
    Route::post('delete-account', [AuthController::class, 'deleteUserAccount']);

    //Master Settings
    // Route::get('syllabus-list', [MasterSettingsController::class, 'packageTypeList']);
    // Route::get('grade-list', [MasterSettingsController::class, 'gradeList']);
    // Route::get('category-list', [MasterSettingsController::class, 'categoryList']);
    // Route::post('admin/syllabus-save-or-update', [MasterSettingsController::class, 'saveOrUpdatePackageType']);
    
});

// Route::post('trancate-data', [MasterSettingsController::class, 'trancateData']);

Route::any('{url}', function(){
    return response()->json([
        'status' => false,
        'message' => 'Route Not Found!',
        'data' => []
    ], 404);
})->where('url', '.*');
