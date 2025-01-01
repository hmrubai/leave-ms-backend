<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\HsepBalanceSetting;
use App\Models\FiscalYear;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\LeaveCutExplanation;
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
            ->where('leave_policies.is_active', true)
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $setting_list
        ], 200);
    }

    public function employeeLeaveBalanceList(Request $request)
    {
        $fiscal_year = FiscalYear::where('is_active', true)->first();
        $fiscal_year_id = $fiscal_year->id;
        $employee_id = $request->employee_id ? $request->employee_id : 0;

        $employee = EmployeeInfo::select(
            'employee_infos.*', 
            'designations.title as designation', 
            'departments.name as department', 
            'users.image',
            'users.institution',
            'users.education',
            'users.user_type',
            'companies.name as company_name',
            'branches.name as branch_name',
            'divisions.name as division_name',
            'districts.name as district_name',
            'upazilas.name as city_name',
            'unions.name as area_name',
            'employment_types.type as employment_type'
        )
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('companies', 'companies.id', 'employee_infos.company_id')
        ->leftJoin('branches', 'branches.id', 'employee_infos.branch_id')
        ->leftJoin('employment_types', 'employment_types.id', 'employee_infos.employment_type_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->leftJoin('divisions', 'divisions.id', 'employee_infos.division_id')
        ->leftJoin('districts', 'districts.id', 'employee_infos.district_id')
        ->leftJoin('upazilas', 'upazilas.id', 'employee_infos.city_id')
        ->leftJoin('unions', 'unions.id', 'employee_infos.area_id')
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
            ->where('leave_policies.is_active', true)
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        foreach ($employee->balance_list as $item) {
            $item->cutting_explanation = LeaveCutExplanation::where('leave_balance_id', $item->id)->get();
            $item->has_cutting_history = LeaveCutExplanation::where('leave_balance_id', $item->id)->get()->count() ? true : false;
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
        ], 200);
    }

    public function employeePreviousLeaveBalanceList(Request $request)
    {
        $fiscal_year_id = $request->fiscal_year_id ? $request->fiscal_year_id : 0;
        $leave_policy_id = $request->leave_policy_id ? $request->leave_policy_id : 0;
        $employee_id = $request->employee_id ? $request->employee_id : 0;

        if($fiscal_year_id == 0){
            $fiscal_year = FiscalYear::where('is_active', true)->first();
            $fiscal_year_id = $fiscal_year->id; 
        }

        if(!$employee_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Employee ID',
                'data' => []
            ], 409);
        }

        $employee = EmployeeInfo::select(
            'employee_infos.*', 
            'designations.title as designation', 
            'departments.name as department', 
            'users.image',
            'users.institution',
            'users.education',
            'users.user_type',
            'companies.name as company_name',
            'branches.name as branch_name',
            'divisions.name as division_name',
            'districts.name as district_name',
            'upazilas.name as city_name',
            'unions.name as area_name',
            'employment_types.type as employment_type'
        )
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('companies', 'companies.id', 'employee_infos.company_id')
        ->leftJoin('branches', 'branches.id', 'employee_infos.branch_id')
        ->leftJoin('employment_types', 'employment_types.id', 'employee_infos.employment_type_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->leftJoin('divisions', 'divisions.id', 'employee_infos.division_id')
        ->leftJoin('districts', 'districts.id', 'employee_infos.district_id')
        ->leftJoin('upazilas', 'upazilas.id', 'employee_infos.city_id')
        ->leftJoin('unions', 'unions.id', 'employee_infos.area_id')
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
            ->when($leave_policy_id, function ($query) use ($leave_policy_id){
                return $query->where('leave_balances.leave_policy_id', $leave_policy_id);
            })
            ->where('leave_policies.is_active', true)
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        foreach ($employee->balance_list as $item) {
            $item->cutting_explanation = LeaveCutExplanation::where('leave_balance_id', $item->id)->get();
            $item->has_cutting_history = LeaveCutExplanation::where('leave_balance_id', $item->id)->get()->count() ? true : false;
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
        ], 200);
    }

    public function leaveBalanceUpdate2024(Request $request){
        $employee = EmployeeInfo::where('is_stuckoff', false)->get();
        $sl_balance = 9.36;
        $cl_balance = 6.64;
        $al_balance = 10;
        
        $leave_balance = LeaveBalance::whereIn('leave_policy_id', [1,2])->where('fiscal_year_id', 4)->get();

        foreach($leave_balance as $item){
            if($item->leave_policy_id == 1){
                LeaveBalance::where('id', $item->id)->update([
                    'total_days' => $sl_balance,
                    'remaining_days' => $sl_balance - $item->availed_days,
                ]);
            }

            if($item->leave_policy_id == 2){
                LeaveBalance::where('id', $item->id)->update([
                    'total_days' => $cl_balance,
                    'remaining_days' => $cl_balance - $item->availed_days,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $leave_balance
        ], 200);
    }

    public function myLeaveBalanceList(Request $request)
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
            'companies.name as company_name',
            'branches.name as branch_name',
            'divisions.name as division_name',
            'districts.name as district_name',
            'upazilas.name as city_name',
            'unions.name as area_name',
            'employment_types.type as employment_type'
        )
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('companies', 'companies.id', 'employee_infos.company_id')
        ->leftJoin('branches', 'branches.id', 'employee_infos.branch_id')
        ->leftJoin('employment_types', 'employment_types.id', 'employee_infos.employment_type_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->leftJoin('divisions', 'divisions.id', 'employee_infos.division_id')
        ->leftJoin('districts', 'districts.id', 'employee_infos.district_id')
        ->leftJoin('upazilas', 'upazilas.id', 'employee_infos.city_id')
        ->leftJoin('unions', 'unions.id', 'employee_infos.area_id')
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
            ->where('leave_policies.is_active', true)
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();
        
        foreach ($employee->balance_list as $item) {
            $item->cutting_explanation = LeaveCutExplanation::where('leave_balance_id', $item->id)->get();
            $item->has_cutting_history = LeaveCutExplanation::where('leave_balance_id', $item->id)->get()->count() ? true : false;
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
        ], 200);
    }

    public function addLeaveBalanceManually(Request $request)
    {
        $employee_id = $request->employee_id ? $request->employee_id : 0;
        $employment_type_id = $request->employment_type_id ? $request->employment_type_id : 0;

        if(!$employee_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Employee ID',
                'data' => []
            ], 409);
        }

        if(!$employment_type_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Employment Type',
                'data' => []
            ], 409);
        }

        $employee_details = EmployeeInfo::where('id', $employee_id)->first();
        $fiscal_year = FiscalYear::where('is_active', true)->first();
        $leave_policy = LeavePolicy::all();

        foreach ($leave_policy as $policy) {
            $isBalanceExist = LeaveBalance::where('employee_id', $employee_id)->where('leave_policy_id', $policy->id)->where('fiscal_year_id', $fiscal_year->id)->first();

            if(!$isBalanceExist){
                $setting = LeaveBalanceSetting::where('leave_policy_id', $policy->id)->where('employment_type_id', $employment_type_id)->first();
             
                if(!empty($setting)){

                    if($policy->is_applicable_for_all){
                        LeaveBalance::create([
                            'employee_id' => $employee_id,
                            'user_id' => $employee_details->user_id,
                            'leave_policy_id' => $policy->id,
                            'fiscal_year_id' => $fiscal_year->id,
                            'total_days' => $setting->total_days,
                            'availed_days' => 0,
                            'remaining_days' => $setting->total_days,
                            'carry_forward_balance' => 0,
                            'is_active' => true
                        ]);
                    }else{
                        if($policy->applicable_for == 'Male' && $employee_details->gender == 'Male')
                        {
                            LeaveBalance::create([
                                'employee_id' => $employee_id,
                                'user_id' => $employee_details->user_id,
                                'leave_policy_id' => $policy->id,
                                'fiscal_year_id' => $fiscal_year->id,
                                'total_days' => $setting->total_days,
                                'availed_days' => 0,
                                'remaining_days' => $setting->total_days,
                                'carry_forward_balance' => 0,
                                'is_active' => true
                            ]); 
                        }

                        if($policy->applicable_for == 'Female' && $employee_details->gender == 'Female')
                        {
                            LeaveBalance::create([
                                'employee_id' => $employee_id,
                                'user_id' => $employee_details->user_id,
                                'leave_policy_id' => $policy->id,
                                'fiscal_year_id' => $fiscal_year->id,
                                'total_days' => $setting->total_days,
                                'availed_days' => 0,
                                'remaining_days' => $setting->total_days,
                                'carry_forward_balance' => 0,
                                'is_active' => true
                            ]); 
                        }

                    }
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Leave balance already added! You can update!',
                    'data' => []
                ], 409);
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Leave Balance added successfull!',
            'data' => []
        ], 200);
    }

    public function addLeaveBalanceForSingleTypeManually(Request $request)
    {
        $leave_policy_id = $request->leave_policy_id ? $request->leave_policy_id : 0;
        $employee_list = EmployeeInfo::all();
        $fiscal_year = FiscalYear::where('is_active', true)->first();
        $leave_policy = LeavePolicy::where('id', $leave_policy_id)->first();

        foreach ($employee_list as $employee) {
            $isBalanceExist = LeaveBalance::where('employee_id', $employee->id)->where('leave_policy_id', $leave_policy_id)->where('fiscal_year_id', $fiscal_year->id)->first();

            if(!$isBalanceExist){

                $setting = LeaveBalanceSetting::where('leave_policy_id', $leave_policy_id)->where('employment_type_id', $employee->employment_type_id)->first();
                if(!empty($setting)){

                    if($leave_policy->is_applicable_for_all){
                        LeaveBalance::create([
                            'employee_id' => $employee->id,
                            'user_id' => $employee->user_id,
                            'leave_policy_id' => $leave_policy_id,
                            'fiscal_year_id' => $fiscal_year->id,
                            'total_days' => $setting->total_days,
                            'availed_days' => 0,
                            'remaining_days' => $setting->total_days,
                            'carry_forward_balance' => 0,
                            'is_active' => true
                        ]);
                    }else{
                        if($leave_policy->applicable_for == 'Male' && $employee->gender == 'Male')
                        {
                            LeaveBalance::create([
                                'employee_id' => $employee->id,
                                'user_id' => $employee->user_id,
                                'leave_policy_id' => $leave_policy_id,
                                'fiscal_year_id' => $fiscal_year->id,
                                'total_days' => $setting->total_days,
                                'availed_days' => 0,
                                'remaining_days' => $setting->total_days,
                                'carry_forward_balance' => 0,
                                'is_active' => true
                            ]); 
                        }

                        if($leave_policy->applicable_for == 'Female' && $employee->gender == 'Female')
                        {
                            LeaveBalance::create([
                                'employee_id' => $employee->id,
                                'user_id' => $employee->user_id,
                                'leave_policy_id' => $leave_policy_id,
                                'fiscal_year_id' => $fiscal_year->id,
                                'total_days' => $setting->total_days,
                                'availed_days' => 0,
                                'remaining_days' => $setting->total_days,
                                'carry_forward_balance' => 0,
                                'is_active' => true
                            ]); 
                        }

                    }
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Leave Balance added successfull!',
            'data' => []
        ], 200);
    }

    public function updateEmployeeLeaveBalance(Request $request)
    {
        LeaveBalance::where('id', $request->id)->update([
            'total_days' => $request->total_days,
            'availed_days' => $request->availed_days,
            'remaining_days' => $request->remaining_days
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave balance updated successful',
            'data' => []
        ], 200);
    }

    public function cutEmployeeLeaveBalance(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'id' => 'required',
            'total_cutting_days' => 'required',
            'note' => 'required'
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $leave_balance = LeaveBalance::where('id', $request->id)->first();

        if($leave_balance->remaining_days < $request->total_cutting_days){
            return response()->json([
                'status' => false,
                'message' => 'Please, enter correct value!',
                'data' => []
            ], 409);
        }

        LeaveCutExplanation::create([
            'employee_id' => $leave_balance->employee_id,
            'user_id' => $leave_balance->user_id,
            'leave_policy_id' => $leave_balance->leave_policy_id,
            'leave_balance_id' => $request->id,
            'fiscal_year_id' => $leave_balance->fiscal_year_id,
            'total_cutting_days' => $request->total_cutting_days,
            'note' => $request->note,
        ]);

        LeaveBalance::where('id', $request->id)->update([
            'availed_days' => $leave_balance->availed_days + $request->total_cutting_days,
            'remaining_days' => $leave_balance->remaining_days - $request->total_cutting_days
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave balance updated successful',
            'data' => []
        ], 200);
    }

    public function resolvedCuttingLeaveBalance(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'id' => 'required'
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateRequest->errors()
            ], 409);
        }

        $cut_explanation = LeaveCutExplanation::where('id', $request->id)->first();

        if(empty($cut_explanation)){
            return response()->json([
                'status' => false,
                'message' => 'Explanation not found!',
                'data' => []
            ], 404);
        }

        $leave_balance = LeaveBalance::where('id', $cut_explanation->leave_balance_id)->first();

        LeaveBalance::where('id', $cut_explanation->leave_balance_id)->update([
            'availed_days' => $leave_balance->availed_days - $cut_explanation->total_cutting_days,
            'remaining_days' => $leave_balance->remaining_days + $cut_explanation->total_cutting_days
        ]);

        LeaveCutExplanation::where('id', $request->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Explanation has been deleted successful',
            'data' => []
        ], 200);
    }

    public function shiftFiscalYear(Request $request){
        $employees = EmployeeInfo::where('is_stuckoff', false)->get();

        foreach($employees as $employee){
            $leave_balance = LeaveBalance::where('fiscal_year_id', 4)->where('employee_id', $employee->id)->get();

            foreach($leave_balance as $balance){

                if($balance->leave_policy_id == 3){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => $balance->remaining_days,
                        'availed_days' => 0,
                        'remaining_days' => $balance->remaining_days,
                        'carry_forward_balance' => $balance->remaining_days,
                        'is_active' => true
                    ]); 
                }
                elseif($balance->leave_policy_id == 4){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 120,
                        'availed_days' => 0,
                        'remaining_days' => 120,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
                elseif($balance->leave_policy_id == 5){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 10,
                        'availed_days' => 0,
                        'remaining_days' => 10,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
                elseif($balance->leave_policy_id == 6){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 7,
                        'availed_days' => 0,
                        'remaining_days' => 7,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
                elseif($balance->leave_policy_id == 8){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 7,
                        'availed_days' => 0,
                        'remaining_days' => 7,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
                elseif($balance->leave_policy_id == 10){
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 10,
                        'availed_days' => 0,
                        'remaining_days' => 10,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
                else{
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'leave_policy_id' => $balance->leave_policy_id,
                        'fiscal_year_id' => 5,
                        'total_days' => 0,
                        'availed_days' => 0,
                        'remaining_days' => 0,
                        'carry_forward_balance' => 0,
                        'is_active' => true
                    ]); 
                }
            }

        }

        return response()->json([
            'status' => true,
            'message' => 'Leave Balance Forworded Successful',
            'data' => []
        ], 200);
    }

}

