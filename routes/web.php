<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Leave Management System of BacBon Limited.'
    ], 200);
});
