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
}
