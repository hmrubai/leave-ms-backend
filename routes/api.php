<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MasterSettingsController;


Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
// Route::get('country-list', [MasterSettingsController::class, 'countryList']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('profile-update', [AuthController::class, 'updateUser']);
    Route::get('admin/expert-list', [AuthController::class, 'getAdminExpertList']);
    Route::post('admin/save-update-expert', [AuthController::class, 'saveOrUpdateUser']);
    Route::post('delete-account', [AuthController::class, 'deleteUserAccount']);

    //Master Settings
    // Route::get('syllabus-list', [MasterSettingsController::class, 'packageTypeList']);
    // Route::get('grade-list', [MasterSettingsController::class, 'gradeList']);
    // Route::get('category-list', [MasterSettingsController::class, 'categoryList']);
    // Route::post('admin/syllabus-save-or-update', [MasterSettingsController::class, 'saveOrUpdatePackageType']);
    
});

// Route::post('trancate-data', [MasterSettingsController::class, 'trancateData']);

Route::any('{url}', function(){
    return response()->json([
        'status' => false,
        'message' => 'Route Not Found!',
        'data' => []
    ], 404);
})->where('url', '.*');
