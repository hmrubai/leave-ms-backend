<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\LeaveBalance;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use App\Models\LeaveBalanceSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LeaveBalanceController extends Controller
{
    public function saveOrUpdateLeaveBalanceSetting (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'company_id' => 'required',
                    'employment_type_id' => 'required',
                    'leave_policy_id' => 'required',
                    'total_days' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 409);
                }

                LeaveBalanceSetting::where('id', $request->id)->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Setting has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = LeaveBalanceSetting::where('employment_type_id', $request->employment_type_id)
                    ->where('company_id', $request->company_id)
                    ->where('leave_policy_id', $request->leave_policy_id)
                    ->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'company_id' => 'required',
                        'employment_type_id' => 'required',
                        'leave_policy_id' => 'required',
                        'total_days' => 'required'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 409);
                    }

                    LeaveBalanceSetting::create($request->all());
                    return response()->json([
                        'status' => true,
                        'message' => 'Setting has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Setting already Exist!',
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

    public function leaveBalanceSettingList(Request $request)
    {
        $employment_type_id = $request->employment_type_id ? $request->employment_type_id : 0;

        $setting_list = LeaveBalanceSetting::select('leave_balance_settings.*', 'companies.name as company_name', 'employment_types.type as employment_type', 'leave_policies.leave_title', 'leave_policies.leave_short_code')
            ->leftJoin('companies', 'companies.id', 'leave_balance_settings.company_id')
            ->leftJoin('employment_types', 'employment_types.id', 'leave_balance_settings.employment_type_id')
            ->leftJoin('leave_policies', 'leave_policies.id', 'leave_balance_settings.leave_policy_id')
            ->when($employment_type_id, function ($query) use ($employment_type_id){
                return $query->where('leave_balance_settings.employment_type_id', $employment_type_id);
            })
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $setting_list
        ], 200);
    }
}
