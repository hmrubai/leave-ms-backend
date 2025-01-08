<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\DayType;
use App\Models\Calendar;
use App\Models\Department;
use App\Models\FiscalYear;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\EmployeeInfo;
use App\Models\LeaveApprovelFlowSetting;
use App\Models\LeaveCutExplanation;
use App\Models\LeaveBalanceSetting;
use App\Models\LeaveApplications;
use App\Models\LeaveApplicationDetails;
use App\Models\LeaveApplicationApprovals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    //Individual Leave Register Report
    public function getIndividualLeaveRedister(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $employee = EmployeeInfo::where('id', $request->employee_id)->first();

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employee_id = $employee->id;

        // Step 1: Determine the fiscal_year_id
        $fiscalYear = FiscalYear::where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            // ->where('is_active', 1)
            ->first();

        if (!$fiscalYear) {
            return response()->json([
                'status' => false,
                'message' => 'No fiscal year found for the provided date range.',
                'data' => []
            ], 409);
        }

        $fiscalYearId = $fiscalYear->id;

        // Query to generate the report
        $report = LeaveApplications::with(['leaveBalance' => function ($query) use ($employee_id, $fiscalYearId) {
                $query->where('employee_id', $employee_id)
                ->where('fiscal_year_id', $fiscalYearId);
            }])
            ->where('leave_applications.employee_id', $employee_id)
            ->whereBetween('leave_applications.start_date', [$startDate, $endDate])
            ->select('leave_applications.leave_policy_id', 'leave_policies.leave_title')
            ->selectRaw('CAST(SUM(total_applied_days) AS DECIMAL(10,2)) AS total_applied_days, COUNT(*) as total_leave_count')
            ->selectRaw('SUM(CASE WHEN leave_applications.is_half_day = 1 THEN 1 ELSE 0 END) as half_day_count')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
            ->where('leave_applications.leave_status', 'Approved')
            ->groupBy('leave_applications.leave_policy_id')
            ->get();

        // Map the report to include leave balance details
        $report = $report->map(function ($item) {
            $item->total_applied_days = $item->total_applied_days;
            $item->total_leave_count = (int) $item->total_leave_count;
            $item->half_day_count = (int) $item->half_day_count;

            $item->leave_balance = $item->leaveBalance ? [
                'total_days' => $item->leaveBalance->total_days,
                'availed_days' => $item->leaveBalance->availed_days,
                'remaining_days' => $item->leaveBalance->remaining_days,
                'carry_forward_balance' => $item->leaveBalance->carry_forward_balance,
            ] : null;
    
            unset($item->leaveBalance); // Remove raw relationship data
            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfull',
            'data' => $report
        ], 200);
    }

    public function downloadLeaveReportPdf(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $employee = EmployeeInfo::select('employee_infos.*', 'designations.title as designation', 'departments.name as department', 'users.image', 'users.user_type')
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->where('employee_infos.id', $request->employee_id)
        ->first();

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employee_id = $employee->id;

        // Step 1: Determine the fiscal_year_id
        $fiscalYear = FiscalYear::where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            // ->where('is_active', 1)
            ->first();

        if (!$fiscalYear) {
            return response()->json([
                'status' => false,
                'message' => 'No fiscal year found for the provided date range.',
                'data' => []
            ], 409);
        }

        $fiscalYearId = $fiscalYear->id;

        // Query to generate the report
        $report = LeaveApplications::with(['leaveBalance' => function ($query) use ($employee_id, $fiscalYearId) {
                $query->where('employee_id', $employee_id)
                ->where('fiscal_year_id', $fiscalYearId);
            }])
            ->where('leave_applications.employee_id', $employee_id)
            ->whereBetween('leave_applications.start_date', [$startDate, $endDate])
            ->select('leave_applications.leave_policy_id', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->selectRaw('CAST(SUM(total_applied_days) AS DECIMAL(10,2)) AS total_applied_days, COUNT(*) as total_leave_count')
            ->selectRaw('SUM(CASE WHEN leave_applications.is_half_day = 1 THEN 1 ELSE 0 END) as half_day_count')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
            ->where('leave_applications.leave_status', 'Approved')
            ->groupBy('leave_applications.leave_policy_id')
            ->get();

        // Map the report to include leave balance details
        $report = $report->map(function ($item) {
            $item->total_applied_days = $item->total_applied_days;
            $item->total_leave_count = (int) $item->total_leave_count;
            $item->half_day_count = (int) $item->half_day_count;

            $item->leave_balance = $item->leaveBalance ? [
                'total_days' => $item->leaveBalance->total_days,
                'availed_days' => $item->leaveBalance->availed_days,
                'remaining_days' => $item->leaveBalance->remaining_days,
                'carry_forward_balance' => $item->leaveBalance->carry_forward_balance,
            ] : null;
    
            unset($item->leaveBalance); // Remove raw relationship data
            return $item;
        });

        $pdf = Pdf::loadView('reports.individual_register_report', [
            'report' => $report,
            'fiscalYear' => $fiscalYear,
            'employee' => $employee,
        ]);
    
        // Step 5: Download PDF
        return $pdf->download('leave_report.pdf');
    }

    public function getSummaryLeaveRegister(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $employees = [];

        if($request->department_id){
            $employees = EmployeeInfo::where('department_id', $request->department_id)->pluck('id');
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Query to generate the report
        $report = LeaveApplications::whereBetween('leave_applications.start_date', [$startDate, $endDate])
            ->select('leave_applications.leave_policy_id', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->selectRaw('SUM(CAST(total_applied_days AS DECIMAL(10,2))) AS total_applied_days, COUNT(*) AS total_leave_count')
            ->selectRaw('SUM(CASE WHEN leave_applications.is_half_day = 1 THEN 1 ELSE 0 END) as half_day_count')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
            ->where('leave_applications.leave_status', 'Approved')
            ->where('leave_policies.is_active', true)
            ->when(!empty($employees), function ($query) use ($employees) {
                $query->whereIn('employee_id', $employees);
            })
            ->groupBy('leave_applications.leave_policy_id')
            ->get();

        // Map the report to include leave balance details
        $report = $report->map(function ($item) {
            $item->total_applied_days = $item->total_applied_days;
            $item->total_leave_count = (int) $item->total_leave_count;
            $item->half_day_count = (int) $item->half_day_count;
            return $item;
        });

        $report->employee_count = $employees ? sizeof($employees) : "ALL";

        return response()->json([
            'status' => true,
            'message' => 'Successfull',
            'data' => $report
        ], 200);
    }

    public function downloadSummaryLeaveRegister(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $employees = [];
        $has_department = false;
        $department_id = null;

        if($request->department_id == "null"){
            $department_id = null;
        }
        elseif($request->department_id == "0"){
            $department_id = null;
        }
        else{
            $department_id = $request->department_id;
            $has_department = true;
        }

        if($department_id){
            $employees = EmployeeInfo::where('department_id', $request->department_id)->pluck('id');
            $department = Department::where('id', $request->department_id)->first();
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Query to generate the report
        $report = LeaveApplications::whereBetween('leave_applications.start_date', [$startDate, $endDate])
            ->select('leave_applications.leave_policy_id', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->selectRaw('CAST(SUM(total_applied_days) AS DECIMAL(10,2)) AS total_applied_days, COUNT(*) as total_leave_count')
            ->selectRaw('SUM(CASE WHEN leave_applications.is_half_day = 1 THEN 1 ELSE 0 END) as half_day_count')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
            ->where('leave_applications.leave_status', 'Approved')
            ->where('leave_policies.is_active', true)
            ->when(!empty($employees), function ($query) use ($employees) {
                $query->whereIn('employee_id', $employees);
            })
            ->groupBy('leave_applications.leave_policy_id')
            ->get();

        // Map the report to include leave balance details
        $report = $report->map(function ($item) {
            $item->total_applied_days = $item->total_applied_days;
            $item->total_leave_count = (int) $item->total_leave_count;
            $item->half_day_count = (int) $item->half_day_count;
            return $item;
        });

        $pdf = Pdf::loadView('reports.summary_register_report', [
            'report' => $report,
            'department' => $department ?? null,
            'has_department' => $has_department,
            'employee_count' => $employees ? sizeof($employees) : "ALL"
        ]);
    
        // Step 5: Download PDF
        return $pdf->download('leave_report.pdf');
    }

}
