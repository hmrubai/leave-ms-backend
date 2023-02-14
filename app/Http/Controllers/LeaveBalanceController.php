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
            ->orderBy('leave_policies.leave_title', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
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
}
