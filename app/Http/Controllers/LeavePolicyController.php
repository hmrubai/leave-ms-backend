<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class LeavePolicyController extends Controller
{
    public function saveOrUpdateLeavePolicy (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'leave_title' => 'required',
                    'leave_short_code' => 'required',
                    'total_days' => 'required',
                    'company_id' => 'required',
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 409);
                }

                LeavePolicy::where('id', $request->id)->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Leave Policy has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = LeavePolicy::where('leave_title', $request->leave_title)->where('company_id', $request->company_id)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'leave_title' => 'required',
                        'leave_short_code' => 'required',
                        'total_days' => 'required',
                        'company_id' => 'required',
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 409);
                    }

                    $policy = LeavePolicy::create($request->all());

                    $fiscal_year = FiscalYear::where('is_active', true)->first();
                    $fiscal_year_id = $fiscal_year->id; 

                    $employees = EmployeeInfo::where('is_active', true)->where('is_stuckoff', false)->get();

                    foreach ($employees as $employee) {
                        if($policy->is_applicable_for_all){
                            LeaveBalance::create([
                                'employee_id' => $employee->id,
                                'user_id' => $employee->user_id,
                                'leave_policy_id' => $policy->id,
                                'fiscal_year_id' => $fiscal_year_id,
                                'total_days' => $policy->total_days,
                                'availed_days' => 0,
                                'remaining_days' => $policy->total_days,
                                'carry_forward_balance' => 0,
                                'is_active' => true
                            ]);
                        }else{
                            if($policy->applicable_for == 'Male' && $employee->gender == 'Male')
                            {
                                LeaveBalance::create([
                                    'employee_id' => $employee->id,
                                    'user_id' => $employee->user_id,
                                    'leave_policy_id' => $policy->id,
                                    'fiscal_year_id' => $fiscal_year_id,
                                    'total_days' => $policy->total_days,
                                    'availed_days' => 0,
                                    'remaining_days' => $policy->total_days,
                                    'carry_forward_balance' => 0,
                                    'is_active' => true
                                ]); 
                            }
    
                            if($policy->applicable_for == 'Female' && $employee->gender == 'Female')
                            {
                                LeaveBalance::create([
                                    'employee_id' => $employee->id,
                                    'user_id' => $employee->user_id,
                                    'leave_policy_id' => $policy->id,
                                    'fiscal_year_id' => $fiscal_year_id,
                                    'total_days' => $policy->total_days,
                                    'availed_days' => 0,
                                    'remaining_days' => $policy->total_days,
                                    'carry_forward_balance' => 0,
                                    'is_active' => true
                                ]); 
                            }
                        }
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Leave Policy has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Leave Policy already Exist!',
                        'data' => []
                    ], 409);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 400);
        }
    }

    public function leavePolicyList (Request $request)
    {
        $lp_list = LeavePolicy::where("is_active", true)
        ->orderBy('leave_policies.leave_title', 'ASC')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $lp_list
        ], 200);
    }

    public function leavePolicyListByCompanyID (Request $request)
    {
        $company_id = $request->company_id ? $request->company_id : 0;

        $lp_list = LeavePolicy::where("is_active", true)
        ->when($company_id, function ($query) use ($company_id){
            return $query->where('leave_policies.company_id', $company_id);
        })
        ->orderBy('leave_policies.leave_title', 'ASC')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $lp_list
        ], 200);
    }

    public function userLeavePolicyList (Request $request)
    {
        $user_id = $request->user()->id;

        $employee = EmployeeInfo::where('user_id', $user_id)->first();
        $fiscal_year = FiscalYear::where('is_active', true)->first();

        $leave_policy_ids = LeaveBalance::where("is_active", true)
            ->where("employee_id", $employee->id)
            ->where('fiscal_year_id', $fiscal_year->id)
            ->pluck('leave_policy_id');
        
        $lp_list = LeavePolicy::where("is_active", true)->whereIn("id", $leave_policy_ids)
        ->orderBy('leave_policies.leave_title', 'ASC')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $lp_list
        ], 200);
    }
}
