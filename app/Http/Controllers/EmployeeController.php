<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\Designation;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{

    public function generateEmployeeCode($company_id, $employee_id){
        $company = Company::where('id', $company_id)->first();

        if(!empty($company))
        {
            return $company->company_prefix . str_pad($employee_id, $company->employee_code_length ? $company->employee_code_length: 4, '0', STR_PAD_LEFT);
        }
        else{
            return 'BB' . str_pad($employee_id, 4, '0', STR_PAD_LEFT);
        }
    }

    public function addLeaveBalance($employee_id)
    {
        $employee_details = EmployeeInfo::where('id', $employee_id)->first();
        $employment_type_id = $employee_details->employment_type_id;
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
            }
        }
        return true;
    }

    public function addManualLeaveBalance(Request $request)
    {
        $employee_id = $request->employee_id;
        $employee_details = EmployeeInfo::where('id', $employee_id)->first();
        $employment_type_id = $employee_details->employment_type_id;
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
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Leave Balance Added Successful',
            'data' => []
        ], 200);
    }

    public function saveEmployee (Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'name' => 'required',
            'father_name' => 'required',
            'mother_name' => 'required',
            'employee_id' => 'required',
            'email' => 'required',
            'mobile' => 'required',
            'nid' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
            'date_of_birth' => 'required',
            'joining_date' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'company_id' => 'required',
            'branch_id' => 'required',
            'department_id' => 'required',
            'employment_type_id' => 'required',
            'designation_id' => 'required',
            'division_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'user_type' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $is_active = false;

        if($request->is_active == "true"){
            $is_active = true;
        }

        $isExist = EmployeeInfo::where('email', $request->email)->first();
        if (empty($isExist)) 
        {
            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user_type = 'Employee';

            if($request->user_type){
                $user_type = $request->user_type;
            }

            $employee_code = $this->generateEmployeeCode($request->company_id, $request->employee_id);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->mobile,
                'employee_code' => $employee_code,
                'company_id' => $request->company_id,
                'address' => $request->present_address,
                'institution' => $request->institution,
                'education' => $request->education,
                'user_type' => $user_type,
                'password' => Hash::make('BB@2023')
            ]);

            $employee = EmployeeInfo::create([
                "user_id" => $user->id,
                "name" => $request->name,
                "email" => $request->email,
                "mobile" => $request->mobile,
                "present_address" => $request->present_address,
                "permanent_address" => $request->permanent_address,
                "mobile" => $request->mobile,
                "father_name" => $request->father_name,
                "mother_name" => $request->mother_name,
                "employee_id" => $request->employee_id,
                "employee_code" => $employee_code,
                "nid" => $request->nid,
                "date_of_birth" => $request->date_of_birth,
                "joining_date" => $request->joining_date,
                "marital_status" => $request->marital_status,
                "gender" => $request->gender,
                "blood_group" => $request->blood_group,
                "company_id" => $request->company_id,
                "branch_id" => $request->branch_id,
                "department_id" => $request->department_id,
                "designation_id" => $request->designation_id,
                "employment_type_id" => $request->employment_type_id,
                "division_id" => $request->division_id,
                "district_id" => $request->district_id,
                "city_id" => $request->city_id,
                "area_id" => $request->area_id,
                "is_stuckoff" => false,
                "is_active" => $is_active,
                "office_contact_number" => $request->office_contact_number,
                "finger_print_id" => $request->finger_print_id,
                "personal_alt_contact_number" => $request->personal_alt_contact_number,
                "personal_email" => $request->personal_email,
                "passport_number" => $request->passport_number,
                "spouse_name" => $request->spouse_name,
                "spouse_number" => $request->spouse_number,
                "fathers_contact_number" => $request->fathers_contact_number,
                "mothers_contact_number" => $request->mothers_contact_number,
                "referee_office" => $request->referee_office,
                "referee_relative" => $request->referee_relative,
                "referee_contact_details" => $request->referee_contact_details,
                "key_skills" => $request->key_skills,
                "highest_level_of_study" => $request->highest_level_of_study,
                "e_tin" => $request->e_tin,
                "applicable_tax_amount" => $request->applicable_tax_amount,
                "official_achievement" => $request->official_achievement,
                "remarks" => $request->remarks,
            ]);

            if($request->hasFile('image')){
                User::where('id', $user->id)->update([
                    'image' => $profile_url
                ]);
            }

            $this->addLeaveBalance($employee->id);

            return response()->json([
                'status' => true,
                'message' => 'Employee has been added successfully',
                'data' => []
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Employee already Exist!',
                'data' => []
            ], 409);
        }
    }

    public function updateEmployee (Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'id' => 'required',
            'name' => 'required',
            'father_name' => 'required',
            'mother_name' => 'required',
            'employee_id' => 'required',
            'mobile' => 'required',
            'nid' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
            'date_of_birth' => 'required',
            'joining_date' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'company_id' => 'required',
            'branch_id' => 'required',
            'department_id' => 'required',
            'designation_id' => 'required',
            'employment_type_id' => 'required',
            'division_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'user_type' => 'required'
        ]);

        $is_active = false;

        if($request->is_active == "true"){
            $is_active = true;
        }

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $isExist = EmployeeInfo::where('id', $request->id)->first();

        if (!empty($isExist)) 
        {
            $user_type = 'Employee';

            if($request->user_type){
                $user_type = $request->user_type;
            }

            $employee_code = $this->generateEmployeeCode($request->company_id, $request->employee_id);

            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user = User::where('id', $isExist->user_id)->update([
                'name' => $request->name,
                'contact_no' => $request->mobile,
                'employee_code' => $employee_code,
                'company_id' => $request->company_id,
                'address' => $request->present_address,
                'institution' => $request->institution,
                'education' => $request->education,
                'user_type' => $user_type
            ]);

            EmployeeInfo::where('id', $request->id)->update([
                "name" => $request->name,
                "mobile" => $request->mobile,
                "present_address" => $request->present_address,
                "permanent_address" => $request->permanent_address,
                "mobile" => $request->mobile,
                "father_name" => $request->father_name,
                "mother_name" => $request->mother_name,
                "employee_id" => $request->employee_id,
                "employee_code" => $employee_code,
                "nid" => $request->nid,
                "date_of_birth" => $request->date_of_birth,
                "joining_date" => $request->joining_date,
                "marital_status" => $request->marital_status,
                "gender" => $request->gender,
                "blood_group" => $request->blood_group,
                "company_id" => $request->company_id,
                "branch_id" => $request->branch_id,
                "designation_id" => $request->designation_id,
                "department_id" => $request->department_id,
                "employment_type_id" => $request->employment_type_id,
                "division_id" => $request->division_id,
                "district_id" => $request->district_id,
                "city_id" => $request->city_id,
                "area_id" => $request->area_id,
                "is_active" => $is_active,
                "office_contact_number" => $request->office_contact_number,
                "finger_print_id" => $request->finger_print_id,
                "personal_alt_contact_number" => $request->personal_alt_contact_number,
                "personal_email" => $request->personal_email,
                "passport_number" => $request->passport_number,
                "spouse_name" => $request->spouse_name,
                "spouse_number" => $request->spouse_number,
                "fathers_contact_number" => $request->fathers_contact_number,
                "mothers_contact_number" => $request->mothers_contact_number,
                "referee_office" => $request->referee_office,
                "referee_relative" => $request->referee_relative,
                "referee_contact_details" => $request->referee_contact_details,
                "key_skills" => $request->key_skills,
                "highest_level_of_study" => $request->highest_level_of_study,
                "e_tin" => $request->e_tin,
                "applicable_tax_amount" => $request->applicable_tax_amount,
                "official_achievement" => $request->official_achievement,
                "remarks" => $request->remarks,
            ]);

            if($request->hasFile('image')){
                $existing_user = User::where('id', $isExist->user_id)->first();
                if($existing_user->image){
                    unlink($existing_user->image);
                }

                $existing_user->update([
                    'image' => $profile_url
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Employee has been updated successfully',
                'data' => []
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Employee does not exist!',
                'data' => []
            ], 200);
        }
    }

    public function employeeList (Request $request)
    {
        $employee_list = EmployeeInfo::select('employee_infos.*', 'designations.title as designation', 'departments.name as department', 'users.image', 'users.user_type')
        ->leftJoin('users', 'users.id', 'employee_infos.user_id')
        ->leftJoin('designations', 'designations.id', 'employee_infos.designation_id')
        ->leftJoin('departments', 'departments.id', 'employee_infos.department_id')
        ->orderBy('employee_infos.name', 'ASC')
        ->get();
        
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee_list
        ], 200);
    }

    public function employeeDetailsByID (Request $request)
    {
        $employee_id = $request->employee_id ? $request->employee_id : 0;

        if(!$employee_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Employee ID',
                'data' => []
            ], 200);
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
        
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $employee
        ], 200);
    }

}