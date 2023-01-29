<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\LeavePolicy;
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
                    ], 401);
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
                        ], 401);
                    }

                    LeavePolicy::create($request->all());
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
                    ], 200);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 200);
        }
    }

    // 'company_id',
    // 'leave_title',
    // 'leave_short_code',
    // 'total_days',
    // 'is_applicable_for_all',
    // 'applicable_for',
    // 'is_leave_cut_applicable',
    // 'is_carry_forward',
    // 'is_document_upload',
    // 'is_holiday_deduct',
    // 'document_upload_after_days',
    // 'max_carry_forward_days',
    // 'is_active',
}
