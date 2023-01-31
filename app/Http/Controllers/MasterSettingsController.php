<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Designation;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{

    public function saveOrUpdateDesignation (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'title' => 'required',
                    'company_id' => 'required',
                    'branch_id' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 409);
                }

                Designation::where('id', $request->id)->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Designation has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = Designation::where('title', $request->title)->where('company_id', $request->company_id)->where('branch_id', $request->branch_id)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'title' => 'required',
                        'company_id' => 'required',
                        'branch_id' => 'required'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 409);
                    }

                    Designation::create($request->all());
                    return response()->json([
                        'status' => true,
                        'message' => 'Designation has been created successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Designation already Exist!',
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

    public function designationList (Request $request)
    {
        $designation_list = Designation::select(
            'designations.*',
            'companies.name as company_name',
            'branches.name as branch_name'
        )
        ->leftJoin('companies', 'companies.id', 'designations.company_id')
        ->leftJoin('branches', 'branches.id', 'designations.branch_id')
        ->orderBy('designations.title', 'ASC')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $designation_list
        ], 200);
    }

    public function designationListByID (Request $request)
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

        $designation_list = Designation::select(
                'designations.*',
                'companies.name as company_name',
                'branches.name as branch_name'
            )
        ->when($company_id, function ($query) use ($company_id){
            return $query->where('designations.company_id', $company_id);
        })
        ->when($branch_id, function ($query) use ($branch_id){
            return $query->where('designations.branch_id', $branch_id);
        })
        ->orderBy('designations.title', 'ASC')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $designation_list
        ], 200);
    }

    public function saveOrUpdateFiscalYear (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'fiscal_year' => 'required',
                    'company_id' => 'required',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 409);
                }

                FiscalYear::where('id', $request->id)->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Fiscal Year has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = FiscalYear::where('fiscal_year', $request->fiscal_year)->where('company_id', $request->company_id)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'fiscal_year' => 'required',
                        'company_id' => 'required',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 409);
                    }

                    $fiscal_year = FiscalYear::where('is_active', true)->get();
                    foreach ($fiscal_year as $item) {
                        FiscalYear::where('id', $item->id)->update([
                            'is_active' => false
                        ]);
                    }

                    FiscalYear::create($request->all());
                    return response()->json([
                        'status' => true,
                        'message' => 'Fiscal Year has been added successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Fiscal Year already Exist!',
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

    public function fiscalYearListByID (Request $request)
    {
        $company_id = $request->company_id;

        if(!$company_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach Company ID',
                'data' => []
            ], 200);
        }

        $fiscal_year_list = FiscalYear::where('company_id', $company_id)->where("is_active", true)->orderBy('fiscal_year', 'ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $fiscal_year_list
        ], 200);
    }

}
