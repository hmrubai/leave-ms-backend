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

class LeaveApplicationController extends Controller
{
    public function checkLeaveValidity(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_policy_id' => 'required',
            // 'is_half_day' => 'required',
            // 'half_day' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $user_id = $request->user()->id;
        $leave_policy_id = $request->leave_policy_id;
        
        $employee = EmployeeInfo::where('user_id', $user_id)->first();
        $leave_policy = LeavePolicy::where('id', $leave_policy_id)->first();
        $fiscal_year = FiscalYear::where('is_active', true)->first();

        $fiscal_year_end_date = Carbon::parse($fiscal_year->end_date);
        $check_start_date = Carbon::parse($request->start_date);
        $check_end_date = Carbon::parse($request->end_date);

        $is_valid = true;
        if ($check_start_date->gte($fiscal_year_end_date)) { 
            $is_valid = false;
        }

        if ($check_end_date->gte($fiscal_year_end_date)) { 
            $is_valid = false;
        }

        $is_exist_start = LeaveApplications::whereBetween('start_date', [$check_start_date, $check_end_date])->where('employee_id', $employee->id)->where('leave_status', '!=', "Rejected")->first();
        $is_exist_end = LeaveApplications::whereBetween('end_date', [$check_start_date, $check_end_date])->where('employee_id', $employee->id)->where('leave_status', '!=', "Rejected")->first();

        if(!empty($is_exist_start) || !empty($is_exist_end)){
            return response()->json([
                'status' => false,
                'message' => 'You apleady sent an application on this date!',
                'data' => []
            ], 409);
        }

        if(!$is_valid){
            return response()->json([
                'status' => false,
                'message' => 'You can not apply for a leave for the multiple Fiscal Year',
                'data' => []
            ], 409);
        }

        $leave_balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('fiscal_year_id', $fiscal_year->id)
            ->where('leave_policy_id', $leave_policy_id)
            ->first();

        if(empty($leave_balances)){
            return response()->json([
                'status' => false,
                'message' => 'Balance Not Found! Please, contact to HR department.',
                'data' => []
            ], 409);
        }

        if(!$leave_policy->is_holiday_deduct){
            $day_type = DayType::where('title', "Work Day")->first();
            $calendar_days = Calendar::where('day_type_id', $day_type->id)
                ->whereBetween('date', [$check_start_date, $check_end_date])
                ->get();
        }else{
            $calendar_days = Calendar::whereBetween('date', [$check_start_date, $check_end_date])->get();
        }

        $total_calendar_days = sizeof($calendar_days);

        if($total_calendar_days <= 0){
            return response()->json([
                'status' => false,
                'message' => 'Please, check date!',
                'data' => []
            ], 409);
        }

        if($request->is_half_day){
            $total_calendar_days = $total_calendar_days - 0.5;
        }

        if($total_calendar_days > $leave_balances->remaining_days){
            return response()->json([
                'status' => false,
                'message' => 'You don\'t have sufficient leave balance!',
                'data' => []
            ], 409);
        }

        $new_remaining_days = $leave_balances->remaining_days - $total_calendar_days;

        return response()->json([
            'status' => true,
            'message' => 'Valid',
            'data' => ["total_applied_days" => $total_calendar_days, "remaining_days" => $new_remaining_days]
        ], 200);
    }

