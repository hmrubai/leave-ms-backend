<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\DayType;
use App\Models\Calendar;
use Illuminate\Http\Request;
use App\Models\DayStatusSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function dayTypeList(Request $request)
    {
        $day_type_list = DayType::orderBy('title', 'ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $day_type_list
        ], 200);
    }

    public function dayStatusList(Request $request)
    {
        $day_status = DayStatusSetting::select(
            'day_status_settings.*', 
            'dt_1.title as saturday_status',
            'dt_1.day_short_code as saturday_day_short_code',
            'dt_2.title as sunday_status',
            'dt_2.day_short_code as sunday_day_short_code',
            'dt_3.title as monday_status',
            'dt_3.day_short_code as monday_day_short_code',
            'dt_4.title as tuesday_status',
            'dt_4.day_short_code as tuesday_day_short_code',
            'dt_5.title as wednesday_status',
            'dt_5.day_short_code as wednesday_day_short_code',
            'dt_6.title as thursday_status',
            'dt_6.day_short_code as thursday_day_short_code',
            'dt_7.title as friday_status',
            'dt_7.day_short_code as friday_day_short_code'
        )
        ->leftJoin('day_types as dt_1', 'dt_1.id', 'day_status_settings.saturday')
        ->leftJoin('day_types as dt_2', 'dt_2.id', 'day_status_settings.sunday')
        ->leftJoin('day_types as dt_3', 'dt_3.id', 'day_status_settings.monday')
        ->leftJoin('day_types as dt_4', 'dt_4.id', 'day_status_settings.tuesday')
        ->leftJoin('day_types as dt_5', 'dt_5.id', 'day_status_settings.wednesday')
        ->leftJoin('day_types as dt_6', 'dt_6.id', 'day_status_settings.thursday')
        ->leftJoin('day_types as dt_7', 'dt_7.id', 'day_status_settings.friday')
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $day_status
        ], 200);
    }

    public function updateDayStatusSetup(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'id' => 'required',
            'saturday' => 'required',
            'sunday' => 'required',
            'monday' => 'required',
            'tuesday' => 'required',
            'wednesday' => 'required',
            'thursday' => 'required',
            'friday' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        DayStatusSetting::where('id', $request->id)->update([
            'saturday' => $request->saturday,
            'sunday' =>  $request->sunday,
            'monday' => $request->monday,
            'tuesday' => $request->tuesday,
            'wednesday' => $request->wednesday,
            'thursday' => $request->thursday,
            'friday' => $request->friday
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Day Status has been updated successfully',
            'data' => []
        ], 200);
    }

    public function getCalendar(){
        $calendar_list = Calendar::select(
            'calendars.id',
            'calendars.date',
            'calendars.year',
            'calendars.day_title',
            'calendars.day_note',
            'calendars.month_in_number',
            'calendars.day_type_id',
            'calendars.is_active',
            'day_types.title as day_type_title',
            'day_types.day_short_code as day_type_short_code'
        )
        ->leftJoin('day_types', 'day_types.id', 'calendars.day_type_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $calendar_list
        ], 200);
    }

    public function updateCalendar(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'id' => 'required',
            'day_type_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        Calendar::where('id', $request->id)->update([
            'day_type_id' => $request->day_type_id,
            'day_note' =>  $request->day_note ?? $request->day_note
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Calendar has been updated successfully',
            'data' => []
        ], 200);
    }
}
