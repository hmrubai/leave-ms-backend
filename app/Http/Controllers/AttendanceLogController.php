<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\Calendar;
use App\Models\LeaveApplications;
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
        $finger_print_id = $employee->finger_print_id;

        $dates = [];

        if ($check_start_date && $check_end_date) {
            while ($check_start_date->lte($check_end_date)) {
                $dates[] = $check_start_date->toDateString();
                $check_start_date->addDay(); // Move to the next day
            }
        }

        if($check_start_date && $check_end_date){

            $lateInTime = Carbon::createFromFormat('H:i:s', '09:00:00');
            $earlyOutTime = Carbon::createFromFormat('H:i:s', '17:00:00');

            if($request->start_grace_time != null){
                $lateInTime->add(CarbonInterval::createFromFormat('H:i:s', $request->start_grace_time));
            }
            
            if($request->end_grace_time != null){
                $interval = CarbonInterval::createFromFormat('H:i:s', $request->end_grace_time)->invert();
                $earlyOutTime->add($interval);
            }

            $total_working_hr = "00:00:00";
            $total_late = $total_early_out = $total_early_out = $total_al = $total_cl = $total_sl = $total_ho = $total_pl = $total_ml = $total_ll = $total_wl = 0;

            $response_date = [];

            $leave_types = ["CL", "AL", "SL", "ML", "HO", "PL", "LL", "WL"];
            $total_leaves = [
                "CL" => 0, "AL" => 0, "SL" => 0, "ML" => 0, 
                "HO" => 0, "PL" => 0, "LL" => 0, "WL" => 0
            ];

            $total_working_seconds = 0;

            foreach ($dates as $log_date) {
                $attendance_data = AttendanceLog::select('*')
                    ->where('log_date', $log_date)
                    ->when($finger_print_id, function ($query) use ($finger_print_id){
                        return $query->where('finger_print_id', $finger_print_id);
                    })
                    ->first();

                if ($attendance_data && $attendance_data->total_time) {
                    // Convert HH:MM:SS to total seconds
                    list($hours, $minutes, $seconds) = explode(":", $attendance_data->total_time);
                    $total_working_seconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }

                $day_code = "A";
                $is_leave = false;
                $leave_count = 0;
                $is_half_leave = false;

                $isOnLeave = LeaveApplications::select(
                        'leave_policies.leave_short_code',
                        'leave_applications.total_applied_days',
                        'leave_applications.is_half_day',
                    )
                    ->where('leave_applications.employee_id', $employee_id)
                    ->where('leave_applications.leave_status', 'Approved')
                    ->whereDate('leave_applications.start_date', '<=', $log_date)
                    ->whereDate('leave_applications.end_date', '>=', $log_date)
                    ->leftJoin('leave_policies', 'leave_policies.id', 'leave_applications.leave_policy_id')
                    ->first();
                
                if($isOnLeave){
                    $day_code = $isOnLeave->leave_short_code;

                    if (in_array($isOnLeave->leave_short_code, $leave_types)) {
                        $total_leaves[$isOnLeave->leave_short_code] += $isOnLeave->is_half_day ? 0.5 : 1;
                        $leave_count = $leave_count + $isOnLeave->is_half_day ? 0.5 : 1;
                    }
                    
                    $is_leave = true;
                    if($attendance_data){
                        $is_half_leave = true;
                        $day_code = $isOnLeave->leave_short_code;
                    }
                }else{
                    if($attendance_data){
                        $day_code = "P";
                    }else{
                        $calendar_days = Calendar::select('calendars.day_title as day_name', 'day_types.title as day_title', 'day_types.day_short_code')->where('calendars.date', $log_date)
                        ->leftJoin('day_types', 'day_types.id', 'calendars.day_type_id')
                        ->first();
                        $day_code = $calendar_days->day_short_code;
                    }
                }

                $late_in = false;
                $early_out = false;
                $has_working_hour_completed = true;
                if($attendance_data){
                    $start_time = Carbon::createFromFormat('H:i:s', $attendance_data->start_time);
                    $end_time = Carbon::createFromFormat('H:i:s', $attendance_data->end_time);

                    $totalSeconds = $start_time->diffInSeconds($end_time);
                    $totalHours = $totalSeconds / 3600;

                    // Total Hours Check
                    $thresholdHours = 8; // 8 hours threshold
                    $has_working_hour_completed = $totalHours >= $thresholdHours;

                    // Late In 
                    if ($start_time->greaterThan($lateInTime)) {
                        $late_in = true;
                        $total_late++;
                    }

                    // Early Out 
                    if ($end_time->lessThan($earlyOutTime)) {
                        $early_out = true;
                        $total_early_out++;
                    }

                    array_push($response_date, [
                        'log_date' => $log_date, 
                        'start_time' => $attendance_data->start_time, 
                        'end_time' => $attendance_data->end_time, 
                        'total_time' => $attendance_data->total_time, 
                        'late_in' => $late_in, 
                        'early_out' => $early_out, 
                        'has_working_hour_completed' => $has_working_hour_completed, 
                        'punch_time' => $attendance_data->punch_log, 
                        'day_code' => $day_code, 
                        'is_leave' => $is_leave, 
                        'is_half_leave' => $is_half_leave, 
                        'is_leave' => $is_leave, 
                    ]);
                }elseif($isOnLeave && !$attendance_data){
                    array_push($response_date, [
                        'log_date' => $log_date, 
                        'start_time' => "00:00:00", 
                        'end_time' => "00:00:00", 
                        'total_time' => "00:00:00",
                        'late_in' => false, 
                        'early_out' => false, 
                        'has_working_hour_completed' => true, 
                        'punch_time' => null, 
                        'day_code' => $day_code, 
                        'is_leave' => $is_leave, 
                        'is_half_leave' => $is_half_leave, 
                        'is_leave' => $is_leave, 
                    ]);
                }elseif(!$isOnLeave && !$attendance_data){
                    if($day_code == "P"){
                        $day_code = "A";
                    }
                    array_push($response_date, [
                        'log_date' => $log_date, 
                        'start_time' => "00:00:00", 
                        'end_time' => "00:00:00", 
                        'total_time' => "00:00:00",
                        'late_in' => false, 
                        'early_out' => false, 
                        'has_working_hour_completed' => true, 
                        'punch_time' => null, 
                        'day_code' => $day_code, 
                        'is_leave' => $is_leave, 
                        'is_half_leave' => $is_half_leave, 
                        'is_leave' => $is_leave, 
                    ]);
                }
            }
            // Convert total seconds back to HH:MM:SS format
            $total_hours = floor($total_working_seconds / 3600);
            $total_minutes = floor(($total_working_seconds % 3600) / 60);
            $total_seconds = $total_working_seconds % 60;

            $total_working_hr = sprintf("%02d:%02d:%02d", $total_hours, $total_minutes, $total_seconds);

            $working_days = Calendar::where('calendars.day_type_id', 1)->whereBetween('date', [$request->start_date, $request->end_date])->get()->count();

            // Expected working hours (each day is 8 hours)
            $expected_working_seconds = $working_days * 8 * 3600;

            $ew_hours = floor($expected_working_seconds / 3600);
            $ew_minutes = floor(($expected_working_seconds % 3600) / 60);
            $ew_seconds = $expected_working_seconds % 60;

            $expected_working_hour = sprintf("%02d:%02d:%02d", $ew_hours, $ew_minutes, $ew_seconds);

            // Calculate overtime (only if actual time > expected time)
            $overtime_seconds = max(0, $total_working_seconds - $expected_working_seconds);

            $overtime_hours = floor($overtime_seconds / 3600);
            $overtime_minutes = floor(($overtime_seconds % 3600) / 60);
            $overtime_seconds = $overtime_seconds % 60;

            $total_over_time = sprintf("%02d:%02d:%02d", $overtime_hours, $overtime_minutes, $overtime_seconds);
        }

        $counts = array_count_values(array_column($response_date, 'day_code'));
        
        $leave_count_all = ($counts['SL'] ?? 0) + ($counts['CL'] ?? 0) + ($counts['AL'] ?? 0) + ($counts['ML'] ?? 0) + ($counts['HO'] ?? 0) + ($counts['PL'] ?? 0) + ($counts['LL'] ?? 0) + ($counts['WL'] ?? 0);
        $total_weekend_holiday = ($counts['H'] ?? 0) + ($counts['W'] ?? 0);
        $total_present = ($counts['P'] ?? 0);
        $total_absent = ($counts['A'] ?? 0);

        return response()->json([
            'status' => false,
            'message' => 'Please check Date and Employee!',
            'data' => [
                "record_data" => $response_date, 
                "status_count" => $counts,
                "leave_count" => $leave_count_all, 
                "total_weekend_holiday" => $total_weekend_holiday, 
                "expected_working_hour" => $expected_working_hour, 
                "total_working_hr" => $total_working_hr, 
                "total_over_time" => $total_over_time, 
                "total_present" => $total_present, 
                "total_absent" => $total_absent, 
                "summary" => $total_leaves, 
                "total_working_days" => sizeof($dates), 
                "total_late" => $total_late, 
                "total_early_out" => $total_early_out
                ]
        ], 200);
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