    public function applyForALeave(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_policy_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $user_id = $request->user()->id;
        $leave_policy_id = $request->leave_policy_id;
        
        $employee = EmployeeInfo::select('employee_infos.*', 'designations.title as designation', 'departments.name as department')
            ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
            ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
            ->where('employee_infos.user_id', $user_id)
            ->first();

        $leave_policy = LeavePolicy::where('id', $leave_policy_id)->first();
        $fiscal_year = FiscalYear::where('is_active', true)->first();

        $fiscal_year_end_date = Carbon::parse($fiscal_year->end_date);
        $check_start_date = Carbon::parse($request->start_date);
        $check_end_date = Carbon::parse($request->end_date);

        $is_valid = true;
        if ($check_start_date->gte($fiscal_year_end_date)) { 
            $is_valid = false;
        }

        if ($check_end_date->gte($fiscal_year_end_date)) { 
            $is_valid = false;
        }

        $is_exist_start = LeaveApplications::whereBetween('start_date', [$check_start_date, $check_end_date])->where('employee_id', $employee->id)->where('leave_status', '!=', "Rejected")->first();
        $is_exist_end = LeaveApplications::whereBetween('end_date', [$check_start_date, $check_end_date])->where('employee_id', $employee->id)->where('leave_status', '!=', "Rejected")->first();

        if(!empty($is_exist_start) || !empty($is_exist_end)){
            return response()->json([
                'status' => false,
                'message' => 'You apleady sent an application on this date!',
                'data' => []
            ], 409);
        }

        if(!$is_valid){
            return response()->json([
                'status' => false,
                'message' => 'You can not apply for a leave for the multiple Fiscal Year',
                'data' => []
            ], 409);
        }

        $leave_balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('fiscal_year_id', $fiscal_year->id)
            ->where('leave_policy_id', $leave_policy_id)
            ->first();

        if(empty($leave_balances)){
            return response()->json([
                'status' => false,
                'message' => 'Balance Not Found! Please, contact to HR department.',
                'data' => []
            ], 409);
        }

        if(!$leave_policy->is_holiday_deduct){
            $day_type = DayType::where('title', "Work Day")->first();
            $calendar_days = Calendar::where('day_type_id', $day_type->id)
                ->whereBetween('date', [$check_start_date, $check_end_date])
                ->get();
        }else{
            $calendar_days = Calendar::whereBetween('date', [$check_start_date, $check_end_date])->get();
        }

        $total_calendar_days = sizeof($calendar_days);

        if($total_calendar_days <= 0){
            return response()->json([
                'status' => false,
                'message' => 'Please, check date!',
                'data' => []
            ], 409);
        }

        if($request->is_half_day){
            $total_calendar_days = $total_calendar_days - 0.5;
        }

        if($total_calendar_days > $leave_balances->remaining_days){
            return response()->json([
                'status' => false,
                'message' => 'You don\'t have sufficient leave balance!',
                'data' => []
            ], 409);
        }

        $new_remaining_days = $leave_balances->remaining_days - $total_calendar_days;

        $leave_flow = LeaveApprovelFlowSetting::select(
            'leave_approvel_flow_settings.*',
            'employee_infos.email as approval_email'
        )
        ->leftJoin('employee_infos', 'employee_infos.id', 'leave_approvel_flow_settings.approval_authority_id')
        ->where('leave_approvel_flow_settings.employee_id', $employee->id)
        ->where('leave_approvel_flow_settings.is_active', true)
        ->get();

        $leave_application = LeaveApplications::create([
            'employee_id' => $employee->id,
            'user_id' => $user_id,
            'leave_policy_id' => $leave_policy_id,
            'start_date' => $check_start_date,
            'end_date' => $check_end_date,
            'total_applied_days' => $total_calendar_days,
            'is_half_day' => $request->is_half_day,
            'half_day' => $request->half_day,
            'leave_reason' => $request->reason,
            'leave_status' => "Pending"
        ]);

        foreach ($calendar_days as $leave_day) {
            LeaveApplicationDetails::create([
                'application_id' => $leave_application->id,
                'employee_id' => $employee->id,
                'user_id' => $user_id,
                'leave_policy_id' => $leave_policy_id,
                'date' => $leave_day->date
            ]);
        }

        $recipants_emails = [];

        $step_count = 1;
        foreach ($leave_flow as $flow) {
            $step_flag = "Pending";
            if($step_count == 1){
                $step_flag = "Active";
                array_push($recipants_emails, $flow->approval_email);
            }

            LeaveApplicationApprovals::create([
                'application_id' => $leave_application->id,
                'approval_id' => $flow->approval_authority_id,
                'employee_id' => $employee->id,
                'user_id' => $user_id,
                'leave_policy_id' => $leave_policy_id,
                'step' => $flow->step,
                'approval_status' => 'Pending',
                'step_flag' => $step_flag
            ]);

            $step_count++;
        }

        LeaveBalance::where('id', $leave_balances->id)->update([
            "remaining_days" => $new_remaining_days,
            "availed_days" => $leave_balances->availed_days + $total_calendar_days
        ]);

        $email_object = [
            "applicant_name" => $employee->name,
            "applicant_email" => $employee->email,
            "designation" => $employee->designation,
            "department" => $employee->department,
            "leave_type" => $leave_policy->leave_title,
            "start_date" => date("d/m/Y", strtotime($check_start_date)),
            "end_date" => date("d/m/Y", strtotime($check_end_date)),
            "total_days" => $total_calendar_days
        ];

        array_push($recipants_emails, $employee->email);

        app('App\Http\Controllers\NotificationController')->sendEmailForLeave($recipants_emails, $email_object);

        return response()->json([
            'status' => true,
            'message' => 'Leave application submited successful.',
            'data' => []
        ], 200);
    }

    public function getLeaveApplication(Request $request){
        $user_id = $request->user()->id;
        $employee = EmployeeInfo::where('user_id', $user_id)->first();

        $leave_list = LeaveApplications::select(
            'leave_applications.*',
            'leave_policies.leave_title'
        )
        ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
        ->where('employee_id', $employee->id)
        ->orderBy('id', "DESC")
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful1',
            'data' => $leave_list
        ], 200);
    }

    public function getLeaveDetailsByID(Request $request)
    {
        $application_id = $request->leave_application_id ? $request->leave_application_id : 0;

        $leave_details = LeaveApplications::select(
            'leave_applications.*',
            'leave_policies.leave_title'
        )
        ->where('leave_applications.id', $application_id)
        ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
        ->first();

        $leave_flow = LeaveApplicationApprovals::select(
            'leave_application_approvals.approval_id',
            'leave_application_approvals.step',
            'leave_application_approvals.approval_status',
            'leave_application_approvals.step_flag',
            'leave_application_approvals.updated_at',
            'employee_infos.name as authority_name'
        )
        ->where('leave_application_approvals.application_id', $application_id)
        ->leftJoin('employee_infos', 'employee_infos.id', 'leave_application_approvals.approval_id')
        ->get();

        $employee = EmployeeInfo::select('employee_infos.*', 'designations.title as designation', 'departments.name as department', 'users.image', 'users.user_type')
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->where('employee_infos.id', $leave_details->employee_id)
        ->first();

        $fiscal_year = FiscalYear::where('is_active', true)->first();
        $leave_balances = LeaveBalance::select('leave_balances.*', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->where('leave_balances.employee_id', $leave_details->employee_id)
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_balances.leave_policy_id')
            ->where('leave_balances.fiscal_year_id', $fiscal_year->id)
            ->get();

        $response_details = [
            "leave" => $leave_details,
            "leave_flow" => $leave_flow,
            "employee" => $employee,
            "leave_balances" => $leave_balances,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Successful1',
            'data' => $response_details
        ], 200);
    }
}
