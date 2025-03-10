<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeavePolicyController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\MasterSettingsController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveApprovalFlowSetupController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HsepBalanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ReportController;


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

    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/update-password', [AuthController::class, 'updatePasswordByAdmin']);

    //Company
    Route::post('admin/company-save-or-update', [OrganizationController::class, 'saveOrUpdateCompany']);
    Route::get('admin/company-list', [OrganizationController::class, 'companyList']);

    //Branch
    Route::post('admin/branch-save-or-update', [OrganizationController::class, 'saveOrUpdateBranch']);
    Route::get('admin/branch-list', [OrganizationController::class, 'branchList']);
    Route::get('admin/branch-list-by-company-id/{company_id}', [OrganizationController::class, 'branchListByCompanyID']);

    //Designation
    Route::post('admin/designation-save-or-update', [MasterSettingsController::class, 'saveOrUpdateDesignation']);
    Route::get('admin/designation-list', [MasterSettingsController::class, 'designationList']);
    Route::get('admin/designation-list-by-id/{company_id}/{branch_id}', [MasterSettingsController::class, 'designationListByID']);

    //Wing
    Route::post('admin/wing-save-or-update', [MasterSettingsController::class, 'saveOrUpdateWing']);
    Route::get('admin/wing-list', [MasterSettingsController::class, 'wingList']);
    Route::get('admin/wing-list-by-id/{company_id}/{branch_id}', [MasterSettingsController::class, 'wingListByID']);

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
    Route::post('admin/make-employee-offboarded', [EmployeeController::class, 'makeEmployeeOffboard']);
    Route::get('admin/offboarded-employee-list', [EmployeeController::class, 'offboardEmployeeList']);

    Route::post('admin/update-leave-balance', [EmployeeController::class, 'addManualLeaveBalance']);
    
    //FiscalYear
    Route::post('admin/fiscal-year-save-or-update', [MasterSettingsController::class, 'saveOrUpdateFiscalYear']);
    Route::get('admin/fiscal-year-list', [MasterSettingsController::class, 'fiscalYearList']);
    Route::get('admin/fiscal-year-list-by-id/{company_id}', [MasterSettingsController::class, 'fiscalYearListByID']);
    
    //Leave Policy
    Route::post('admin/leave-policy-save-or-update', [LeavePolicyController::class, 'saveOrUpdateLeavePolicy']);
    Route::get('admin/leave-policy-list', [LeavePolicyController::class, 'leavePolicyList']);
    Route::get('admin/leave-policy-list-by-id/{company_id}', [LeavePolicyController::class, 'leavePolicyListByCompanyID']);
    Route::get('leave/user-policy-list', [LeavePolicyController::class, 'userLeavePolicyList']);

    //Employment Type
    Route::post('admin/employment-type-save-or-update', [MasterSettingsController::class, 'saveOrUpdateEmploymentType']);
    Route::get('admin/employment-type-list', [MasterSettingsController::class, 'employmentTypeList']);

    //Leave Balance Setting
    Route::post('admin/leave-setting-save-or-update', [LeaveBalanceController::class, 'saveOrUpdateLeaveBalanceSetting']);
    Route::get('admin/leave-setting-list/{employment_type_id}', [LeaveBalanceController::class, 'leaveBalanceSettingList']);

    //Hsep Leave Balance
    Route::get('admin/hsep-balance-list', [HsepBalanceController::class, 'hsepLeaveBalanceList']);
    Route::post('admin/add-hsep-balance', [HsepBalanceController::class, 'addHsepBalance']);
    Route::get('admin/hsep-balance-history', [HsepBalanceController::class, 'hsepBalanceHistory']);

    Route::get('admin/leave-balance-list/{employee_id}', [LeaveBalanceController::class, 'employeeLeaveBalanceList']);
    Route::post('admin/leave-balance-update', [LeaveBalanceController::class, 'updateEmployeeLeaveBalance']);
    Route::post('admin/cut-leave-balance', [LeaveBalanceController::class, 'cutEmployeeLeaveBalance']);
    Route::post('admin/resolved-cutting-leave-balance', [LeaveBalanceController::class, 'resolvedCuttingLeaveBalance']);
    Route::get('my/leave-balance-list', [LeaveBalanceController::class, 'myLeaveBalanceList']);
    Route::post('admin/leave-setting-manually', [LeaveBalanceController::class, 'addLeaveBalanceManually']);
    Route::get('admin/previous-leave-balance-list', [LeaveBalanceController::class, 'employeePreviousLeaveBalanceList']);   
    Route::get('admin/balance-update-2024', [LeaveBalanceController::class, 'leaveBalanceUpdate2024']);   

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
    Route::get('my/calendar-list', [CalendarController::class, 'getAcademicCalenadr']);
    
    //Leave Application
    Route::get('leave/application-list', [LeaveApplicationController::class, 'getLeaveApplication']);
    Route::post('leave/check-validity', [LeaveApplicationController::class, 'checkLeaveValidity']);
    Route::post('leave/submit-application', [LeaveApplicationController::class, 'applyForALeave']);
    Route::get('leave/application-details-by-id/{leave_application_id}', [LeaveApplicationController::class, 'getLeaveDetailsByID']);
    
    //Approval Authority Leave Application
    Route::get('approval/pending/application-list', [LeaveApplicationController::class, 'getApprovalAuthorityPendingLeaveList']);
    Route::get('approval/approved/application-list', [LeaveApplicationController::class, 'getApprovalAuthorityApprovedLeaveList']);
    Route::get('approval/rejected/application-list', [LeaveApplicationController::class, 'getApprovalAuthorityRejectedLeaveList']);
    Route::get('admin/leave-application-list', [LeaveApplicationController::class, 'getAdminAllLeaveApplications']);
    Route::post('approval/leave-application-filter', [LeaveApplicationController::class, 'getAuthorityFilterLeaveApplications']);
    Route::post('leave/approve-leave', [LeaveApplicationController::class, 'approveLeave']);
    Route::post('leave/reject-leave', [LeaveApplicationController::class, 'rejectLeave']);
    Route::post('leave/withdraw-leave', [LeaveApplicationController::class, 'withdrawLeave']);

    //Dashboard
    Route::get('dashboard-summary', [DashboardController::class, 'dashboardSummary']);
    Route::get('approval-dashboard-summary', [DashboardController::class, 'getApprovalDashboardSummary']);
    
    //Email Sending
    Route::post('notification/send-email', [NotificationController::class, 'checkEmailSending']);

    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('profile-update', [AuthController::class, 'updateUser']);
    Route::get('admin/expert-list', [AuthController::class, 'getAdminExpertList']);
    Route::post('admin/save-update-expert', [AuthController::class, 'saveOrUpdateUser']);
    Route::post('delete-account', [AuthController::class, 'deleteUserAccount']);

    //Attendance
    Route::post('admin/upload-attendance-log', [AttendanceLogController::class, 'uploadPunchLog']);
    Route::post('admin/attendance-log', [AttendanceLogController::class, 'getAdminPunchLog']);
    Route::post('self/attendance-log', [AttendanceLogController::class, 'getSelfPunchLog']);

    //Notice Board
    Route::post('admin/notice-save-or-update', [NoticeBoardController::class, 'saveOrUpdateNotice']);
    Route::post('admin/notice-delete', [NoticeBoardController::class, 'deleteNotice']);
    Route::get('admin/notice-list', [NoticeBoardController::class, 'noticeList']);

    //Report Controller
    Route::post('admin/individual-register', [ReportController::class, 'getIndividualLeaveRedister']);
    Route::post('admin/individual-register/download', [ReportController::class, 'downloadLeaveReportPdf']);

    //Summary Report
    Route::post('admin/summary-register', [ReportController::class, 'getSummaryLeaveRegister']);
    Route::post('admin/individual-summary-report', [ReportController::class, 'getIndividualSummaryReport']);
    Route::post('admin/summary-register/download', [ReportController::class, 'downloadSummaryLeaveRegister']);

});

//Report Controller
//Route::get('admin/individual-register', [ReportController::class, 'getIndividualLeaveRedister']);

// Route::get('admin/import-employee', [EmployeeController::class, 'import']);
// Route::post('trancate-data', [MasterSettingsController::class, 'trancateData']);

// Route::post('admin/add-leave-balance-manually-for-single-policy', [LeaveBalanceController::class, 'addLeaveBalanceForSingleTypeManually']);
// Route::post('admin/shift-fiscal-year', [LeaveBalanceController::class, 'shiftFiscalYear']);

Route::any('{url}', function(){
    return response()->json([
        'status' => false,
        'message' => 'Route Not Found!',
        'data' => []
    ], 404);
})->where('url', '.*');
