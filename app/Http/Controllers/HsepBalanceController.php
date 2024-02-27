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
use App\Models\LeaveCutExplanation;
use App\Models\LeaveBalanceSetting;

use App\Models\HsepBalanceSetting;
use App\Models\HsepBalanceAddedHistory;
use App\Models\HsepBalanceAddedHistoryDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HsepBalanceController extends Controller
{
    public function addHsepBalance(Request $request)
    {
        $user_id = $request->user()->id;

        $month = date('m');
        $year = date('Y');

        $is_added = HsepBalanceAddedHistory::where('month_in_number', $month)->where('year', $year)->first();
        if($is_added){
            return response()->json([
                'status' => false,
                'message' => 'Balance has already been added!',
                'data' => []
            ], 422);
        }

        $hsep_balance_add = HsepBalanceAddedHistory::create([
            'month_in_number' => $month,
            'year' => $year,
            'added_by' => $user_id,
            'added_at' => date("Y-m-d H:i:s")
        ]);

        $employee_list = EmployeeInfo::where('is_hsep', 1)->get();
        $fiscal_year = FiscalYear::where('is_active', true)->first();

        $leave_policy = LeavePolicy::all();

        foreach ($employee_list as $employee) {

            foreach ($leave_policy as $policy) {

                $hsep_balance = HsepBalanceSetting::where('leave_policy_id', $policy->id)->first();

                $isBalanceExist = LeaveBalance::where('employee_id', $employee->id)->where('leave_policy_id', $policy->id)->where('fiscal_year_id', $fiscal_year->id)->first();
                if($isBalanceExist){
                    LeaveBalance::where('id', $isBalanceExist->id)->update([
                        'remaining_days' => $isBalanceExist->remaining_days + $hsep_balance->total_days,
                        'total_days' => $isBalanceExist->total_days + $hsep_balance->total_days
                    ]);
                }

                HsepBalanceAddedHistoryDetail::create([
                    'hsep_balance_added_history_id' => $hsep_balance_add->id,
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'leave_policy_id' => $policy->id,
                    'added_balances' => $hsep_balance->total_days
                ]);

                // $isBalanceExist = LeaveBalance::where('employee_id', $employee->id)->where('leave_policy_id', $policy->id)->where('fiscal_year_id', $fiscal_year->id)->first();
                // return response()->json([
                //     'status' => true,
                //     'message' => 'Monthly Balance added successful',
                //     'data' => $isBalanceExist
                // ], 200);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Monthly Balance added successful',
            'data' => $employee_list
        ], 200);
    }

    public function hsepBalanceHistory(Request $request){
        $added_list = HsepBalanceAddedHistory::select('hsep_balance_added_histories.*', 'employee_infos.name as user_name')
            ->leftJoin('employee_infos', 'employee_infos.user_id', 'hsep_balance_added_histories.added_by')
            ->orderBy('hsep_balance_added_histories.id', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $added_list
        ], 200);
    }

    //Hsep Leave Balance List
    public function hsepLeaveBalanceList(Request $request)
    {
        $setting_list = HsepBalanceSetting::select('hsep_balance_settings.*', 'companies.name as company_name', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->leftJoin('companies', 'companies.id', 'hsep_balance_settings.company_id')
            ->leftJoin('leave_policies', 'leave_policies.id', 'hsep_balance_settings.leave_policy_id')
            ->where('leave_policies.is_active', true)
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $setting_list
        ], 200);
    }
}
