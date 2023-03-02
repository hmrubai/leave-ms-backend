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
}
