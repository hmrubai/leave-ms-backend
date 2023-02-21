<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeavePolicyController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\MasterSettingsController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveApprovalFlowSetupController;


Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::post('/auth/login-via-code', [AuthController::class, 'loginViaCode']);

// Route::get('country-list', [MasterSettingsController::class, 'countryList']);
Route::get('division-list', [LocationController::class, 'divisionList']);
Route::get('district-list/{division_id}', [LocationController::class, 'districtListByID']);
Route::get('upazila-list/{district_id}', [LocationController::class, 'upazilaListByID']);
Route::get('area-list/{upazilla_id}', [LocationController::class, 'unionListByID']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('admin/company-save-or-update', [OrganizationController::class, 'saveOrUpdateCompany']);
    Route::get('admin/company-list', [OrganizationController::class, 'companyList']);

    Route::post('admin/branch-save-or-update', [OrganizationController::class, 'saveOrUpdateBranch']);
    Route::get('admin/branch-list', [OrganizationController::class, 'branchList']);
    Route::get('admin/branch-list-by-company-id/{company_id}', [OrganizationController::class, 'branchListByCompanyID']);

    //Designation
    Route::post('admin/designation-save-or-update', [MasterSettingsController::class, 'saveOrUpdateDesignation']);
    Route::get('admin/designation-list', [MasterSettingsController::class, 'designationList']);
    Route::get('admin/designation-list-by-id/{company_id}/{branch_id}', [MasterSettingsController::class, 'designationListByID']);

    //Department
    Route::post('admin/department-save-or-update', [OrganizationController::class, 'saveOrUpdateDepartment']);
    Route::get('admin/department-list', [OrganizationController::class, 'departmentList']);
    Route::get('admin/department-list-by-id/{company_id}/{branch_id}', [OrganizationController::class, 'departmentListByID']);

    //Employee 
    Route::post('admin/add-employee', [EmployeeController::class, 'saveEmployee']);
    Route::post('admin/update-employee', [EmployeeController::class, 'updateEmployee']);
    Route::get('admin/employee-list', [EmployeeController::class, 'employeeList']);
    Route::get('admin/employee-details-by-id/{employee_id}', [EmployeeController::class, 'employeeDetailsByID']);
    Route::get('admin/employee-filter-list', [EmployeeController::class, 'employeeFilterList']);
    Route::get('admin/approval-authority-list', [EmployeeController::class, 'approvalAuthorityList']);

    Route::post('admin/update-leave-balance', [EmployeeController::class, 'addManualLeaveBalance']);
    
    //FiscalYear
    Route::post('admin/fiscal-year-save-or-update', [MasterSettingsController::class, 'saveOrUpdateFiscalYear']);
    Route::get('admin/fiscal-year-list', [MasterSettingsController::class, 'fiscalYearList']);
    Route::get('admin/fiscal-year-list-by-id/{company_id}', [MasterSettingsController::class, 'fiscalYearListByID']);
    
    //Leave Policy
    Route::post('admin/leave-policy-save-or-update', [LeavePolicyController::class, 'saveOrUpdateLeavePolicy']);
    Route::get('admin/leave-policy-list', [LeavePolicyController::class, 'leavePolicyList']);
    Route::get('admin/leave-policy-list-by-id/{company_id}', [LeavePolicyController::class, 'leavePolicyListByCompanyID']);
    
    //Employment Type
    Route::post('admin/employment-type-save-or-update', [MasterSettingsController::class, 'saveOrUpdateEmploymentType']);
    Route::get('admin/employment-type-list', [MasterSettingsController::class, 'employmentTypeList']);

    //Leave Balance Setting
    Route::post('admin/leave-setting-save-or-update', [LeaveBalanceController::class, 'saveOrUpdateLeaveBalanceSetting']);
    Route::get('admin/leave-setting-list/{employment_type_id}', [LeaveBalanceController::class, 'leaveBalanceSettingList']);
    Route::get('admin/leave-balance-list/{employee_id}', [LeaveBalanceController::class, 'employeeLeaveBalanceList']);
    Route::post('admin/leave-balance-update', [LeaveBalanceController::class, 'updateEmployeeLeaveBalance']);

    //Approval Flow Setup
    Route::post('admin/add-approval-flow', [LeaveApprovalFlowSetupController::class, 'addApprovalFlow']);
    Route::get('admin/approval-flow-list', [LeaveApprovalFlowSetupController::class, 'approvalFlowList']);
    Route::post('admin/update-approval-flow', [LeaveApprovalFlowSetupController::class, 'updateApprovalFlow']);

    //Calender Setup
    Route::get('admin/day-type-list', [CalendarController::class, 'dayTypeList']);
    Route::get('admin/day-status-list', [CalendarController::class, 'dayStatusList']);
    Route::post('admin/update-day-status', [CalendarController::class, 'updateDayStatusSetup']);
    Route::get('admin/calender', [CalendarController::class, 'getCalendar']);
    Route::post('admin/update-calender', [CalendarController::class, 'updateCalendar']);
    Route::post('admin/generate-calender', [CalendarController::class, 'generateCalendar']);
    Route::get('admin/year-list', [CalendarController::class, 'getYearList']);

    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('profile-update', [AuthController::class, 'updateUser']);
    Route::get('admin/expert-list', [AuthController::class, 'getAdminExpertList']);
    Route::post('admin/save-update-expert', [AuthController::class, 'saveOrUpdateUser']);
    Route::post('delete-account', [AuthController::class, 'deleteUserAccount']);
    
});

// Route::post('trancate-data', [MasterSettingsController::class, 'trancateData']);

Route::any('{url}', function(){
    return response()->json([
        'status' => false,
        'message' => 'Route Not Found!',
        'data' => []
    ], 404);
})->where('url', '.*');
