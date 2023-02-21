<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\LeaveApprovelFlowSetting;
use App\Models\LeaveBalanceSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LeaveApprovalFlowSetupController extends Controller
{
    public function addApprovalFlow(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'employee_ids' => 'required',
            'steps' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        if(!sizeof($request->steps)){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach approval flow!',
                'data' => []
            ], 200);
        }
        
        $step_count = 1;
        $insert_data = [];
        foreach ($request->steps as $step) {
            foreach ($request->employee_ids as $emp_id){

                LeaveApprovelFlowSetting::where('employee_id', $emp_id)->update([
                    "is_active" => false
                ]);

                array_push($insert_data, [
                    "employee_id" => $emp_id,
                    "approval_authority_id" => $step['authority_id'],
                    "step" => $step_count,
                    "is_active" => true
                ]);
            }
            $step_count++;
        }

        LeaveApprovelFlowSetting::insert($insert_data);

        return response()->json([
            'status' => true,
            'message' => 'Leave approval flow added successful',
            'data' => []
        ], 200);
    }

    public function approvalFlowList(Request $request)
    {
        $employee_id = $request->employee_id ? $request->employee_id : 0;
        $approval_list = LeaveApprovelFlowSetting::select(
            'leave_approvel_flow_settings.id',
            'leave_approvel_flow_settings.employee_id',
            'leave_approvel_flow_settings.approval_authority_id',
            'leave_approvel_flow_settings.step',
            'leave_approvel_flow_settings.is_active',
            'leave_approvel_flow_settings.employee_id',
            'employee.name as employee_name',
            'employee.email as employee_email',
            'authority.name as authority_name',
            'authority.email as authority_email'
        )
        ->when($employee_id, function ($query) use ($employee_id){
            return $query->where('leave_approvel_flow_settings.employee_id', $employee_id);
        })
        ->where('leave_approvel_flow_settings.is_active', true)
        ->leftJoin('employee_infos as employee', 'employee.id', 'leave_approvel_flow_settings.employee_id')
        ->leftJoin('employee_infos as authority', 'authority.id', 'leave_approvel_flow_settings.approval_authority_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $approval_list
        ], 200);
    }

    public function updateApprovalFlow(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'id' => 'required',
            'approval_authority_id' => 'required',
            'employee_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $is_exist = LeaveApprovelFlowSetting::where('employee_id', $request->employee_id)->where('is_active', true)->where('id', '!=', $request->id)->first();

        if(!empty($is_exist)){
            return response()->json([
                'status' => false,
                'message' => 'Approval authority already exist in flow!',
                'data' => []
            ], 200);
        }else{
            LeaveApprovelFlowSetting::where('id', $request->id)->update([
                'approval_authority_id' => $request->approval_authority_id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave approval flow updated successful',
                'data' => []
            ], 200);
        }

    }
}
