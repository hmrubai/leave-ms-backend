<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function saveOrUpdateCompany (Request $request)
    {
        try {
            if($request->id)
            {
                $validateUser = Validator::make($request->all(), 
                [
                    'name' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 401);
                }

                $company_logo = null;
                if($request->hasFile('file')){
                    $image = $request->file('file');
                    $time = time();
                    $company_image = "company_image_" . $time . '.' . $image->getClientOriginalExtension();
                    $destination = 'uploads/company_image';
                    $image->move($destination, $company_image);
                    $company_logo = $destination . '/' . $company_image;
                }

                Company::where('id', $request->id)->update([
                    "name" => $request->name,
                    "address" => $request->address,
                    "contact_no" => $request->contact_no,
                    "company_email" => $request->company_email,
                    "hr_email" => $request->hr_email,
                    "leave_email" => $request->leave_email,
                    "employee_code_length" => $request->employee_code_length,
                    "company_prefix" => $request->company_prefix,
                    "is_active" => $request->is_active
                ]);

                if($request->hasFile('file')){
                    Company::where('id', $request->id)->update([
                        'company_logo' => $company_logo
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Company has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = Company::where('name', $request->name)->where('company_email', $request->company_email)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'name' => 'required',
                        'company_email' => 'required|email|unique:companies'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 401);
                    }

                    $company_logo = null;
                    if($request->hasFile('file')){
                        $image = $request->file('file');
                        $time = time();
                        $company_image = "company_image_" . $time . '.' . $image->getClientOriginalExtension();
                        $destination = 'uploads/company_image';
                        $image->move($destination, $company_image);
                        $company_logo = $destination . '/' . $company_image;
                    }

                    $company = Company::create([
                        "name" => $request->name,
                        "address" => $request->address,
                        "contact_no" => $request->contact_no,
                        "company_email" => $request->company_email,
                        "hr_email" => $request->hr_email,
                        "leave_email" => $request->leave_email,
                        "employee_code_length" => $request->employee_code_length,
                        "company_prefix" => $request->company_prefix,
                        "is_active" => $request->is_active
                    ]);

                    if($request->hasFile('file')){
                        Company::where('id', $company->id)->update([
                            'company_logo' => $company_logo
                        ]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Company has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Company already exist!',
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

    public function companyList (Request $request)
    {
        $company_list = Company::where("is_active", true)->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $company_list
        ], 200);
    }

    public function saveOrUpdateBranch (Request $request)
    {
        try {
            if($request->id)
            {
                $validateUser = Validator::make($request->all(), 
                [
                    'name' => 'required',
                    'company_id' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 401);
                }

                $isCompanyExist = Company::where('id', $request->company_id)->first();

                if(empty($isCompanyExist)){
                    return response()->json([
                        'status' => false,
                        'message' => 'Company not found!',
                        'data' => []
                    ], 200);
                }

                Branch::where('id', $request->id)->update([
                    "name" => $request->name,
                    "address" => $request->address,
                    "contact_no" => $request->contact_no,
                    "company_id" => $request->company_id,
                    "is_active" => $request->is_active
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Branch has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = Branch::where('name', $request->name)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'name' => 'required',
                        'company_id' => 'required'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 401);
                    }

                    $isCompanyExist = Company::where('id', $request->company_id)->first();

                    if(empty($isCompanyExist)){
                        return response()->json([
                            'status' => false,
                            'message' => 'Company not found!',
                            'data' => []
                        ], 200);
                    }

                    Branch::create([
                        "name" => $request->name,
                        "address" => $request->address,
                        "contact_no" => $request->contact_no,
                        "company_id" => $request->company_id,
                        "is_active" => $request->is_active
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Branch has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Branch already exist!',
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

    public function branchList (Request $request)
    {
        $branch_list = Branch::where("is_active", true)->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $branch_list
        ], 200);
    }

    public function branchListByCompanyID (Request $request)
    {
        $branch_list = Branch::where('company_id', $request->company_id)->where("is_active", true)->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $branch_list
        ], 200);
    }

    public function saveOrUpdateDepartment (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'name' => 'required',
                    'company_id' => 'required',
                    'branch_id' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 401);
                }

                Department::where('id', $request->id)->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Department has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = Department::where('name', $request->name)->where('company_id', $request->company_id)->where('branch_id', $request->branch_id)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'name' => 'required',
                        'company_id' => 'required',
                        'branch_id' => 'required'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 401);
                    }

                    Department::create($request->all());
                    return response()->json([
                        'status' => true,
                        'message' => 'Department has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Department already Exist!',
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

    public function departmentList (Request $request)
    {
        $department_list = Department::where("is_active", true)->where("is_active", true)->orderBy('name', 'ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $department_list
        ], 200);
    }

    public function departmentListByID (Request $request)
    {
        $company_id = $request->company_id;
        $branch_id = $request->branch_id;

        if(!$company_id || !$branch_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Company ID Or Branch ID',
                'data' => []
            ], 200);
        }

        $department_list = Department::where('company_id', $company_id)->where('branch_id', $branch_id)->where("is_active", true)->orderBy('name', 'ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $department_list
        ], 200);
    }
}
