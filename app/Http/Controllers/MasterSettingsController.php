<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
// use App\Models\Grade;
// use App\Models\Country;
// use App\Models\Category;
// use App\Models\PackageType;

// use App\Models\Correction;
// use App\Models\CorrectionRating;
// use App\Models\Payment;
// use App\Models\PaymentDetail;
// use App\Models\TopicConsume;

use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{
    // public function trancateData(Request $request)
    // {
    //     Correction::truncate();
    //     CorrectionRating::truncate();
    //     Payment::truncate();
    //     PaymentDetail::truncate();
    //     TopicConsume::truncate();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Truncated Successful',
    //         'data' => []
    //     ], 200);
    // }

    // public function packageTypeList(Request $request)
    // {
    //     $package_list = PackageType::select('id', 'name', 'price', 'limit')->where('is_active', true)->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $package_list
    //     ], 200);
    // }
    
    // public function gradeList(Request $request)
    // {
    //     $grade_list = Grade::select('id', 'name')->where('is_active', true)->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $grade_list
    //     ], 200);
    // }

    // public function categoryList(Request $request)
    // {
    //     $category_list = Category::select('id', 'name')->where('is_active', true)->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $category_list
    //     ], 200);
    // }

    // public function countryList(Request $request)
    // {
    //     $country_list = Country::select('id', 'country_name')->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $country_list
    //     ], 200);
    // }

    // //Admin Methods
    // public function admin_PackageTypeList(Request $request)
    // {
    //     $package_list = PackageType::all();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $package_list
    //     ], 200);
    // }

    // public function saveOrUpdatePackageType (Request $request)
    // {
    //     try {
    //         if($request->id){
    //             $type = PackageType::where('id', $request->id)->update($request->all());
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Type has been updated successfully',
    //                 'data' => []
    //             ], 200);

    //         } else {
    //             $isExist = PackageType::where('name', $request->name)->first();
    //             if (empty($isExist)) {
    //                 $type = PackageType::create($request->all());
    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => 'Type has been created successfully',
    //                     'data' => []
    //                 ], 200);
    //             }else{
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Type already Exist!',
    //                     'data' => []
    //                 ], 200);
    //             }
    //         }

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'data' => []
    //         ], 200);
    //     }
    // }

    // public function adminGradeList(Request $request)
    // {
    //     $grade_list = Grade::all();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $grade_list
    //     ], 200);
    // }

    // public function saveOrUpdateGrade (Request $request)
    // {
    //     try {
    //         if($request->id){
    //             $type = Grade::where('id', $request->id)->update($request->all());
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Grade has been updated successfully',
    //                 'data' => []
    //             ], 200);

    //         } else {
    //             $isExist = Grade::where('name', $request->name)->first();
    //             if (empty($isExist)) {
    //                 $type = Grade::create($request->all());
    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => 'Grade has been created successfully',
    //                     'data' => []
    //                 ], 200);
    //             }else{
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Grade already Exist!',
    //                     'data' => []
    //                 ], 200);
    //             }
    //         }

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'data' => []
    //         ], 200);
    //     }
    // }

    // public function adminCategoryList(Request $request)
    // {
    //     $category_list = Category::all();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $category_list
    //     ], 200);
    // }

    // public function saveOrUpdateCategory (Request $request)
    // {
    //     try {
    //         if($request->id){
    //             $type = Category::where('id', $request->id)->update($request->all());
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Category has been updated successfully',
    //                 'data' => []
    //             ], 200);

    //         } else {
    //             $isExist = Category::where('name', $request->name)->first();
    //             if (empty($isExist)) {
    //                 $type = Category::create($request->all());
    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => 'Category has been created successfully',
    //                     'data' => []
    //                 ], 200);
    //             }else{
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Category already Exist!',
    //                     'data' => []
    //                 ], 200);
    //             }
    //         }

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'data' => []
    //         ], 200);
    //     }
    // }

    // public function markGradeList(Request $request)
    // {
    //     $grade = ['BelowSatisfaction', 'Satisfactory', 'Good', 'Better', 'Excellent'];

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'List Successful',
    //         'data' => $grade
    //     ], 200);
    // }

}
