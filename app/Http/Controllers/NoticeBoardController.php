<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\NoticeBoard;
use App\Models\EmployeeInfo;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class NoticeBoardController extends Controller
{
    public function saveOrUpdateNotice (Request $request)
    {
        try {
            if($request->id){
                $validateUser = Validator::make($request->all(), 
                [
                    'title' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'data' => $validateUser->errors()
                    ], 409);
                }

                $user_id = $request->user()->id;
                $employee = EmployeeInfo::where('user_id', $user_id)->first();

                NoticeBoard::where('id', $request->id)->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'created_by' => $employee->id,
                    'notice_type' => $request->notice_type ?? "Medium",
                    'is_active' => $request->is_active
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Notice has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = NoticeBoard::where('title', $request->title)->where('description', $request->description)->first();
                if (empty($isExist)) 
                {
                    $validateUser = Validator::make($request->all(), 
                    [
                        'title' => 'required'
                    ]);

                    if($validateUser->fails()){
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'data' => $validateUser->errors()
                        ], 409);
                    }

                    $user_id = $request->user()->id;
                    $employee = EmployeeInfo::where('user_id', $user_id)->first();

                    NoticeBoard::create([
                        'title' => $request->title,
                        'description' => $request->description,
                        'created_by' => $employee->id,
                        'notice_type' => $request->notice_type ?? "Medium",
                        'is_active' => $request->is_active
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Notice has been added successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Notice already Exist!',
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

    public function noticeList (Request $request)
    {
        $notice_list = NoticeBoard::where("is_active", true)->orderBy('id', 'ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $notice_list
        ], 200);
    }

    public function deleteNotice(Request $request)
    {
        $validateRequest = Validator::make($request->all(), 
        [
            'id' => 'required'
        ]);

        NoticeBoard::where('id', $request->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Notice has been deleted successful',
            'data' => []
        ], 200);
    }
}
