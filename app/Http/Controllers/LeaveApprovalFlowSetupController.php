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
use PhpParser\Node\Stmt\Foreach_;

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
}
