<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\EmployeeInfo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    public function uploadPunchLog(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'data' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'No data found! Please attach data!',
                'data' => $validateUser->errors()
            ], 409);
        }

        $insert_data = [];

        foreach($request->data as $item)
        {
            $timestamps = explode(";", $item['punch_time']);
            $start_time = min($timestamps);
            $end_time = max($timestamps);

            $start = Carbon::createFromTimeString($start_time);
            $end = Carbon::createFromTimeString($end_time);
            $diff = $start->diff($end);
            $total_time = $diff->format('%H:%I:%S');

            array_push($insert_data, [
                'finger_print_id' => $item['employee_id'],
                'log_date' => $item['punch_date'],
                'punch_log' => $item['punch_time'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'total_time' => $total_time,
                'is_processed' => false
            ]);
        }

        if(sizeof($insert_data)){
            AttendanceLog::insert($insert_data);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Attendance data uploaded successfully!',
            'data' => []
        ], 200);
    }

    public function getAdminPunchLog(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please check Date and Employee!',
                'data' => $validateUser->errors()
            ], 409);
        }

        $employee_id = $request->employee_id ? $request->employee_id : 0;

        $check_start_date = $request->start_date ? Carbon::parse($request->start_date) : null;
        $check_end_date = $request->end_date ? Carbon::parse($request->end_date) : null;

        $employee = EmployeeInfo::where('id', $employee_id)->first();

        if(!$employee->finger_print_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, Update Finger Print ID!',
                'data' => $validateUser->errors()
            ], 409);
        }

        $finger_print_id = $employee->finger_print_id;

        if($check_start_date && $check_end_date){
            
            $attendance_list = AttendanceLog::select('*')
            ->whereBetween('log_date', [$check_start_date, $check_end_date])
            ->when($finger_print_id, function ($query) use ($finger_print_id){
                return $query->where('finger_print_id', $finger_print_id);
            })
            ->orderBy('id', "ASC")
            ->get();

            foreach ($attendance_list as $item) {
                $startTime = Carbon::createFromFormat('H:i:s', $item->start_time);
                $end_time = Carbon::createFromFormat('H:i:s', $item->end_time);

                $WorkingHour = Carbon::createFromFormat('H:i:s', $item->total_time);

                $thresholdTime = Carbon::createFromFormat('H:i:s', '08:00:00');
                $lateInTime = Carbon::createFromFormat('H:i:s', '09:00:00');
                $earlyOutTime = Carbon::createFromFormat('H:i:s', '17:00:00');

                // Total Hours
                if ($WorkingHour->lessThan($thresholdTime)) {
                    $item->has_working_hour_completed = false;
                } else {
                    $item->has_working_hour_completed = true;
                }

                // Late In 
                if ($startTime->greaterThan($lateInTime)) {
                    $item->late_in = true;
                } else {
                    $item->late_in = false;
                }

                // Early Out 
                if ($end_time->lessThan($earlyOutTime)) {
                    $item->early_out = true;
                } else {
                    $item->early_out = false;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Successful1',
                'data' => $attendance_list
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'No Data Found!',
            'data' => []
        ], 409);
    }

    public function getSelfPunchLog(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please check Date and Employee!',
                'data' => $validateUser->errors()
            ], 409);
        }
        $user_id = $request->user()->id;
        $employee = EmployeeInfo::where('user_id', $user_id)->first();

        $check_start_date = $request->start_date ? Carbon::parse($request->start_date) : null;
        $check_end_date = $request->end_date ? Carbon::parse($request->end_date) : null;

        if(!$employee->finger_print_id){
            return response()->json([
                'status' => false,
                'message' => 'Please, Update Finger Print ID!',
                'data' => $validateUser->errors()
            ], 409);
        }

        $finger_print_id = $employee->finger_print_id;

        if($check_start_date && $check_end_date){
            
            $attendance_list = AttendanceLog::select('*')
            ->whereBetween('log_date', [$check_start_date, $check_end_date])
            ->when($finger_print_id, function ($query) use ($finger_print_id){
                return $query->where('finger_print_id', $finger_print_id);
            })
            ->orderBy('id', "ASC")
            ->get();

            foreach ($attendance_list as $item) {
                $startTime = Carbon::createFromFormat('H:i:s', $item->start_time);
                $end_time = Carbon::createFromFormat('H:i:s', $item->end_time);

                $WorkingHour = Carbon::createFromFormat('H:i:s', $item->total_time);

                $thresholdTime = Carbon::createFromFormat('H:i:s', '08:00:00');
                $lateInTime = Carbon::createFromFormat('H:i:s', '09:00:00');
                $earlyOutTime = Carbon::createFromFormat('H:i:s', '17:00:00');

                // Total Hours
                if ($WorkingHour->lessThan($thresholdTime)) {
                    $item->has_working_hour_completed = false;
                } else {
                    $item->has_working_hour_completed = true;
                }

                // Late In 
                if ($startTime->greaterThan($lateInTime)) {
                    $item->late_in = true;
                } else {
                    $item->late_in = false;
                }

                // Early Out 
                if ($end_time->lessThan($earlyOutTime)) {
                    $item->early_out = true;
                } else {
                    $item->early_out = false;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Successful1',
                'data' => $attendance_list
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'No Data Found!',
            'data' => []
        ], 409);
    }
}
