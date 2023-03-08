<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\DayType;
use App\Models\Calendar;
use App\Models\FiscalYear;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\EmployeeInfo;
use App\Models\LeaveApprovelFlowSetting;
use App\Models\LeaveBalanceSetting;
use App\Models\LeaveApplications;
use App\Models\LeaveApplicationDetails;
use App\Models\LeaveApplicationApprovals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function dashboardSummary(Request $request)
    {
        $user_id = $request->user()->id;
        $employee = EmployeeInfo::where('user_id', $user_id)->first();
        $fiscal_year = FiscalYear::where('is_active', true)->first();
        $fiscal_year_id = $fiscal_year->id;
        $employee_id = $employee->id ? $employee->id : 0;

        $employee = EmployeeInfo::select(
            'employee_infos.*', 
            'designations.title as designation', 
            'departments.name as department', 
            'users.image',
            'users.institution',
            'users.education',
            'users.user_type',
            'employment_types.type as employment_type'
        )
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('employment_types', 'employment_types.id', 'employee_infos.employment_type_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->where('employee_infos.id', $employee_id)
        ->first();

        $employee->balance_list = LeaveBalance::select('leave_balances.*', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->leftJoin('fiscal_years', 'fiscal_years.id', 'leave_balances.fiscal_year_id')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_balances.leave_policy_id')
            ->when($employee_id, function ($query) use ($employee_id){
                return $query->where('leave_balances.employee_id', $employee_id);
            })
            ->when($fiscal_year_id, function ($query) use ($fiscal_year_id){
                return $query->where('leave_balances.fiscal_year_id', $fiscal_year_id);
            })
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        $employee->leave_list = LeaveApplications::select(
                'leave_applications.*',
                'leave_policies.leave_title',
                'leave_policies.leave_short_code'
            )
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
            ->where('leave_applications.employee_id', $employee_id)
            ->orderBy('leave_applications.id', "DESC")
            ->get();

        $employee->approved_leave_list = $employee->leave_list->where('leave_status', "Approved");
        $employee->count_total = $employee->leave_list->count();
        $employee->count_pending = $employee->leave_list->where('leave_status', "Pending")->count();
        $employee->count_approved= $employee->leave_list->where('leave_status', "Approved")->count();
        $employee->count_rejected = $employee->leave_list->where('leave_status', "Rejected")->count();

        $employee->weekend_holiday = Calendar::select(
            'calendars.id',
            'calendars.date',
            'calendars.day_note',
            'day_types.title as day_type_title',
            'day_types.day_short_code as day_type_short_code'
        )
        ->where('calendars.day_type_id', '!=', 1)
        ->leftJoin('day_types', 'day_types.id', 'calendars.day_type_id')
        ->orderBy('id', "DESC")
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
        ], 200);
    }
}
